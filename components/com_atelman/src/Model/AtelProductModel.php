<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Atelman
 * @author     iFoundries <wpsub@ifoundries.com>
 * @copyright  2025 iFoundries
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Atelman\Component\Atelman\Site\Model;
// No direct access.
defined('_JEXEC') or die;

use Atelman\Component\Atelman\Administrator\Helper\AtelmanHelper;
use \Joomla\CMS\Factory;
use \Joomla\Utilities\ArrayHelper;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Table\Table;
use \Joomla\CMS\MVC\Model\FormModel;
use \Joomla\CMS\Object\CMSObject;
use \Joomla\CMS\Helper\TagsHelper;

/**
 * Atelman model.
 *
 * @since  1.0.0
 */
class AtelProductModel extends FormModel
{
	private $item = null;



	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm(
			'com_atelman.rmaitem',
			'rmaitemform',
			array(
				'control'   => 'jform',
				'load_data' => $loadData
			)
		);

		if (empty($form)) {
			return false;
		}

		return $form;
	}

	public function getProductsByKeyword($keyword = '')
	{

		if (empty($keyword))
			return false;

		$db = Factory::getDBO();

		$where = array();

		$keyword = strtolower($keyword);
		$keywordEscaped = $db->quote($db->escape($keyword, true) . '%', false);
		$where[] = ' LOWER(product_no) LIKE ' . $keywordEscaped . ' OR LOWER(model_no) LIKE ' . $keywordEscaped . ' OR LOWER(product_name) LIKE ' . $keywordEscaped;

		$where = (count($where) ? ' WHERE (' . implode(') AND (', $where) . ')' : '');

		$query = " SELECT * FROM #__at_products " . $where . ' LIMIT 30 ';
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		return $rows;
	}


	public function getSerialNoByKeyword($keyword = '')
	{

		if (empty($keyword)) return false;

		$db = Factory::getDBO();

		$where = array();

		$keyword = strtolower($keyword);
		$keywordEscaped = $db->quote($db->escape($keyword, true) . '%', false);

		$where[] = ' LOWER(w.serial_no) LIKE ' . $keywordEscaped;

		$where = (count($where) ? ' WHERE (' . implode(') AND (', $where) . ')' : '');

		$query = " SELECT w.*, p.product_no, p.model_no FROM #__at_warranty_items AS w "
			.	" LEFT JOIN #__at_products AS p ON p.id = w.product_id "
			. $where
			. ' LIMIT 30 ';
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		return $rows;
	}
}
