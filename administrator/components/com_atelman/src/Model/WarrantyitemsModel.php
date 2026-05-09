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
 * Methods supporting a list of Warrantyitems records.
 *
 * @since  1.0.0
 */
class WarrantyitemsModel extends ListModel
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
				'warranty_id',
				'a.warranty_id',
				'customer_id',
				'a.customer_id',
				'product_id',
				'a.product_id',
				'serial_no',
				'a.serial_no',
				'serial_no_2',
				'a.serial_no_2',
				'replacement_pn',
				'a.replacement_pn',
				'po_no',
				'a.po_no',
				'so_no',
				'a.so_no',
				'invoice_no',
				'a.invoice_no',
				'purchase_date',
				'a.purchase_date',
				'comments',
				'a.comments',
				'expired_date',
				'a.expired_date',
				'expired_date_manual',
				'a.expired_date_manual',
				'extended_warranty',
				'a.extended_warranty',
				'extended_expired_date',
				'a.extended_expired_date',
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

		//ifoundries
		$this->setState('filter.expiry_month', $this->getUserStateFromRequest($this->context . '.filter.expiry_month', 'filter_expiry_month', '', 'cmd'));
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
		$id .= ':' . $this->getState('filter.distributor');
		$id .= ':' . $this->getState('filter.expiry_month');
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
		$currentUser	= Factory::getUser();
		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.id,a.serial_no,a.serial_no_2,a.extended_warranty,a.expired_date,a.extended_expired_date,a.customer_id,a.replacement_pn'
			)
		);
		// $query->select([
		// 	'a.id',
		// 	'a.serial_no',
		// 	'a.serial_no_2',
		// 	'a.extended_warranty',
		// 	'a.expired_date',
		// 	'a.extended_expired_date',
		// 	'a.customer_id',
		// 	'a.replacement_pn',

		// 	'p.product_no',
		// 	'p.model_no',
		// 	'p2.model_no AS model_no2',

		// 	'u.name AS company_name',
		// 	'u.address AS company_address',
		// 	'u.username',

		// 	'c.country',
		// 	'COALESCE(
		// 		NULLIF(a.expired_date_manual, "0000-00-00 00:00:00"),
		// 		NULLIF(a.extended_expired_date, "0000-00-00 00:00:00"),
		// 		NULLIF(a.expired_date, "0000-00-00 00:00:00")
		// 	) AS ' . $db->quoteName('real_expiry_date')
		// ]);

		// main table
		$query->from($db->quoteName('#__at_warranty_items', 'a'));

		// JOIN table
		$query->select('p.product_no,p.model_no');
		$query->join('LEFT', $db->quoteName('#__at_products', 'p') . ' ON p.id = a.product_id');

		$query->select('p2.model_no AS model_no2');
		$query->join('LEFT', $db->quoteName('#__at_products', 'p2') . ' ON p2.product_no = a.replacement_pn');

		$query->select('u.name AS company_name,u.address AS company_address,u.username');
		$query->join('LEFT', $db->quoteName('#__users', 'u') . ' ON u.customer_id = a.customer_id');

		$query->select('c.country,COALESCE(
		a.expired_date_manual,
		a.extended_expired_date,
		a.expired_date
		) AS real_expiry_date');
		$query->join('LEFT', $db->quoteName('#__at_countries', 'c') . ' ON c.id = u.country_id');


		if ($currentUser->gid == 24) { // distributor, see his own data
			$query->where('a.customer_id = ' . $currentUser->customer_id);
			$query->where('u.country_id = ' . $currentUser->country_id);
		}

		if ($currentUser->gid == 23) { // Manager, can his own country, and can see other data within country but diff user
			$query->where(' u.country_id = ' . $currentUser->country_id);
		}
		// Filter by search in title

		$search = $this->getState('filter.search');
		if (!empty($search)) {
			if (stripos($search, 'id:') === 0) {
				$query->where('a.id = ' . (int) substr($search, 3));
			} else {
				$search = $db->Quote('%' . $db->escape($search, true) . '%');
				$searchConditions = array();
				$searchConditions[] = 'a.so_no LIKE ' . $search;
				$searchConditions[] = 'a.po_no LIKE ' . $search;
				$searchConditions[] = 'p.product_no LIKE ' . $search;
				$searchConditions[] = 'p.model_no LIKE ' . $search;
				$searchConditions[] = 'a.serial_no LIKE ' . $search;
				$searchConditions[] = 'a.serial_no_2 LIKE ' . $search;
				$query->where('(' . implode(' OR ', $searchConditions) . ')');
			}
		}

		//ifoundries
		$expiry_month = $this->getState('filter.expiry_month');
		if ($expiry_month) {
			$condition = [
				'(a.expired_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ' . $expiry_month . ' MONTH))',
				'(a.expired_date_manual BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ' . $expiry_month . ' MONTH))',
				'(a.extended_expired_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ' . $expiry_month . ' MONTH))'
			];

			$query->where('(' . implode(' OR ', $condition) . ')');
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
		if ($orderCol == "id") {
			$orderCol = "a.id";
		}

		if ($orderCol && $orderDirn) {
			//$query->order($db->escape($orderCol . ' ' . $orderDirn));
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
		if (empty($pks))
			return false;
		PluginHelper::importPlugin('atelesis', 'logs');
		$db = Factory::getDBO();
		$user = Factory::getUser();

		$warranty_idsarr = array();
		$warranty_items_arr = array();

		foreach ($pks as $c) :
			$query = " SELECT * FROM #__at_warranty_items WHERE id = " . $c;
			$db->setQuery($query);
			$mps = $db->loadObject();

			$warranty_id = $mps->warranty_id;
			$warranty_items_arr[] = $mps;

			if (!in_array($warranty_id, $warranty_idsarr))
				array_push($warranty_idsarr, $warranty_id);

		endforeach;

		$query = " SELECT * FROM #__at_warranty_items WHERE id IN (" . implode(',', $pks) . ") ";
		$db->setQuery($query);
		$items = $db->loadObjectList();

		$query = $db->getQuery(true);
		$query->delete($db->quoteName('#__at_warranty_items'))
			->where($db->quoteName('id') . ' IN (' . implode(',', $pks) . ')');

		$db->setQuery($query);
		$db->execute();

		foreach ($items as $item) :
			$dispatcher = Factory::getApplication()->getDispatcher();
			$log = new \stdClass();
			$log->section = 'WARRANTY_REG_ITEM';
			$log->action_type = 'DELETE';
			$log->action_by = $user->id;
			$log->action_remarks = 'Delete Warranty Registration Item : ' . $item->serial_no;  // must have serial number
			$log->id = '';
			$before_update	 			= json_encode($item);
			$after_update 				= json_encode(array());
			$event = new \Joomla\Event\Event('onAfterAction', [
				'log' => $log,
				'before' => $before_update,
				'after' => $after_update,
			]);
			$dispatcher->dispatch('onAfterAction', $event);
		endforeach;
		// if warranty items still exist on warranty_reg, for housekeeping
		foreach ($warranty_idsarr as $wid) :
			$query = " SELECT * FROM #__at_warranty_items WHERE warranty_id = " . $wid;
			$db->setQuery($query);
			$exist = $db->loadObjectList();
			if (!$exist) {

				$query = $db->getQuery(true);
				$query->delete($db->quoteName('#__at_warranty_register'))
					->where($db->quoteName('id') . ' = ' . $wid);

				$db->setQuery($query);
				$db->execute();
			}
		endforeach;
		return true;
	}
}
