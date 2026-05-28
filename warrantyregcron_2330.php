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
use Joomla\CMS\User\User;
use Joomla\CMS\User\UserHelper;

date_default_timezone_set('Singapore');
set_time_limit(0);
$db = Factory::getDbo();

$image_file_path = JPATH_SITE . '/atelftp';

if (!is_dir($image_file_path)) {
    die("Wrong path: {$image_file_path}");
}

$d = dir($image_file_path);

$error_row = [];
$total_items = 0;
$total_item_invoice_updated = 0;
$total_item_not_inserted = 0;

$customer_row = [];
$total_customer_created = 0;

while (false !== ($entry = $d->read())) {

    if ($entry !== '.' && $entry !== '..' && !is_dir($image_file_path . '/' . $entry)) {

        // Only process isbdata.csv
        if (!preg_match('/^isbdata\.csv$/i', $entry)) {
            continue;
        }

        $file = $image_file_path . '/' . $entry;

        if (($handle = fopen($file, "r")) !== false) {

            $line = fgetcsv($handle, 4096, ",");

            if ($line === false || count($line) !== 8) {
                fclose($handle);
                continue;
            }

            // Check existing warranty register
            $query = $db->getQuery(true)
                ->select($db->quoteName('id'))
                ->from($db->quoteName('#__at_warranty_register'))
                ->where($db->quoteName('first_name') . ' = ' . $db->quote($entry));

            $db->setQuery($query);

            $warranty_id = $db->loadResult();

            if (!$warranty_id) {

                $query = $db->getQuery(true)
                    ->insert($db->quoteName('#__at_warranty_register'))
                    ->columns($db->quoteName(['first_name']))
                    ->values($db->quote($entry));

                $db->setQuery($query);
                $db->execute();

                $warranty_id = $db->insertid();
            }

            while (($data = fgetcsv($handle, 4096, ",")) !== false) {

                if (count($data) < 8) {
                    continue;
                }

                /*
                 * 0 => Customer No
                 * 1 => PO No.
                 * 2 => SO No.
                 * 3 => Invoice No
                 * 4 => Part No
                 * 5 => Model No
                 * 6 => Serial No
                 * 7 => Ship Date
                 */

                $customer_id = trim($data[0]);
                $po_no = trim($data[1]);
                $so_no = trim($data[2]);
                $invoice_no = trim($data[3]);

                $part_no = trim($data[4]);
                $model_no = trim($data[5]);

                $serial_no = trim($data[6]);
                $pdate = trim($data[7]);

                // Product lookup
                $query = $db->getQuery(true)
                    ->select([
                        $db->quoteName('id'),
                        $db->quoteName('warranty')
                    ])
                    ->from($db->quoteName('#__at_products'))
                    ->where($db->quoteName('product_no') . ' = ' . $db->quote($part_no));

                $db->setQuery($query);

                $tmpproducts = $db->loadObject();

                $product_id = $tmpproducts->id ?? 0;
                $warranty_month = $tmpproducts->warranty ?? 0;

                // Product not found
                if (!$product_id) {

                    $error_text = 'Product not found';

                    $error_row[] =
                        $customer_id . "|" .
                        $po_no . "|" .
                        $so_no . "|" .
                        $invoice_no . "|" .
                        $part_no . "|" .
                        $model_no . "|" .
                        $serial_no . "|" .
                        $pdate . "|" .
                        $error_text;

                    $total_item_not_inserted++;

                    continue;
                }

                // Date processing
                $tmp = explode('/', $pdate);

                if (count($tmp) !== 3) {

                    $error_text = 'Invalid Purchase Date Format';

                    $error_row[] =
                        $customer_id . "|" .
                        $po_no . "|" .
                        $so_no . "|" .
                        $invoice_no . "|" .
                        $part_no . "|" .
                        $model_no . "|" .
                        $serial_no . "|" .
                        $pdate . "|" .
                        $error_text;

                    $total_item_not_inserted++;

                    continue;
                }

                $day = (int)$tmp[0] - 1;
                $month = (int)$tmp[1] + (int)$warranty_month;
                $year = (int)$tmp[2];

                $purchase_date = date("Y-m-d", mktime(0, 0, 0, (int)$tmp[1], (int)$tmp[0], (int)$tmp[2]));
                $expired_date = date("Y-m-d", mktime(0, 0, 0, $month, $day, $year));
                $extended_warranty = 3;
                $extended_expired_date = date("Y-m-d", mktime(0, 0, 0, ($month + $extended_warranty), $day, $year));

                // Check customer
                // NOTE:
                // customer_id column may not exist in Joomla 5 users table
                // Using username instead

                $query = $db->getQuery(true)
                    ->select($db->quoteName('id'))
                    ->from($db->quoteName('#__users'))
                    ->where($db->quoteName('username') . ' = ' . $db->quote($customer_id));

                $db->setQuery($query);

                $customer_exist = $db->loadResult();

                // Create user if not exists
                if (!$customer_exist) {

                    $email = $customer_id . '@atg.lc';

                    $randomPassword = UserHelper::genRandomPassword();

                    $userData = [
                        'name' => $customer_id,
                        'username' => $customer_id,
                        'password' => $randomPassword,
                        'password2' => $randomPassword,
                        'email' => $email,
                        'block' => 0,
                        'groups' => [2]
                    ];

                    $user = new User;

                    if (!$user->bind($userData)) {

                        $error_text = 'User bind failed';

                        $error_row[] =
                            $customer_id . "|" .
                            $po_no . "|" .
                            $so_no . "|" .
                            $invoice_no . "|" .
                            $part_no . "|" .
                            $model_no . "|" .
                            $serial_no . "|" .
                            $pdate . "|" .
                            $error_text;

                        continue;
                    }

                    if (!$user->save()) {

                        $error_text = 'User save failed';

                        $error_row[] =
                            $customer_id . "|" .
                            $po_no . "|" .
                            $so_no . "|" .
                            $invoice_no . "|" .
                            $part_no . "|" .
                            $model_no . "|" .
                            $serial_no . "|" .
                            $pdate . "|" .
                            $error_text;

                        continue;
                    }

                    $customer_row[] = $customer_id;

                    $total_customer_created++;

                    $customer_exist = $user->id;
                }

                // Check existing warranty item
                $query = $db->getQuery(true)
                    ->select($db->quoteName('id'))
                    ->from($db->quoteName('#__at_warranty_items'))
                    ->where(
                        '(' .
                        $db->quoteName('serial_no') . ' = ' . $db->quote($serial_no)
                        . ' OR ' .
                        $db->quoteName('serial_no_2') . ' = ' . $db->quote($serial_no)
                        . ')'
                    )
                    ->where(
                        $db->quoteName('so_no') . ' = ' . $db->quote($so_no)
                    );

                $db->setQuery($query);

                $id_exist = $db->loadResult();

                // Insert warranty item
                if (!$id_exist) {

                    $columns = [
                        'warranty_id',
                        'product_id',
                        'serial_no',
                        'purchase_date',
                        'expired_date',
                        'customer_id',
                        'po_no',
                        'so_no',
                        'invoice_no',
                        'extended_warranty',
                        'extended_expired_date'
                    ];

                    $values = [
                        $db->quote($warranty_id),
                        $db->quote($product_id),
                        $db->quote($serial_no),
                        $db->quote($purchase_date),
                        $db->quote($expired_date),
                        $db->quote($customer_id),
                        $db->quote($po_no),
                        $db->quote($so_no),
                        $db->quote($invoice_no),
                        $db->quote($extended_warranty),
                        $db->quote($extended_expired_date)
                    ];

                    $query = $db->getQuery(true)
                        ->insert($db->quoteName('#__at_warranty_items'))
                        ->columns($db->quoteName($columns))
                        ->values(implode(',', $values));

                    $db->setQuery($query);
                    $db->execute();

                    $total_items++;

                } else {

                    $error_text = 'SO Number and Serial Number exist';

                    $error_row[] =
                        $customer_id . "|" .
                        $po_no . "|" .
                        $so_no . "|" .
                        $invoice_no . "|" .
                        $part_no . "|" .
                        $model_no . "|" .
                        $serial_no . "|" .
                        $pdate . "|" .
                        $error_text;

                    $total_item_not_inserted++;
                }
            }

            fclose($handle);

            // Optional:
            // unlink($file);

            // Build email report
            $body = '';

            $body .= '<html><body>';

            $body .= '<div style="margin-bottom:20px;">';
            $body .= 'Warranty Registration Automation Script was Running';
            $body .= '</div>';

            if ($total_customer_created > 0) {

                $body .= '<div style="margin-bottom:20px">';
                $body .= 'Customer created are <strong>';
                $body .= implode(',', $customer_row);
                $body .= '</strong></div>';
            }

            if ($error_row) {

                $body .= '<div style="margin-bottom:20px;">';
                $body .= 'Oops, there were some errors during import.';
                $body .= '</div>';

                $body .= '<div style="margin-bottom:5px;">';
                $body .= 'Total Items that were imported: <strong>';
                $body .= $total_items;
                $body .= '</strong></div>';

                $body .= '<div style="margin-bottom:20px;">';
                $body .= 'Total Items that were NOT imported: <strong>';
                $body .= $total_item_not_inserted;
                $body .= '</strong></div>';

                $body .= '<table border="1" cellpadding="5" cellspacing="0">';

                $body .= '<tr>';
                $body .= '<th>Customer ID</th>';
                $body .= '<th>PO No.</th>';
                $body .= '<th>SO No.</th>';
                $body .= '<th>Invoice No.</th>';
                $body .= '<th>Part No.</th>';
                $body .= '<th>Model No.</th>';
                $body .= '<th>Serial No.</th>';
                $body .= '<th>Purchase / Ship Date</th>';
                $body .= '<th>Note</th>';
                $body .= '</tr>';

                foreach ($error_row as $erw) {

                    $w = explode('|', $erw);

                    $body .= '<tr>';

                    foreach ($w as $v) {
                        $body .= '<td>' . htmlspecialchars($v) . '</td>';
                    }

                    $body .= '</tr>';
                }

                $body .= '</table>';

            } else {

                $body .= '<div style="margin-bottom:20px">';
                $body .= 'All Items were imported. Total Items imported were <strong>';
                $body .= $total_items;
                $body .= '</strong></div>';
            }

            $body .= '</body></html>';

            echo $body;

            // Send email
            try {

                $mail = Factory::getMailer();

                $mail->isHtml(true);

                $mail->addRecipient([
                    'ata-webadmin@alliedtelesis.com.sg'
                ]);

                $mail->addBcc([
                    'meibin20032002@gmail.com'
                ]);

                $mail->setSubject(
                    'Allied Telesis : Warranty Registration Daily Automation was Running'
                );

                $mail->setBody($body);

                $mail->send();

            } catch (Exception $e) {

                echo '<pre>';
                echo 'Mail Error: ' . $e->getMessage();
                echo '</pre>';
            }
        }
    }
}

$d->close();