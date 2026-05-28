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
use Joomla\CMS\User\UserHelper;

$db = Factory::getDbo();

$csv_path = JPATH_SITE . '/atelftp';

if (!is_dir($csv_path)) {
    die('CSV folder not found: ' . $csv_path);
}

$d = dir($csv_path) or die("Wrong path: $csv_path");

$totalImported = 0;
$totalSkipped  = 0;
$totalError    = 0;

while (false !== ($entry = $d->read())) {

    // skip folder
    if ($entry == '.' || $entry == '..') {
        continue;
    }

    $file = $csv_path . '/' . $entry;

    if (is_dir($file)) {
        continue;
    }

    // only customer.csv
    if (!preg_match('/^customer\.csv$/i', $entry)) {
        continue;
    }

    echo "<hr>";
    echo "Processing: {$entry}<br>";

    $handle = fopen($file, "r");

    if ($handle === false) {
        echo "Cannot open file<br>";
        continue;
    }

    // read header
    $header = fgetcsv($handle, 4096, ",");

    if (!$header || count($header) < 2) {
        echo "Invalid CSV header<br>";
        fclose($handle);
        continue;
    }

    $lineNo = 1;

    $db->transactionStart();

    try {

        while (($data = fgetcsv($handle, 4096, ",")) !== false) {

            $lineNo++;

            // validate column
            if (count($data) < 2) {
                $totalSkipped++;
                continue;
            }

            $customer_id = trim($data[0]);
            $name        = trim($data[1]);

            // empty data
            if (empty($customer_id) || empty($name)) {
                $totalSkipped++;
                continue;
            }

            // check existing user
            $query = $db->getQuery(true)
                ->select($db->quoteName('id'))
                ->from($db->quoteName('#__users'))
                ->where(
                    $db->quoteName('customer_id') . ' = ' .
                    $db->quote($customer_id)
                );

            $db->setQuery($query);

            $existUserId = $db->loadResult();

            if ($existUserId) {

                echo "Skip existing customer_id: {$customer_id}<br>";

                $totalSkipped++;
                continue;
            }

            // hashed password
            $password = UserHelper::hashPassword($customer_id);

            // insert user
            $columns = [
                'name',
                'username',
                'password',
                'email',
                'block',
                'sendEmail',
                'registerDate',
                'lastvisitDate',
                'activation',
                'params',
                'requireReset',
                'resetCount',
                'otpKey',
                'otep',
                'customer_id'
            ];

            $values = [
                $db->quote($name),
                $db->quote($customer_id),
                $db->quote($password),
                $db->quote($customer_id . '@dummy.local'),
                0,
                0,
                $db->quote(date('Y-m-d H:i:s')),
                $db->quote($db->getNullDate()),
                $db->quote(''),
                $db->quote('{}'),
                0,
                0,
                $db->quote(''),
                $db->quote(''),
                $db->quote($customer_id)
            ];

            $query = $db->getQuery(true)
                ->insert($db->quoteName('#__users'))
                ->columns($db->quoteName($columns))
                ->values(implode(',', $values));

            $db->setQuery($query);
            $db->execute();

            $user_id = $db->insertid();

            if (!$user_id) {
                throw new Exception("Cannot create user at line {$lineNo}");
            }

            // assign user group
            $query = $db->getQuery(true)
                ->insert($db->quoteName('#__user_usergroup_map'))
                ->columns([
                    $db->quoteName('user_id'),
                    $db->quoteName('group_id')
                ])
                ->values((int) $user_id . ',24');

            $db->setQuery($query);
            $db->execute();

            echo "Imported: {$customer_id}<br>";

            $totalImported++;
        }

        $db->transactionCommit();

    } catch (Exception $e) {

        $db->transactionRollback();

        echo "<span style='color:red'>";
        echo "ERROR: " . $e->getMessage();
        echo "</span><br>";

        $totalError++;
    }

    fclose($handle);

    // remove file after import
    // unlink($file);
}

$d->close();

echo "<hr>";
echo "<h3>IMPORT SUMMARY</h3>";

echo "Imported: {$totalImported}<br>";
echo "Skipped: {$totalSkipped}<br>";
echo "Errors: {$totalError}<br>";

echo "<br><strong>Import completed</strong>";