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
use Joomla\CMS\Mail\MailFactoryInterface;
use Mpdf\Mpdf;
use Joomla\CMS\Filesystem\File as JFile;


/**
 * Log model.
 *
 * @since  1.0.0
 */
class CsvModel extends AdminModel
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
	public $typeAlias = 'com_atelman.log';

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
	public function getTable($type = 'Log', $prefix = 'Administrator', $config = array())
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
			'com_atelman.log',
			'log',
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

	/*
			*
			*	Import CSV for Warranty Registration only
			*
		*/

	public function CSVWarrantyRegistrationInsert()
	{
		$db = Factory::getContainer()->get('DatabaseDriver');

		setlocale(LC_ALL, 'en_US.UTF-8');

		set_time_limit(0);

		jimport('joomla.filesystem.file');

		$file	=	JRequest::getVar('csvfile', NULL, 'files');

		if (!$file)
			return false;

		$filename = JFile::makeSafe($file['name']);

		if (!$filename)
			return false;

		// if filename not csv

		//Set up the source and destination of the file
		$src = $file['tmp_name'];

		$header = array();

		$fp = fopen($src, 'r');

		$line = fgetcsv($fp, 4096); // database header;

		foreach ($line as $t) :
			$header[] = $t;
		endforeach;

		$text = '';

		$atelwarrantyitem 	= &JTable::getInstance('AtelWarrantyItem', 'Table');
		$atelproduct 		= &JTable::getInstance('AtelProduct', 'Table');
		$atelcountry 		= &JTable::getInstance('AtelCountry', 'Table');
		$atelcompany 		= &JTable::getInstance('AtelCompany', 'Table');

		while (($line = fgetcsv($fp, 5000, ',')) !== FALSE) {
			if ($line) {
				$empty	=	false;

				foreach ($line as $t) :
					if (!empty($t)) {
						$notempty = true;
						break;
					}
				endforeach;

				if ($notempty) {
					$data = array();
					for ($i = 0; $i < count($header); $i++) {
						$dt		=	str_replace("'", "\'", $line[$i]);
						$dt		=	str_replace('"', '\"', $dt);

						$data[] = "'" . $dt . "'";
					}

					jimport('joomla.user.helper');

					$fieldcount			= count($data);

					if ($fieldcount != 8)
						return false;

					$atelwarrantyitem->id				= '';
					$atelwarrantyitem->warranty_id		= 0;
					$atelwarrantyitem->product_id		= 0;
					$atelwarrantyitem->customer_id 		= $db->getEscaped($line[0], true);
					$atelwarrantyitem->po_no 			= $db->getEscaped($line[1], true);
					$atelwarrantyitem->so_no 			= $db->getEscaped($line[2], true);
					$atelwarrantyitem->invoice_no 		= $db->getEscaped($line[3], true);
					$product_no 						= $db->getEscaped($line[4], true);
					$model_no 							= $db->getEscaped($line[5], true);
					$atelwarrantyitem->serial_no 		= $db->getEscaped($line[6], true);
					$atelwarrantyitem->purchase_date 	= $db->getEscaped($line[7], true); // dd/mm/yyyy

					$company_name						=	'Allied Telesis'; // hardcoded - must have in company db
					$country_name						=	'Singapore'; // hardcoded - must have in country db

					$atelwarrantyitem->purchase_country = $atelcountry->checkRow($country_name)->id;
					$atelwarrantyitem->purchase_from 	= $atelcompany->checkRow($company_name)->id;

					$tmp = explode('/', $atelwarrantyitem->purchase_date); // d - m - Y
					$purchase_date = date("Y-m-d", mktime(0, 0, 0, $tmp[1], $tmp[0], $tmp[2]));

					// new format purchase_date
					$atelwarrantyitem->purchase_date	=	$purchase_date;
					$atelwarrantyitem->expired_date		=	$purchase_date;

					if ($row = $atelproduct->checkRow(trim($product_no), trim($model_no))) {
						// if exist, update it
						$atelwarrantyitem->product_id 	= $row->id;

						$day 	= 	$tmp[0] - 1;
						$month 	= 	$tmp[1] + intval($row->warranty);
						$year	=	$tmp[2];

						$expired_date = date("Y-m-d", mktime(0, 0, 0, $month, $day, $year));

						$atelwarrantyitem->expired_date 	= 	$expired_date;
					}

					$tmp = $atelwarrantyitem->store();
				}
			}
		}

		fclose($fp);

		return true;
	}


	/*
			*
			*	Import CSV for RMA (2 types : open / close
			*
		*/

	public function CSVRMAImport()
	{
		$db = Factory::getContainer()->get('DatabaseDriver');
		$app   = \Joomla\CMS\Factory::getApplication();
		setlocale(LC_ALL, 'en_US.UTF-8');
		set_time_limit(0);

		$input = $app->input;
		$file  = $input->files->get('rmafile', null, 'array');

		if (!$file) return false;

		$src = $file['tmp_name'];

		$header = array();

		$fp = fopen($src, 'r');

		$line = fgetcsv($fp, 4096); // database header;

		foreach ($line as $t) :
			$header[] = $t;
		endforeach;

		$text = '';

		$atelrmarequest =  $this->getTable('Rmarequest');
		$atelrmaitem =  $this->getTable('Rmaitem');
		$atelwarrantyitem =  $this->getTable('Warrantyitem');
		$atelproduct =  $this->getTable('Product');
		$atelusers 	=  $this->getTable('Users');

		while (($line = fgetcsv($fp, 5000, ',')) !== FALSE) {
			if ($line) {
				$empty	=	false;

				foreach ($line as $t) :
					if (!empty($t)) {
						$notempty = true;
						break;
					}
				endforeach;

				if ($notempty) {
					$data = array();
					for ($i = 0; $i < count($header); $i++) {
						$dt		=	str_replace("'", "\'", $line[$i]);
						$dt		=	str_replace('"', '\"', $dt);

						$data[] = "'" . $dt . "'";
					}


					$fieldcount			= count($data);

					if ($fieldcount != 7)
						return false;

					$rma_number				=	trim($line[0]);
					$request_date			=	trim($line[1]);
					$customer_id 			= trim($line[2]);
					$part_number			=	trim($line[3]);
					$receive_date 		= trim($line[4]);
					$issue_date 			= trim($line[5]);
					$rma_status 			= trim($line[6]);

					$tmp = explode('/', $request_date); // d - m - Y
					$request_date = date("Y-m-d", mktime(0, 0, 0, $tmp[1], $tmp[0], $tmp[2]));

					$tmp = explode('/', $issue_date); // d - m - Y
					$issue_date = date("Y-m-d", mktime(0, 0, 0, $tmp[1], $tmp[0], $tmp[2]));

					$tmp = explode('/', $receive_date); // d - m - Y
					$receive_date = date("Y-m-d", mktime(0, 0, 0, $tmp[1], $tmp[0], $tmp[2]));

					$atelrmaitem->status = $rma_status;
					$atelrmaitem->received_date = $receive_date;
					$atelrmaitem->created_date = $request_date;
					$atelrmaitem->customer_id = $customer_id;
					$atelrmaitem->is_import_csv = 1;

					$product_id = '';
					if ($row = AtelmanHelper::checkRowProduct($part_number)) {
						$atelrmaitem->product_id 	= $row->id;
					}

					/* create rma request id */
					$customer = AtelmanHelper::getItemByCustomerId($customer_id);
					$country = AtelmanHelper::getCountry($customer->country_id);
					$atelrmarequest->id = '';
					$atelrmarequest->user_id = $customer->id;
					$atelrmarequest->fullname = $customer->name;
					$atelrmarequest->contact_name = $customer->contact_name;
					$atelrmarequest->address = $customer->address . "\r\n" . $customer->city . "\r\n" . $country->country . ' ' . $customer->zipcode;
					$atelrmarequest->telephone = $customer->telephone;
					$atelrmarequest->fax = $customer->fax;
					$atelrmarequest->email = $customer->email;
					$arq = $atelrmarequest->store();

					$atelrmaitem->id				= '';
					$atelrmaitem->rma_request_id = $atelrmarequest->id;
					$atelrmaitem->rmacode		= $rma_number;

					$tmp = $atelrmaitem->store();
				}
			}
		}

		fclose($fp);

		return true;
	}


	/*
			*
			* 	Export CSV - Currently, only Warranty Registration and RMA Request
			*
		*/
	public function exportCSV($post)
	{
		global $app, $option;
		$app = Factory::getApplication();

		$db				= Factory::getDBO();
		$currentUser	= Factory::getUser();
		$limit = "";
		$fp = fopen('php://output', 'w');
		if ($post['view'] == 'warrantyreg') {

			$titletxt = 'WarrantyRegistration';

			$filter_order		= $app->getUserStateFromRequest("$option.warrantyreg.filter_order",		'filter_order',	'a.name', 'cmd');
			$filter_order_Dir	= $app->getUserStateFromRequest("$option.warrantyreg.filter_order_Dir",	'filter_order_Dir',	'',	'word');

			$filter_company		= $app->getUserStateFromRequest("$option.warrantyreg.filter_company",	'filter_company',	0, 'int');
			$filter_country		= $app->getUserStateFromRequest("$option.warrantyreg.filter_country",	'filter_country',	0,	'int');
			$filter_expiry_month	= $app->getUserStateFromRequest("$option.warrantyreg.filter_country", 'filter_expiry_month',	0,	'int');
			$search				= $app->getUserStateFromRequest("$option.warrantyreg.search",			'search', 	'',		'string');

			if (strpos($search, '"') !== false) {
				$search = str_replace(array('=', '<'), '', $search);
			}
			$search = JString::strtolower($search);

			$limit		= $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
			$limitstart = $app->getUserStateFromRequest($option . '.warrantyreg.limitstart', 'limitstart', 0, 'int');

			$where = array();
			if (isset($search) && $search != '') {
				$searchEscaped = $db->Quote('%' . $db->getEscaped($search, true) . '%', false);
				$where[] = ' p.product_no LIKE ' . $searchEscaped . ' OR w.serial_no LIKE ' . $searchEscaped;
			}

			if ($currentUser->gid == 24) { // distributor, must see from groups management first
				$query = " SELECT gx.group_id, GROUP_CONCAT( gcx.country_id SEPARATOR ',') AS countries, gcox.company_id FROM #__at_group_xref AS gx "
					.	" LEFT JOIN #__at_group_country_xref AS gcx ON gcx.group_id = gx.group_id "
					.	" LEFT JOIN #__at_group_company_xref AS gcox ON gcox.group_id = gx.group_id "
					.	" WHERE gx.user_id = " . $currentUser->id
					.	" GROUP BY gx.group_id ";

				$db->setQuery($query);
				$group = $db->loadObject();

				$where[] = ' w.purchase_from = ' . $group->company_id;
				$where[] = ' w.purchase_country IN (' . $group->countries . ')';
			}


			// ensure filter_order has a valid value.
			if (!in_array($filter_order, array('w.id', 'p.product_no', 'p.serial_no', 'c.country'))) {
				$filter_order = 'w.id';
			}

			if (!in_array(strtoupper($filter_order_Dir), array('ASC', 'DESC'))) {
				$filter_order_Dir = '';
			}

			if ($filter_company) {
				$where[] = ' w.purchase_from = ' . $filter_company;
			}

			if ($filter_country) {
				$where[] = ' w.purchase_country = ' . $filter_country;
			}

			if ($filter_expiry_month) {
				$where[] = ' w.expired_date <= DATE_ADD(CURDATE(), INTERVAL ' . $filter_expiry_month . ' MONTH) ';
			}

			$orderby = ' ORDER BY ' . $filter_order . ' ' . $filter_order_Dir;
			$where = (count($where) ? ' WHERE (' . implode(') AND (', $where) . ')' : '');

			$query = ' SELECT w.*,wi.*, p.product_no , c.country AS country_name_2, co.company_name AS company_name_2, wc.country_name AS country_registrant '
				. ' FROM #__at_warranty_items AS w '
				. ' LEFT JOIN #__at_warranty_register AS wi ON wi.id = w.warranty_id '
				. ' LEFT JOIN #__at_products AS p ON p.id = w.product_id '
				. ' LEFT JOIN #__at_countries AS c ON c.id = w.purchase_country '
				. ' LEFT JOIN #__at_companies AS co ON co.id = w.purchase_from '
				. ' LEFT JOIN #__at_world_countries AS wc ON wc.country_code = wi.country '
				. $filter
				. $where
				. $orderby;

			$db->setQuery($query);
			$rows	=	$db->loadObjectList();

			$headers = array();

			$headers[] = '"First Name"';
			$headers[] = '"Last Name"';
			$headers[] = '"Address"';
			$headers[] = '"City"';
			$headers[] = '"Postal Code"';
			$headers[] = '"Country"';
			$headers[] = '"Telephone"';
			$headers[] = '"Fax"';
			$headers[] = '"Email"';
			$headers[] = '"Company Name"';
			$headers[] = '"Job Title"';
			$headers[] = '"Created Date"';

			$headers[] = '"Model No"';
			$headers[] = '"Serial No"';
			$headers[] = '"Date of Purchase"';
			$headers[] = '"Purchase Country"';
			$headers[] = '"Purchase Company"';
			$headers[] = '"Comments"';
			$headers[] = '"Expired Date"';

			$headers = implode(',', $headers);

			$csvcontent = array();

			foreach ($rows as $r) :

				$row = array();
				$row[] = '"' . ($r->first_name) ? $r->first_name : 'N/A' . '"';
				$row[] = '"' . ($r->last_name) ? $r->last_name : 'N/A' . '"';
				$row[] = '"' . ($r->address) ? nl2br($r->address) : 'N/A' . '"';
				$row[] = '"' . ($r->city) ? $r->city : 'N/A' . '"';
				$row[] = '"' . ($r->postal_code) ? $r->postal_code : 'N/A' . '"';
				$row[] = '"' . ($r->country_registrant) ? $r->country_registrant : 'N/A' . '"';
				$row[] = '"' . ($r->telephone) ? $r->telephone : 'N/A' . '"';
				$row[] = '"' . ($r->fax) ? $r->fax : 'N/A' . '"';
				$row[] = '"' . ($r->email) ? $r->email : 'N/A' . '"';
				$row[] = '"' . ($r->company_name) ? $r->company_name : 'N/A' . '"';
				$row[] = '"' . ($r->job_title) ? $r->job_title : 'N/A' . '"';
				$row[] = '"' . ($r->created_date) ? $r->created_date : 'N/A' . '"';

				$row[] = '"' . ($r->product_no) ? $r->product_no : 'N/A' . '"';
				$row[] = '"' . ($r->serial_no) ? $r->serial_no : 'N/A' . '"';
				$row[] = '"' . ($r->purchase_date) ? $r->purchase_date : 'N/A' . '"';
				$row[] = '"' . ($r->country_name_2) ? $r->country_name_2 : 'N/A' . '"';
				$row[] = '"' . ($r->company_name_2) ? $r->company_name_2 : 'N/A' . '"';
				$row[] = '"' . ($r->comments) ? $r->comments : 'N/A' . '"';
				$row[] = '"' . ($r->expired_date) ? $r->expired_date : 'N/A' . '"';

				$csvcontent[] = implode(',', $row);

			endforeach;
		} else if ($post['view'] == 'rmarequest') {
			$titletxt = 'RMARequest';

			$filter_order		= $app->getUserStateFromRequest("filter_order",	'filter_order',		'r.created_date',	'cmd');
			$filter_order_Dir	= $app->getUserStateFromRequest("filter_order_Dir",	'filter_order_Dir',	'DESC',		'word');
			$filter_status		= $app->getUserStateFromRequest("filter_status",	'filter_status',	'',		'word');
			$filter_company		= $app->getUserStateFromRequest("filter_company",	'filter_company',	0, 'int');
			$filter_country		= $app->getUserStateFromRequest("filter_country",	'filter_country',	0,	'int');

			$search				= $app->getUserStateFromRequest("search", 'search', '', 'string');

			if (strpos($search, '"') !== false) {
				$search = str_replace(array('=', '<'), '', $search);
			}
			$search = strtolower($search);

			$limit		= $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getConfig()->get('list_limit'), 'int');
			$limitstart = $app->getUserStateFromRequest('limitstart', 'limitstart', 0, 'int');

			$where = array();
			if (isset($search) && $search != '') {
				$searchEscaped = $db->Quote('%' . $db->escape($search, true) . '%', false);
				$where[] = 'r.rmacode LIKE ' . $searchEscaped . ' OR p.product_no LIKE ' . $searchEscaped . ' OR p.model_no LIKE ' . $searchEscaped;
			}

			if ($filter_status) {
				$where[] = 'r.`status` = ' . $db->Quote($filter_status, false);
			}

			// if checkboxes are ticked, pull that data only
			if (isset($post['cid'])) {
				$cids = implode(",", $post['cid']);
				if (!empty($cids)) {
					$where[] = " r.`id` IN (" . $cids . ") ";
				}
			}

			if ($currentUser->gid == 24) { // distributor, must see from groups management first

				$query = " SELECT gx.group_id, GROUP_CONCAT( gcx.country_id SEPARATOR ',') AS countries, gcox.company_id "
					.	" FROM #__at_group_xref AS gx "
					.	" LEFT JOIN #__at_group_country_xref AS gcx ON gcx.group_id = gx.group_id "
					.	" LEFT JOIN #__at_group_company_xref AS gcox ON gcox.group_id = gx.group_id "
					.	" WHERE gx.user_id = " . $currentUser->id
					.	" GROUP BY gx.group_id ";

				$db->setQuery($query);
				$group = $db->loadObject();

				$where[] = ' wi.purchase_from = ' . $group->company_id;
				$where[] = ' wi.purchase_country IN (' . $group->countries . ')';
			}

			$orderby = ' ORDER BY ' . $filter_order . ' ' . $filter_order_Dir;
			$where = (count($where) ? ' WHERE (' . implode(') AND (', $where) . ')' : '');


			$query = 'SELECT r.*,rr.*,p.model_no, p.product_no , u1.name AS distributor, wi.serial_no, wi.serial_no_2, wi.purchase_date, wi.so_no, rs.status_name '
				.	' , wi.expired_date, wi.expired_date_manual, wi.extended_expired_date '
				. ' FROM #__at_rma_items AS r '
				. ' LEFT JOIN #__at_rma_request AS rr ON rr.id = r.rma_request_id '
				. ' LEFT JOIN #__at_rma_status AS rs ON rs.status_code = r.status '
				. ' LEFT JOIN #__at_warranty_items AS wi ON wi.id = r.warranty_item_id '
				. ' LEFT JOIN #__at_products AS p ON p.id = wi.product_id '
				. ' LEFT JOIN #__users AS u1 ON u1.customer_id = wi.customer_id '

				. $filter
				. $where
				. $orderby;

			//$db->setQuery($query, $limitstart, $limit);
			$db->setQuery($query);
			$rows	=	$db->loadObjectList();

			$headers = array();
			$headers[] = '"Company"';
			$headers[] = '"Contact"';
			$headers[] = '"Address"';
			$headers[] = '"Telephone"';
			$headers[] = '"Fax"';
			$headers[] = '"Email"';

			$headers[] = '"Distributor"';
			$headers[] = '"Part Number"';
			$headers[] = '"Model Number"';
			$headers[] = '"Serial Number"';
			$headers[] = '"Replacement S/N"';
			$headers[] = '"SO Number"';
			$headers[] = '"Invoice Number"';
			$headers[] = '"Request Date"';
			$headers[] = '"Expiry Date"';
			$headers[] = '"Warranty"';
			$headers[] = '"Fault Description"';
			$headers[] = '"Remarks"';

			$headers[] = '"RMA Number"';
			$headers[] = '"Status"';
			$headers[] = '"Replacement (Days)"';
			$headers[] = '"Receipt Date"';
			$headers[] = '"Ship Date"';
			$headers[] = '"Closing Date"';


			$headers = implode(',', $headers);

			$csvcontent = array();
			foreach ($rows as $r) :

				$row = array();
				$row[] = '"' . (($r->fullname) ? $r->fullname : 'N/A') . '"';
				$row[] = '"' . (($r->contact_name) ? $r->contact_name : 'N/A') . '"';
				$row[] = '"' . (($r->address) ? $r->address : 'N/A') . '"';
				$row[] = '"' . (($r->telephone) ? $r->telephone : 'N/A') . '"';
				$row[] = '"' . (($r->fax) ? $r->fax : 'N/A') . '"';
				$row[] = '"' . (($r->email) ? $r->email : 'N/A') . '"';

				$row[] = '"' . (($r->distributor) ? $r->distributor : 'N/A') . '"';
				$row[] = '"' . (($r->product_no) ? $r->product_no : 'N/A') . '"';
				$row[] = '"' . (($r->model_no) ? $r->model_no : 'N/A') . '"';
				$row[] = '"' . (($r->serial_no) ? $r->serial_no : 'N/A') . '"';
				$row[] = '"' . (($r->serial_no_2) ? $r->serial_no_2 : 'N/A') . '"';
				$row[] = '"' . (($r->so_no) ? $r->so_no : 'N/A') . '"';
				$row[] = '"' . (($r->invoice_no) ? $r->invoice_no : 'N/A') . '"';
				$row[] = '"' . (($r->created_date) ? $r->created_date : 'N/A') . '"';

				if ($r->extended_expired_date != '0000-00-00') :
					$r->real_expired_date = date("d/m/Y", strtotime($r->extended_expired_date));
				elseif ($r->expired_date_manual != '0000-00-00'):
					$r->real_expired_date = date("d/m/Y", strtotime($r->expired_date_manual));
				else :
					$r->real_expired_date = date("d/m/Y", strtotime($r->expired_date));
				endif;

				$row[] = '"' . (($r->real_expired_date) ? $r->real_expired_date : 'N/A') . '"';
				$row[] = '"' . (($r->warranty_status) ? $r->warranty_status : 'N/A') . '"';
				$row[] = '"' . (($r->description) ? $r->description : 'N/A') . '"';
				$row[] = '"' . (($r->remarks) ? $r->remarks : 'N/A') . '"';

				$row[] = '"' . (($r->rmacode) ? $r->rmacode : 'N/A') . '"';
				$row[] = '"' . (($r->status_name) ? $r->status_name : 'N/A') . '"';
				$row[] = '"' . (($r->shipping_duration) ? $r->shipping_duration : 'N/A') . '"';
				$row[] = '"' . (($r->received_date != '0000-00-00') ? $r->received_date : 'N/A') . '"';
				$row[] = '"' . (($r->shipped_date != '0000-00-00') ? $r->shipped_date : 'N/A') . '"';
				$row[] = '"' . (($r->closed_date != '0000-00-00') ? $r->closed_date : 'N/A') . '"';

				$csvcontent[] = implode(',', $row);

			endforeach;
		} else if ($post['view'] == 'reportrma') {

			$titletxt = 'ReportRMA';

			$where = array();

			$orderby = '';
			$groupby = '';
			$content = '';

			if ($post['country_id']) {
				$where[] = " u.country_id IN (" . implode(',', $post['country_id']) . ") ";
			}

			switch ($post['rma_report_type']) {
				case 'respond':
					$where[] = " i.rma_assigned_date != '0000-00-00' ";
					$orderby = ' ORDER BY respond_time DESC ';
					break;
				case 'receive':
					$where[] = " i.received_date != '0000-00-00' ";
					$where[] = " i.rma_assigned_date != '0000-00-00' ";
					$orderby = ' ORDER BY receive_time DESC ';
					break;
				case 'ship':
					$where[] = " i.received_date != '0000-00-00' ";
					$where[] = " i.shipped_date != '0000-00-00' ";
					$orderby = ' ORDER BY ship_time DESC ';
					break;
				case 'total':
					$where[] = " i.received_date != '0000-00-00' ";
					$where[] = " i.shipped_date != '0000-00-00' ";
					$orderby = ' ORDER BY total_time DESC ';
					break;
				case 'most-rma-country':

					$groupby = " GROUP BY u.country_id ";
					$orderby = ' ORDER BY total DESC ';

					break;
				case 'most-rma-model':

					$groupby = " GROUP BY i.product_id, u.country_id ";
					$orderby = ' ORDER BY total DESC ';

					break;
				default:

					break;
			}


			// From Date | To_Date
			switch ($post['rma_report_type']) {
				case 'most-rma-country':
				case 'most-rma-model':

					if ($post['from_date'] || $post['to_date']) {

						if ($post['from_date']) {
							$post['from_date'] = strtotime(str_replace('/', '-', $post['from_date']));
							$where[] = " i.created_date >= '" . date("Y-m-d 00:00:00", $post['from_date']) . "' ";
						}

						if ($post['to_date']) {
							$post['to_date'] = strtotime(str_replace('/', '-', $post['to_date']));
							$where[] = " i.created_date <= '" . date("Y-m-d 00:00:00", $post['to_date']) . "' ";
						}
					}

					break;
			}

			$where = (count($where) ? ' WHERE (' . implode(') AND (', $where) . ')' : '');

			$csvcontent = array();

			switch ($post['rma_report_type']) {
				case 'respond':
				case 'receive':
				case 'ship':
				case 'total':
					$query = " SELECT i.*, c.country AS country_name , p.product_no,p.model_no, r.fullname, r.contact_name, "
						.	"	DATEDIFF(i.rma_assigned_date,i.created_date) AS respond_time , "
						.	" DATEDIFF(i.received_date, i.rma_assigned_date) AS receive_time, "
						.	" DATEDIFF(i.shipped_date, i.received_date) AS ship_time, "
						.	" DATEDIFF( IF(i.shipped_date >= i.received_date,i.shipped_date,i.received_date), i.created_date) AS total_time "
						.	" FROM #__at_rma_items AS i "
						.	" LEFT JOIN #__users AS u ON u.customer_id = i.customer_id "
						. " LEFT JOIN #__at_products AS p ON p.id = i.product_id "
						.	" LEFT JOIN #__at_rma_request AS r ON r.id = i.rma_request_id "
						. " LEFT JOIN #__at_countries AS c ON c.id = u.country_id "
						.	$where
						. $orderby
						. $limit;

					$db->setQuery($query);
					$rows = $db->loadObjectList();

					$content = '';

					if ($rows) :

						$headers = array();
						$headers[] = '"Requestor"';
						$headers[] = '"Part Number"';
						$headers[] = '"Model Number"';
						$headers[] = '"Requested S/N"';
						$headers[] = '"Country"';
						$headers[] = '"Duration (Days)"';

						$headers = implode(',', $headers);

						$csvcontent = array();
						foreach ($rows as $r) :

							$row = array();
							$row[] = '"' . $r->fullname . ' (' . $r->contact_name . ')"';
							$row[] = '"' . $r->product_no . '"';
							$row[] = '"' . $r->model_no . '"';
							$row[] = '"' . $r->requested_sn . '"';
							$row[] = '"' . $r->country_name . '"';
							$row[] = '"' . $this->myduration($post['rma_report_type'], $r) . '"';

							$csvcontent[] = implode(',', $row);

						endforeach;
					else :
						$csvcontent[] = 'No Report';
					endif;

					break;


				case 'most-rma-country':
					$query = " SELECT c.country AS country_name, COUNT(i.id) AS total "
						.	" FROM #__at_rma_items AS i "
						.	" LEFT JOIN #__users AS u ON u.customer_id = i.customer_id "
						. " LEFT JOIN #__at_products AS p ON p.id = i.product_id "
						.	" LEFT JOIN #__at_rma_request AS r ON r.id = i.rma_request_id "
						. " LEFT JOIN #__at_countries AS c ON c.id = u.country_id "
						.	$where
						.	$groupby
						. $orderby
						. $limit;

					$db->setQuery($query);
					$rows = $db->loadObjectList();

					$content = '';

					if ($rows) :

						$headers = array();
						$headers[] = '"Country"';
						$headers[] = '"Total"';

						$headers = implode(',', $headers);

						$num_total = 0;
						$csvcontent = array();

						foreach ($rows as $r) :

							$row = array();
							$row[] = '"' . $r->country_name . '"';
							$row[] = '"' . $r->total . '"';


							$csvcontent[] = implode(',', $row);

							$num_total += (int) $r->total;

						endforeach;

					else :
						$csvcontent[] = 'No Report Available.';
					endif;

					break;
				case 'most-rma-model':
					$query = " SELECT c.country AS country_name, i.product_id , p.product_no, p.model_no , COUNT(i.id) AS total "
						.	" FROM #__at_rma_items AS i "
						.	" LEFT JOIN #__users AS u ON u.customer_id = i.customer_id "
						. " LEFT JOIN #__at_products AS p ON p.id = i.product_id "
						.	" LEFT JOIN #__at_rma_request AS r ON r.id = i.rma_request_id "
						. " LEFT JOIN #__at_countries AS c ON c.id = u.country_id "
						.	$where
						.	$groupby
						. $orderby
						. $limit;

					$db->setQuery($query);
					$rows = $db->loadObjectList();

					$content = '';

					if ($rows) :

						$headers = array();
						$headers[] = '"Part Number"';
						$headers[] = '"Model Number"';
						$headers[] = '"Country"';
						$headers[] = '"Total"';

						$headers = implode(',', $headers);

						$num_total = 0;

						$csvcontent = array();

						foreach ($rows as $r) :

							$row = array();
							$row[] = '"' . $r->product_no . '"';
							$row[] = '"' . $r->model_no . '"';
							$row[] = '"' . $r->country_name . '"';
							$row[] = '"' . $r->total . '"';


							$csvcontent[] = implode(',', $row);

							$num_total += (int) $r->total;
						endforeach;

					else :
						$csvcontent[] = 'No Report Available.';
					endif;

					break;
				default:
					$csvcontent[] = 'Please choose Report Type';
					break;
			}
		}




		fclose($fp);

		if (preg_match('Opera(/| )([0-9].[0-9]{1,2})', $_SERVER['HTTP_USER_AGENT'])) {
			$UserBrowser = "Opera";
		} elseif (preg_match('MSIE ([0-9].[0-9]{1,2})', $_SERVER['HTTP_USER_AGENT'])) {
			$UserBrowser = "IE";
		} else {
			$UserBrowser = '';
		}

		$mime_type = ($UserBrowser == 'IE' || $UserBrowser == 'Opera') ? 'application/octetstream' : 'application/octet-stream';
		@ob_end_clean();
		ob_start();

		$host  = $_SERVER['HTTP_HOST'];
		$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');

		$mime_type = ($UserBrowser == 'IE' || $UserBrowser == 'Opera') ? 'application/octetstream' : 'application/octet-stream';
		@ob_end_clean();
		ob_start();

		$csvfilename = 'AT_' . $titletxt . '_' . date('Ymd_His') . '.csv';
		header('Content-Type: ' . $mime_type);
		header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		if ($UserBrowser == 'IE') {
			header("Content-Disposition: inline; filename=\"{$csvfilename}\"");
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
		} else {
			header("Content-Disposition: attachment; filename=\"{$csvfilename}\"");
			header('Pragma: no-cache');
		}

		$csvcontent = implode("\n", $csvcontent);
		print @$headers . "\r" . $csvcontent;

		exit;
	}


	public function CSVimportfile($filecsvtype)
	{

		jimport('joomla.filesystem.file');

		$filepathfromroot = '';
		switch ($filecsvtype) {
			case 'isb':
				$filepathfromroot = 'atelftp/isb.csv';
				$urltorun = \Joomla\CMS\Uri\Uri::root() . 'originaldataisb.php';
				break;
			case 'isbdata':
				$filepathfromroot = 'atelftp/isbdata.csv';
				$urltorun = \Joomla\CMS\Uri\Uri::root() . 'warrantyregcron.php';
				break;
			case 'customer':
				$filepathfromroot = 'atelftp/customer.csv';
				$urltorun = \Joomla\CMS\Uri\Uri::root() . 'customerupload.php';
				break;
		}

		if (file_exists(JPATH_SITE . '/' . $filepathfromroot)) {

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1");
			curl_setopt($ch, CURLOPT_URL, $urltorun);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
			//curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

			$content = curl_exec($ch);

			return true;
		} else {
			return false;
		}
	}

	/*
			*
			* CSV Warranty Registration Update
			*
		*/
	public function CSVWarrantyRegistrationUpdate()
	{
		$app   = \Joomla\CMS\Factory::getApplication();
		$db = $this->getDBO();

		setlocale(LC_ALL, 'en_US.UTF-8');
		set_time_limit(0);

		jimport('joomla.filesystem.file');
		$fp = fopen('php://output', 'w');
		$input = $app->input;
		$file  = $input->files->get('csvfile', null, 'array');

		if (!$file) return false;

		$src = $file['tmp_name'];
		if (strtolower(JFile::getExt($file['name'])) != 'csv') return false;

		// if filename not csv

		$header = array();

		$fp = fopen($src, 'r');

		$line = fgetcsv($fp, 4096); // database header;

		foreach ($line as $t) :
			$header[] = $t;
		endforeach;

		$text = '';

		$atelwarrantyitem 	= $this->getTable('Warrantyitem');

		while (($line = fgetcsv($fp, 5000, ',')) !== FALSE) {
			if ($line) {
				$empty	=	false;

				foreach ($line as $t) :
					if (!empty($t)) {
						$notempty = true;
						break;
					}
				endforeach;

				if ($notempty) {
					$data = array();
					for ($i = 0; $i < count($header); $i++) {
						$dt		=	str_replace("'", "\'", $line[$i]);
						$dt		=	str_replace('"', '\"', $dt);

						$data[] = "'" . $dt . "'";
					}


					$fieldcount			= count($data);

					if ($fieldcount != 8) return false;

					$so_number = $line[0];
					$serial_no = $line[1];

					// Check whether Serial No matched! If not, ignore this row. ---- so_no = ". $db->Quote(trim($so_number), false)." AND
					$query = " SELECT * FROM #__at_warranty_items WHERE serial_no = " . $db->quote(trim($serial_no), false) . " ORDER BY purchase_date DESC LIMIT 1 ";
					$db->setQuery($query);
					$item = $db->loadObject();

					if (!$item->id) continue;

					$atelwarrantyitem->id = $item->id;
					// REPLACEMENT PART NO.
					if ($line[3] != '-') {
						$atelwarrantyitem->replacement_pn 	= $db->escape($line[3], true);
						$query = " SELECT id FROM #__at_products WHERE product_no = '" . $line[3] . "' LIMIT 1 ";
						$db->setQuery($query);
						$result = $db->loadResult();

						if (!$result) {
							unset($atelwarrantyitem->replacement_pn);
						}
					}
					if ($line[3] == '-') {
						unset($atelwarrantyitem->replacement_pn);
					}

					// REPLACEMENT SERIAL NO.
					if ($line[2] != '-') {
						$atelwarrantyitem->serial_no_2 	= $db->escape($line[2], true);
					}
					if ($line[2] == '-') {
						unset($atelwarrantyitem->serial_no_2);
					}

					// CUSTOMER ID
					if ($line[4] != '-') {
						$atelwarrantyitem->customer_id = $db->escape($line[4], true);
					}
					if ($line[4] == '-') {
						unset($atelwarrantyitem->customer_id);
					}

					// Must be check-on this part!!!!
					// EXPIRED DATE (MANUAL)
					if ($line[5] != '-') {

						if (strtotime(str_replace("/", "-", trim($line[5]))) > 0) {
							$atelwarrantyitem->expired_date_manual 	= $db->escape(date("Y-m-d", strtotime(str_replace("/", "-", trim($line[5])))), true);
						} else {
							$atelwarrantyitem->expired_date_manual 	= NULL;
						}
					} else {
						$atelwarrantyitem->expired_date_manual 	= $item->expired_date_manual;
					}
					if ($line[5] == '-') {
						unset($atelwarrantyitem->expired_date_manual);
					}

					// EXTENDED WARRANTY (MONTH)
					if ($line[6] != '-') {
						$atelwarrantyitem->extended_warranty 	= $db->escape($line[6], true);
					}
					if ($line[6] == '-') {
						unset($atelwarrantyitem->extended_warranty);
					}

					if (isset($atelwarrantyitem->extended_warranty)) {

						$tmp = array();

						if ($item->expired_date) { // Existing Expired Date
							$tmp = explode('-', $item->expired_date); // Y - m - d
						}

						if ($atelwarrantyitem->extended_warranty > 0) {
							$atelwarrantyitem->extended_expired_date = date("Y-m-d", mktime(0, 0, 0, (int)$tmp[1] + (int)$atelwarrantyitem->extended_warranty, (int)$tmp[2], (int)$tmp[0]));
						} else {
							$atelwarrantyitem->extended_expired_date = NULL;
						}
					} else { // if not set, or "-"

						$tmp = array();

						if ($item->expired_date) { // Existing Expired Date
							$tmp = explode('-', $item->expired_date); // Y - m - d
						}

						// get current from warranty reg. db
						if ($item->extended_warranty > 0) {
							$atelwarrantyitem->extended_expired_date = date("Y-m-d", mktime(0, 0, 0, (int)$tmp[1] + (int)$item->extended_warranty, (int)$tmp[2], (int)$tmp[0]));
						} else {
							$atelwarrantyitem->extended_expired_date = NULL;
						}
					}

					// COMMENTS
					if ($line[7] != '-') {
						$atelwarrantyitem->comments 	= $db->escape($line[7], true);
					}
					if ($line[7] == '-') {
						unset($atelwarrantyitem->comments);
					}
					$atelwarrantyitem->created_date = $item->created_date;
					$tmp = $atelwarrantyitem->store();
				}
			}
		}

		fclose($fp);

		return true;
	}


	public function exportLogs()
	{
		$db = Factory::getDBO();
		$app = Factory::getApplication();
		$order_by = '';
		$filter_order = $app->input->get('filter_order');
		$filter_order_Dir = $app->input->get('filter_order_Dir');

		if ($filter_order && $filter_order_Dir) {
			$order_by = ' ORDER BY ' . $filter_order . ' ' . $filter_order_Dir;
		}

		$query = " SELECT l.*,u.name FROM #__at_logs AS l LEFT JOIN #__users AS u ON u.id = l.action_by " . $order_by;
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$headers = array();
		$headers[] = '"Action Date"';
		$headers[] = '"Module"';
		$headers[] = '"Action Type"';
		$headers[] = '"Action By"';
		$headers[] = '"Remarks"';

		$headers = implode(',', $headers);

		$csvcontent = array();
		foreach ($rows as $r) :

			$row = array();
			$row[] = '"' . ($r->action_date) ? $r->action_date : 'N/A' . '"';
			$row[] = '"' . ($r->section) ? $r->section : 'N/A' . '"';
			$row[] = '"' . ($r->action_type) ? $r->action_type : 'N/A' . '"';
			$row[] = '"' . ($r->name) ? $r->name : 'N/A' . '"';
			$row[] = '"' . ($r->remarks) ? $r->remarks : 'N/A' . '"';

			$csvcontent[] = implode(',', $row);

		endforeach;

		if (preg_match('Opera(/| )([0-9].[0-9]{1,2})', $_SERVER['HTTP_USER_AGENT'])) {
			$UserBrowser = "Opera";
		} elseif (preg_match('MSIE ([0-9].[0-9]{1,2})', $_SERVER['HTTP_USER_AGENT'])) {
			$UserBrowser = "IE";
		} else {
			$UserBrowser = '';
		}

		$mime_type = ($UserBrowser == 'IE' || $UserBrowser == 'Opera') ? 'application/octetstream' : 'application/octet-stream';
		@ob_end_clean();
		ob_start();

		$host  = $_SERVER['HTTP_HOST'];
		$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');

		$mime_type = ($UserBrowser == 'IE' || $UserBrowser == 'Opera') ? 'application/octetstream' : 'application/octet-stream';
		@ob_end_clean();
		ob_start();

		$csvfilename = 'AT_Logs_' . date('Ymd_His') . '.csv';
		header('Content-Type: ' . $mime_type);
		header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		if ($UserBrowser == 'IE') {
			header("Content-Disposition: inline; filename=\"{$csvfilename}\"");
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
		} else {
			header("Content-Disposition: attachment; filename=\"{$csvfilename}\"");
			header('Pragma: no-cache');
		}

		$csvcontent = implode("\n", $csvcontent);
		print $headers . "\r" . $csvcontent;
	}

	private function myduration($type, $data)
	{

		//type cast, current time, difference in timestamps
		$timestamp      = 0;
		$current_time   = 0;
		$diff           = 0;

		$data->created_date = date("d-m-Y", strtotime($data->created_date));

		switch ($type) {
			case 'respond':

				$timestamp      = (int) strtotime($data->created_date);
				$current_time   = (int) strtotime($data->rma_assigned_date);
				$diff           = $current_time - $timestamp;

				break;
			case 'receive':

				//type cast, current time, difference in timestamps
				$timestamp      = (int) strtotime($data->rma_assigned_date);
				$current_time   = (int) strtotime($data->received_date);
				$diff           = $current_time - $timestamp;

				break;
			case 'ship':

				//type cast, current time, difference in timestamps
				$timestamp      = (int) strtotime($data->received_date);
				$current_time   = (int) strtotime($data->shipped_date);
				$diff           = $current_time - $timestamp;

				break;
			case 'total':

				$latest_date = $data->shipped_date;
				if (strtotime($data->received_date) > strtotime($data->shipped_date)) {
					$latest_date = $data->received_date;
				}

				//type cast, current time, difference in timestamps
				$timestamp      = (int) strtotime($data->created_date);
				$current_time   = (int) strtotime($latest_date);
				$diff           = $current_time - $timestamp;
				break;
		}

		//intervals in seconds
		$intervals      = array('day' => 86400); // 'year' => 31556926, 'month' => 2629744, 'hour' => 3600, 'minute'=> 60, 'week' => 604800

		$diff = floor($diff / $intervals['day']);
		return (int) $diff;
	}
}
