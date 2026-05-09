<?php

define('_JEXEC', 1);
define('JPATH_BASE', dirname(__FILE__));

use \Joomla\CMS\Factory;
//setlocale(LC_ALL, 'en_US.UTF-8');	
date_default_timezone_set('Singapore');
set_time_limit(0);

/*$pass = $_GET['pass'];
		$username = $_GET['user'];
		
		$username 	= 	'admin';
		$pass		= 	'12345';
	*/
/* Credentials */
//if($username != 'admin') return;
//if($pass != '12345') return;


require_once(JPATH_BASE . '/includes/defines.php');
require_once(JPATH_BASE . '/includes/framework.php');
$mainframe = Factory::getApplication('site');

jimport('joomla.mail.mail');

$db = Factory::getDBO();


//$fp = fopen(dirname(__FILE__).'/atrmaftp/RMA_'.date("dmYHi").'.csv',"w");

$query = " SELECT r.*, p.product_no FROM #__at_rma_items AS r "
	.	" LEFT JOIN #__at_products AS p ON p.id = r.product_id "
	.	" WHERE DATE(r.created_date) = CURDATE() ";

/*$query = " SELECT r.*, p.product_no FROM #__at_rma_items AS r "
	.	" LEFT JOIN #__at_products AS p ON p.id = r.product_id "
	.	" WHERE r.created_date <= NOW() AND r.created_date >= DATE_SUB(NOW(), INTERVAL 1 HOUR); ";*/

$db->setQuery($query);
$rma_items = $db->loadObjectList();

$listarray = array();
$listarray[] = array("RMA Nbr", "Requested S/N", "Customer", "Ship-To", "Order Date", "Item Nbr", "Quantity", "Problem", "Warranty");

if (!empty($rma_items)) {
	foreach ($rma_items as $rma_item) {
		$rma1 = $rma_item->rmacode; // RMA Number
		$rma2 = $rma_item->customer_id; // Customer ID
		$rma3 = $rma_item->customer_id; // Ship To - Customer ID
		$rma4 = date("d-m-Y", strtotime($rma_item->created_date)); // Order / Created Date
		$rma5 = $rma_item->product_no; // Model No.
		$rma6 = 1; // Quantity
		$rma7 = $rma_item->description; // Description the Problem
		$rma8 = $rma_item->warranty_status; // Warranty
		$rma9 = $rma_item->requested_sn; // Serial No.

		$itm = array($rma1, $rma9, $rma2, $rma3, $rma4, $rma5, $rma6, $rma7, $rma8);
		$listarray[] = $itm;
	}
}

/*foreach($listarray as $fields) {
		fputcsv($fp, $fields);
	}*/

//fclose($fp);

// mail the condition of process
$body = '';
$body .= '<html><body>';
$body .= '<div style="margin-bottom:20px;">Today RMA Creation : ' . date("d-m-Y") . '</div>';
$body .= '<table border="1">';
$body .= '<tr>';
foreach ($listarray[0] as $tmp) {
	$body .= '<td><strong>' . $tmp . '</strong></td>';
}
$body .= '</tr>';
unset($listarray[0]);


foreach ($listarray as $data) {
	$body .= '<tr>';
	foreach ($data as $tmp) {
		$body .= '<td>' . $tmp . '</td>';
	}
	$body .= '</tr>';
}

$body .= '</table>';
$body .= '</body></html>';
echo $body;

$mail = Factory::getMailer();

$mail->isHTML(true);
$mail->addRecipient(array('ata-webadmin@alliedtelesis.com.sg', 'Amy.Tchin@alliedtelesis.com.sg'));
//$mail->addRecipient(array('ajunizar@gmail.com'));
$mail->addBCC(array('andreas@ifoundries.com'));
//$mail->addReplyTo(array('andreas@ifoundries.com', 'Adminme'));
//$mail->setSender( array( 'andreas@ifoundries.com', 'Me' ) );
$mail->setSubject('Allied Telesis : Today RMA Creation was Running on 10:00PM daily');
//$mail->AddEmbeddedImage( JPATH_SITE.DS.'templates'.DS.'mapsearch'.DS.'images'.DS.'logo_100.png', 'logo_id', 'logo.png', 'base64', 'image/png' );
$mail->setBody($body);

$sent = $mail->Send();
