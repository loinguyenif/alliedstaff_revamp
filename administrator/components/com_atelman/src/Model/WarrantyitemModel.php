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

use Atelman\Component\Atelman\Administrator\Helper\AtelmanHelper;
use \Joomla\CMS\Table\Table;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Plugin\PluginHelper;
use \Joomla\CMS\MVC\Model\AdminModel;
use \Joomla\CMS\Helper\TagsHelper;
use \Joomla\CMS\Filter\OutputFilter;
use \Joomla\CMS\Event\Model;
use Joomla\CMS\Event\AbstractEvent;
use \Joomla\Event\Event;
use stdClass;

/**
 * Warrantyitem model.
 *
 * @since  1.0.0
 */
class WarrantyitemModel extends AdminModel
{
	/**
	 * @var    string  The prefix to use with controller messages.
	 *
	 * @since  1.0.0
	 */
	protected $text_prefix = 'COM_ATELMAN';

	/**
	 * @var    string  Alias to manage history control
	 *
	 * @since  1.0.0
	 */
	public $typeAlias = 'com_atelman.warrantyitem';

	/**
	 * @var    null  Item data
	 *
	 * @since  1.0.0
	 */
	protected $item = null;




	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   string  $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  Table    A database object
	 *
	 * @since   1.0.0
	 */
	public function getTable($type = 'Warrantyitem', $prefix = 'Administrator', $config = array())
	{
		return parent::getTable($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      An optional array of data for the form to interogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  \JForm|boolean  A \JForm object on success, false on failure
	 *
	 * @since   1.0.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app = Factory::getApplication();

		// Get the form.
		$form = $this->loadForm(
			'com_atelman.warrantyitem',
			'warrantyitem',
			array(
				'control' => 'jform',
				'load_data' => ''
			)
		);



		if (empty($form)) {
			return false;
		}

		return $form;
	}



	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since   1.0.0
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = Factory::getApplication()->getUserState('com_atelman.edit.warrantyitem.data', array());

		if (empty($data)) {
			if ($this->item === null) {
				$this->item = $this->getItem();
			}

			$data = $this->item;
		}

		return $data;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed    Object on success, false on failure.
	 *
	 * @since   1.0.0
	 */
	public function getItem($pk = null)
	{

		$pk = (int) ($pk ?: $this->getState($this->getName() . '.id'));
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query
			->select([
				$db->quoteName('w') . '.*',
				$db->quoteName('w2') . '.*',
				$db->quoteName('w.id', 'warranty_item_id'),
				$db->quoteName('p.product_no'),
				$db->quoteName('p.model_no'),
				$db->quoteName('p.warranty'),
				$db->quoteName('wc.country_name', 'country_name_3'),
				$db->quoteName('u.name', 'customer_name'),
				$db->quoteName('u.id', 'customer_user_id'),
				$db->quoteName('u.is_internal'),
				$db->quoteName('p.is_previous3years'),
			])
			->from($db->quoteName('#__at_warranty_items', 'w'))
			->join('LEFT', $db->quoteName('#__at_warranty_register', 'w2') . ' ON ' . $db->quoteName('w2.id') . ' = ' . $db->quoteName('w.warranty_id'))
			->join('LEFT', $db->quoteName('#__at_products', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('w.product_id'))
			->join('LEFT', $db->quoteName('#__at_world_countries', 'wc') . ' ON ' . $db->quoteName('wc.country_code') . ' = ' . $db->quoteName('w2.country'))
			->join('LEFT', $db->quoteName('#__users', 'u') . ' ON ' . $db->quoteName('w.customer_id') . ' = ' . $db->quoteName('u.customer_id'))
			->where($db->quoteName('w.id') . ' = ' . (int) $pk);

		$db->setQuery($query);

		try {
			$item = $db->loadObject();

			if (empty($item)) {
				return false;
			}
		} catch (\RuntimeException $e) {
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
			return false;
		}

		return $item;
	}

	/**
	 * Method to duplicate an Warrantyitem
	 *
	 * @param   array  &$pks  An array of primary key IDs.
	 *
	 * @return  boolean  True if successful.
	 *
	 * @throws  Exception
	 */
	public function duplicate(&$pks)
	{
		$app = Factory::getApplication();
		$user = $app->getIdentity();
		$dispatcher = $this->getDispatcher();

		// Access checks.
		if (!$user->authorise('core.create', 'com_atelman')) {
			throw new \Exception(Text::_('JERROR_CORE_CREATE_NOT_PERMITTED'));
		}

		$context    = $this->option . '.' . $this->name;

		// Include the plugins for the save events.
		PluginHelper::importPlugin($this->events_map['save']);

		$table = $this->getTable();

		foreach ($pks as $pk) {

			if ($table->load($pk, true)) {
				// Reset the id to create a new record.
				$table->id = 0;

				if (!$table->check()) {
					throw new \Exception($table->getError());
				}


				// Create the before save event.
				$beforeSaveEvent = AbstractEvent::create(
					$this->event_before_save,
					[
						'context' => $context,
						'subject' => $table,
						'isNew'   => true,
						'data'    => $table,
					]
				);

				// Trigger the before save event.
				$dispatchResult = Factory::getApplication()->getDispatcher()->dispatch($this->event_before_save, $beforeSaveEvent);

				// Check if dispatch result is an array and handle accordingly
				$result = isset($dispatchResult['result']) ? $dispatchResult['result'] : [];

				// Proceed with your logic
				if (in_array(false, $result, true) || !$table->store()) {
					throw new \Exception($table->getError());
				}

				// Trigger the after save event.
				Factory::getApplication()->getDispatcher()->dispatch(
					$this->event_after_save,
					AbstractEvent::create(
						$this->event_after_save,
						[
							'context'    => $context,
							'subject'    => $table,
							'isNew'      => true,
							'data'       => $table,
						]
					)
				);
			} else {
				throw new \Exception($table->getError());
			}
		}

		// Clean cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @param   Table  $table  Table Object
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	protected function prepareTable($table)
	{
		jimport('joomla.filter.output');

		if (empty($table->id)) {
			// Set ordering to the last item if not set
			if (@$table->ordering === '') {
				$db = $this->getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__at_warranty_items');
				$max             = $db->loadResult();
				$table->ordering = $max + 1;
			}
		}
	}



	public function save($post)
	{

		$db = Factory::getDBO();
		$atelwarrantyitem 	= $this->getTable('Warrantyitem');
		$atelproduct		= $this->getTable('Product');
		$atelwarrantyhistory = $this->getTable('Warrantyhistory');

		$row = new stdClass();

		if ($post['product_no']) {
			$checkId = AtelmanHelper::checkRowProduct($post['product_no']);
			$atelwarrantyitem->product_id = $checkId;
		} else {
			return false;
		}

		if ($post['replacement_pn']) {
			$query = " SELECT id FROM #__at_products WHERE product_no = " . $db->Quote($post['replacement_pn'], false);
			$db->setQuery($query);

			if (!$db->loadResult()) {
				$post['replacement_pn'] = '';
			}
		}


		if (!$atelwarrantyitem->bind($post)) {
			return false;
		}

		$atelwarrantyitem->id = $post['cid']; // if exist, it updates, otherwise, save new input

		$user = Factory::getUser();

		$query = " SELECT is_internal FROM #__users WHERE customer_id = '" . $post['customer_id'] . "' ";
		$db->setQuery($query);
		$is_internal = $db->loadResult();

		// How many months I get based on Is_Internal Customer, and July 1st 2010
		$product_month = 0;

		$post['purchase_date'] = str_replace("/", "-", $post['purchase_date']);
		$post['expired_date'] = str_replace("/", "-", $post['expired_date']);
		$post['expired_date_manual'] = str_replace("/", "-", $post['expired_date_manual']);

		if ($is_internal == 1) {

			$product_month = 12;
		} else {

			// Time for July 1st,2010.
			$timeon_1stjuly2010 = mktime(0, 0, 0, 7, 1, 2010);
			$timeon_purchasedate = strtotime($post['purchase_date']);

			$product_month = $row->warranty;

			//	if purchase_date before 1st july 2010, not including 1st july 2010, just based on products warranty
			if ($timeon_purchasedate < $timeon_1stjuly2010) {

				if ($row->is_previous3years) {
					$product_month = 36;
				} else {
					$product_month = $row->warranty;
				}
			} else {

				if (intval($row->warranty) == 36)
					$product_month = 60;
			}
		}

		// set Expiry Date based on Product Warranty, and New Warranty Registration
		if (!$post['cid']) {
			$atelwarrantyitem->created_date = date("Y-m-d H:i:s");
			$tmp 	= 	explode('-', date("Y-m-d", strtotime($post['purchase_date'])));
			$year 	= 	(int) $tmp[0];
			$month 	= 	(int) $tmp[1];
			$day	=	(int) $tmp[2] - 1;

			$atelwarrantyitem->purchase_date = date("Y-m-d", strtotime($post['purchase_date']));

			$atelwarrantyitem->expired_date_manual = ($post['expired_date_manual']) ? date("Y-m-d", strtotime($post['expired_date_manual'])) : '0000-00-00';

			$atelwarrantyitem->expired_date = date("Y-m-d", mktime(0, 0, 0, $month + $product_month, $day, $year));

			$atelwarrantyitem->extended_expired_date = date("Y-m-d", mktime(0, 0, 0, ($month + $product_month + (int) $post['extended_warranty']), $day, $year));
		} else { // EDIT , UPDATING

			$atelwarrantyitem->purchase_date = date("Y-m-d", strtotime($post['purchase_date']));

			$atelwarrantyitem->expired_date = date("Y-m-d", strtotime($post['expired_date']));

			$atelwarrantyitem->expired_date_manual = ($post['expired_date_manual']) ? date("Y-m-d", strtotime($post['expired_date_manual'])) : '0000-00-00';

			$year = 0;
			$month = 0;
			$day = 0;

			$tmp 	= 	explode('-', date("Y-m-d", strtotime($post['expired_date'])));
			$year 	= 	(int) $tmp[0];
			$month 	= 	(int) $tmp[1];
			$day	=	(int) $tmp[2];

			if ($post['extended_warranty'])
				$atelwarrantyitem->extended_expired_date = date("Y-m-d", mktime(0, 0, 0, $month + $atelwarrantyitem->extended_warranty, $day, $year));
			else
				$atelwarrantyitem->extended_expired_date = '0000-00-00';
		}

		$item = array();

		if ($post['cid']) {
			$query = " SELECT * FROM #__at_warranty_items WHERE id = " . $db->quote($post['cid'], false);
			$db->setQuery($query);
			$item = $db->loadObject();
			$atelwarrantyitem->warranty_id = $item->warranty_id;
			$atelwarrantyitem->created_date = $item->created_date;
		}

		if ($atelwarrantyitem->store()) {

			if ($post['replacement_pn'] != '' || $post['serial_no_2'] != '') {
				$atelwarrantyhistory->id = '';
				$atelwarrantyhistory->warranty_id = $post['cid'];
				$atelwarrantyhistory->serial_no_2 = $post['serial_no_2'];
				$atelwarrantyhistory->replacement_pn = $post['replacement_pn'];
				$atelwarrantyhistory->created_date = date("Y-m-d H:i:s");

				$result = $atelwarrantyhistory->store();
			}

			PluginHelper::importPlugin('atelesis', 'logs');
			$dispatcher = Factory::getApplication()->getDispatcher();
			$log = new stdClass();
			$log->section = 'WARRANTY_REG_ITEM';
			if ($post['cid']) {
				$log->action_type = 'EDIT_SAVE';
			} else {
				$log->action_type = 'NEW_SAVE';
			}
			$log->action_by = $user->id;
			$log->action_remarks = 'Saving Warranty Registration Item on Serial No. : ' . (($post['serial_no']) ? $post['serial_no'] : 'N/A');
			$log->id = (($item->id) ? $item->id : $atelwarrantyitem->id);

			$before_update = json_encode($item);
			$after_update = json_encode($post);

			// Fire the onAfterStoreUser trigger
			$event = new \Joomla\Event\Event('onAfterAction', [
				'log' => $log,
				'before' => $before_update,
				'after' => $after_update,
			]);

			$dispatcher->dispatch('onAfterAction', $event);

			return $atelwarrantyitem;
		}

		return false;
	}

	public function check_acl($cid)
	{

		$user 	= Factory::getUser();
		$db		= Factory::getDBO();

		// check user_id acl
		$query = " SELECT gcox.company_id, GROUP_CONCAT(CAST(gcx.country_id AS CHAR)) as country_ids FROM #__at_group_xref AS gx "
			.	" LEFT JOIN #__at_group_company_xref AS gcox ON gcox.group_id = gx.group_id "
			.	" LEFT JOIN #__at_group_country_xref AS gcx ON gcx.group_id = gx.group_id "
			.	" WHERE gx.user_id = " . $user->id
			.	" GROUP BY gx.user_id ";

		$db->setQuery($query);
		$user_acl = $db->loadObject();

		$query = " SELECT * FROM #__at_warranty_items AS wi "
			.	" WHERE wi.id = " . $cid;

		$db->setQuery($query);
		$witems = $db->loadObject();

		$country_arr = explode(",", $user_acl->country_ids);

		// check company and country , check user_id
		if ($user_acl->company_id == $witems->purchase_from && in_array($witems->purchase_country, $country_arr))
			return true;

		return false;
	}
}
