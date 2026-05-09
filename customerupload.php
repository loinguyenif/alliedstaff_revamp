<?php

use \Joomla\CMS\Factory;
use Joomla\CMS\User\UserHelper;

define('_JEXEC', 1);
define('JPATH_BASE', dirname(__FILE__));
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
		if (!preg_match('/^customer\.csv$/i', $entry))
			continue;

		/* if have same file name, use that id */


		if (($handle = fopen($image_file_path . '/' . $entry, "r")) !== FALSE) {

			$line = fgetcsv($handle, 4096, ","); // database header;

			$header = array();

			foreach ($line as $t) :
				$header[] = $t;
			endforeach;

			$cnt = count($header);

			if ($cnt != 2) {
				continue;
			}
			$text = '';
			while (($data = fgetcsv($handle, 4096, ",")) !== FALSE) {

				/* 	Data Field */

				$customer_id 	= 	trim($data[0]);
				$name 			= 	trim($data[1]);

				// check whether Customer ID alreadyd exist, if exist, do not insert data into user db
				$query = " SELECT id FROM #__users WHERE customer_id = " . $db->Quote(trim($customer_id), false);
				$db->setQuery($query);
				$cust_id_exist = $db->loadResult();

				if ($cust_id_exist)
					continue;
				$password = UserHelper::hashPassword($customer_id);
				//$salt = JUserHelper::genRandomPassword(32);
				//$crypt = JUserHelper::getCryptedPassword($customer_id, $salt);
				//$password = $crypt . ':' . $salt;

				$query = "INSERT INTO #__users (name, username, password, usertype, block, sendEmail, gid, customer_id) "
					. "VALUES ("
					. $db->quote($name)
					. ", " . $db->quote($customer_id)
					. ", " . $db->quote($password)
					. ", " . $db->quote('Administrator')
					. ", 0"
					. ", 0"
					. ", 24"
					. ", " . $db->quote($customer_id)
					. ")";

				$db->setQuery($query);
				$db->execute();

				$user_id = $db->insertid();


				$query = $db->getQuery(true)
					->insert($db->quoteName('#__user_usergroup_map'))
					->columns([$db->quoteName('user_id'), $db->quoteName('group_id')])
					->values((int) $userId . ', 24');

				$db->setQuery($query);
				$db->execute();
			}

			fclose($handle);
			//unlink($image_file_path.'/'.$entry);

		}
	}
}

$d->close();
