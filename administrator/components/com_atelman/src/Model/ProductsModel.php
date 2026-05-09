<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Atelman
 * @author     iFoundries <wpsub@ifoundries.com>
 * @copyright  2025 iFoundries
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Atelman\Component\Atelman\Administrator\Model;
// No direct access.
defined('_JEXEC') or die;

use \Joomla\CMS\MVC\Model\ListModel;
use \Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Helper\TagsHelper;
use \Joomla\Database\ParameterType;
use \Joomla\Utilities\ArrayHelper;
use Atelman\Component\Atelman\Administrator\Helper\AtelmanHelper;
use \Joomla\CMS\Plugin\PluginHelper;

/**
 * Methods supporting a list of Products records.
 *
 * @since  1.0.0
 */
class ProductsModel extends ListModel
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see        JController
	 * @since      1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id',
				'a.id',
				'product_no',
				'a.product_no',
				'model_no',
				'a.model_no',
				'product_name',
				'a.product_name',
				'warranty',
				'a.warranty',
				'is_previous3years',
				'a.is_previous3years',
				'created_date',
				'a.created_date',
			);
		}

		parent::__construct($config);
	}








	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   Elements order
	 * @param   string  $direction  Order direction
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// List state information.
		parent::populateState('id', 'ASC');

		$context = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $context);

		// Split context into component and optional section
		if (!empty($context)) {
			$parts = FieldsHelper::extract($context);

			if ($parts) {
				$this->setState('filter.component', $parts[0]);
				$this->setState('filter.section', $parts[1]);
			}
		}
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return  string A store id.
	 *
	 * @since   1.0.0
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.state');


		return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  DatabaseQuery
	 *
	 * @since   1.0.0
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'DISTINCT a.*'
			)
		);
		$query->from('`#__at_products` AS a');



		// Filter by search in title
		$search = $this->getState('filter.search');

		if (!empty($search)) {

			if (stripos($search, 'id:') === 0) {
				$query->where('a.id = ' . (int) substr($search, 3));
			} else {
				$search = $db->Quote('%' . $db->escape($search, true) . '%');
				$searchConditions = array();
				$searchConditions[] = 'a.product_no LIKE ' . $search;
				$searchConditions[] = 'a.model_no LIKE ' . $search;
				$searchConditions[] = 'a.product_name LIKE ' . $search;
				$query->where('(' . implode(' OR ', $searchConditions) . ')');
			}
		}

		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering', 'id');
		$orderDirn = $this->state->get('list.direction', 'ASC');

		if ($orderCol && $orderDirn) {
			$query->order($db->escape($orderCol . ' ' . $orderDirn));
		}

		return $query;
	}

	/**
	 * Get an array of data items
	 *
	 * @return mixed Array of data items on success, false on failure.
	 */
	public function getItems()
	{
		$items = parent::getItems();


		return $items;
	}


	public function getSerialNoByKeyword($keyword = '')
	{
		if (empty($keyword)) return false;

		$db = Factory::getContainer()->get('DatabaseDriver');
		$user = Factory::getUser();

		$query = " SELECT customer_id, country_id FROM #__users WHERE id = " . $user->id;
		$db->setQuery($query);
		$tmpobj = $db->loadObject();

		$customer_id 		= 	$tmpobj->customer_id;
		$country_id 		= 	$tmpobj->country_id;
		$group_id 			= 	$user->gid; // 23	= Manager, 24 = Distributor

		$where = array();

		if ($group_id == 23) { // Manager can see Distributor on that country

			$query = " SELECT customer_id FROM #__users WHERE country_id = " . $country_id;
			$db->setQuery($query);
			$arrtmp = $db->loadResultArray();

			$where[] = ' w.customer_id IN (' . $db->Quote(implode('\',\'', $arrtmp), false) . ') ';
		} else if ($group_id == 24) {

			$where[] = ' w.customer_id = ' . $db->Quote($customer_id, false);
		}

		$keyword = strtolower($keyword);
		$keywordEscaped = $db->Quote($db->escape($keyword, true) . '%', false);

		$serial_no_qry = " (CASE "
			.	" WHEN w.serial_no_2 != '' THEN LOWER(w.serial_no_2) "
			.	" WHEN w.serial_no != '' THEN LOWER(w.serial_no) "
			.	" ELSE 0 "
			. " END) ";

		$where_case = $serial_no_qry . " LIKE " . $keywordEscaped;

		$where[] = $where_case;

		// get latest purchase_date
		//$where_case2 = " (CASE "
		//.	" WHEN serial_no_2 != '' THEN LOWER(serial_no_2) "
		//.	" WHEN serial_no != '' THEN LOWER(serial_no) "
		//.	" ELSE 0 "
		//. " END) LIKE ".$keywordEscaped;

		//$where[] = " w.purchase_date = ( SELECT MAX(purchase_date) FROM #__at_warranty_items WHERE ".$where_case2." ) ";

		$where = (count($where) ? ' WHERE (' . implode(') AND (', $where) . ')' : '');

		$query = " SELECT w.* , p.product_no, p.model_no, ( SELECT r2.rmacode FROM #__at_rma_items AS r2 WHERE r2.customer_id = w.customer_id AND r2.invoice_no = w.invoice_no ORDER BY r2.created_date DESC LIMIT 1) AS previous_rma_number "
			.	"	FROM #__at_warranty_items AS w "
			.	" LEFT JOIN #__at_products AS p ON p.id = w.product_id "
			. $where
			. " LIMIT 30 ";

		$db->setQuery($query);
		$rows = $db->loadObjectList();

		return $rows;
	}


	public function remove($pks)
	{
		PluginHelper::importPlugin('atelesis', 'logs');
		$db = $this->getDbo();
		$user = Factory::getUser();
		if (empty($pks)) {
			return 0;
		}

		$table = $this->getTable('Product');
		$count = 0;
		foreach ($pks as $id) {
			if ($table->load($id)) {
				if ($table->delete($id)) {
					$dispatcher = Factory::getApplication()->getDispatcher();
					$log = new \stdClass();
					$log->section = 'PRODUCT_ITEM';
					$log->action_type = 'DELETE';
					$log->action_by = $user->id;
					$log->action_remarks = 'Delete Product - ' . $table->model_no . ' (' . $table->product_no . ') ';
					$log->id = '';
					$before_update = json_encode($table);
					$after_update = json_encode(array());

					$event = new \Joomla\Event\Event('onAfterAction', [
						'log' => $log,
						'before' => $before_update,
						'after' => $after_update,
					]);

					$dispatcher->dispatch('onAfterAction', $event);
					$count++;
				}
			}
		}

		return $count;
	}
}
