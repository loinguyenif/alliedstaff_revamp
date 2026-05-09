<?php

define('_JEXEC', 1);
define('JPATH_BASE', dirname(__FILE__));

use \Joomla\CMS\Factory;
//setlocale(LC_ALL, 'en_US.UTF-8');	
set_time_limit(0);

/*$pass = $_GET['pass'];
		$username = $_GET['user'];
		
		$username 	= 	'admin';
		$pass		= 	'12345';
		
		// Credentials
		if($username != 'admin') return;
		if($pass != '12345') return;
	*/

require_once(JPATH_BASE . '/includes/defines.php');
require_once(JPATH_BASE . '/includes/framework.php');
require_once(JPATH_BASE . '/libraries/joomla/user/helper.php');

$mainframe = Factory::getApplication('site');

$db = Factory::getDBO();

$image_file_path = JPATH_SITE . '/atelftp';

$d = dir($image_file_path) or die("Wrong path: $image_file_path");

while (false !== ($entry = $d->read())) {

	if ($entry != '.' && $entry != '..' && !is_dir($dir . $entry)) {


		/* check the CSV extensions */
		if (!preg_match('/^srlrplnt\.csv$/i', $entry))
			continue;

		/* if have same file name, use that id */


		if (($handle = fopen($image_file_path . '/' . $entry, "r")) !== FALSE) {

			$line = fgetcsv($handle, 4096, ","); // database header;

			$header = array();

			foreach ($line as $t) :
				$header[] = $t;
			endforeach;

			$cnt = count($header);

			if ($cnt != 5) {
				continue;
			}
			$text = '';
			while (($data = fgetcsv($handle, 4096, ",")) !== FALSE) {

				/* 	Data Field */

				$rma_number 					= 	trim($data[0]);
				$original_sn 					= 	trim($data[1]);
				$replacement_sn 			= 	trim($data[2]);
				$replacement_pn 			= 	trim($data[3]);
				$ship_date						=		trim($data[4]);


				// check whether Customer ID alreadyd exist, if exist, do not insert data into user db
				$query = " SELECT id, warranty_item_id FROM #__at_rma_items WHERE rmacode = " . $db->Quote(trim($rma_number), false) . " AND requested_sn = " . $db->Quote(trim($original_sn), false) . " LIMIT 1 ";
				$db->setQuery($query);
				$rma_exist = $db->loadObject();

				if (!$rma_exist) // if not exist, do not do anything.
					continue;

				$warranty_item_id = $rma_exist->warranty_item_id;
				$rma_id						=	$rma_exist->id;

				$tmp = explode('/', $ship_date); // d/m/y

				$day 	= 	$tmp[0];
				$month 	= 	$tmp[1];
				$year	=	$tmp[2];

				$ship_date = date("Y-m-d", mktime(0, 0, 0, $month, $day, $year));

				// UPDATE for this RMA
				$query = " UPDATE #__at_rma_items SET replacement_pn = '$replacement_pn' , replacement_sn = '$replacement_sn' , shipped_date = '" . date("Y-m-d", strtotime($ship_date)) . "', status = 'ship' WHERE id = '$rma_id' ";
				$db->setQuery($query);
				$db->query();

				// UPDATE also in Warranty for Replacement S/n or P/N
				$query = " UPDATE #__at_warranty_items SET replacement_pn = '$replacement_pn' , serial_no_2 = '$replacement_sn' WHERE id = '$warranty_item_id' ";
				$db->setQuery($query);
				$db->query();
			}

			fclose($handle);
			//unlink($image_file_path.'/'.$entry);

		}
	}
}

$d->close();
