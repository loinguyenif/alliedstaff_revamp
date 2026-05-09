<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Atelman
 * @author     iFoundries <wpsub@ifoundries.com>
 * @copyright  2025 iFoundries
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Atelman\Component\Atelman\Site\Model;
// No direct access.
defined('_JEXEC') or die;

use Atelman\Component\Atelman\Administrator\Helper\AtelmanHelper;
use \Joomla\CMS\Factory;
use \Joomla\Utilities\ArrayHelper;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Table\Table;
use \Joomla\CMS\MVC\Model\FormModel;
use \Joomla\CMS\Object\CMSObject;
use \Joomla\CMS\Helper\TagsHelper;

/**
 * Atelman model.
 *
 * @since  1.0.0
 */
class AtelcheckrequestModel extends FormModel
{
	private $item = null;



	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm(
			'com_atelman.rmaitem',
			'rmaitemform',
			array(
				'control'   => 'jform',
				'load_data' => $loadData
			)
		);

		if (empty($form)) {
			return false;
		}

		return $form;
	}



	public function getRMAStatus($rmacode = '')
	{

		if (!$rmacode)
			return false;

		$db = Factory::getDBO();

		$query = " SELECT i.`status`, s.status_name, i.shipping_duration, i.received_date, i.shipped_date, i.closed_date FROM #__at_rma_items AS i "
			.	" LEFT JOIN #__at_rma_status AS s ON s.status_code = i.status "
			.	" WHERE i.rmacode = " . $db->Quote($rmacode, false)
			.	" LIMIT 1 ";
		$db->setQuery($query);
		$status = $db->loadObject();

		return $status;
	}

	public function getWarrantyStatus($post)
	{

		// helper
		$helper = 	new AtelmanHelper();
		$db = Factory::getDBO();

		$po_no 		= strtolower($post['po_no']);
		$so_no 		= strtolower($post['so_no']);
		$invoice_no = strtolower($post['invoice_no']);
		$serial_no	= strtolower($post['serial_no']);

		if (!$po_no && !$so_no && !$invoice_no && !$serial_no)
			return false;

		if ($po_no) {
			$where[] = ' LOWER(w.po_no) = ' . $db->Quote($po_no);
		}

		if ($so_no) {
			$where[] = ' LOWER(w.so_no) = ' . $db->Quote($so_no);
		}

		if ($invoice_no) {
			$where[] = ' LOWER(w.invoice_no) = ' . $db->Quote($invoice_no);
		}

		if ($serial_no) {

			$snoquery = '';

			$serial_no = $db->Quote($serial_no);

			$query = 'SELECT COUNT(id)'
				. ' FROM #__at_warranty_items '
				. ' WHERE serial_no_2 = ' . $serial_no
				. ' LIMIT 1 ';

			$db->setQuery($query);

			if ($db->loadResult()) {
				$snoquery = ' w.serial_no_2 = ' . $serial_no;
			} else {
				$snoquery = ' ( w.serial_no = ' . $serial_no . ' AND w.serial_no_2 = "" ) ';
			}

			$where[] = $snoquery;
		}

		$where = (count($where) ? ' WHERE (' . implode(') AND (', $where) . ')' : '');

		$query = " SELECT w.* FROM #__at_warranty_items AS w "
			.	$where
			. " ORDER BY w.purchase_date DESC ";
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		/* Create Table */
		if (!empty($rows)) :
			$html = '<div style="color:#000">';
			$html .= '<table border="1" cellspacing="1" cellpadding="1" style="border-collapse:collapse">';
			$html .= '<tr>';
			$html .= '<th width="160px;">' . Text::_('COM_ATELMAN_PO_NO') . '</th>';
			$html .= '<th width="0px;">' . Text::_('COM_ATELMAN_SO_NO') . '</th>';
			$html .= '<th width="70px;">' . Text::_('COM_ATELMAN_INVOICE_NO') . '</th>';
			$html .= '<th width="170px;">' . Text::_('COM_ATELMAN_MODEL_NO') . '</th>';
			$html .= '<th width="150px;">' . Text::_('COM_ATELMAN_SERIAL_NO') . '</th>';
			$html .= '<th width="80px;">' . Text::_('COM_ATELMAN_PURCHASE_DATE') . '</th>';
			$html .= '<th width="80px;">' . Text::_('COM_ATELMAN_EXPIRY_DATE') . '</th>';
			$html .= '</tr>';

			$arrSerialNo = array();

			foreach ($rows as $r) :

				$serial_no = (($r->serial_no_2) ? $r->serial_no_2 : $r->serial_no);

				if (in_array($serial_no, $arrSerialNo)) {
					continue;
				}

				array_push($arrSerialNo, $serial_no);

				$html .= '<tr>';
				$html .= '<td width="160px;">' . $r->po_no . '</td>';
				$html .= '<td width="70px;">' . $r->so_no . '</td>';
				$html .= '<td width="70px;">' . $r->invoice_no . '</td>';
				$html .= '<td width="170px">' . ((!empty($r->replacement_pn)) ? $helper->getProductByPartNumber($r->replacement_pn)->model_no : $helper->getItemById('products', $r->product_id)->model_no) . '</td>';
				$html .= '<td width="150px;">' . $serial_no . '</td>';
				$html .= '<td width="80px;">' . date("d M Y", strtotime($r->purchase_date)) . '</td>';
				$html .= '<td width="80px;">' . (($r->expired_date_manual != NULL && $r->expired_date_manual != '0000-00-00 00:00:00') ? date("d M Y", strtotime($r->expired_date_manual)) : (($r->extended_warranty > 0) ? date("d M Y", strtotime($r->extended_expired_date)) : date("d M Y", strtotime($r->expired_date)))) . '</td>';
				$html .= '</tr>';
			endforeach;
			$html .= '</table>';
			$html .= '</div>';
		else:
			$html = 'Warranty does not exist';
		endif;

		return $html;
	}



	public function getServiceContract($post)
	{

		// helper
		$helper = 	new AtelmanHelper();
		$db = Factory::getDBO();

		$service_contract_no = strtolower($post['service_contract_no']);
		$serial_no = strtolower($post['serial_no']);

		if (!$service_contract_no && !$serial_no)
			return false;


		if ($service_contract_no) {
			$where[] = ' LOWER(s.service_contract_no) = ' . $db->Quote($service_contract_no);
		}

		if ($serial_no) {
			$where[] = ' LOWER(sp.serial_no) = ' . $db->Quote($serial_no);
		}

		$where = (count($where) ? ' WHERE (' . implode(') AND (', $where) . ')' : '');

		$query = " SELECT sp.*,s.*, CASE WHEN s.customer_id != '' THEN (SELECT u.name FROM #__users AS u WHERE u.customer_id = s.customer_id LIMIT 1) ELSE '' END AS distributor "
			. " FROM #__at_service_contract_product_xref AS sp "
			. " LEFT JOIN #__at_service_contract AS s ON s.id = sp.service_contract_id "
			.	$where
			. " ORDER BY s.expiry_date ASC ";

		$db->setQuery($query);
		$rows = $db->loadObjectList();

		/* Create Table */
		if (!empty($rows)) :
			$html = '<a class="button" href="index.php?option=com_atelman&task=rmaitems.exportServiceContract&servicecontractno=' . $service_contract_no . '&serialno=' . $serial_no . '">Export CSV</a><br /><br />';
			$html .= '<div style="color:#000">';
			$html .= '<table border="1" cellspacing="1" cellpadding="1" style="border-collapse:collapse">';
			$html .= '<tr>';
			$html .= '<th>#</th>';
			$html .= '<th width="160px;">' . Text::_('Service Contract #') . '</th>';
			$html .= '<th width="0px;">' . Text::_('Part #') . '</th>';
			$html .= '<th width="70px;">' . Text::_('Model #') . '</th>';
			$html .= '<th width="170px;">' . Text::_('Serial #') . '</th>';
			$html .= '<th width="150px;">' . Text::_('Expiry Date') . '</th>';
			$html .= '<th width="80px;">' . Text::_('Service Type') . '</th>';
			$html .= '<th width="80px;">' . Text::_('PO #') . '</th>';
			$html .= '<th width="80px;">' . Text::_('Distributor') . '</th>';
			$html .= '<th width="80px;">' . Text::_('Client') . '</th>';
			$html .= '</tr>';

			$arrSerialNo = array();
			$i = 1;

			foreach ($rows as $r) :

				$html .= '<tr>';
				$html .= '<td>' . $i . '</td>';
				$html .= '<td width="160px;">' . $r->service_contract_no . '</td>';
				$html .= '<td width="70px;">' . $r->part_no . '</td>';
				$html .= '<td width="70px;">' . $r->model_no . '</td>';
				$html .= '<td width="170px">' . $r->serial_no . '</td>';
				$html .= '<td width="150px;">' . date("d M Y", strtotime($r->expiry_date)) . '</td>';
				$html .= '<td width="80px;">' . $r->service_type . '</td>';
				$html .= '<td width="80px;">' . $r->po_no . '</td>';
				$html .= '<td width="80px;">' . $r->distributor . '</td>';
				$html .= '<td width="80px;">' . $r->client_name . '</td>';
				$html .= '</tr>';

				$i++;
			endforeach;
			$html .= '</table>';
			$html .= '</div>';
		else:
			$html = 'Service Contract does not exist';
		endif;

		return $html;
	}



	public function exportServiceContract($service_contract_no = '', $serial_no = '')
	{

		$db = Factory::getDBO();

		if ($service_contract_no) {
			$where[] = ' s.service_contract_no = ' . $db->Quote($service_contract_no);
		}

		if ($serial_no) {
			$where[] = ' sp.serial_no = ' . $db->Quote($serial_no);
		}

		$where = (count($where) ? ' WHERE (' . implode(') AND (', $where) . ')' : '');

		$query = 'SELECT s.*, sp.serial_no, sp.model_no, sp.part_no, CASE WHEN s.customer_id != \'\' THEN (SELECT u.name FROM #__users AS u WHERE u.customer_id = s.customer_id LIMIT 1) ELSE \'\' END AS distributor '
			. ' FROM #__at_service_contract_product_xref AS sp '
			. ' RIGHT JOIN #__at_service_contract AS s ON sp.service_contract_id = s.id '
			. $where
			. " ORDER BY s.expiry_date ASC ";;

		$db->setQuery($query);
		$rows	=	$db->loadObjectList();

		$headers = array();
		$headers[] = '"Service Contract Number"';
		$headers[] = '"Part Number"';
		$headers[] = '"Model Number"';
		$headers[] = '"Serial Number"';
		$headers[] = '"Expiry Date"';
		$headers[] = '"Service Type"';
		$headers[] = '"PO Number"';
		$headers[] = '"Distributor"';
		$headers[] = '"Client"';

		$headers = implode(',', $headers);

		$csvcontent = array();

		foreach ($rows as $r) {

			$row = array();
			$row[] = '"' . (($r->service_contract_no) ? $r->service_contract_no : 'N/A') . '"';
			$row[] = '"' . (($r->part_no) ? $r->part_no : 'N/A') . '"';
			$row[] = '"' . (($r->model_no) ? $r->model_no : 'N/A') . '"';
			$row[] = '"' . (($r->serial_no) ? $r->serial_no : 'N/A') . '"';
			$row[] = '"' . (($r->expiry_date) ? date("d M Y", strtotime($r->expiry_date)) : 'N/A') . '"';
			$row[] = '"' . (($r->service_type) ? $r->service_type : 'N/A') . '"';
			$row[] = '"' . (($r->po_no) ? $r->po_no : 'N/A') . '"';
			$row[] = '"' . (($r->distributor) ? $r->distributor : 'N/A') . '"';
			$row[] = '"' . (($r->client_name) ? $r->client_name : 'N/A') . '"';

			/*if($r->extended_expired_date != '0000-00-00') : 
				$r->real_expired_date = date("d/m/Y", strtotime($r->extended_expired_date));
				elseif($r->expired_date_manual != '0000-00-00'):
				$r->real_expired_date = date("d/m/Y", strtotime($r->expired_date_manual));
				else :
				$r->real_expired_date = date("d/m/Y", strtotime($r->expired_date));
				endif;*/


			$csvcontent[] = implode(',', $row);
		}

		if (preg_match('/Opera(\/| )([0-9]\.[0-9]{1,2})/i', $_SERVER['HTTP_USER_AGENT'])) {
			$UserBrowser = 'Opera';
		} elseif (preg_match('/MSIE ([0-9]\.[0-9]{1,2})/i', $_SERVER['HTTP_USER_AGENT'])) {
			$UserBrowser = 'IE';
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

		$csvfilename = 'AT_' . date('Ymd_His') . '.csv';
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
