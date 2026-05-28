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
date_default_timezone_set('Singapore');
set_time_limit(0);
$db = Factory::getDbo();

/*
|--------------------------------------------------------------------------
| Get RMA Items
|--------------------------------------------------------------------------
*/

$query = $db->getQuery(true)
    ->select([
        'r.*',
        'p.product_no'
    ])
    ->from($db->quoteName('#__at_rma_items', 'r'))
    ->leftJoin(
        $db->quoteName('#__at_products', 'p')
        . ' ON p.id = r.product_id'
    )
    ->where('DATE(r.created_date) = CURDATE()');

$db->setQuery($query);

$rma_items = $db->loadObjectList();

/*
|--------------------------------------------------------------------------
| Build Array
|--------------------------------------------------------------------------
*/

$listarray = [];

$listarray[] = [
    "RMA Nbr",
    "Requested S/N",
    "Customer",
    "Ship-To",
    "Order Date",
    "Item Nbr",
    "Quantity",
    "Problem",
    "Warranty"
];

if (!empty($rma_items)) {

    foreach ($rma_items as $rma_item) {

        $rma1 = $rma_item->rmacode ?? '';
        $rma2 = $rma_item->customer_id ?? '';
        $rma3 = $rma_item->customer_id ?? '';
        $rma4 = '';

        if (!empty($rma_item->created_date)) {

            $rma4 = date(
                "d-m-Y",
                strtotime($rma_item->created_date)
            );
        }

        $rma5 = $rma_item->product_no ?? '';
        $rma6 = 1;
        $rma7 = $rma_item->description ?? '';
        $rma8 = $rma_item->warranty_status ?? '';
        $rma9 = $rma_item->requested_sn ?? '';

        $itm = [
            $rma1,
            $rma9,
            $rma2,
            $rma3,
            $rma4,
            $rma5,
            $rma6,
            $rma7,
            $rma8
        ];

        $listarray[] = $itm;
    }
}

/*
|--------------------------------------------------------------------------
| Build Email Body
|--------------------------------------------------------------------------
*/

$body = '';

$body .= '<html><body>';

$body .= '<div style="margin-bottom:20px;">';
$body .= 'Today RMA Creation : ' . date("d-m-Y");
$body .= '</div>';

$body .= '<div style="margin-bottom:20px;">';
$body .= 'Total RMA Items : <strong>' . (count($listarray) - 1) . '</strong>';
$body .= '</div>';

$body .= '<table border="1" cellpadding="5" cellspacing="0">';

/*
|--------------------------------------------------------------------------
| Table Header
|--------------------------------------------------------------------------
*/

if (!empty($listarray[0])) {

    $body .= '<tr>';

    foreach ($listarray[0] as $tmp) {

        $body .= '<th>' . htmlspecialchars($tmp) . '</th>';
    }

    $body .= '</tr>';
}

/*
|--------------------------------------------------------------------------
| Table Rows
|--------------------------------------------------------------------------
*/

unset($listarray[0]);

foreach ($listarray as $data) {

    $body .= '<tr>';

    foreach ($data as $tmp) {

        $body .= '<td>' . htmlspecialchars($tmp) . '</td>';
    }

    $body .= '</tr>';
}

$body .= '</table>';

$body .= '</body></html>';

echo $body;

/*
|--------------------------------------------------------------------------
| Send Email
|--------------------------------------------------------------------------
*/

try {

    $mail = Factory::getMailer();

    $mail->isHtml(true);

    $mail->addRecipient([
        'ata-webadmin@alliedtelesis.com.sg',
        'Amy.Tchin@alliedtelesis.com.sg'
    ]);

    $mail->addBcc([
        'meibin20032002@gmail.com'
    ]);

    $mail->setSubject(
        'Allied Telesis : Today RMA Creation was Running on 10:00PM daily'
    );

    $mail->setBody($body);

    $mail->send();

} catch (Exception $e) {

    echo '<pre>';
    echo 'Mail Error: ' . $e->getMessage();
    echo '</pre>';
}