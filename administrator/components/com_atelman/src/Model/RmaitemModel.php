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

use \Joomla\CMS\Table\Table;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Plugin\PluginHelper;
use \Joomla\CMS\MVC\Model\AdminModel;
use \Joomla\CMS\Helper\TagsHelper;
use \Joomla\CMS\Filter\OutputFilter;
use \Joomla\CMS\Event\Model;
use Joomla\CMS\Event\AbstractEvent;
use Joomla\Component\Plugins\Administrator\Helper\PluginsHelper;
use stdClass;

/**
 * Rmaitem model.
 *
 * @since  1.0.0
 */
class RmaitemModel extends AdminModel
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
	public $typeAlias = 'com_atelman.rmaitem';

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
	public function getTable($type = 'Rmaitem', $prefix = 'Administrator', $config = array())
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
			'com_atelman.rmaitem',
			'rmaitem',
			array(
				'control' => 'jform',
				'load_data' => $loadData
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
		$data = Factory::getApplication()->getUserState('com_atelman.edit.rmaitem.data', array());

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

		$pk = $pk ?: (int) $this->getState($this->getName() . '.id');
		$db = Factory::getDbo();
		$query = $db->getQuery(true);

		// SELECT
		$query->select([
			'r.*',
			'rr.*',
			'p.product_no',
			'p.model_no',
			'p.product_name',
			'r.id AS rma_id',
			'u.name AS customer_name',
			'r.created_date AS rma_request_date',
			'u.customer_id',
			'rs.status_name',
			'wi.serial_no',
			'wi.serial_no_2',
			'wi.invoice_no',
			'wi.so_no',
			'wi.purchase_date',
			'wi.expired_date',
			'wi.expired_date_manual',
			'wi.extended_expired_date',
			'(SELECT r2.rmacode
                FROM ' . $db->quoteName('#__at_rma_items', 'r2') . '
                WHERE r2.customer_id = r.customer_id
                AND r2.so_no = r.so_no
                AND r2.replacement_sn = r.requested_sn
                ORDER BY r2.created_date DESC
                LIMIT 1
            ) AS previous_rma_number'
		]);

		// FROM + JOINs
		$query->from($db->quoteName('#__at_rma_items', 'r'))
			->join('LEFT', $db->quoteName('#__at_rma_request', 'rr') . ' ON ' . $db->quoteName('rr.id') . ' = ' . $db->quoteName('r.rma_request_id'))
			->join('LEFT', $db->quoteName('#__at_warranty_items', 'wi') . ' ON ' . $db->quoteName('wi.id') . ' = ' . $db->quoteName('r.warranty_item_id'))
			->join('LEFT', $db->quoteName('#__at_products', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('r.product_id'))
			->join('LEFT', $db->quoteName('#__users', 'u') . ' ON ' . $db->quoteName('u.customer_id') . ' = ' . $db->quoteName('r.customer_id'))
			->join('LEFT', $db->quoteName('#__at_rma_status', 'rs') . ' ON ' . $db->quoteName('rs.status_code') . ' = ' . $db->quoteName('r.status'));

		// WHERE
		$query->where($db->quoteName('r.id') . ' = ' . (int) $pk);

		$db->setQuery($query);
		$item = $db->loadObject();

		if (!$item) {
			$item = new \stdClass();
		}

		return $item;
	}

	/**
	 * Method to duplicate an Rmaitem
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
				$db->setQuery('SELECT MAX(ordering) FROM #__at_rma_items');
				$max             = $db->loadResult();
				$table->ordering = $max + 1;
			}
		}
	}


	public function itemRmaItem($id)
	{
		$app = Factory::getApplication();
		$db	= Factory::getDBO();
		$query = 'SELECT r.*,rr.*, p.product_no, p.model_no, p.product_name, r.id AS rma_id, u.name AS customer_name, r.created_date AS rma_request_date, u.customer_id, rs.status_name , '
			.	' wi.serial_no, wi.serial_no_2, wi.invoice_no, wi.so_no, wi.purchase_date, wi.expired_date, wi.expired_date_manual, wi.extended_expired_date, '
			. ' ( SELECT r2.rmacode FROM #__at_rma_items AS r2 WHERE r2.customer_id = r.customer_id AND r2.so_no = r.so_no AND r2.replacement_sn = r.requested_sn ORDER BY r2.created_date DESC LIMIT 1) AS previous_rma_number '
			. ' FROM #__at_rma_items AS r '
			. ' LEFT JOIN #__at_rma_request AS rr ON rr.id = r.rma_request_id '
			. ' LEFT JOIN #__at_warranty_items AS wi ON wi.id = r.warranty_item_id '
			. ' LEFT JOIN #__at_products AS p ON p.id = r.product_id '
			. ' LEFT JOIN #__users AS u ON u.customer_id = r.customer_id '
			. ' LEFT JOIN #__at_rma_status AS rs ON rs.status_code = r.`status` '
			. ' WHERE r.id = ' . $db->quote($id);

		$db->setQuery($query);
		$row = $db->loadObject();
		return $row;
	}
	public function itemGroupXref($userId)
	{
		$app = Factory::getApplication();
		$db	= Factory::getDBO();
		$query = " SELECT gx.group_id, ga.`access` "
			.	" FROM #__at_group_xref AS gx "
			.	" LEFT JOIN #__at_group_access AS ga ON ga.group_id = gx.group_id "
			.	" WHERE gx.user_id = " . $userId
			.	" ";

		$db->setQuery($query);
		$group = $db->loadObject();
		return $group;
	}
	public function fileNameRMA($id)
	{
		$app = Factory::getApplication();
		$db	= Factory::getDBO();
		$query = " SELECT d.id, d.`status`, d.filename,d.created_date,d.is_airway_bill, s.status_code, s.status_name FROM #__at_rma_downloads AS d "
			.	" LEFT JOIN #__at_rma_status AS s ON s.status_code = d.status "
			.	" WHERE d.rma_item_id = " . $db->quote($id) . " AND (d.status != 'RMAORDER' AND d.status != 'RMAREQUEST') "
			.	" ORDER BY d.created_date ASC ";

		$db->setQuery($query);
		$filenames = $db->loadObjectList();
		return $filenames;
	}

	public function fileNameRMAOrderRequest($id)
	{
		$app = Factory::getApplication();
		$db	= Factory::getDBO();
		$query = " SELECT * FROM #__at_rma_downloads "
			.	" WHERE rma_item_id = " . $db->quote($id) . " AND (`status` = 'RMAORDER' OR `status` = 'RMAREQUEST') "
			.	" ORDER BY created_date ASC ";

		$db->setQuery($query);
		$filenames = $db->loadObjectList();
		return $filenames;
	}


	public function save($post)
	{

		$app = Factory::getApplication();
		$db = Factory::getDBO();

		$atelrmaitem =  $this->getTable('rmaitem');
		$atelwarrantyitem = $this->getTable('warrantyitem');
		$atelwarrantyhistory = $this->getTable('warrantyhistory');

		$user = Factory::getUser();

		/* check extensions */
		// got any file
		$file = $app->input->files->get('document_file', array(), 'FILES');
		$rma_order_file = $app->input->files->get('rma_order_file', array(), 'FILES');
		$rma_request_file = $app->input->files->get('rma_request_file', array(), 'FILES');

		$rma_item_id 	= $post['cid'];
		$status				=	$post['status'];
		$rma_number 	=	$post['rmacode'];

		if ($post['rma_request_id']) {
			$atelrmarequest = $this->getTable('rmarequest');

			$atelrmarequest->id = $post['rma_request_id'];
			$atelrmarequest->fullname = $post['fullname'];
			$atelrmarequest->contact_name = $post['contact_name'];
			$atelrmarequest->address = $post['address'];
			$atelrmarequest->city = $post['city'];
			$atelrmarequest->state = $post['state'];
			$atelrmarequest->postal_code = $post['postal_code'];
			$atelrmarequest->country = $post['country'];
			$atelrmarequest->telephone = $post['telephone'];
			$atelrmarequest->fax = $post['fax'];
			$atelrmarequest->email = $post['email'];

			$atelrmarequest->store();
		}

		if (!empty($file)) {

			$flname = array();

			/**
			 * 25 = Super Administrator
			 * 32 = Logistics
			 * 31 = Store
			 * 34 = Supervisor
			 **/

			// This checks only happens for "Store" and "Logistics" Group
			// "Supervisor" and "Super Admin" no need to check the total uploads
			if ($user->gid != 25 && $user->gid != 34) {


				// check how many files for "Receive" or "Ship" status
				if ($status == 'receive' || $status == 'ship') {

					$db_checked = false;

					// check DB if they have already uploaded at least 2
					$query = " SELECT COUNT(id) FROM #__at_rma_downloads WHERE `status` = '$status' AND rma_item_id = '$rma_item_id' ";
					$db->setQuery($query);
					$total_files = $db->loadResult();

					// check current upload file and make sure total is 2
					if ($total_files < 2) {

						for ($i = 0; $i < 5; $i++) {
							if ($file['size'][$i] > 0) {
								$total_files++;
							}
						}

						if ($total_files < 2) {
							$this->setError(Text::_('RMA cannot be updated. You have not uploaded / total upload at least 2 documents'));
							return false;
						}
					}
				}

				// check how many files for "RMA Receipt Closed" or "RMA Shipment Closed" status
				if ($status == 'receive_close' || $status == 'ship_close') {

					$tmp = explode('_', $status);
					$status = $tmp[0];

					$db_checked = false;

					// check DB if they have already uploaded at least 3
					$query = " SELECT COUNT(id) FROM #__at_rma_downloads WHERE `status` = '$status' AND rma_item_id = '$rma_item_id' ";
					$db->setQuery($query);
					$total_files = $db->loadResult();

					// check current upload file and make sure total is 3
					if ($total_files < 3) {

						for ($i = 0; $i < 5; $i++) {
							if ($file['size'][$i] > 0) {
								$total_files++;
							}
						}

						if ($total_files < 3) {
							$this->setError(Text::_('RMA cannot be closed. You have not uploaded / total upload at least 3 documents'));
							return false;
						}
					}
				}
			}

			// Upload the Files
			for ($i = 0; $i < 5; $i++) {

				$is_airway_bill = 0;

				if ($file['size'][$i] > 0) {

					$newfilename	=	time() . '-' . preg_replace("/\s+/", "-", $file["name"][$i]);

					$allowedExts 	= array("pdf", "jpg", "jpeg", "gif", "png");
					$temp = explode(".", $file["name"][$i]);
					$extension = end($temp);

					if (in_array($extension, $allowedExts)) {

						if ($file["error"][$i] == 0) {

							$userId =	 	$user->id;

							$is_airway_bill = 0;

							if (isset($post['is_airway_bill'][$i])) {
								$is_airway_bill = 1;
							}


							$data = new stdClass();
							$data->rma_item_id = $rma_item_id;
							$data->status = $status;
							$data->filename = $newfilename;
							$data->uploaded_by = $userId;
							$data->is_airway_bill = $is_airway_bill;
							$data->created_date = date("Y-m-d H:i:s");

							// Insert the object into the __at_rma_downloads table.
							$result = $db->insertObject('#__at_rma_downloads', $data);

							move_uploaded_file($file["tmp_name"][$i], JPATH_ADMINISTRATOR . "/atelesis_docs/" . $newfilename);

							array_push($flname, $newfilename);
						}
					} else {
						$this->setError(Text::_('RMA is not saved. File Upload is invalid for ' . $file['name'][$i] . '. Please check your file type'));
						return false;
					}
				}
			}

			if (!empty($flname)) {
				PluginHelper::importPlugin('atelesis', 'logs');
				$dispatcher = Factory::getApplication()->getDispatcher();
				$log = new stdClass;
				$log->section = 'RMA_ITEM';
				$log->action_type = 'UPLOAD';
				$log->action_by = $user->id;
				$log->action_remarks = "Upload " . implode(", ", $flname) . " file on RMA Item : RMA #" . (($post['rmacode']) ? $post['rmacode'] : 'N/A');
				$log->id = $post['cid'];

				$event = new \Joomla\Event\Event('onAfterAction', [
					'log' => $log
				]);
				// Fire the onAfterStoreUser trigger
				$dispatcher->dispatch('onAfterAction', $event);
			}
		}


		// RMA Order File
		if ($rma_order_file['size'] > 0) {

			$flname = array();

			$newfilename	=	time() . '-' . preg_replace("/\s+/", "-", $rma_order_file["name"]);

			$allowedExts 	= array("pdf", "jpg", "jpeg", "gif", "png");
			$temp = explode(".", $rma_order_file["name"]);
			$extension = end($temp);

			if (in_array($extension, $allowedExts)) {

				if ($rma_order_file["error"] == 0) {

					$userId =	 	$user->id;


					$data = new stdClass();
					$data->rma_item_id = $rma_item_id;
					$data->status = 'RMAORDER';
					$data->filename = $newfilename;
					$data->uploaded_by = $userId;
					$data->is_airway_bill = 0;
					$data->created_date = date("Y-m-d H:i:s");

					// Insert the object into the __at_rma_downloads table.
					$result = $db->insertObject('#__at_rma_downloads', $data);

					move_uploaded_file($rma_order_file["tmp_name"], JPATH_ADMINISTRATOR . "/atelesis_docs/" . $newfilename);

					array_push($flname, $newfilename);

					PluginHelper::importPlugin('atelesis', 'logs');
					$dispatcher = Factory::getApplication()->getDispatcher();
					$log = new stdClass();
					$log->section = 'RMA_ITEM';
					$log->action_type = 'UPLOAD';
					$log->action_by = $user->id;
					$log->action_remarks = "Upload RMA Order " . implode(", ", $flname) . " file on RMA Item : RMA #" . (($post['rmacode']) ? $post['rmacode'] : 'N/A');
					$log->id = $post['cid'];
					$event = new \Joomla\Event\Event('onAfterAction', [
						'log' => $log
					]);
					// Fire the onAfterStoreUser trigger
					$dispatcher->dispatch('onAfterAction', $event);
				}
			} else {
				$this->setError(JText::_('RMA is not saved. File Upload is invalid for ' . $rma_order_file['name'] . '. Please check your file type'));
				return false;
			}
		}


		// RMA Request File
		if ($rma_request_file['size'] > 0) {

			$flname = array();

			$newfilename	=	time() . '-' . preg_replace("/\s+/", "-", $rma_request_file["name"]);

			$allowedExts 	= array("pdf", "jpg", "jpeg", "gif", "png");
			$temp = explode(".", $rma_request_file["name"]);
			$extension = end($temp);

			if (in_array($extension, $allowedExts)) {

				if ($rma_request_file["error"] == 0) {

					$userId =	 	$user->id;

					$data = new stdClass();
					$data->rma_item_id = $rma_item_id;
					$data->status = 'RMAREQUEST';
					$data->filename = $newfilename;
					$data->uploaded_by = $userId;
					$data->is_airway_bill = 0;
					$data->created_date = date("Y-m-d H:i:s");

					// Insert the object into the __at_rma_downloads table.
					$result = $db->insertObject('#__at_rma_downloads', $data);

					move_uploaded_file($rma_request_file["tmp_name"], JPATH_ADMINISTRATOR . "/atelesis_docs/" . $newfilename);

					array_push($flname, $newfilename);

					PluginHelper::importPlugin('atelesis', 'logs');
					$dispatcher = Factory::getApplication()->getDispatcher();
					$log = new stdClass();
					$log->section = 'RMA_ITEM';
					$log->action_type = 'UPLOAD';
					$log->action_by = $user->id;
					$log->action_remarks = "Upload RMA Request " . implode(", ", $flname) . " file on RMA Item : RMA #" . (($post['rmacode']) ? $post['rmacode'] : 'N/A');
					$log->id = $post['cid'];

					$event = new \Joomla\Event\Event('onAfterAction', [
						'log' => $log
					]);
					// Fire the onAfterStoreUser trigger
					$dispatcher->dispatch('onAfterAction', $event);
				}
			} else {
				$this->setError(Text::_('RMA is not saved. File Upload is invalid for ' . $rma_request_file['name'] . '. Please check your file type'));
				return false;
			}
		}


		if ($post['replacement_date']) {
			$post['replacement_date'] = date("Y-m-d", strtotime(str_replace("/", "-", $post['replacement_date'])));
		} else {

			$post['replacement_date'] = '0000-00-00';

			if ($post['status'] == 'receive') {

				if ($post['warranty_status'] == 'IN' || $post['warranty_status'] == 'DOA') {
					$post['shipping_duration'] = 14;
					$post['replacement_date'] = date("Y-m-d", (time() + 14 * 86400));
				} else if ($post['warranty_status'] == 'OUT') {
					$post['shipping_duration'] = 30;
					$post['replacement_date'] = date("Y-m-d", (time() + 30 * 86400));
				}
			}
		}


		$query = " SELECT * FROM #__at_rma_items WHERE id = '$rma_item_id' ";
		$db->setQuery($query);
		$tmprma = $db->loadObject();

		if (!empty($post['rmacode']) && $rma_item_id) {
			// implement this with 'open' status for Assigned RMA Date before
			if ($tmprma->rma_assigned_date == '0000-00-00') {
				$post['rma_assigned_date'] = date("Y-m-d", time());
			}
		}

		if ($post['received_date']) {
			$post['received_date'] = date("Y-m-d", strtotime(str_replace("/", "-", $post['received_date'])));
		} else {
			$post['received_date'] = '0000-00-00';

			if ($post['status'] == 'receive') {
				$post['received_date'] = date("Y-m-d", time());
			}
		}

		if ($post['shipped_date']) {
			$post['shipped_date'] = date("Y-m-d", strtotime(str_replace("/", "-", $post['shipped_date'])));
		} else {
			$post['shipped_date'] = '0000-00-00';

			if ($post['status'] == 'ship') {
				$post['shipped_date'] = date("Y-m-d", time());
			}
		}

		if ($post['closed_date']) {
			$post['closed_date'] = date("Y-m-d", strtotime(str_replace("/", "-", $post['closed_date'])));
		} else {
			$post['closed_date'] = '0000-00-00';

			if ($post['status'] == 'close') {
				$post['closed_date'] = date("Y-m-d", time());
			}
		}

		if ($post['replacement_pn']) {
			$query = " SELECT id FROM #__at_products WHERE product_no = " . $db->quote($post['replacement_pn'], false);
			$db->setQuery($query);

			if (!$db->loadResult()) {
				$post['replacement_pn'] = '';
			}
		}

		if (empty($post['status'])) {
			$post['status'] = $tmprma->status;
		}

		/* Update Original S/N - coz it is wronged. */
		if (!empty($post['update_sn'])) {
			$query = " SELECT id FROM #__at_warranty_items WHERE serial_no = " . $db->quote($post['update_sn'], false);
			$db->setQuery($query);
			$warranty_item_id = $db->loadResult();

			if ($warranty_item_id) {
				$post['warranty_item_id'] = $warranty_item_id;
			} else {
				$this->setError(Text::_('RMA is not saved. Warranty Registration is not exist in system : ' . $db->quote($post['update_sn'])), 'error');
				return false;
			}
		}



		if (!$atelrmaitem->bind($post)) {
			return false;
		}



		$atelrmaitem->id = $post['cid']; // if exist, it updates, otherwise, save new input

		$item = array();

		if ($post['cid']) {
			$query = " SELECT * FROM #__at_rma_items WHERE id = " . $db->quote($post['cid'], false);
			$db->setQuery($query);
			$item = $db->loadObject();

			foreach ($item as $key => $value) {
				if ($value !== null && $value !== '') {
					$atelrmaitem->$key = $value;
				}
			}
			$atelrmaitem->remarks = $post['remarks'];
			$atelrmaitem->description = $post['description'];
			$atelrmaitem->requested_sn = $post['requested_sn'];
			$atelrmaitem->replacement_pn = $post['replacement_pn'];
			$atelrmaitem->replacement_sn = $post['replacement_sn'];
		}

		$result = $atelrmaitem->store();

		//update warranty item table 
		$atelwarrantyitem->id = $post['warranty_item_id'];
		$atelwarrantyitem->serial_no_2 = $post['replacement_sn'];
		$atelwarrantyitem->replacement_pn = $post['replacement_pn'];

		//$result = $atelwarrantyitem->store();
		$result = $db->updateObject('#__at_warranty_items', $atelwarrantyitem, 'id');

		if ($post['replacement_sn'] != '') {

			$atelwarrantyhistory->id = '';
			$atelwarrantyhistory->warranty_id = $post['warranty_item_id'];
			$atelwarrantyhistory->serial_no_2 = $post['replacement_sn'];
			$atelwarrantyhistory->replacement_pn = $post['replacement_pn'];
			$result = $atelwarrantyhistory->store();
		}

		PluginHelper::importPlugin('atelesis', 'logs');
		$dispatcher = Factory::getApplication()->getDispatcher();
		$log = new stdClass();
		$log->section = 'RMA_ITEM';
		if ($post['cid']) {
			$log->action_type = 'EDIT_SAVE';
		} else {
			$log->action_type = 'NEW_SAVE';
		}
		$log->action_by = $user->id;
		$log->action_remarks 	= 'Saving RMA Item : RMA #' . (($post['rmacode']) ? $post['rmacode'] : 'N/A');
		$log->id = $item->id;

		$before_update = json_encode($item);
		$after_update = json_encode($post);

		// Fire the onAfterStoreUser trigger
		$event = new \Joomla\Event\Event('onAfterAction', [
			'log' => $log,
			'before' => $before_update,
			'after' => $after_update,
		]);

		$dispatcher->dispatch('onAfterAction', $event);

		return $result;
	}
}
