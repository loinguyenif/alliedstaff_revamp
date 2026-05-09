<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Atelman
 * @author     iFoundries <wpsub@ifoundries.com>
 * @copyright  2025 iFoundries
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Atelman\Component\Atelman\Site\Helper;

defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\MVC\Model\BaseDatabaseModel;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Object\CMSObject;
use Joomla\CMS\HTML\HTMLHelper as JHtml;

/**
 * Class AtelmanFrontendHelper
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
	 * Gets the edit permission for an user
	 *
	 * @param   mixed  $item  The item
	 *
	 * @return  bool
	 */
	public static function canUserEdit($item)
	{
		$permission = false;
		$user       = Factory::getApplication()->getIdentity();

		if ($user->authorise('core.edit', 'com_atelman') || (isset($item->created_by) && $user->authorise('core.edit.own', 'com_atelman') && $item->created_by == $user->id) || $user->authorise('core.create', 'com_atelman')) {
			$permission = true;
		}

		return $permission;
	}

	public function getWorldCountryHTML()
	{

		$db = &JFactory::getDBO();

		$query = " SELECT * FROM #__at_world_countries ";
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$options = array();
		$options[]	= JHTML::_('select.option', '', '-- Select Country --');
		foreach ($rows as $r) :
			$options[] 	= 	JHTML::_('select.option', $r->country_code, $r->country_name);
		endforeach;

		return JHTML::_('select.genericlist', $options, 'country', ' ', 'value', 'text');
	}

	public function getCountryHTML($row_id)
	{

		$db = Factory::getDBO();

		$query = " SELECT * FROM #__at_countries ORDER by country ASC ";
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$options = array();
		$options[]	= JHTML::_('select.option', '', '-- Select Country --');
		foreach ($rows as $r) :
			$options[] 	= 	JHTML::_('select.option', $r->id, $r->country);
		endforeach;

		return JHTML::_('select.genericlist', $options, 'purchase_country[]', ' class="required" onchange="javascript:searchCompanyBasedOnCountry(this.value, ' . $row_id . ')" ', 'value', 'text', '');
	}

	public function getCompanyHTML($row_id)
	{

		$db = &JFactory::getDBO();

		$query = " SELECT * FROM #__users WHERE gid = 24 ORDER BY name ASC ;";
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$options = array();
		$options[]	= JHTML::_('select.option', '', '-- Select Company --');

		foreach ($rows as $r) :
			$options[] 	= 	JHTML::_('select.option', $r->id, $r->name);
		endforeach;

		return JHTML::_('select.genericlist', $options, 'purchase_from[]', ' class="required" ', 'value', 'text');
	}

	public function getItemById($section, $id = 0)
	{

		if (!$id)
			return false;

		$db = JFactory::getDBO();

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

	public function getProductByPartNumber($part_no)
	{

		if (!$part_no)
			return false;

		$db = JFactory::getDBO();

		$query = " SELECT * FROM #__at_products WHERE product_no = '$part_no'  ";

		$db->setQuery($query);
		$row = $db->loadObject();

		return $row;
	}

	public function getRMAStatusTextArray()
	{
		$db = JFactory::getDBO();

		$query = " SELECT * FROM #__at_rma_status ORDER BY ordering ASC ";
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$tmp = array();
		foreach ($rows as $r) :
			$tmp[$r->status_code] = $r->status_name;
		endforeach;

		return $tmp;
	}

	public function validateEMAIL($email)
	{
		$result = preg_match("/[a-zA-Z0-9_-.+]+@[a-zA-Z0-9-]+.[a-zA-Z]+/i", $email);
		return $result;
	}
}
