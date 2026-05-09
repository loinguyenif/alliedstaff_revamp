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

/**
 * Methods supporting a list of Servicecontracts records.
 *
 * @since  1.0.0
 */
class ServicecontractsModel extends ListModel
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
				'user_id',
				'a.user_id',
				'service_type',
				'a.service_type',
				'expiry_date',
				'a.expiry_date',
				'service_contract_no',
				'a.service_contract_no',
				'cover_length',
				'a.cover_length',
				'po_no',
				'a.po_no',
				'client_name',
				'a.client_name',
				'customer_id',
				'a.customer_id',
				'remarks',
				'a.remarks',
				'reminder1',
				'a.reminder1',
				'start_date',
				'a.start_date',
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
		$this->setState('filter.expiry', $this->getUserStateFromRequest($this->context . '.filter.expiry', 'filter_expiry', '', 'cmd'));
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
		$id .= ':' . $this->getState('filter.expiry');
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
		$currentUser	= Factory::getUser();
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'DISTINCT a.*'
			)
		);
		$query->from('`#__at_service_contract` AS a');
		// Join over the service_contract_product_xref
		$query->select('cp.*');
		$query->join('LEFT', '#__at_service_contract_product_xref AS `cp` ON `cp`.service_contract_id = a.`id`');

		// Join over the user field 'user_id'
		$query->select('CASE WHEN a.customer_id != \'\' THEN (SELECT u.name FROM #__users AS u WHERE u.customer_id = a.customer_id LIMIT 1) ELSE \'\' END AS distributor_name  , cp.id AS pid');
		$query->join('LEFT', '#__users AS `u` ON `u`.customer_id = a.`customer_id`');



		// Filter by search in title
		$search = $this->getState('filter.search');

		if (!empty($search)) {
			if (stripos($search, 'id:') === 0) {
				$query->where('a.id = ' . (int) substr($search, 3));
			} else {
				$search = $db->Quote('%' . $db->escape($search, true) . '%');
				$searchConditions = array();
				$searchConditions[] = 'a.service_contract_no LIKE ' . $search;
				$searchConditions[] = 'cp.part_no LIKE ' . $search;
				$searchConditions[] = 'cp.model_no LIKE ' . $search;
				$searchConditions[] = 'cp.serial_no LIKE ' . $search;
				$query->where('(' . implode(' OR ', $searchConditions) . ')');
			}
		}

		if ($currentUser->gid == 24) { // distributor, see his own data
			$query->where(' a.customer_id LIKE ' . $db->quote($currentUser->customer_id));
		}
		if ($currentUser->gid == 23) { // Manager, can see his own country, and can see other data within country but diff user
			$query1 = " SELECT GROUP_CONCAT('\'',customer_id,'\'') FROM #__users WHERE country_id = " . $currentUser->country_id . " ";
			$db->setQuery($query1);
			$result = $db->loadResult();

			$query->where(' a.customer_id IN (' . $result . ')');
		}

		$country  = $this->getState('filter.country');
		if ($country) {
			$query->where('u.country_id=' . $country);
		}

		$distributor  = $this->getState('filter.distributor');
		if ($distributor) {
			$query->where('a.customer_id=' . $db->quote($distributor));
		}

		$expiry  = $this->getState('filter.expiry');
		if ($expiry == 1) {
			$query->where('a.expiry_date > ' . $db->Quote(date("Y-m-d", time()), false));
			$query->where('a.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 6 MONTH)');
			// order asc
			$query->order('a.expiry_date ASC');
		}


		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering', 'a.id');
		$orderDirn = $this->state->get('list.direction', 'ASC');
		if ($orderCol == "id") {
			$orderCol = "a.id";
		}
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
}
