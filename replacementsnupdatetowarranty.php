<?php

define('_JEXEC', 1);
define('JPATH_BASE', dirname(__FILE__));
define('DS', DIRECTORY_SEPARATOR);

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

// check whether Customer ID alreadyd exist, if exist, do not insert data into user db
$query = " SELECT r.* FROM #__at_rma_items AS r "
	.	" ORDER BY r.created_date ASC";

$db->setQuery($query);
$rma_items = $db->loadObjectList();

if (!empty($rma_items)) {
	$i = 1;
	$updated = 1;
	$warranty_not_exist = 1;
	$text = '';
	foreach ($rma_items as $rma) {

		$replacement_sn 			= $rma->replacement_sn;
		$replacement_pn 			= $rma->replacement_pn;
		$requested_sn 				= $rma->requested_sn;

		$query = " SELECT id FROM #__at_warranty_items WHERE serial_no = '$requested_sn' LIMIT 1 ";
		$db->setQuery($query);
		$warranty_item_id = $db->loadResult();

		if ($warranty_item_id) {

			$query = " UPDATE #__at_warranty_items SET serial_no_2 = '$replacement_sn' , replacement_pn = '$replacement_pn' WHERE id = '$warranty_item_id' ";
			$db->setQuery($query);
			$db->query();

			$text .= $i . '. Warranty S/N : ' . $requested_sn . ' . Updated Replacement S/N : ' . ($replacement_sn ? $replacement_sn : '-') . ' (P/N : ' . ($replacement_pn ? $replacement_pn : '-') . ') (Created : ' . date("d-m-Y", strtotime($rma->created_date)) . ')<br /> ';
			$updated++;
		} else {
			$text .= $i . '. (No update) Original S/N not exist in Warranty Registration Table: ' . $requested_sn . ' (Created : ' . date("d-m-Y", strtotime($rma->created_date)) . ')<br /> ';
			$warranty_not_exist++;
		}

		$i++;
	}

	echo 'Updated : ' . $updated . '<br />';
	echo 'Warranty Not Exist : ' . $warranty_not_exist . '<br />';
	echo '<br />' . $text . '<br />';
}
