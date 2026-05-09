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
 * Methods supporting a list of Rmaitems records.
 *
 * @since  1.0.0
 */
class RmaitemsModel extends ListModel
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
				'rma_request_id',
				'a.rma_request_id',
				'rmacode',
				'a.rmacode',
				'warranty_item_id',
				'a.warranty_item_id',
				'product_id',
				'a.product_id',
				'customer_id',
				'a.customer_id',
				'requested_sn',
				'a.requested_sn',
				'replacement_pn',
				'a.replacement_pn',
				'replacement_sn',
				'a.replacement_sn',
				'warranty_status',
				'a.warranty_status',
				'description',
				'a.description',
				'status',
				'a.status',
				'shipping_duration',
				'a.shipping_duration',
				'so_no',
				'a.so_no',
				'invoice_no',
				'a.invoice_no',
				'remarks',
				'a.remarks',
				'replacement_date',
				'a.replacement_date',
				'rma_assigned_date',
				'a.rma_assigned_date',
				'received_date',
				'a.received_date',
				'shipped_date',
				'a.shipped_date',
				'closed_date',
				'a.closed_date',
				'created_date',
				'a.created_date',
				'is_import_csv',
				'a.is_import_csv',
				'created_by',
				'a.created_by',
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



		//ifoundries
		$this->setState('filter.status', $this->getUserStateFromRequest($this->context . '.filter.status', 'filter_status', '', 'cmd'));
		$this->setState('filter.distributor', $this->getUserStateFromRequest($this->context . '.filter.distributor', 'filter_distributor', '', 'string'));
		$this->setState('filter.country', $this->getUserStateFromRequest($this->context . '.filter.country', 'filter_country', '', 'string'));

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
		//ifoundries
		$id .= ':' . $this->getState('filter.country');
		$id .= ':' . $this->getState('filter.status');
		$id .= ':' . $this->getState('filter.distributor');

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
		$query->from('`#__at_rma_items` AS a');

		// Join over the product field 'product_id'
		$query->select('p.product_no,p.model_no');
		$query->join('LEFT', '#__at_products AS `p` ON `p`.id = a.`product_id`');

		// Join over the request field 'rma_request'
		$query->select('r.fullname AS customer_name');
		$query->join('LEFT', '#__at_rma_request AS `r` ON r.id = a.rma_request_id ');

		// Join over the users field 'customer_id'
		$query->select('u.name AS distributor_name');
		$query->join('LEFT', '#__users AS `u` ON a.customer_id = u.customer_id AND u.customer_id != \'\' ');



		// Filter by search in title
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			if (stripos($search, 'id:') === 0) {
				$query->where('a.id = ' . (int) substr($search, 3));
			} else {
				$search = $db->Quote('%' . $db->escape($search, true) . '%');
				$searchConditions = array();
				$searchConditions[] = 'a.rmacode LIKE ' . $search;
				$searchConditions[] = 'p.product_no LIKE ' . $search;
				$searchConditions[] = 'p.model_no LIKE ' . $search;
				$searchConditions[] = 'a.requested_sn LIKE ' . $search;
				$query->where('(' . implode(' OR ', $searchConditions) . ')');
			}
		}


		//ifoundries
		$status = $this->getState('filter.status');

		if ($status) {
			$query->where('a.status=' .  $db->quote($status));
		}

		$country  = $this->getState('filter.country');
		if ($country) {
			$query->where('u.country_id=' . $country);
		}

		$distributor  = $this->getState('filter.distributor');
		if ($distributor) {
			$query->where('a.customer_id=' . $db->quote($distributor));
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

	public function remove($pks)
	{
		PluginHelper::importPlugin('atelesis', 'logs');
		$db = $this->getDbo();
		$user = Factory::getUser();
		if (empty($pks)) {
			return 0;
		}

		$table = $this->getTable('rmaitem');


		$rma_request_idsarr = array();
		$rma_serial_no_arr = array();

		foreach ($pks as $c) :
			$query = " SELECT rma_request_id FROM #__at_rma_items WHERE id = " . $c;
			$db->setQuery($query);
			$rma_request_id = $db->loadResult();
			if (!in_array($rma_request_id, $rma_request_idsarr))
				array_push($rma_request_idsarr, $rma_request_id);
		endforeach;

		$query = " SELECT wi.*,ri.rmacode FROM #__at_rma_items AS ri "
			.	" LEFT JOIN #__at_warranty_items AS wi ON wi.id = ri.warranty_item_id "
			.	" WHERE ri.id IN (" . implode(',', $pks) . ") ";
		$db->setQuery($query);
		$items = $db->loadObjectList();

		$query = $db->getQuery(true);
		$query->delete($db->quoteName('#__at_rma_items'))
			->where($db->quoteName('id') . ' IN (' . implode(',', $pks) . ')');

		$db->setQuery($query);
		$db->execute();


		foreach ($items as $item) :
			$dispatcher = Factory::getApplication()->getDispatcher();
			$log = new \stdClass();
			$log->section = 'RMA_ITEM';
			$log->action_type = 'DELETE';
			$log->action_by = $user->id;
			$log->action_remarks = 'Delete RMA Item : RMA #' . ($item->rmacode ? $item->rmacode : 'N/A');
			$log->id =	'';

			$before_update = json_encode($item);
			$after_update = json_encode(array());

			$event = new \Joomla\Event\Event('onAfterAction', [
				'log' => $log,
				'before' => $before_update,
				'after' => $after_update,
			]);
			$dispatcher->dispatch('onAfterAction', $event);
		endforeach;

		// Remove Files (if any) and 
		$query = " SELECT * FROM #__at_rma_downloads WHERE rma_item_id IN (" . implode(',', $pks) . ")";
		$db->setQuery($query);
		$downloads = $db->loadObjectList();


		$query = $db->getQuery(true);
		$query->delete($db->quoteName('#__at_rma_downloads'))
			->where($db->quoteName('rma_item_id') . ' IN (' . implode(',', $pks) . ')');
		$db->setQuery($query);
		$db->execute();

		foreach ($downloads as $d) {
			unlink(JPATH_ADMINISTRATOR . "/atelesis_docs/" . $d->filename);
		}

		return true;
	}
}
