<?php

use \Joomla\CMS\Factory;
use Joomla\CMS\User\UserHelper;

define('_JEXEC', 1);
define('JPATH_BASE', dirname(__FILE__));
define('DS', DIRECTORY_SEPARATOR);

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
jimport('joomla.user.helper');

$db = Factory::getDBO();

$image_file_path = JPATH_SITE . '/atelftp';

$d = dir($image_file_path) or die("Wrong path: $image_file_path");

$error_row = array();
$total_items = 0;
$total_item_invoice_updated = 0;
$total_item_not_inserted = 0;

$customer_row = array();
$total_customer_created = 0;

$product_added_array = array();

while (false !== ($entry = $d->read())) {

	if ($entry != '.' && $entry != '..' && !is_dir($entry)) {

		/* check the CSV extensions */
		if (!preg_match('/^isbdata\.csv$/i', $entry))
			continue;

		if (($handle = fopen($image_file_path . '/' . $entry, "r")) !== FALSE) {

			$line = fgetcsv($handle, 4096, ","); // database header;

			$header = array();

			foreach ($line as $t) :
				$header[] = $t;
			endforeach;

			$cnt = count($header);

			if ($cnt != 8) {
				continue;
			}
			/* if have same file name, use that id */
			$query = " SELECT id FROM #__at_warranty_register WHERE first_name = '$entry' LIMIT 1 ";
			$db->setQuery($query);
			$warranty_id = $db->loadResult();

			if (!$warranty_id) {
				/* before iterating, set #__at_warranty_register for CSV filename */
				$query = $db->getQuery(true)
					->insert($db->quoteName('#__at_warranty_register'))
					->columns($db->quoteName('first_name'))
					->values($db->quote($entry));

				$db->setQuery($query);
				$db->execute();

				$warranty_id = $db->insertid();
			}



			while (($data = fgetcsv($handle, 4096, ",")) !== FALSE) {

				/* 	Data Field */
				/*
						*  	0 => Customer No
						*	1 => PO No.
						*	2 => SO No.
						*	3 => Invoice No
						*	4 => Part No
						*	5 => Model No
						*	6 => Serial No
						*	7 => Ship Date
						*
					*/

				$customer_id 	= 	trim($data[0]);
				$po_no 			= 	trim($data[1]);
				$so_no			= 	trim($data[2]);
				$invoice_no 	=	trim($data[3]);

				$part_no 		= 	trim($data[4]);
				$model_no 		= 	trim($data[5]);

				$serial_no 		= 	trim($data[6]);
				$pdate 			= 	trim($data[7]);

				/* Part No ID - search if exist */
				$query = " SELECT id,warranty FROM #__at_products WHERE product_no = '$part_no' LIMIT 1";
				$db->setQuery($query);
				$tmpproducts = $db->loadObject();

				$product_id = $tmpproducts->id;
				$warranty_month = $tmpproducts->warranty; // warranty in months

				if (empty($product_id)) {

					array_push($product_added_array, $part_no);

					$warranty_month = 60;

					/* insert into #__at_products */
					$columns = ['product_no', 'model_no', 'warranty'];
					$values  = [
						$db->quote($part_no),
						$db->quote($model_no),
						$db->quote($warranty_month)
					];

					$query = $db->getQuery(true)
						->insert($db->quoteName('#__at_products'))
						->columns($db->quoteName($columns))
						->values(implode(',', $values));

					$db->setQuery($query);
					$db->execute();

					$product_id = $db->insertid();
				} // if product_id doesnt exist, add it to product table.

				/* To Do : add Internal Customer --> warranty is 12 Month in every item */
				/* To Do : add External Customer --> warranty is depending on the model */

				/* Expired Date - must see from #__at_products */
				$tmp = explode('/', $pdate); // d - m - Y

				$day 	= 	$tmp[0] - 1;
				$month 	= 	$tmp[1] + intval($warranty_month);
				$year	=	$tmp[2];

				$purchase_date = date("Y-m-d", mktime(0, 0, 0, $tmp[1], $tmp[0], $tmp[2]));
				$expired_date = date("Y-m-d", mktime(0, 0, 0, $month, $day, $year));
				$extended_warranty = 3;
				$extended_expired_date = date("Y-m-d", mktime(0, 0, 0, ($month + $extended_warranty), $day, $year));


				// iFoundries : 25 May 2017 . check customer has account in com_users - START
				$query = " SELECT id FROM #__users WHERE customer_id = '$customer_id' ";
				$db->setQuery($query);
				$customer_exist = $db->loadResult();

				if (!$customer_exist) { // if not exist, add users

					$email = $customer_id . '@atg.lc';

					//$salt  = UserHelper::genRandomPassword(32);
					//$crypt = UserHelper::getCryptedPassword($customer_id, $salt, 'md5-hex');
					//$password = $crypt . ':' . $salt;

					$password = UserHelper::hashPassword($customer_id);

					$query = "INSERT INTO " . $db->quoteName('#__users') . " ("
						. $db->quoteName('name') . ", "
						. $db->quoteName('username') . ", "
						. $db->quoteName('email') . ", "
						. $db->quoteName('password') . ", "
						. $db->quoteName('usertype') . ", "
						. $db->quoteName('block') . ", "
						. $db->quoteName('sendEmail') . ", "
						. $db->quoteName('gid') . ", "
						. $db->quoteName('customer_id')
						. ") VALUES ("
						. $db->quote($customer_id) . ", "
						. $db->quote($customer_id) . ", "
						. $db->quote($email) . ", "
						. $db->quote($password) . ", "
						. $db->quote('Administrator') . ", "
						. $db->quote(0) . ", "
						. $db->quote(0) . ", "
						. $db->quote(24) . ", "
						. $db->quote($customer_id)
						. ")";

					$db->setQuery($query);
					$db->execute();

					$user_id = $db->insertid();

					$query = $db->getQuery(true)
						->insert($db->quoteName('#__user_usergroup_map'))
						->columns([$db->quoteName('user_id'), $db->quoteName('group_id')])
						->values((int) $user_id . ', ' . 24); // 7 = Administrator

					$db->setQuery($query);
					$db->execute();

					$customer_row[] = $customer_id;
					$total_customer_created++;
				}
				// iFoundries : 25 May 2017 . check customer has account in com_users - ENDED


				// CHECK Serial No and SO #
				$query = " SELECT id FROM #__at_warranty_items WHERE (serial_no = '$serial_no' OR serial_no_2 = '$serial_no') AND so_no = '$so_no' LIMIT 1 ";
				$db->setQuery($query);
				$id_exist = $db->loadResult();

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
						$warranty_id,
						$product_id,
						$serial_no,
						$purchase_date,
						$expired_date,
						$customer_id,
						$po_no,
						$so_no,
						$invoice_no,
						$extended_warranty,
						$extended_expired_date
					];

					$query = $db->getQuery(true)
						->insert($db->quoteName('#__at_warranty_items'))
						->columns($db->quoteName($columns))
						->values(implode(',', $db->quote($values)));

					$db->setQuery($query);
					$db->execute();

					$total_items++;
				} else {

					if ($invoice_no) {
						// Check based on Serial #, SO #, and Invoice #
						$query = " SELECT id FROM #__at_warranty_items WHERE (serial_no = '$serial_no' OR serial_no_2 = '$serial_no') AND so_no = '$so_no' AND invoice_no = '$invoice_no' LIMIT 1 ";
						$db->setQuery($query);
						$row_exist = $db->loadResult();

						if (!$row_exist) { // if row doesnt exist

							// UPDATE Invoice Number
							$query = $db->getQuery(true)
								->update($db->quoteName('#__at_warranty_items'))
								->set($db->quoteName('invoice_no') . ' = ' . $db->quote($invoice_no))
								->where('(' . $db->quoteName('serial_no') . ' = ' . $db->quote($serial_no)
									. ' OR ' . $db->quoteName('serial_no_2') . ' = ' . $db->quote($serial_no) . ')')
								->where($db->quoteName('so_no') . ' = ' . $db->quote($so_no));

							$db->setQuery($query);
							$db->execute();

							$total_item_invoice_updated++;
						} else {

							$error_text = 'Invoice Number exists for these SO Number and Serial Number';
							$error_row[] = $customer_id . "|" . $po_no . "|" . $so_no . "|" . $invoice_no . "|" . $part_no . "|" . $model_no . "|" . $serial_no . "|" . $pdate . "|" . $error_text;
						}
					} else {

						$error_text = 'SO Number and Serial Number exist';
						$error_row[] = $customer_id . "|" . $po_no . "|" . $so_no . "|" . $invoice_no . "|" . $part_no . "|" . $model_no . "|" . $serial_no . "|" . $pdate . "|" . $error_text;
					}
					// else skip it
					$total_item_not_inserted++;
				}
			}

			fclose($handle);
			//unlink($image_file_path.'/'.$entry);
		}

		// mail the condition of process
		$body = '';
		$body .= '<html><body>';
		$body .= '<div style="margin-bottom:20px;">Warranty Registration Automation Script was Running</div>';

		if ($total_customer_created > 0) {
			$body .= '<div style="margin-bottom:20px"><strong>' . $total_customer_created . '</strong> Customer created : <br /><strong>' . implode($customer_row, ',') . '</strong></div>';
		}

		if (!empty($product_added_array)) {
			$body .= '<div style="margin-bottom:20px"><strong>' . count($product_added_array) . '</strong> Product created : <br /><strong>' . implode($product_added_array, ', ') . '</strong></div>';
		}

		if ($total_item_invoice_updated > 0) {
			$body .= '<div style="margin-bottom:20px">Total Items that were invoiced: <strong>' . $total_item_invoice_updated . '</strong></div>';
		}

		if ($error_row) {

			$body .= '<div style="margin-bottom:20px;">Oops, there were some errors during import; please see below.</div>';
			$body .= '<div style="margin-bottom:5px;">Total Items that were imported: <strong>' . $total_items . '</strong></div>';
			$body .= '<div style="margin-bottom:20px;">Total Items that were NOT imported: <strong>' . $total_item_not_inserted . '</strong></div>';
			$body .= '<table cellpadding="1" cellspacing="1">';
			$body .= '<tr>';
			$body .= '<th>Customer ID</th><th>PO No.</th><th>SO No.</th><th>Invoice No.</th><th>Part No.</th><th>Model No.</th><th>Serial No.</th><th>Purchase / Ship Date</th><th>Note</th>';
			$body .= '</tr>';

			foreach ($error_row as $key => $erw) :

				$w = explode('|', $erw);

				$body .= '<tr>';
				$body .= '<td>' . $w[0] . '</td><td>' . $w[1] . '</td><td>' . $w[2] . '</td><td>' . $w[3] . '</td><td>' . $w[4] . '</td><td>' . $w[5] . '</td><td>' . $w[6] . '</td><td>' . $w[7] . '</td><td>' . $w[8] . '</td>';
				$body .= '</tr>';

			endforeach;

			$body .= '</table>';
		} else {
			$body .= '<div style="margin-bottom:20px">Total Items that were imported: <strong>' . $total_items . '</strong></div>';
		}

		$body .= '</body></html>';
		echo $body;
		$mail = Factory::getMailer();

		$mail->isHTML(true);

		$mail->addRecipient(array('ata-webadmin@alliedtelesis.com.sg', 'Amy.Tchin@alliedtelesis.com.sg'));
		$mail->addBCC(array('andreas@ifoundries.com'));
		//$mail->addReplyTo(array('andreas@ifoundries.com', 'Adminme'));
		//$mail->setSender( array( 'andreas@ifoundries.com', 'Me' ) );
		$mail->setSubject('Allied Telesis : Warranty Registration Daily Automation was Running');
		//$mail->AddEmbeddedImage( JPATH_SITE.DS.'templates'.DS.'mapsearch'.DS.'images'.DS.'logo_100.png', 'logo_id', 'logo.png', 'base64', 'image/png' );
		$mail->setBody($body);

		$sent = $mail->Send();
	}
}

$d->close();
