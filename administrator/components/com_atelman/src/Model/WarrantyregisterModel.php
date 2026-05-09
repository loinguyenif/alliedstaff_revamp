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
 * Warrantyregister model.
 *
 * @since  1.0.0
 */
class WarrantyregisterModel extends AdminModel
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
	public $typeAlias = 'com_atelman.warrantyregister';

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
	public function getTable($type = 'Warrantyregister', $prefix = 'Administrator', $config = array())
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
			'com_atelman.warrantyregister',
			'warrantyregister',
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
		$data = Factory::getApplication()->getUserState('com_atelman.edit.warrantyregister.data', array());

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
	 * Method to duplicate an Warrantyregister
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
				$db->setQuery('SELECT MAX(ordering) FROM #__at_warranty_register');
				$max             = $db->loadResult();
				$table->ordering = $max + 1;
			}
		}
	}


	public function checkWarranty()
	{

		$app = \Joomla\CMS\Factory::getApplication();
		// helper
		$helper = new AtelmanHelper();
		$user = Factory::getUser();
		$db = Factory::getDBO();
		$group_id = $user->gid; // 23	= Manager, 24 = Distributor

		$po_no = $app->input->get('po_no');
		$so_no = $app->input->get('so_no');
		$invoice_no = $app->input->get('invoice_no');
		$serial_no = $app->input->get('serial_no');

		$html = '';

		if ($po_no || $so_no || $invoice_no || $serial_no) {

			$where = array();

			if ($po_no) {
				$where[] = ' w.po_no = ' . $db->quote($po_no);
			}
			if ($so_no) {
				$where[] = ' w.so_no = ' . $db->quote($so_no);
			}
			if ($invoice_no) {
				$where[] = ' w.invoice_no = ' . $db->quote($invoice_no);
			}
			if ($serial_no) {
				$where[] = " (CASE "
					.	" WHEN w.serial_no_2 != '' THEN LOWER(serial_no_2) LIKE " . $db->quote($serial_no, false) . " "
					.	" WHEN w.serial_no != '' THEN LOWER(serial_no) LIKE " . $db->quote($serial_no, false)
					.	" ELSE 0 "
					. " END) ";
			}

			/**/
			$query = " SELECT customer_id, country_id FROM #__users WHERE id = " . $user->id;
			$db->setQuery($query);
			$tmpobj = $db->loadObject();

			$customer_id 		= 	$tmpobj->customer_id;
			$country_id 		= 	$tmpobj->country_id;

			if ($group_id == 23) { // Manager can see Distributor on that country

				$query = " SELECT customer_id FROM #__users WHERE country_id = " . $country_id;
				$db->setQuery($query);
				$arrtmp = $db->loadColumn();

				$where[] = ' w.customer_id IN (' . $db->quote(implode('\',\'', $arrtmp), false) . ') ';
			} else if ($group_id == 24) {

				$where[] = ' w.customer_id = ' . $db->quote($customer_id, false);
			}

			$where = (count($where) ? ' WHERE ' . implode(' AND ', $where) . '' : '');

			$query = " SELECT w.*, u.name FROM #__at_warranty_items AS w "
				.	" LEFT JOIN #__users AS u ON u.customer_id = w.customer_id "
				.	$where;

			$db->setQuery($query);
			$items = $db->loadObjectList();

			if ($items) {

				$html .= '<table border="1" style="border-collapse:collapse;">';
				$html .= '<tr style="font-weight:bold;">';
				$html .= '<td width="180px" align="left">Distributor</td>';
				$html .= '<td width="150px" align="left">PO Number</td>';
				$html .= '<td width="70px" align="left">SO Number</td>';
				$html .= '<td width="70px" align="left">Invoice Number</td>';
				$html .= '<td width="130px" align="left">Part Number</td>';
				$html .= '<td width="170px" align="left">Model Number</td>';
				$html .= '<td width="130px" align="left">Serial Number</td>';
				$html .= '<td width="90px" align="left">Ship Date</td>';
				$html .= '<td width="90px" align="left">Expiry Date</td>';
				$html .= '</tr>';

				foreach ($items as $i):

					$expiry_date = $i->expired_date;
					if ($i->extended_expired_date != '0000-00-00 00:00:00' && $i->extended_expired_date != NULL) $expiry_date = $i->extended_expired_date;
					if ($i->expired_date_manual != '0000-00-00 00:00:00' && $i->expired_date_manual != NULL) $expiry_date = $i->expired_date_manual;


					$html .= '<tr>';
					$html .= '<td>' . $i->name . '</td>';
					$html .= '<td>' . $i->po_no . '</td>';
					$html .= '<td>' . $i->so_no . '</td>';
					$html .= '<td>' . $i->invoice_no . '</td>';
					$html .= '<td>' . ((!empty($i->replacement_pn)) ? $i->replacement_pn : $helper->getItemById('products', $i->product_id)->product_no) . '</td>';
					$html .= '<td>' . ((!empty($i->replacement_pn)) ? $helper->getProductByPartNumber($i->replacement_pn)->model_no : $helper->getItemById('products', $i->product_id)->model_no) . '</td>';
					$html .= '<td>' . (($i->serial_no_2) ? $i->serial_no_2 : $i->serial_no) . '</td>';
					$html .= '<td>' . date("d M Y", @strtotime($i->purchase_date)) . '</td>';
					$html .= '<td>' . date("d M Y", @strtotime($expiry_date)) . '</td>';
					$html .= '</tr>';
				endforeach;

				$html .= '</table>';
			}
		}

		$obj = new stdClass();
		$obj->html = $html;
		$obj->status = ((!empty($items)) ? 1 : 0);
		return $obj;
	}
}
