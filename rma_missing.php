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
use Joomla\CMS\Mail\MailHelper;


date_default_timezone_set('Singapore');
set_time_limit(0);
$db  = Factory::getDbo();

/* Export File */
$exportPath = JPATH_BASE . '/atrmaftp';

if (!is_dir($exportPath)) {
    mkdir($exportPath, 0755, true);
}

$fileName = 'RMA_' . date('dmYHi') . '.csv';
$filePath = $exportPath . '/' . $fileName;

/* Open CSV */
$fp = fopen($filePath, 'w');

if (!$fp) {
    die('Unable to create CSV file');
}

/* CSV Header */
$headers = [
    'RMA Nbr',
    'Requested S/N',
    'Customer',
    'Ship-To',
    'Order Date',
    'Item Nbr',
    'Quantity',
    'Problem',
    'Warranty'
];

fputcsv($fp, $headers);

/* Get RMA Items */
$query = $db->getQuery(true)
    ->select([
        'r.*',
        'p.product_no'
    ])
    ->from('#__at_rma_items AS r')
    ->leftJoin('#__at_products AS p ON p.id = r.product_id')
    ->where("r.created_date >= '2017-10-04'")
    ->where("r.created_date < '2017-10-07'")
    ->order('r.created_date ASC');

$db->setQuery($query);

$rmaItems = $db->loadObjectList();

$totalExported = 0;
$tableRows     = '';

if (!empty($rmaItems)) {

    foreach ($rmaItems as $item) {

        $warrantyStatus =
            ($item->warranty_status === 'DOA')
            ? 'IN'
            : $item->warranty_status;

        $row = [
            $item->rmacode,
            $item->requested_sn,
            $item->customer_id,
            $item->customer_id,
            date('d-m-Y', strtotime($item->created_date)),
            $item->product_no,
            1,
            $item->description,
            $warrantyStatus
        ];

        /* Write CSV */
        fputcsv($fp, $row);

        /* Email Table */
        $tableRows .= '<tr>';

        foreach ($row as $col) {
            $tableRows .= '<td>'
                . htmlspecialchars((string) $col)
                . '</td>';
        }

        $tableRows .= '</tr>';

        $totalExported++;
    }
}

fclose($fp);

/* Email Body */
$body = '
<html>
<body>

<h3>RMA Export Completed</h3>
<p>Date: <strong>' . date('d-m-Y H:i:s') . '</strong></p>
<p>Total Exported: <strong>' . $totalExported . '</strong></p>

<table border="1" cellpadding="5" cellspacing="0">
    <tr>';

foreach ($headers as $header) {

    $body .= '<th>'
        . htmlspecialchars($header)
        . '</th>';
}

$body .= '
    </tr>
    ' . $tableRows . '
</table>

</body>
</html>';

/* Output */
echo $body;

/* Send Email */
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
        'Allied Telesis : RMA Export Completed'
    );

    $mail->setBody($body);

    /* Attach CSV */
    if (file_exists($filePath)) {
        $mail->addAttachment($filePath);
    }

    $mail->send();

    echo '<p><strong>Email Sent Successfully</strong></p>';

} catch (Exception $e) {

    echo '<p><strong>Email Error:</strong> '
        . htmlspecialchars($e->getMessage())
        . '</p>';
}