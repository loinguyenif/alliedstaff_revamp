<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Atelman
 * @author     iFoundries <wpsub@ifoundries.com>
 * @copyright  2025 iFoundries
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Atelman\Component\Atelman\Administrator\Helper;
// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Object\CMSObject;
use Joomla\CMS\HTML\HTMLHelper as JHtml;

/**
 * Atelman helper.
 *
 * @since  1.0.0
 */
class AtelmanHelper
{
	/**
	 * Gets the files attached to an item
	 *
	 * @param   int     $pk     The item's id
	 *
	 * @param   string  $table  The table's name
	 *
	 * @param   string  $field  The field's name
	 *
	 * @return  array  The files
	 */
	public static function getFiles($pk, $table, $field)
	{
		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);

		$query
			->select($field)
			->from($table)
			->where('id = ' . (int) $pk);

		$db->setQuery($query);

		return explode(',', $db->loadResult());
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @return  CMSObject
	 *
	 * @since   1.0.0
	 */
	public static function getActions()
	{
		$user = Factory::getApplication()->getIdentity();
		$result = new CMSObject;

		$assetName = 'com_atelman';

		$actions = array(
			'core.admin',
			'core.manage',
			'core.create',
			'core.edit',
			'core.edit.own',
			'core.edit.state',
			'core.delete'
		);

		foreach ($actions as $action) {
			$result->set($action, $user->authorise($action, $assetName));
		}

		return $result;
	}


	public static function checkRowProduct($productNo)
	{
		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true)
			->select('*')
			->from($db->quoteName('#__at_products'))
			->where($db->quoteName('product_no') . ' = ' . $db->quote($productNo));

		$db->setQuery($query);
		$rows = $db->loadObject();
		return $rows->id;
	}

	public static function getRMAStatusTextArray()
	{
		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true)
			->select('*')
			->from($db->quoteName('#__at_rma_status'))
			->order('ordering ASC');
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$tmp = array();
		foreach ($rows as $r) :
			$tmp[$r->status_code] = $r->status_name;
		endforeach;

		return $tmp;
	}

	public static function statusType($status = "")
	{

		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true)
			->select('*')
			->from($db->quoteName('#__at_rma_status'))
			->order('ordering ASC');
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$options[] 	= 	JHtml::_('select.option', '', '-- Select RMA Status --');
		foreach ($rows as $r) :
			$options[] 	= 	JHtml::_('select.option', $r->status_code, $r->status_name);
		endforeach;

		return JHtml::_('select.genericlist', $options, 'filter_status', 'class="form-select" onchange="document.adminForm.submit();"', 'value', 'text', $status);
	}

	public static function getCountryFilter($filter_country = 0)
	{
		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true)
			->select('*')
			->from($db->quoteName('#__at_countries'))
			->order('country ASC');
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$country = array();

		$country[] = JHTML::_('select.option',  0,  '-- Select Country --');
		foreach ($rows as $r):
			$country[] = JHTML::_('select.option',  $r->id,  Text::_($r->country));
		endforeach;

		return JHTML::_('select.genericlist',   $country, 'filter_country', 'class="inputbox form-select" size="1" onchange="document.adminForm.submit( );"', 'value', 'text', "$filter_country");
	}

	public static function getDistributorFilter($filter_distributor = 0, $country_id = 0, $user_ids = array())
	{ // plus Manager now

		$db = Factory::getContainer()->get('DatabaseDriver');
		$user = Factory::getApplication()->getIdentity();

		$plus_sql = '';

		if (!empty($user_ids)) {
			$plus_sql .= " AND id IN (" . implode(',', $user_ids) . ")";
		}

		if ($user->gid == 23 && !empty($user->country_id)) {
			$plus_sql .= " AND country_id = " . $user->country_id;
		}

		if ($country_id) {
			$plus_sql .= " AND country_id = " . $country_id;
		}

		$query = " SELECT DISTINCT(customer_id), name FROM atel_users WHERE (gid = 24 OR gid = 23) " . $plus_sql . " GROUP BY customer_id ORDER BY name ASC ";
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$company = array();

		$company[] = JHTML::_('select.option',  0,  '-- Select Customer / Distributor --');

		foreach ($rows as $r):
			$company[] = JHTML::_('select.option',  $r->customer_id,  Text::_($r->name . ' [' . $r->customer_id . ']'));
		endforeach;

		return JHTML::_('select.genericlist',   $company, 'filter_distributor', 'class="inputbox form-select" size="1" onchange="document.adminForm.submit( );"', 'value', 'text', "$filter_distributor");
	}



	public static function getCompanies()
	{

		$db = Factory::getContainer()->get('DatabaseDriver');
		$user = Factory::getApplication()->getIdentity();

		$where = array();
		if ($user->gid == 23) { // Manager
			$where[] = ' u.country_id = ' . $db->Quote($user->country_id, false);
			$where[] = ' u.country_id != \'\' ';
			$where[] = ' u.customer_id != \'\' ';
			$where[] = ' u.gid != 25 '; // not Super Administrator
		}

		$where = (count($where) ? ' WHERE ' . implode(' AND ', $where) : '');

		$query = " SELECT c.* FROM #__users AS u "
			.	" RIGHT JOIN #__at_companies AS c ON c.customer_id = u.customer_id "
			.	$where
			. " GROUP BY u.customer_id "
			. " ORDER BY c.company_name ASC ";

		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$company = array();

		$company[] = JHTML::_('select.option',  '',  '-- Select Company --');
		foreach ($rows as $r):
			$company[] = JHTML::_('select.option',  $r->customer_id,  Text::_($r->company_name . ' [' . $r->customer_id . ']'));
		endforeach;

		return JHTML::_('select.genericlist',   $company, 'fullname_tmp', 'class="inputbox required" size="1" style="width:100%;" onchange="javascript:loadCustomer(this.value)" ', 'value', 'text', $user->customer_id);
	}


	public static function getCountry($pk)
	{
		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);

		$query
			->select('country')
			->from('#__at_countries')
			->where('id = ' . (int) $pk);

		$db->setQuery($query);

		return $db->loadResult();
	}

	public static function getProductByPartNumber($part_no)
	{

		if (!$part_no)
			return false;
		$db = Factory::getContainer()->get('DatabaseDriver');

		$query = " SELECT * FROM #__at_products WHERE product_no = '$part_no'  ";

		$db->setQuery($query);
		$row = $db->loadObject();

		return $row;
	}


	public static function loadCompany($id)
	{
		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true)
			->select('*')
			->from($db->quoteName('#__at_companies'))
			->where($db->quoteName('customer_id') . ' = ' . $db->quote($id));

		$db->setQuery($query);
		$rows = $db->loadObject();
		return $rows;
	}

	public static function getItemById($section, $id = 0)
	{

		if (!$id)
			return false;

		$db = Factory::getContainer()->get('DatabaseDriver');

		switch ($section) {
			case 'products':
				$query = " SELECT * FROM #__at_products WHERE id = '$id'  ";
				break;
			case 'countries':
				$query = " SELECT * FROM #__at_countries WHERE id = '$id'  ";
				break;
			case 'companies':
				$query = " SELECT * FROM #__at_companies WHERE id = '$id'  ";
				break;
		}

		$db->setQuery($query);
		$row = $db->loadObject();

		return $row;
	}

	public static function getItemByCustomerId($customer_id = '')
	{
		$db = Factory::getContainer()->get('DatabaseDriver');

		$query = $db->getQuery(true)
			->select('*')
			->from($db->quoteName('#__users'))
			->where($db->quoteName('customer_id') . ' = ' . $db->quote($customer_id));

		$db->setQuery($query);
		$row = $db->loadObject();

		return $row;
	}


	public static function statusTypeUpdate($status = '')
	{

		$user = Factory::getUser();
		$db = Factory::getDBO();

		$query = " SELECT * FROM #__at_rma_status ORDER BY ordering ASC ";
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$options = array();

		$options[] 	= 	JHTML::_('select.option', '', '', 'value', 'text');

		/**
		 * 25 = Super Administrator
		 * 32 = Logistics
		 * 31 = Store
		 * 34 = Supervisor
		 **/

		foreach ($rows as $r) :

			$disabled = false;

			// Store
			if ($user->gid == 31 && $status == 'open') {
				continue;
			}

			if ($user->gid == 31 && $status == 'await') {
				if ($r->status_code != 'await' && $r->status_code != 'ship' && $r->status_code != 'receive') {
					continue;
				}
			}

			if ($user->gid == 31 && ($status == 'receive' || $status == 'ship')) {
				if ($r->status_code != 'ship' && $r->status_code != 'receive') {
					continue;
				}
			}

			if ($user->gid == 31 && ($status == 'receive_close')) {
				if ($r->status_code != 'ship') {
					continue;
				}
			}

			if ($user->gid == 31 && ($status == 'ship_close')) {
				if ($r->status_code != 'receive') {
					continue;
				}
			}


			if ($user->gid == 31 && ($status == 'close')) {
				continue;
			}

			// Logistics
			if ($user->gid == 32 && $status == 'open') {
				if ($r->status_code != 'await') continue;
			}

			if ($user->gid == 32 && ($status == 'receive' || $status == 'ship' || $status == 'receive_close' || $status == 'ship_close')) {
				if ($r->status_code != 'receive_close' && $r->status_code != 'ship_close') continue;
			}

			if ($user->gid == 32 && $status == 'await') {
				if ($r->status_code != 'await') continue;
			}

			if ($user->gid == 32 && $status == 'close') {
				if ($r->status_code != 'close') continue;
			}

			$options[] 	= 	JHTML::_('select.option', $r->status_code, $r->status_name, 'value', 'text', $disabled);

		endforeach;

		if (!empty($options)) {
			return JHTML::_('select.genericlist', $options, 'status', '', 'value', 'text', '');
		} else {
			return '-';
		}
	}
	public static function statusItem($status)
	{
		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = " SELECT status_name FROM #__at_rma_status WHERE  status_code = '" . $status . "' ";
		$db->setQuery($query);
		$statusesHTML = $db->loadResult();
		return $statusesHTML;
	}


	public static function getSelectHTML($section = '', $name = '', $selected = '')
	{
		$db = Factory::getDBO();

		$arry = array();

		switch ($section) {
			case 'distributors':
				$query = " SELECT id, CONCAT(name, IF(address, CONCAT(' - ' ,address), '')) AS value FROM #__users WHERE gid = 24 AND block = 0 ORDER BY name ASC ";
				$arry[] = JHTML::_('select.option',  0,  '-- Select Distributor --');

				$db->setQuery($query);
				$rows = $db->loadObjectList();

				if (!empty($rows)) :
					foreach ($rows as $r):
						$arry[] = JHTML::_('select.option',  $r->id,  JText::_($r->value));
					endforeach;
				endif;

				break;
			case 'countries':
				$query = " SELECT id, country AS value FROM #__at_countries ORDER BY country ASC ";
				$arry[] = JHTML::_('select.option',  0,  '-- Select Countries --');

				$db->setQuery($query);
				$rows = $db->loadObjectList();

				if (!empty($rows)) :
					foreach ($rows as $r):
						$arry[] = JHTML::_('select.option',  $r->id,  JText::_($r->value));
					endforeach;
				endif;
				break;
			case 'rma_report_type':
				$arry[] = JHTML::_('select.option', '', '-- Select RMA Report Type --');
				$arry[] = JHTML::_('select.option', 'respond', 'Respond Time --> RMA Request Date - Assigned RMA Number');
				$arry[] = JHTML::_('select.option', 'receive', 'Receive Time --> Assigned RMA Number - Faulty Unit(s) Received');
				$arry[] = JHTML::_('select.option', 'ship', 'Ship Time --> Receipt Date - Ship Date');
				$arry[] = JHTML::_('select.option', 'total', 'Total Time --> RMA Request Date - Ship / Receipt Date');
				$arry[] = JHTML::_('select.option', 'most-rma-country', 'Most RMA Items --> Country');
				$arry[] = JHTML::_('select.option', 'most-rma-model', 'Most RMA Items --> Model');
				break;
		}

		return JHTML::_('select.genericlist',   $arry, $name, 'class="inputbox" size="1" ', 'value', 'text', $selected);
	}


	public static function getCheckboxHTML($section = '', $name = '', $selected = '')
	{
		$db = Factory::getDBO();

		$arry = array();

		switch ($section) {

			case 'countries':
				$query = " SELECT id, country AS value FROM #__at_countries ORDER BY country ASC ";

				$db->setQuery($query);
				$rows = $db->loadObjectList();

				if (!empty($rows)) :
					foreach ($rows as $r):
						$arry[] = '<input type="checkbox" id="chxbox-' . $name . $r->id . '" name="' . $name . '[]" value="' . $r->id . '" /> <label for="chxbox-' . $name . $r->id . '">' . $r->value . '</label>';
					endforeach;
				endif;
				break;
		}
		return implode('<br />', $arry);
	}

	public static function countryCheckbox($id, $column)
	{

		$db = Factory::getDBO();

		$arry = array();

		$query = " SELECT id, country AS value FROM #__at_countries ORDER BY country ASC ";

		$db->setQuery($query);
		$rows = $db->loadObjectList();


		$count_total = count($rows) + 1;
		$count_tmp = ceil($count_total / $column);

		$width = (100 / $column) . '%';

		$arry_tmp = array();

		if (!empty($rows)) {

			$arry[] = '<div class="column" style="width:' . $width . '">';
			$arry[] = '<input type="checkbox" id="rma-all-countries" class="all-country-chxbox" name="" value="" /><label for="rma-all-countries">All Countries</label><br />';

			$key = 2;
			$i = 0;

			foreach ($rows as $r) {

				$arry_tmp[] = '<input type="checkbox" class="country-chxbox" id="chxbox-' . $id . $r->id . '" name="' . $id . '[]" value="' . $r->id . '" /> <label for="chxbox-' . $id . $r->id . '">' . $r->value . '</label>';

				$key++;
				$i++;

				if ($key == $count_tmp + 1) { // place it to a new column for next item;

					$arry[] = implode('<br />', $arry_tmp);
					$arry_tmp = array();
					$key = 1;

					$arry[] = '</div>';

					if ($i < $count_total) {
						$arry[] = '<div class="column" style="width:' . $width . '">';
					}
				}
			}

			if ($i < $count_total) {
				$arry[] = implode('<br />', $arry_tmp);
				$arry[] = '</div>';
			}
		}

		return implode('', $arry);
	}


	public static function getGroupXref($userId = '')
	{
		$db = Factory::getContainer()->get('DatabaseDriver');

		$query = $db->getQuery(true)
			->select($db->quoteName(['gx.group_id', 'ga.access']))
			->from($db->quoteName('#__at_group_xref', 'gx'))
			->join(
				'LEFT',
				$db->quoteName('#__at_group_access', 'ga') . ' ON ' .
					$db->quoteName('ga.group_id') . ' = ' . $db->quoteName('gx.group_id')
			)
			->where($db->quoteName('gx.user_id') . ' = ' . (int) $userId);

		$db->setQuery($query);
		$result = $db->loadObject();

		return $result;
	}

	public static function getServiceContract($id)
	{
		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true)
			->select('*')
			->from($db->quoteName('#__at_service_contract'))
			->where($db->quoteName('id') . ' = ' . $db->quote($id));

		$db->setQuery($query);
		$rows = $db->loadObject();
		return $rows;
	}

	public static function getCountryUser($id)
	{
		if ($id) {
			$db = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from($db->quoteName('#__at_countries'));
			$query->where('id=' . $id);
			$db->setQuery($query);
			$results = $db->loadObject();
			return $results->country;
		} else {
			return "N/A";
		}
	}


	public static function getExpiryMonthFilter($filter_expiry_month)
	{

		$expirymonth = array();

		$expirymonth[] = JHTML::_('select.option',  0,  '-- Select Remaining Expiry Month --'); // cheat to 3 year expiry date

		$expirymonth[] = JHTML::_('select.option',  3,  '3 Month(s) before expiry');
		$expirymonth[] = JHTML::_('select.option',  6,  '6 Month(s) before expiry');
		$expirymonth[] = JHTML::_('select.option',  9,  '9 Month(s) before expiry');
		$expirymonth[] = JHTML::_('select.option',  12,  '1 Year(s) before expiry');

		return JHTML::_('select.genericlist',   $expirymonth, 'filter_expiry_month', 'class="inputbox form-select" size="1" onchange="document.adminForm.submit( );"', 'value', 'text', "$filter_expiry_month");
	}



	public static function getRealViewLevels($user = null)
	{
		$db = Factory::getDbo();
		if ($user === null) {
			$user = Factory::getUser();
		}

		$userGroups = $user->groups;

		if (empty($userGroups)) {
			return [];
		}

		// Truy vấn bảng viewlevels để tìm access level chứa các group này
		$query = $db->getQuery(true)
			->select(['id', 'title', 'rules'])
			->from($db->quoteName('#__viewlevels'));
		$db->setQuery($query);

		$viewlevels = $db->loadObjectList();
		$userViewLevels = [];

		foreach ($viewlevels as $viewlevel) {
			$rules = json_decode($viewlevel->rules, true);
			if (!empty($rules) && array_intersect($userGroups, $rules)) {
				$userViewLevels[] = (int) $viewlevel->id;
			}
		}

		return $userViewLevels;
	}
}
