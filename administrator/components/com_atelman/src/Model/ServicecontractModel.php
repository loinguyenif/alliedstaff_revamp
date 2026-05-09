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
use stdClass;

/**
 * Servicecontract model.
 *
 * @since  1.0.0
 */
class ServicecontractModel extends AdminModel
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
	public $typeAlias = 'com_atelman.servicecontract';

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
	public function getTable($type = 'Servicecontract', $prefix = 'Administrator', $config = array())
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
			'com_atelman.servicecontract',
			'servicecontract',
			array(
				'control' => 'jform',
				'load_data' => false
			)
		);
		if ($form === false) {
			throw new \Exception($this->getError(), 500);
		}


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
		$data = Factory::getApplication()->getUserState('com_atelman.edit.servicecontract.data', array());

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

		// if ($item = parent::getItem($pk)) {
		// 	if (isset($item->params)) {
		// 		$item->params = json_encode($item->params);
		// 	}

		// 	// Do any procesing on fields here if needed
		// }

		$db = $this->getDatabase();
		$query = $db->getQuery(true);

		// Nếu $pk không được truyền vào, dùng từ state hoặc request
		$cid = $pk ?: (int) $this->getState($this->getName() . '.id');

		$query
			->select([
				$db->quoteName('s') . '.*',
				$db->quoteName('cp.id', 'service_contract_item_id'),
				$db->quoteName('cp.serial_no'),
				$db->quoteName('cp.model_no'),
				$db->quoteName('cp.part_no'),
				$db->quoteName('u.name', 'distributor_name', 'u.country_id')
			])
			->from($db->quoteName('#__at_service_contract', 's'))
			->join(
				'LEFT',
				$db->quoteName('#__at_service_contract_product_xref', 'cp') . ' ON ' .
					$db->quoteName('cp.service_contract_id') . ' = ' . $db->quoteName('s.id')
			)
			->join(
				'LEFT',
				$db->quoteName('#__users', 'u') . ' ON ' .
					$db->quoteName('u.customer_id') . ' = ' . $db->quoteName('s.customer_id')
			)
			->where($db->quoteName('cp.id') . ' = ' . (int) $cid);

		$db->setQuery($query);
		$item = $db->loadObject();

		// Gọi parent để Joomla xử lý dữ liệu chung (nếu cần)
		if (empty($item)) {
			$item = parent::getItem($pk);
		}


		return $item;
	}

	/**
	 * Method to duplicate an Servicecontract
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
				$db->setQuery('SELECT MAX(ordering) FROM #__at_service_contract');
				$max             = $db->loadResult();
				$table->ordering = $max + 1;
			}
		}
	}



	/*
			*	
			*	Submit Service Contract Creation
			*	
		*/
	public function submitcontract($post)
	{
		$db = Factory::getDBO();

		$atelservicecontract = $this->getTable('Servicecontract');
		$atelservicecontractitem = $this->getTable('Servicecontractproductxref');

		$post['customer_id'] = $post['fullname_tmp'];

		$user = Factory::getUser();

		if (!$user->guest) {
			$post['user_id'] = $user->id;
		}

		$post['expiry_date'] = ($post['expiry_date']) ? date("Y-m-d", strtotime(str_replace("/", "-", $post['expiry_date']))) : NULL;

		if (!$atelservicecontract->bind($post))
			return false;

		$atelservicecontract->created_date = date('Y-m-d H:i:s');
		$tmp = $atelservicecontract->store();

		$row_id = $atelservicecontract->id; // new id for service contract id

		$arr_products = $post['print']; // all data is in here

		$total = count($arr_products);

		for ($i = 1; $i <= $total; $i++) :

			$product_id = $arr_products[$i]['product_id'];
			$part_no =	$arr_products[$i]['product_no'];
			$model_no =	$arr_products[$i]['product_model'];
			$serial_no = $arr_products[$i]['serial_no']; // either serial_no or serial_no_2
			$warranty_id =	$arr_products[$i]['warranty_id'];
			//$warranty_customer_id 		=	$arr_products[$i]['warranty_customer_id'];

			if (!isset($product_id)) {
				continue;
			}

			$atelservicecontractitem->id = '';
			$atelservicecontractitem->service_contract_id = $row_id;
			$atelservicecontractitem->warranty_id = $warranty_id;
			$atelservicecontractitem->serial_no = $serial_no;
			$atelservicecontractitem->part_no = $part_no;
			$atelservicecontractitem->model_no = $model_no;

			$atelservicecontractitem->store();

		endfor;
		return $row_id;
	}

	/*
			*	
			*	Update Service Contract Item
			*	
		*/
	public function updateservicecontract($post)
	{
		$app   = Factory::getApplication();
		$db = Factory::getDBO();
		$user = Factory::getUser();

		$atelservicecontract = $this->getTable('Servicecontract');
		$atelservicecontractitem = $this->getTable('Servicecontractproductxref');

		$data = array();

		if ($post['expiry_date']) {
			$data['expiry_date'] 			= ($post['expiry_date']) ? date("Y-m-d", strtotime(str_replace("/", "-", $post['expiry_date']))) : NULL;
		}

		if ($post['start_date']) {
			$data['start_date'] 			= ($post['start_date']) ? date("Y-m-d", strtotime(str_replace("/", "-", $post['start_date']))) : NULL;
		}

		$loadData = AtelmanHelper::getServiceContract($post['service_contract_id']);
		$data['id'] = $post['service_contract_id'];
		if (!empty($post['fullname_tmp'])) {
			$data['customer_id'] = $post['fullname_tmp'];
		} else {
			$data['customer_id'] = $loadData->customer_id;
		}
		$data['service_contract_no'] = $post['service_contract_no'];
		$data['po_no'] = $post['po_no'];
		$data['cover_length'] = $post['cover_length'];
		$data['service_type'] = $post['service_type'];
		$data['client_name'] = $post['client_name'];
		$data['remarks'] = $post['remarks'];
		$data['created_date'] = $loadData->created_date;


		if (!$atelservicecontract->bind($data))
			return false;

		$serial_no = $post['serial_no'];

		$query = " SELECT p.*, wi.serial_no, wi.id AS warranty_id FROM #__at_products AS p " .
			" LEFT JOIN #__at_warranty_items AS wi ON wi.product_id = p.id   " .
			" WHERE wi.serial_no = '" . $serial_no . "' LIMIT 1 ";

		$db->setQuery($query);
		$tmps = $db->loadObject();
		$itemdata = array();
		$itemdata['id']				=	$post['cid'];
		$itemdata['serial_no'] 	= $post['serial_no'];
		$itemdata['part_no'] 	= $post['part_no'];
		$itemdata['model_no'] 	= $post['model_no'];
		$itemdata['service_contract_id'] 	= $post['service_contract_id'];

		if ($tmps) { // if system has for this serial_no in warranty table, please update to this.

			$itemdata = array();
			$itemdata['id']				=	$post['cid'];
			$itemdata['warranty_id'] 	= $tmps->warranty_id;
			$itemdata['serial_no'] 	= $tmps->serial_no;
			$itemdata['part_no'] 	= $tmps->product_no;
			$itemdata['model_no'] 	= $tmps->model_no;
			$itemdata['service_contract_id'] 	= $post['service_contract_id'];
		}

		if (!$atelservicecontractitem->bind($itemdata))
			return false;

		$result = $atelservicecontractitem->store();

		if ($atelservicecontract->store()) {

			PluginHelper::importPlugin('atelesis', 'logs');
			$dispatcher = Factory::getApplication()->getDispatcher();
			$log = new stdClass();
			$log->section = 'SERVICE_CONTRACT_ITEM';
			$log->action_type = 'EDIT_SAVE';
			$log->action_by = $user->id;
			$log->action_remarks = 'Saving Service Contract : #' . (($data['service_contract_no']) ? $data['service_contract_no'] : '');
			$log->id = $post['cid'];

			$before_update = json_encode($post);
			$after_update = json_encode($data);

			// Fire the onAfterStoreUser trigger
			$event = new \Joomla\Event\Event('onAfterAction', [
				'log' => $log,
				'before' => $before_update,
				'after' => $after_update,
			]);

			$dispatcher->dispatch('onAfterAction', $event);
		}


		return true;
	}




	/*
			*	
			*	Print Service Contract
			*	
		*/
	public function prints($post)
	{
		$db = &JFactory::getDBO();

		if (empty($post['file_id'])) return false;

		$query = " SELECT filename FROM #__at_rma_downloads WHERE id IN (" . implode(',', $post['file_id']) . ") ";
		$db->setQuery($query);
		$tmps = $db->loadResultArray();

		$pdf = new FPDI();

		foreach ($tmps as $t) {
			$path = JPATH_ADMINISTRATOR . '/atelesis_docs/' . $t;

			/* Check Extensions */
			$info = pathinfo($path);

			switch ($info["extension"]) {
				case 'pdf':

					$pageCount = $pdf->setSourceFile($path);
					for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
						$tplIdx = $pdf->ImportPage($pageNo, '/TrimBox');
						$s = $pdf->getTemplatesize($tplIdx);
						$pdf->AddPage($s['w'] > $s['h'] ? 'L' : 'P', array($s['w'], $s['h']));
						$pdf->useTemplate($tplIdx);
					}

					break;
				case 'tiff':
				case 'jpg':
				case 'gif':
				case 'png':
					// add a page
					$pdf->AddPage();
					$pdf->Image($path, 0, 0, 0, 0);
					break;
			}
		}

		$pdf->IncludeJS("print('false');");
		return $pdf->Output('allied_telesis.pdf', 'I');
	}


	/*
			*	
			*	Get Service Contract ID
			*	
		*/
	public function getServiceContractData($by, $value)
	{
		$db = Factory::getDBO();

		$query = " SELECT s.*, u.name, u.email, u.address, u.contact_name, u.zipcode, u.city, u.state, u.telephone, u.fax FROM #__at_service_contract_product_xref AS si "
			.	" LEFT JOIN #__at_service_contract AS s ON s.id = si.service_contract_id "
			.	" LEFT JOIN #__users AS u ON u.customer_id = s.customer_id ";

		switch ($by) {

			case 'service_contract_id':
				$query .= " WHERE s.id = '$value' ";
				break;
		}

		$db->setQuery($query);
		$result = $db->loadObject();

		return $result;
	}

	public function getDataByServiceContractId($service_contract_id)
	{

		$db = Factory::getDBO();

		$query = " SELECT *  "
			.	" FROM #__at_service_contract_product_xref AS si "
			.	" WHERE si.service_contract_id = '$service_contract_id' "
			.	" ORDER BY si.serial_no ASC ";

		$db->setQuery($query);
		$results = $db->loadObjectList();

		return $results;
	}

	public function getCustomerDataByCustomerID($customer_id)
	{

		$db = Factory::getDBO();

		$query = " SELECT * "
			.	" FROM #__users AS u "
			.	" WHERE u.customer_id = '$customer_id' ";

		$db->setQuery($query);
		$results = $db->loadObject();

		return $results;
	}



	/*
			*	
			*	DELETE DOWNLOAD ID
			*	
		*/

	public function delete_files($post)
	{
		$db = &JFactory::getDBO();

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

			$query = "DELETE FROM #__at_rma_downloads WHERE id = '" . $t->id . "' ";
			$db->setQuery($query);
			$db->query();

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
		$db = &JFactory::getDBO();

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
		$attachments = $db->loadResultArray();

		$subject = 'RMA Attachment';

		$body			=	'<img src="cid:logo_id" alt="logo" /><br />';

		$mail = &JFactory::getMailer();

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
		$mail->AddEmbeddedImage(JPATH_SITE . DS . 'templates' . DS . 'rhuk_milkyway' . DS . 'images' . DS . 'ATelesis_2color_web.png', 'logo_id', 'ATelesis_2color_web.png', 'base64', 'image/png');

		$mail->addRecipient($recipients);

		return $mail->Send();
	}
}
