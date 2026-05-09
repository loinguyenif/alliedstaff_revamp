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

$query = " SELECT DISTINCT(customer_id), name FROM #__users WHERE customer_id != '' AND gid IN(23,24) GROUP BY customer_id;  ";
$db->setQuery($query);
$users = $db->loadObjectList();

foreach ($users as $u) {
	$query = " SELECT id FROM #__at_companies WHERE customer_id = '" . $u->customer_id . "'; ";
	$db->setQuery($query);
	$id = $db->loadResult();

	if ($id) {
	} else {

		$columns = ['customer_id', 'company_name'];
		$values  = [$u->customer_id, $u->name];

		$query = $db->getQuery(true)
			->insert($db->quoteName('#__at_companies'))
			->columns($db->quoteName($columns))
			->values(implode(',', $db->quote($values)));

		$db->setQuery($query);
		$db->execute();
	}
}
