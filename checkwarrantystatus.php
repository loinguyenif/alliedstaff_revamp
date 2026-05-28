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

use \Joomla\CMS\Factory;
use Joomla\CMS\User\UserHelper;
//setlocale(LC_ALL, 'en_US.UTF-8');	
date_default_timezone_set('Singapore');
set_time_limit(0);
$db = Factory::getDbo();

$query = " SELECT i.id, i.customer_id, i.purchase_date , i.expired_date_manual , i.extended_warranty, u.is_internal, p.warranty, p.is_previous3years FROM #__at_warranty_items AS i "
	.	" LEFT JOIN #__at_products AS p ON i.product_id = p.id "
	.	" LEFT JOIN #__users AS u ON i.customer_id = u.customer_id ";
//echo $query."<br />";
$db->setQuery($query);
$rows = $db->loadObjectList();

if (!empty($rows)) {

	foreach ($rows as $r) {

		$expired_date_manual = $r->expired_date_manual;
		$extended_warranty = (int) $r->extended_warranty;
		$extended_expired_date = '0000-00-00';
		$expired_date = '0000-00-00';

		// Calculate Expiry Date
		// if internal, expiry_date must be 12 months 
		if ($r->is_internal == 1) {

			/* Expired Date - must see from #__at_products */
			$tmp = explode('-', $r->purchase_date); // Y - m - d

			$day 	= 	$tmp[2] - 1;
			$month 	= 	$tmp[1] + 12;
			$year	=	$tmp[0];

			$expired_date = date("Y-m-d", mktime(0, 0, 0, $month, $day, $year));
		} else // external mode, or product doesnt have any customer id
		{

			// Time for July 1st,2010.
			$timeon_1stjuly2010 = mktime(0, 0, 0, 7, 1, 2010);
			$timeon_purchasedate = strtotime($r->purchase_date);

			//	if purchase_date before 1st july 2010, not including 1st july 2010, just based on products warranty
			if ($timeon_purchasedate < $timeon_1stjuly2010) {
				$tmp = explode('-', $r->purchase_date); // Y - m - d

				if ($r->is_previous3years == 1) {
					$warranty_year = 36;
				} else {
					$warranty_year = intval($r->warranty);
				}

				$day 	= 	$tmp[2] - 1;
				$month 	= 	$tmp[1] + $warranty_year;
				$year	=	$tmp[0];

				$expired_date = date("Y-m-d", mktime(0, 0, 0, $month, $day, $year));
			} else //	if purchase_date on and after 1st july 2010, follow Warranty (Months)
			{

				$tmp = explode('-', $r->purchase_date); // Y - m - d

				$day 	= 	$tmp[2] - 1;
				$month 	= 	$tmp[1] + intval($r->warranty);
				$year	=	$tmp[0];

				$expired_date = date("Y-m-d", mktime(0, 0, 0, $month, $day, $year));
			}
		}

		// calculate correct Extended Expiry Date, must have Extended Warranty (Month)
		if ($extended_warranty > 0) {

			$tmp = explode('-', $expired_date); // Y - m - d

			$day 	= 	$tmp[2];
			$month 	= 	$tmp[1] + intval($extended_warranty);
			$year	=	$tmp[0];

			$extended_expired_date = date("Y-m-d", mktime(0, 0, 0, $month, $day, $year));
		}

		$query = $db->getQuery(true)
			->update($db->quoteName('#__at_warranty_items'))
			->set($db->quoteName('expired_date') . ' = ' . $db->quote($expired_date))
			->set($db->quoteName('extended_expired_date') . ' = ' . $db->quote($extended_expired_date))
			->where($db->quoteName('id') . ' = ' . (int) $r->id);

		$db->setQuery($query);
		$db->execute();
	}
	echo 'Warranty Registration updated successfully.';
} else {
	echo 'No Warranty Registration found.';
}
