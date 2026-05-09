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


/**
 * Log model.
 *
 * @since  1.0.0
 */
class CsvservicecontractModel extends AdminModel
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
			
				Import CSV for Service Contract
				Column A : Service Contract No. <br />
				Column B : PO Number <br />
				Column C : Client Name <br />
				Column D : Distributor <br />
				Column E : Service Type <br />
				Column F : Length Cover <br />
				Column G : Model No <br />
				Column H : Serial No <br />
				Column I : Expiry Date <br />
				Column J : Start Date <br />
				
			
		*/

	public function CSVServiceContractImport()
	{
		$db = Factory::getContainer()->get('DatabaseDriver');
		$app   = \Joomla\CMS\Factory::getApplication();
		setlocale(LC_ALL, 'en_US.UTF-8');
		set_time_limit(0);

		$input = $app->input;
		$file  = $input->files->get('servicecontractfile', null, 'array');

		if (!$file) return false;

		$src = $file['tmp_name'];

		$header = array();

		$fp = fopen($src, 'r');

		$line = fgetcsv($fp, 4096); // database header;

		foreach ($line as $t) :
			$header[] = $t;
		endforeach;

		$text = '';

		$atelservicecontract = $this->getTable('Servicecontract');
		$atelservicecontractitem = $this->getTable('Servicecontractproductxref');

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

					if ($fieldcount != 9) return false;

					$service_contract_no	=	trim($line[0]);
					$po_no					=	trim($line[1]);
					$client_name 			= 	trim($line[2]);
					$service_type 			= 	trim($line[3]);
					$length_cover			=	trim($line[4]);
					$model_no				=	trim($line[5]);
					$serial_no 				= 	trim($line[6]);
					$expiry_date			=	trim($line[7]);
					$start_date				=	trim($line[8]);

					if ($service_contract_no == 'VN151937') {
						$serial_no = substr($serial_no, 0, 16); // take first 16 characters
					}


					switch ($service_type) {
						case 'Net.Cover Advanced':
						case 'Net. Cover Advanced':
						case 'Delivery Standard':
							$service_type = 'NCA';
							break;
						case 'Software Download Service':
							$service_type = 'SOFTWARE';
							break;
						case 'Net. Cover Preferred':
						case 'Net.Cover Preferred':
						case 'Basic':
							$service_type = 'NCP';
							break;
						case 'Net. Cover Elite':
						case 'Net.Cover Elite':
							$service_type = 'NCE';
							break;
					}


					if (empty($service_contract_no)) {
						continue;
					}

					$query = " SELECT w.customer_id, w.product_id , w.id FROM #__at_warranty_items AS w WHERE w.serial_no = '$serial_no' LIMIT 1; ";
					$db->setQuery($query);
					$detail = $db->loadObject();

					if ($expiry_date == 'TBA' || $expiry_date == '') {
						$expiry_date = '0000-00-00';
					} else {
						$tmp = explode('/', $expiry_date); // d - m - Y
						$expiry_date = date("Y-m-d", mktime(0, 0, 0, $tmp[1], $tmp[0], $tmp[2]));
					}

					if ($start_date == 'TBA' || $start_date == '') {
						$start_date = '0000-00-00';
					} else {
						$tmp = explode('/', $start_date); // d - m - Y
						$start_date = date("Y-m-d", mktime(0, 0, 0, $tmp[1], $tmp[0], $tmp[2]));
					}

					$query = " SELECT id FROM #__at_service_contract WHERE service_contract_no = '$service_contract_no' LIMIT 1; ";
					$db->setQuery($query);
					$id = $db->loadResult();

					$atelservicecontract->id = '';
					if ($id) {
						$atelservicecontract->id = $id;
					}

					$atelservicecontract->service_contract_no 		= $service_contract_no;
					$atelservicecontract->po_no 					= $po_no;
					$atelservicecontract->start_date 				= $start_date;
					$atelservicecontract->expiry_date 				= $expiry_date;
					$atelservicecontract->cover_length				= $length_cover;
					if (!empty($detail->customer_id)) {
						$atelservicecontract->customer_id				= $detail->customer_id;
					}
					$atelservicecontract->service_type 				= $service_type;
					$atelservicecontract->client_name 				= $client_name;

					$result = $atelservicecontract->store();

					$service_contract_item_id = '';
					$query = " SELECT id FROM #__at_service_contract_product_xref WHERE serial_no = '" . $serial_no . "' AND service_contract_id = '" . $atelservicecontract->id . "' LIMIT 1; ";
					$db->setQuery($query);
					$result = $db->loadResult();

					if ($result) {
						$service_contract_item_id = $result;
					}

					$query = " SELECT * FROM #__at_products WHERE id = '" . $detail->product_id . "' LIMIT 1; ";
					$db->setQuery($query);
					$parts = $db->loadObject();

					$atelservicecontractitem->id 					= $service_contract_item_id;
					$atelservicecontractitem->service_contract_id 	= $atelservicecontract->id;
					$atelservicecontractitem->warranty_id 			= $detail->id;
					$atelservicecontractitem->serial_no 			= $serial_no;
					$atelservicecontractitem->model_no 				= $parts->model_no;
					$atelservicecontractitem->part_no 				= $parts->product_no;

					$result = $atelservicecontractitem->store();
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
		$db = Factory::getContainer()->get('DatabaseDriver');
		$app   = Factory::getApplication();

		$db				= Factory::getDBO();
		$currentUser	= Factory::getUser();

		$titletxt = 'ServiceContract';

		$filter_expiry = $post['filter_expiry'];
		$filter_distributor = $post['filter_distributor'];
		$filter_country = $post['filter_country'];

		$search =  $post['filter']['search'];

		if (strpos($search, '"') !== false) {
			$search = str_replace(array('=', '<'), '', $search);
		}
		$search = strtolower($search);

		$where = array();
		if (isset($search) && $search != '') {
			$searchEscaped = $db->quote('%' . $db->escape($search, true) . '%', false);
			$where[] = ' sp.part_no LIKE ' . $searchEscaped . ' OR sp.model_no LIKE ' . $searchEscaped . ' OR sp.serial_no LIKE ' . $searchEscaped . ' OR s.service_contract_no LIKE ' . $searchEscaped;
		}

		if ($filter_expiry) {
			$where[] = ' s.expiry_date > ' . $db->quote(date("Y-m-d", time()), false);
			$where[] = ' s.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 6 MONTH)';
		}

		if ($filter_distributor) {
			$where[] = " s.customer_id = '$filter_distributor' ";
		}

		if ($filter_distributor) {
			$where[] = " s.customer_id = '$filter_distributor' ";
		}

		if ($filter_country) {
			$where[] = "u.country_id = '$filter_country' ";
		}

		// if checkboxes are ticked, pull that data only
		if (!empty($post['cid'])) {
			$cids = implode(",", $post['cid']);
			if (!empty($cids)) {
				$where[] = " sp.`id` IN (" . $cids . ") ";
			}
		}

		if ($currentUser->gid == 24) { // distributor, must see from groups management first
			$where[] = ' s.customer_id = ' . $currentUser->customer_id;
		}

		if ($currentUser->gid == 23) { // Manager, can see his own country, and can see other data within country but diff user
			$query = " SELECT GROUP_CONCAT('\'',customer_id,'\'') FROM #__users WHERE country_id = " . $currentUser->country_id . " ";
			$db->setQuery($query);
			$result = $db->loadResult();
			$where[] = ' s.customer_id IN (' . $result . ')';
		}

		$where = (count($where) ? ' WHERE (' . implode(') AND (', $where) . ')' : '');


		$query = 'SELECT s.*, sp.serial_no, sp.model_no, sp.part_no, u.name AS distributor_name '
			. ' FROM #__at_service_contract_product_xref AS sp '
			. ' RIGHT JOIN #__at_service_contract AS s ON sp.service_contract_id = s.id '
			. ' LEFT JOIN #__users AS u ON u.customer_id = s.customer_id '
			. $where;

		$db->setQuery($query);
		$rows	=	$db->loadObjectList();

		$headers = array();
		$headers[] = '"Service Contract Number"';
		$headers[] = '"Start Date"';
		$headers[] = '"Expiry Date"';
		$headers[] = '"Service Type"';
		$headers[] = '"Part Number"';
		$headers[] = '"Model Number"';
		$headers[] = '"Serial Number"';
		$headers[] = '"PO Number"';
		$headers[] = '"Customer Name"';
		$headers[] = '"Client Name"';
		//$headers[] = '"Remarks"';	

		$headers = implode(',', $headers);

		$csvcontent = array();

		foreach ($rows as $r) {

			$row = array();
			$row[] = '"' . (($r->service_contract_no) ? $r->service_contract_no : 'N/A') . '"';

			$row[] = '"' . (($r->start_date) ? date("d-m-Y", strtotime($r->start_date)) : 'N/A') . '"';
			$row[] = '"' . (($r->expiry_date) ? $r->expiry_date : 'N/A') . '"';
			$row[] = '"' . (($r->service_type) ? $r->service_type : 'N/A') . '"';
			$row[] = '"' . (($r->part_no) ? $r->part_no : 'N/A') . '"';
			$row[] = '"' . (($r->model_no) ? $r->model_no : 'N/A') . '"';
			$row[] = '"' . (($r->serial_no) ? $r->serial_no : 'N/A') . '"';
			$row[] = '"' . (($r->po_no) ? $r->po_no : 'N/A') . '"';
			$row[] = '"' . (($r->distributor_name) ? $r->distributor_name : 'N/A') . '"';
			$row[] = '"' . (($r->client_name) ? $r->client_name : 'N/A') . '"';

			$csvcontent[] = implode(',', $row);
		}
		$fp = fopen('php://output', 'w');
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
		print $headers . "\r" . $csvcontent;

		exit;
	}
}
