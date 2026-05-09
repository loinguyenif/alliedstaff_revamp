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

use Atelman\Component\Atelman\Site\Helper\AtelmanHelper;
use \Joomla\CMS\Table\Table;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Plugin\PluginHelper;
use \Joomla\CMS\MVC\Model\AdminModel;
use \Joomla\CMS\Helper\TagsHelper;
use \Joomla\CMS\Filter\OutputFilter;
use \Joomla\CMS\Event\Model;
use Joomla\CMS\Event\AbstractEvent;
use PDF_JS;

require_once JPATH_ADMINISTRATOR  . '/components/com_atelman/lib/fpdf26/vendor/autoload.php';
require_once JPATH_ADMINISTRATOR . '/components/com_atelman/src/Helper/pdf_js.php';

use setasign\Fpdi\Fpdi;
use stdClass;

//require_once JPATH_COMPONENT_ADMINISTRATOR . '/lib/fpdf/fpdi.php';

/**
 * Rmarequest model.
 *
 * @since  1.0.0
 */
class RmarequestModel extends AdminModel
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
	public $typeAlias = 'com_atelman.rmarequest';

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
	public function getTable($type = 'Rmarequest', $prefix = 'Administrator', $config = array())
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
			'com_atelman.rmarequest',
			'rmarequest',
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
		$data = Factory::getApplication()->getUserState('com_atelman.edit.rmarequest.data', array());

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

		if ($item = parent::getItem($pk)) {
			if (isset($item->params)) {
				$item->params = json_encode($item->params);
			}

			// Do any procesing on fields here if needed
		}

		return $item;
	}

	/**
	 * Method to duplicate an Rmarequest
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
				$db->setQuery('SELECT MAX(ordering) FROM #__at_rma_request');
				$max             = $db->loadResult();
				$table->ordering = $max + 1;
			}
		}
	}


	/*
			*	
			*	Submit RMA 
			*	
		*/
	public function submitrma($post)
	{
		$db = Factory::getContainer()->get('DatabaseDriver');

		$atelrmarequest =  $this->getTable('Rmarequest');
		$atelrmaitem =  $this->getTable('Rmaitem');
		$atelwarrantyitem =  $this->getTable('Warrantyitem');

		$user = Factory::getUser();

		if (!$user->guest) {
			$post['user_id'] = $user->id;
		}

		$query = " SELECT customer_id FROM #__users WHERE country_id = 13 ";
		$db->setQuery($query);
		$india_customers_array = $db->loadColumn();

		if (!$atelrmarequest->bind($post))
			return false;
		$atelrmarequest->created_date = date('Y-m-d H:i:s');
		$tmp = 	$atelrmarequest->store();

		$row_id = 	$atelrmarequest->id; // new id for rma request id

		$arr_products = 	$post['print']; // all data is in here

		$total = count($arr_products);

		//$addition_remarks = "Please send back only bare faulty unit to:\n\nAllied Telesis Asia Pacific Pte Ltd\nRMA Store\n4th floor\n11 Tai Seng Link\nSingapore 534182\n\nATTN: AMY TCHIN\nDEPT:RMA\n";

		$in_warranty = false;

		for ($i = 1; $i <= $total; $i++) :

			$product_id = $arr_products[$i]['product_id'];
			$product_no =	$arr_products[$i]['product_no'];
			$serial_no = $arr_products[$i]['serial_no']; // either serial_no or serial_no_2
			$wstatus = $arr_products[$i]['warranty_status'];
			$fdescription = $arr_products[$i]['description'];
			$remarks = ((!empty($arr_products[$i]['remarks'])) ? $arr_products[$i]['remarks'] : '');
			$so_no = $arr_products[$i]['so_no'];
			$invoice_no = $arr_products[$i]['invoice_no'];
			$warranty_id =	$arr_products[$i]['warranty_id'];
			$warranty_customer_id =	$arr_products[$i]['warranty_customer_id'];

			if (!isset($product_id)) {
				continue;
			}

			$atelrmaitem->id								=		'';
			$atelrmaitem->rma_request_id 		= 	$row_id;
			$atelrmaitem->warranty_item_id	= 	$warranty_id;
			$atelrmaitem->warranty_status =		$wstatus;
			$atelrmaitem->description =		$fdescription;
			$atelrmaitem->remarks =		$remarks;
			$atelrmaitem->so_no =		$so_no;
			$atelrmaitem->invoice_no =		$invoice_no;
			$atelrmaitem->customer_id =		(in_array($warranty_customer_id, $india_customers_array) ? '80001363' : $warranty_customer_id);
			$atelrmaitem->requested_sn =		$serial_no;
			$atelrmaitem->product_id =		$product_id;
			$atelrmaitem->replacement_pn =		$product_no;
			$atelrmaitem->rmacode = 	'';

			//ifoundries added : 25 May 2017 : check current RMA 
			$prefix = '';
			switch ($wstatus) {
				case 'IN':
				case 'DOA':
					$prefix = 'T60';
					break;
					//case 'OUT':
					//$prefix = 'T68';
					//break;
			}

			//check serino on service contract
			$checkSeri = $this->checkSeriNumberNetCon($serial_no);
			if ($checkSeri) {
				$prefix = 'T80';
			}

			if ($prefix) {
				$query = " SELECT position_no FROM #__at_posno WHERE section = 'RMA' AND prefix = '$prefix' ";
				$db->setQuery($query);
				$position_no = $db->loadResult();

				$rmacode = $prefix . sprintf('%05d', $position_no);

				$atelrmaitem->status = 'await';
				$atelrmaitem->rmacode = 	$rmacode;
				$atelrmaitem->rma_assigned_date = 	date("Y-m-d", time());
				$in_warranty = true;
			} else {
				$atelrmaitem->status = 'open';
			}
			// END


			if ($wstatus == 'IN' || $wstatus == 'DOA') {
				$atelrmaitem->shipping_duration = 14;
				//$atelrmaitem->replacement_date = date("Y-m-d", (time() + 14*86400));
			} else if ($wstatus == 'OUT') {
				$atelrmaitem->shipping_duration = 30;
				//$atelrmaitem->replacement_date = date("Y-m-d", (time() + 30*86400));
			}

			//check serino on service contract
			if ($checkSeri) {
				if ($checkSeri->service_type == "NCA") {
					$atelrmaitem->shipping_duration = 14;
				} else {
					$atelrmaitem->shipping_duration = 30;
				}
			}

			$atelrmaitem->created_date = date('Y-m-d H:i:s');
			$atelrmaitem->store();

			$atelwarrantyitem->id = $warranty_id;
			$atelwarrantyitem->replacement_pn = $product_no;
			$atelwarrantyitem->store();

		endfor;

		if ($in_warranty) {
			$query = $db->getQuery(true)
				->update($db->quoteName('#__at_posno'))
				->set($db->quoteName('position_no') . ' = position_no + 1')
				->where($db->quoteName('section') . ' = ' . $db->quote('RMA'))
				->where($db->quoteName('prefix') . ' = ' . $db->quote('T60'));

			$db->setQuery($query);
			$db->execute();
		}
		if ($checkSeri) {
			$query = $db->getQuery(true)
				->update($db->quoteName('#__at_posno'))
				->set($db->quoteName('position_no') . ' = position_no + 1')
				->where($db->quoteName('section') . ' = ' . $db->quote('RMA'))
				->where($db->quoteName('prefix') . ' = ' . $db->quote('T80'));

			$db->setQuery($query);
			$db->execute();
		}

		return $row_id;
	}

	/*
			*	
			*	Update RMA
			*	
		*/
	public function updaterma($post)
	{
		$app = Factory::getApplication();
		$db = Factory::getDBO();
		$user = Factory::getUser();

		$atelrmaitem = $this->getTable('RmaItem');

		$file = $app->input->files->get('document_file', array(), 'FILES');
		$rma_order_file = $app->input->files->get('rma_order_file', array(), 'FILES');
		$rma_request_file = $app->input->files->get('rma_request_file', array(), 'FILES');

		$data = array();
		$cids = explode(",", $post['cid']); // 100,101,...etc

		if (trim($post['rmacode'])) {
			$data['rmacode'] = $post['rmacode'];
		}

		if (!empty($post['status'])) {
			$data['status'] = $post['status'];
		}

		if ($post['shipping_duration']) {
			$data['shipping_duration'] = (int) $post['shipping_duration'];
			$data['replacement_date'] = date("Y-m-d", (time() + $data['shipping_duration'] * 86400));
		} else {
			unset($data['shipping_duration']);
		}

		switch ($data['status']) {
			case 'receive':
				$data['received_date'] = date("Y-m-d", time());
				break;
			case 'ship':
				$data['shipped_date'] = date("Y-m-d", time());
				break;
			case 'close':
				$data['closed_date'] = date("Y-m-d", time());
				break;
		}

		if ($post['received_date']) {
			$data['received_date'] 	= ($post['received_date']) ? date("Y-m-d", strtotime(str_replace("/", "-", $post['received_date']))) : '0000-00-00';
		}

		if ($post['shipped_date']) {
			$data['shipped_date'] = ($post['shipped_date']) ? date("Y-m-d", strtotime(str_replace("/", "-", $post['shipped_date']))) : '0000-00-00';
		}

		if ($post['closed_date']) {
			$data['closed_date'] = ($post['closed_date']) ? date("Y-m-d", strtotime(str_replace("/", "-", $post['closed_date']))) : '0000-00-00';
		}

		$status = $data['status'];
		$rma_number = $data['rmacode'];

		// upload file
		if ($file['size'] > 0) {

			$is_airway_bill = $post['is_airway_bill'];

			$allowedExts 	= array("pdf", "jpg", "jpeg", "gif", "png");
			$temp = explode(".", $file["name"]);
			$extension = end($temp);

			if (in_array($extension, $allowedExts)) {

				$uploaded_by 	= $user->id;

				$rma_array 	= array();
				$filepath 	=	'';

				$newfilename	=	time() . '-' . preg_replace("/\s+/", "-", $file["name"]);

				if (move_uploaded_file($file["tmp_name"], JPATH_ADMINISTRATOR . "/atelesis_docs/" . $newfilename)) {
					$filepath = JPATH_ADMINISTRATOR . "/atelesis_docs/" . $newfilename;
				}

				foreach ($cids as $cid) {

					$data = new stdClass();
					$data->rma_item_id = $cid;
					$data->status = $status;
					$data->filename = $newfilename;
					$data->is_airway_bill = $is_airway_bill;
					$data->uploaded_by = $uploaded_by;
					$data->created_date = date("Y-m-d H:i:s");
					// Insert the object into the __at_rma_downloads table.
					$result = $db->insertObject('#__at_rma_downloads', $data);

					PluginHelper::importPlugin('atelesis', 'logs');
					$dispatcher = Factory::getApplication()->getDispatcher();
					$log = new stdClass();
					$log->section = 'RMA_ITEM';
					$log->action_type = 'UPLOAD';
					$log->action_by = $user->id;
					$log->action_remarks = "Upload $newfilename file on RMA Item using RMA Batch Update.";
					$log->id = $cid;

					$event = new \Joomla\Event\Event('onAfterAction', [
						'log' => $log
					]);

					// Fire the onAfterStoreUser trigger
					$dispatcher->dispatch('onAfterAction', $event);
				}
			} else {
				//$this->setError(JText::_('RMAs are not saved. File Upload is invalid for '. $file['name'].'. Please check your file type'));
				return false;
			}
		}

		// RMA Order
		if ($rma_order_file['size'] > 0) {

			$allowedExts 	= array("pdf", "jpg", "jpeg", "gif", "png");
			$temp = explode(".", $rma_order_file["name"]);
			$extension = end($temp);

			if (in_array($extension, $allowedExts)) {

				$uploaded_by 	= $user->id;

				$rma_array 	= array();
				$filepath 	=	'';

				$newfilename	=	time() . '-' . preg_replace("/\s+/", "-", $rma_order_file["name"]);

				if (move_uploaded_file($rma_order_file["tmp_name"], JPATH_ADMINISTRATOR . "/atelesis_docs/" . $newfilename)) {
					$filepath = JPATH_ADMINISTRATOR . "/atelesis_docs/" . $newfilename;
				}

				foreach ($cids as $cid) {
					$data = new stdClass();
					$data->rma_item_id = $cid;
					$data->status = 'RMAORDER';
					$data->filename = $newfilename;
					$data->is_airway_bill = $is_airway_bill;
					$data->uploaded_by = $uploaded_by;
					$data->created_date = date("Y-m-d H:i:s");
					// Insert the object into the __at_rma_downloads table.
					$result = $db->insertObject('#__at_rma_downloads', $data);

					PluginHelper::importPlugin('atelesis', 'logs');
					$dispatcher = Factory::getApplication()->getDispatcher();
					$log = new stdClass();
					$log->section = 'RMA_ITEM';
					$log->action_type = 'UPLOAD';
					$log->action_by = $user->id;
					$log->action_remarks = "Upload $newfilename file on RMA Item using RMA Batch Update.";
					$log->id = $cid;

					$event = new \Joomla\Event\Event('onAfterAction', [
						'log' => $log
					]);

					// Fire the onAfterStoreUser trigger
					$dispatcher->dispatch('onAfterAction', $event);
				}
			} else {
				//$this->setError(JText::_('RMAs are not saved. File Upload is invalid for '. $file['name'].'. Please check your file type'));
				return false;
			}
		}



		// RMA Request
		if ($rma_request_file['size'] > 0) {

			$allowedExts 	= array("pdf", "jpg", "jpeg", "gif", "png");
			$temp = explode(".", $rma_request_file["name"]);
			$extension = end($temp);

			if (in_array($extension, $allowedExts)) {

				$uploaded_by 	= $user->id;

				$rma_array 	= array();
				$filepath 	=	'';

				$newfilename	=	time() . '-' . preg_replace("/\s+/", "-", $rma_request_file["name"]);

				if (move_uploaded_file($rma_request_file["tmp_name"], JPATH_ADMINISTRATOR . "/atelesis_docs/" . $newfilename)) {
					$filepath = JPATH_ADMINISTRATOR . "/atelesis_docs/" . $newfilename;
				}

				foreach ($cids as $cid) {
					$data = new stdClass();
					$data->rma_item_id = $cid;
					$data->status = 'RMAREQUEST';
					$data->filename = $newfilename;
					$data->is_airway_bill = $is_airway_bill;
					$data->uploaded_by = $uploaded_by;
					$data->created_date = date("Y-m-d H:i:s");
					// Insert the object into the __at_rma_downloads table.
					$result = $db->insertObject('#__at_rma_downloads', $data);

					PluginHelper::importPlugin('atelesis', 'logs');
					$dispatcher = Factory::getApplication()->getDispatcher();
					$$log = new stdClass();
					$log->section = 'RMA_ITEM';
					$log->action_type = 'UPLOAD';
					$log->action_by = $user->id;
					$log->action_remarks = "Upload $newfilename file on RMA Item using RMA Batch Update.";
					$log->id = $cid;

					$event = new \Joomla\Event\Event('onAfterAction', [
						'log' => $log
					]);

					// Fire the onAfterStoreUser trigger
					$dispatcher->dispatch('onAfterAction', $event);
				}
			} else {
				//$this->setError(JText::_('RMAs are not saved. File Upload is invalid for '. $file['name'].'. Please check your file type'));
				return false;
			}
		}


		// if ticked, remove the files, 
		$download	= $this->getTable('Download');

		foreach ($post['files_rma_item_id'] as $rma_item_id) :
			$tmp = $download->delete($rma_item_id);
		endforeach;


		foreach ($cids as $cid) :

			$data['id'] = $cid;

			$qry = " SELECT * FROM #__at_rma_items WHERE id = '$cid' ";
			$db->setQuery($qry);
			$rma = $db->loadObject();

			$rmacode = '';

			if (empty($post['status'])) {
				$data['status'] = $rma->status;
			}

			if (!$rma->rmacode) {
				$rmacode = 'N/A';

				if ($rma->rma_assigned_date == '0000-00-00 00:00:00') {
					$data['rma_assigned_date'] = date("Y-m-d", time());
				}
			}

			if ($data['status'] == 'receive') {

				$shipping_dur = 0;

				if ($data['shipping_duration']) {
					$shipping_dur = (int) $data['shipping_duration'];
					$data['replacement_date'] = date("Y-m-d", (time() + $shipping_dur * 86400));
				} else {
					$shipping_dur = (int) $rma->shipping_duration;
					if ($rma->replacement_date == '0000-00-00') {
						$data['replacement_date'] = date("Y-m-d", (time() + $shipping_dur * 86400));
					} else {
						$data['replacement_date'] = $rma->replacement_date;
					}
				}
			}

			if (!$atelrmaitem->bind($data))
				return false;

			if ($post['cid']) {
				$query = " SELECT * FROM #__at_rma_items WHERE id = " . $db->quote($post['cid'], false);
				$db->setQuery($query);
				$item = $db->loadObject();

				foreach ($item as $key => $value) {
					if ($value !== null && $value !== '') {
						$atelrmaitem->$key = $value;
					}
				}
				if ($post['rmacode']) {
					$atelrmaitem->rmacode = $post['rmacode'];
				}
				if ($post['status']) {
					$atelrmaitem->status = $post['status'];
				}
				if ($post['shipping_duration']) {
					$atelrmaitem->shipping_duration = $post['shipping_duration'];
				}
				if ($post['received_date']) {
					$atelrmaitem->received_date = $post['received_date'];
				}
				if ($post['shipped_date']) {
					$atelrmaitem->shipped_date = $post['shipped_date'];
				}
				if ($post['closed_date']) {
					$atelrmaitem->closed_date = $post['closed_date'];
				}

				if ($atelrmaitem->store()) {

					PluginHelper::importPlugin('atelesis', 'logs');
					$dispatcher = Factory::getApplication()->getDispatcher();
					$log = new stdClass();
					$log->section = 'RMA_ITEM';
					$log->action_type = 'EDIT_SAVE';
					$log->action_by = $user->id;
					$log->action_remarks = 'Saving Mass RMA Item(s) : RMA #' . (($data['rmacode']) ? $data['rmacode'] : $rmacode);
					$log->id = $cid;

					$before_update = json_encode($item);
					$after_update = json_encode($post);

					// Fire the onAfterStoreUser trigger
					$event = new \Joomla\Event\Event('onAfterAction', [
						'log' => $log,
						'before' => $before_update,
						'after' => $after_update,
					]);

					$dispatcher->dispatch('onAfterAction', $event);
				}
			}

		endforeach;

		return true;
	}

	/*
			*	
			*	Print RMA Download
			*	
		*/
	public function prints($post)
	{
		$db = Factory::getDBO();
		if (empty($post['file_id'])) return false;
		$query = " SELECT filename FROM #__at_rma_downloads WHERE id IN (" . implode(',', $post['file_id']) . ") ";
		$db->setQuery($query);
		$tmps = $db->loadColumn();
		$pdf = new Fpdi();
		foreach ($tmps as $t) {
			$path = JPATH_ADMINISTRATOR . '/atelesis_docs/' . $t;
			$info = pathinfo($path);
			switch ($info["extension"]) {
				case 'pdf':
					$pageCount = $pdf->setSourceFile($path);
					for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
						$tplIdx = $pdf->ImportPage($pageNo);
						$size = $pdf->getTemplatesize($tplIdx);
						$pdf->AddPage($size['width'] > $size['height'] ? 'L' : 'P', [$size['width'], $size['height']]);
						$pdf->useTemplate($tplIdx);
					}
					break;
				case 'tiff':
				case 'jpg':
				case 'gif':
				case 'png':
					$pdf->AddPage();
					$pdf->Image($path, 0, 0, 0, 0);
					break;
			}
		}
		return $pdf;
	}


	/*
			*	
			*	Get RMA Request ID by RMA ID
			*	
		*/
	public function getRMARequestData($by, $value)
	{
		$db = Factory::getDBO();

		$query = " SELECT rr.* FROM #__at_rma_items AS r "
			.	" LEFT JOIN #__at_rma_request AS rr ON rr.id = r.rma_request_id ";

		switch ($by) {

			case 'rma_id':
				$query .= " WHERE r.id = '$value' ";
				break;

			case 'rma_number':
				$query .= " WHERE r.rmacode = '$value' ";
				$query .= " ORDER BY rr.created_date DESC ";
				break;
		}

		$db->setQuery($query);
		$result = $db->loadObject();

		return $result;
	}

	public function getDataByRMARequestId($rma_request_id)
	{

		$db = Factory::getDBO();

		$query = " SELECT * , r.created_date as rma_created_date, ( SELECT r2.rmacode FROM #__at_rma_items AS r2 WHERE r2.customer_id = r.customer_id AND r2.invoice_no = r.invoice_no AND r2.replacement_sn = r.requested_sn ORDER BY r2.created_date DESC LIMIT 1) AS previous_rma_number "
			.	" FROM #__at_rma_items AS r "
			.	" LEFT JOIN #__at_products AS p ON p.id = r.product_id "
			.	" LEFT JOIN #__at_warranty_items AS w ON w.id = r.warranty_item_id "
			.	" WHERE r.rma_request_id = '$rma_request_id' "
			.	" ORDER BY p.product_no ASC, r.requested_sn ASC ";

		$db->setQuery($query);
		$results = $db->loadObjectList();

		return $results;
	}

	public function getDataByRMANumber($rma_number)
	{

		$db = Factory::getDBO();

		$query = " SELECT * , r.created_date as rma_created_date, ( SELECT r2.rmacode FROM #__at_rma_items AS r2 WHERE r2.customer_id = r.customer_id AND r2.invoice_no = r.invoice_no AND r2.replacement_sn = r.requested_sn ORDER BY r2.created_date DESC LIMIT 1) AS previous_rma_number "
			.	" FROM #__at_rma_items AS r "
			.	" LEFT JOIN #__at_products AS p ON p.id = r.product_id "
			.	" LEFT JOIN #__at_warranty_items AS w ON w.id = r.warranty_item_id "
			.	" WHERE r.rmacode = '$rma_number' "
			.	" ORDER BY p.product_no ASC, r.requested_sn ASC ";

		$db->setQuery($query);
		$results = $db->loadObjectList();

		return $results;
	}

	/*
			*	
			*	DELETE DOWNLOAD ID
			*	
		*/

	public function delete_files($post)
	{
		$db = Factory::getDBO();

		if (empty($post['file_id'])) return false;

		$query = " SELECT id, filename FROM #__at_rma_downloads WHERE id IN (" . implode(',', $post['file_id']) . ") ";
		$db->setQuery($query);
		$tmps = $db->loadObjectList();

		foreach ($tmps as $t) {

			$filename = $t->filename;

			$path = JPATH_ADMINISTRATOR . '/atelesis_docs/' . $filename;

			$query = " SELECT COUNT(id) FROM #__at_rma_downloads WHERE filename = '$filename' ";
			$db->setQuery($query);
			$result = $db->loadResult();

			$query1 = $db->getQuery(true);
			$query1->delete($db->quoteName('#__at_rma_downloads'))
				->where($db->quoteName('id') . ' = ' . $db->quote($t->id));
			$db->setQuery($query1);
			$db->execute();

			// If this file is really only 1 in DB, remove it!
			if ($result == 1) {

				if (file_exists($path)) {
					unlink($path);
				}
			}
		}

		return true;
	}

	/*
			*	
			*	EMAIL
			*	
		*/
	public function emails($post)
	{
		$db = Factory::getDBO();

		if (empty($post['file_id'])) return false;
		if (empty($post['recipients'])) return false;

		$recipients = array();
		$rectmps = explode(';', $post['recipients']);
		$regex = '/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/i';

		foreach ($rectmps as $email) {
			if (preg_match($regex, $email)) {
				array_push($recipients, $email);
			}
		}

		if (empty($recipients)) return false;

		$query = " SELECT filename FROM #__at_rma_downloads WHERE id IN (" . implode(',', $post['file_id']) . ") ";
		$db->setQuery($query);
		$attachments = $db->loadColumn();

		$subject = 'RMA Attachment';

		$body =	'<img src="cid:logo_id" alt="logo" /><br />';



		$mail = Factory::getMailer();

		/* Email */
		if (!empty($attachments)) {
			foreach ($attachments as $a) {
				$mail->addAttachment(JPATH_ADMINISTRATOR . '/atelesis_docs/' . $a);
			}
		}

		$body .= 'Attachment as attached';

		$mail->IsHTML(true);
		$mail->setSender(array('RMA-AsiaPacific@alliedtelesis.com.sg', 'RMA Admin'));
		$mail->setSubject($subject);
		$mail->setBody($body);
		$mail->AddEmbeddedImage(JPATH_SITE . '/templates/rhuk_milkyway/images/ATelesis_2color_web.png', 'logo_id', 'ATelesis_2color_web.png', 'base64', 'image/png');

		$mail->addRecipient($recipients);

		//return $mail->Send();
	}



	public function checkSeriNumberNetCon($sr)
	{
		if ($sr) {
			$db = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from($db->quoteName('#__at_service_contract_product_xref', 'x'));
			$query->join(
				'LEFT',
				$db->quoteName('#__at_service_contract', 'c') . ' ON ' .
					$db->quoteName('x.service_contract_id') . ' = ' . $db->quoteName('c.id')
			);
			$query->where('x.serial_no="' . $sr . '"');
			$db->setQuery($query);
			$results = $db->loadObject();
			return $results;
		} else {
			return array();
		}
	}
}
