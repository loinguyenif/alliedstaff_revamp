<?php

use \Joomla\CMS\Factory;

define('_JEXEC', 1);
define('JPATH_BASE', dirname(__FILE__));
//setlocale(LC_ALL, 'en_US.UTF-8');	
set_time_limit(0);

$pass = $_GET['pass'];
$username = $_GET['user'];

$username 	= 	'admin';
$pass		= 	'12345';

/* Credentials */
if ($username != 'admin') return;
if ($pass != '12345') return;


require_once(JPATH_BASE . '/includes/defines.php');
require_once(JPATH_BASE . '/includes/framework.php');
$mainframe = Factory::getApplication('site');

$db = Factory::getDBO();

$image_file_path = JPATH_SITE . '/atelftp';

$d = dir($image_file_path) or die("Wrong path: $image_file_path");

while (false !== ($entry = $d->read())) {

	if ($entry != '.' && $entry != '..' && !is_dir($dir . $entry)) {

		/* check the CSV extensions */
		if (!preg_match('/^isb\.csv$/i', $entry))
			continue;

		/* if have same file name, use that id */


		$db->setQuery('TRUNCATE TABLE ' . $db->quoteName('#__at_warranty_register'));
		$db->execute();

		$db->setQuery('TRUNCATE TABLE ' . $db->quoteName('#__at_warranty_items'));
		$db->execute();

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
				set_time_limit(0);
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

				/* Part No ID - Both ProductNo and ModelNo - must exist or else skip this row */
				$query = " SELECT id,warranty FROM #__at_products WHERE product_no = '$part_no' AND model_no = '$model_no' LIMIT 1";
				$db->setQuery($query);
				$tmpproducts = $db->loadObject();

				$product_id = $tmpproducts->id;
				$warranty_month = $tmpproducts->warranty; // warranty in months

				if (!$product_id) {
					continue;
				} // if product_id doesnt exist, skip this row

				/* Expired Date - must see from #__at_products */
				$tmp = explode('/', $pdate); // d - m - Y

				$day 	= 	$tmp[0] - 1;
				$month 	= 	$tmp[1] + intval($warranty_month);
				$year	=	$tmp[2];

				$purchase_date = date("Y-m-d", mktime(0, 0, 0, $tmp[1], $tmp[0], $tmp[2]));
				$expired_date = date("Y-m-d", mktime(0, 0, 0, $month, $day, $year));


				/* insert into #__at_warranty_items */
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
					$db->quote($warranty_id),
					$db->quote($product_id),
					$db->quote($serial_no),
					$db->quote($purchase_date),
					$db->quote($expired_date),
					$db->quote($customer_id),
					$db->quote($po_no),
					$db->quote($so_no),
					$db->quote($invoice_no)
				];

				$query = $db->getQuery(true)
					->insert($db->quoteName('#__at_warranty_items'))
					->columns($db->quoteName($columns))
					->values(implode(',', $values));

				$db->setQuery($query);
				$db->execute();
			}

			fclose($handle);
			//unlink($image_file_path.'/'.$entry);
		}
	}
}

$d->close();
