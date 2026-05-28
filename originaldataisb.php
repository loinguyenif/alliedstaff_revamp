<?php
define('_JEXEC', 1);
define('JPATH_BASE', __DIR__);

require_once JPATH_BASE . '/includes/defines.php';
require_once JPATH_BASE . '/includes/framework.php';

// Boot the DI container
$container = \Joomla\CMS\Factory::getContainer();
$container->alias('session.web', 'session.web.site')
    ->alias('session', 'session.web.site')
    ->alias('JSession', 'session.web.site')
    ->alias(\Joomla\CMS\Session\Session::class, 'session.web.site')
    ->alias(\Joomla\Session\Session::class, 'session.web.site')
    ->alias(\Joomla\Session\SessionInterface::class, 'session.web.site');

// Instantiate the application.
$app = $container->get(\Joomla\CMS\Application\SiteApplication::class);
\Joomla\CMS\Factory::$application = $app;

use Joomla\CMS\Factory;
set_time_limit(0);
$db  = Factory::getDbo();

/* Security */
$user = $_GET['user'] ?? '';
$pass = $_GET['pass'] ?? '';

if ($user !== 'admin' || $pass !== '12345') {
    die('Access Denied');
}

/* CSV Folder */
$path = JPATH_SITE . '/atelftp';

if (!is_dir($path)) {
    die("Wrong path: {$path}");
}

$totalImported = 0;
$totalSkipped  = 0;
$errors        = [];

/* Read Folder */
$dir = dir($path);

while (($file = $dir->read()) !== false) {

    if (
        $file === '.'
        || $file === '..'
        || is_dir($path . '/' . $file)
    ) {
        continue;
    }

    /* Process only isb.csv */
    if (!preg_match('/^isb\.csv$/i', $file)) {
        continue;
    }

    /* Clear old data */
    $db->setQuery('TRUNCATE TABLE ' . $db->quoteName('#__at_warranty_register'));
    $db->execute();

    $db->setQuery('TRUNCATE TABLE ' . $db->quoteName('#__at_warranty_items'));
    $db->execute();

    $handle = fopen($path . '/' . $file, 'r');

    if (!$handle) {
        continue;
    }

    /* Skip header */
    $header = fgetcsv($handle, 4096, ',');

    if (count($header) != 8) {
        fclose($handle);
        continue;
    }

    /* Create register */
    $query = $db->getQuery(true)
        ->insert('#__at_warranty_register')
        ->columns('first_name')
        ->values($db->quote($file));

    $db->setQuery($query);
    $db->execute();

    $warrantyId = $db->insertid();

    /* Read CSV */
    while (($row = fgetcsv($handle, 4096, ',')) !== false) {

        if (count($row) < 8) {
            $totalSkipped++;
            continue;
        }

        [
            $customerId,
            $poNo,
            $soNo,
            $invoiceNo,
            $partNo,
            $modelNo,
            $serialNo,
            $pdate
        ] = array_map('trim', $row);

        /* Get Product */
        $query = $db->getQuery(true)
            ->select(['id', 'warranty'])
            ->from('#__at_products')
            ->where('product_no = ' . $db->quote($partNo))
            ->where('model_no = ' . $db->quote($modelNo));

        $db->setQuery($query);

        $product = $db->loadObject();

        if (!$product) {

            $errors[] = "Product not found: {$partNo}";

            $totalSkipped++;

            continue;
        }

        /* Check duplicate */
        $query = $db->getQuery(true)
            ->select('id')
            ->from('#__at_warranty_items')
            ->where('serial_no = ' . $db->quote($serialNo))
            ->where('so_no = ' . $db->quote($soNo));

        $db->setQuery($query);

        if ($db->loadResult()) {

            $errors[] = "Duplicate: {$serialNo}";

            $totalSkipped++;

            continue;
        }

        /* Build dates */
        $date = explode('/', $pdate);

        if (count($date) != 3) {

            $errors[] = "Invalid date: {$serialNo}";

            $totalSkipped++;

            continue;
        }

        [$day, $month, $year] = array_map('intval', $date);

        $purchaseDate = date(
            'Y-m-d',
            mktime(0, 0, 0, $month, $day, $year)
        );

        $expiredDate = date(
            'Y-m-d',
            mktime(
                0,
                0,
                0,
                $month + (int) $product->warranty,
                $day - 1,
                $year
            )
        );

        /* Insert warranty item */
        $columns = [
            'warranty_id',
            'product_id',
            'serial_no',
            'purchase_date',
            'expired_date',
            'customer_id',
            'po_no',
            'so_no',
            'invoice_no'
        ];

        $values = [
            $db->quote($warrantyId),
            $db->quote($product->id),
            $db->quote($serialNo),
            $db->quote($purchaseDate),
            $db->quote($expiredDate),
            $db->quote($customerId),
            $db->quote($poNo),
            $db->quote($soNo),
            $db->quote($invoiceNo)
        ];

        $query = $db->getQuery(true)
            ->insert('#__at_warranty_items')
            ->columns($db->quoteName($columns))
            ->values(implode(',', $values));

        try {

            $db->setQuery($query);
            $db->execute();

            $totalImported++;

        } catch (Exception $e) {

            $errors[] = $serialNo . ' : ' . $e->getMessage();

            $totalSkipped++;
        }
    }

    fclose($handle);
}

$dir->close();

/* Result */
echo '<h2>Warranty Import Completed</h2>';

echo '<p>Total Imported: <strong>' . $totalImported . '</strong></p>';

echo '<p>Total Skipped: <strong>' . $totalSkipped . '</strong></p>';

if (!empty($errors)) {

    echo '<h3>Errors</h3><ul>';

    foreach ($errors as $error) {
        echo '<li>' . htmlspecialchars($error) . '</li>';
    }

    echo '</ul>';
}