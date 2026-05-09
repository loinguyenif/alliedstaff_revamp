<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Atelman
 * @author     iFoundries <wpsub@ifoundries.com>
 * @copyright  2025 iFoundries
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Atelman\Component\Atelman\Administrator\Controller;


\defined('_JEXEC') or die;

use Atelman\Component\Atelman\Administrator\Helper\AtelmanHelper;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Utilities\ArrayHelper;
use Atelman\Component\Atelman\Administrator\Model\ProductsModel;
use Joomla\CMS\Date\Date;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Session\Session;
use \Joomla\CMS\Plugin\PluginHelper;

/**
 * Rmaitems list controller class.
 *
 * @since  1.0.0
 */
class RmaitemsController extends AdminController
{
	/**
	 * Method to clone existing Rmaitems
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 */
	public function duplicate()
	{
		// Check for request forgeries
		$this->checkToken();

		// Get id(s)
		$pks = $this->input->post->get('cid', array(), 'array');

		try {
			if (empty($pks)) {
				throw new \Exception(Text::_('COM_ATELMAN_NO_ELEMENT_SELECTED'));
			}

			ArrayHelper::toInteger($pks);
			$model = $this->getModel();
			$model->duplicate($pks);
			$this->setMessage(Text::_('COM_ATELMAN_ITEMS_SUCCESS_DUPLICATED'));
		} catch (\Exception $e) {
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
		}

		$this->setRedirect('index.php?option=com_atelman&view=rmaitems');
	}

	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    Optional. Model name
	 * @param   string  $prefix  Optional. Class prefix
	 * @param   array   $config  Optional. Configuration array for model
	 *
	 * @return  object	The Model
	 *
	 * @since   1.0.0
	 */
	public function getModel($name = 'Rmaitem', $prefix = 'Administrator', $config = array())
	{
		return parent::getModel($name, $prefix, array('ignore_request' => true));
	}



	/**
	 * Method to save the submitted ordering values for records via AJAX.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 *
	 * @throws  Exception
	 */
	public function saveOrderAjax()
	{
		// Get the input
		$pks   = $this->input->post->get('cid', array(), 'array');
		$order = $this->input->post->get('order', array(), 'array');

		// Sanitize the input
		ArrayHelper::toInteger($pks);
		ArrayHelper::toInteger($order);

		// Get the model
		$model = $this->getModel();

		// Save the ordering
		$return = $model->saveorder($pks, $order);

		if ($return) {
			echo "1";
		}

		// Close the application
		Factory::getApplication()->close();
	}


	public function checkrma()
	{

		$app = \Joomla\CMS\Factory::getApplication();

		$db = Factory::getContainer()->get('DatabaseDriver');

		$rma_number = $app->input->get('rma_number');

		if ($rma_number) {
			$query = " SELECT s.status_name,s.status_code, i.shipping_duration, i.received_date, i.shipped_date, i.closed_date FROM #__at_rma_items AS i "
				.	" LEFT JOIN #__at_rma_status AS s ON s.status_code = i.status "
				.	" WHERE i.rmacode = '$rma_number' LIMIT 1 ";

			$db->setQuery($query);
			$rma_item = $db->loadObject();
		}

		$status_code = "";
		if (!empty($rma_item)) {

			$dates = '0000-00-00';

			if ($rma_item->status_code == 'receive') {
				$dates = $rma_item->received_date;
			}
			if ($rma_item->status_code == 'ship') {
				$dates = $rma_item->shipped_date;
			}
			if ($rma_item->status_code == 'close') {
				$dates = $rma_item->closed_date;
			}

			$status_code = $rma_item->status_name . (($dates != '0000-00-00') ? ' on ' . date("d M Y", strtotime($dates)) : '') . (($rma_item->status_code == 'receive') ? '; replacement unit(s) will be shipped in approximately ' . $rma_item->shipping_duration . ' day(s).' : '.');
		}

		echo json_encode(array(
			"status" => ((!empty($rma_item)) ? 1 : 0),
			"status_code"	=> 	$status_code
		));
		$app->close();
		exit;
	}


	/*
			*
			* AJAX Function goes here
			*
		*/
	public function ajax()
	{

		$app = \Joomla\CMS\Factory::getApplication();
		$helper					= 	new AtelmanHelper();
		$section = $app->input->get('section', '');

		switch ($section) {

			case 'loadCompany':

				$customer_id = $app->input->get('customer_id', ''); // customer_id

				$row = AtelmanHelper::loadCompany($customer_id);

				echo json_encode(array(
					"status"	=> (!empty($row) ? 1 : 0),
					"data" => 	$row
				));

				break;

			case 'getSerialNo':

				$model = new ProductsModel();
				$post	=	$app->input->post->getArray();
				$rows = $model->getSerialNoByKeyword($post['keyword']);

				$serial_no_list = array();

				if (!empty($rows)) {

					$status = true;

					foreach ($rows as $r) :

						$serial_no_complete 		= (($r->serial_no_2) ? $r->serial_no_2 : $r->serial_no);
						$product_id 						= (!empty($r->replacement_pn) ? $helper->getProductByPartNumber($r->replacement_pn)->id : $r->product_id);
						$product_model 					= (!empty($r->replacement_pn) ? $helper->getProductByPartNumber($r->replacement_pn)->model_no : $r->model_no);
						$product_number 				= (!empty($r->replacement_pn) ? $r->replacement_pn : $r->product_no);
						$invoice_no 						= $r->invoice_no;
						$so_no 									= $r->so_no;
						$po_no 									= $r->po_no;
						$warranty 							= '';
						$previous_rma_number		=	(isset($r->previous_rma_number) ? $r->previous_rma_number : 'N/A');
						$warranty_id						=	$r->id;
						$warranty_customer_id		=	$r->customer_id;
						$ship_date 							= date('d/m/Y', strtotime($r->purchase_date));
						//$expiry_date 					= (($r->extended_warranty>0)?$r->extended_expired_date:($r->expired_date_manual == '0000-00-00'?$r->expired_date:$r->expired_date_manual));
						$expiry_date						= (($r->expired_date_manual == '0000-00-00' ? (($r->extended_warranty > 0) ? $r->extended_expired_date : $r->expired_date) : $r->expired_date_manual));
						$strtotime_expiry_date 	= @strtotime($expiry_date) + (24 * 60 * 60);
						$expiry_date 						= date('d/m/Y', $strtotime_expiry_date - 1);

						if (time() <= $strtotime_expiry_date) {
							$warranty = 'IN';
						}

						if (time() > $strtotime_expiry_date) {
							$warranty = 'OUT';
						}

						// Purchase date : 01-10-2014...DOA latest date : 31-12-2014
						$tmp = explode("-", $r->purchase_date); // 2014-12-31
						$year = $tmp[0];
						$month = $tmp[1] + 3;
						$day = $tmp[2];

						//$doa_time = date("Y-m-d", mktime(0, 0, 0, $month, $day, $year));

						$doa_time = $year . '-' . $month . '-' . $day;
						$days90 = (int) strtotime($doa_time) - 1;

						if (time() <= $days90) {
							$warranty = 'DOA';
						}

						$serial_no_list[$serial_no_complete] = '<a href="javascript:void(0)" onclick="javascript:chooseSerialNo(' . $post['row_id'] . ', \'' . $serial_no_complete . '\',\'' . $product_id . '\',\'' . $product_model . '\' , \'' . $product_number . '\',\'' . $warranty . '\', \'' . $invoice_no . '\' ,\'' . $so_no . '\',\'' . $po_no . '\',\'' . $ship_date . '\',\'' . $expiry_date . '\',\'' . $warranty_id . '\',\'' . $warranty_customer_id . '\',\'' . $previous_rma_number . '\')">' . $serial_no_complete . ' </div>';

					endforeach;
				} else if (empty($post['keyword'])) {
					$status = false;
				} else {
					$serial_no_list[] = '<a href="mailto:RMA-AsiaPacific@alliedtelesis.com.sg">Serial Number not found</a>';
					$status = true;
				}


				echo json_encode(array(
					"status"	=> 	$status,
					"data" => 	implode("", $serial_no_list)
				));
				break;

			case 'RMAReport':

				$data = $app->input->post->getArray();

				parse_str($data['jdata'], $output);

				$model	= $this->getModel('Report', 'Administrator');

				$result = $model->getReport($output);

				echo $result;
				exit;
				break;
			case 'submitRMAPrint':

				$data = $app->input->post->getArray();
				parse_str($data['jdata'], $output);

				# get a list of sort columns and their data to pass to array_multisort
				$sort = array();
				foreach ($output['print'] as $k => $v) {
					$sort['product_no'][$k] = $v['product_no'];
					$sort['so_no'][$k] = $v['so_no'];
					$sort['serial_no'][$k] = $v['serial_no'];
				}

				# sort by event_type desc and then title asc
				array_multisort($sort['product_no'], SORT_ASC, $sort['serial_no'], SORT_ASC, $output['print']);

?>
				<img src="../images/ATelesis_2color_web.png" alt="ATelesis_2color_web.png" /><br />
				<table style="margin-top:20px;">
					<tr>
						<td width="150px">Company</td>
						<td>: <?php echo stripslashes($output['fullname']) ?></td>
					</tr>
					<tr>
						<td>Contact</td>
						<td>: <?php echo stripslashes($output['contact_name']) ?></td>
					</tr>
					<tr>
						<td>Address</td>
						<td>: <?php echo stripslashes($output['address']) ?></td>
					</tr>
					<tr>
						<td>City</td>
						<td>: <?php echo stripslashes($output['city']) ?></td>
					</tr>
					<tr>
						<td>State/Province</td>
						<td>: <?php echo stripslashes($output['state']) ?></td>
					</tr>
					<tr>
						<td>ZIP/Postal Code</td>
						<td>: <?php echo stripslashes($output['postal_code']) ?></td>
					</tr>
					<tr>
						<td>Country/Region</td>
						<td>: <?php echo stripslashes($output['country']) ?></td>
					</tr>
					<tr>
						<td>Telephone</td>
						<td>: <?php echo stripslashes($output['telephone']) ?></td>
					</tr>
					<tr>
						<td>Fax</td>
						<td>: <?php echo stripslashes($output['fax']) ?></td>
					</tr>
					<tr>
						<td>Email</td>
						<td>: <?php echo stripslashes($output['email']) ?></td>
					</tr>
				</table>
				<style type="text/css" media="print">
					@page {
						size: landscape;
					}
				</style>

				<table style="margin-top:20px;border-collapse:collapse" border="1">
					<tr>
						<th style="padding:10px" align="left">#</th>
						<th style="padding:10px" align="left">RMA Number</th>
						<th style="padding:10px" align="left">Status</th>
						<th style="padding:10px" align="left">Part Number</th>
						<th style="padding:10px" align="left">Model Number</th>
						<th style="padding:10px" align="left">Serial Number</th>
						<th style="padding:10px" align="left">Fault Description</th>
						<th style="padding:10px" align="left">SO Number</th>
						<th style="padding:10px" align="left">Invoice Number</th>
						<th style="padding:10px" align="left">Ship Date</th>
						<th style="padding:10px" align="left">Expiry Date</th>
						<th style="padding:10px" align="left">Request Date</th>
						<th style="padding:10px" align="left">Warranty</th>
						<th style="padding:10px" align="left">Previous RMA Number</th>
						<th style="padding:10px" align="left">Remarks</th>
					</tr>
					<?php

					$stats = ATelmanHelper::getRMAStatusTextArray();

					foreach ($output['print'] as $key => $item) :

						if (!$item['serial_no']) continue;
					?>

						<tr>
							<td style="padding:10px" valign="top"><strong><?php echo $key + 1; ?></strong></td>
							<td style="padding:10px" valign="top">N/A</td>
							<td style="padding:10px" valign="top"><?php echo $stats['open'] ?></td>
							<td style="padding:10px" valign="top" width="100px"><?php echo $item['product_no'] ?></td>
							<td style="padding:10px" valign="top" width="250px"><?php echo $item['product_model'] ?></td>
							<td style="padding:10px" valign="top" width="100px"><?php echo $item['serial_no'] ?></td>
							<td style="padding:10px" valign="top" width="250px"><?php echo nl2br($item['description']) ?></td>
							<td style="padding:10px" valign="top"><?php echo $item['so_no'] ?></td>
							<td style="padding:10px" valign="top"><?php echo $item['invoice_no'] ?></td>
							<td style="padding:10px" valign="top"><?php echo date("d M Y", strtotime(str_replace("/", "-", $item['ship_date']))) ?></td>
							<td style="padding:10px" valign="top"><?php echo date("d M Y", strtotime(str_replace("/", "-", $item['expiry_date']))) ?></td>
							<td style="padding:10px" valign="top"><?php echo date("d M Y", time()) ?></td>
							<td style="padding:10px" valign="top"><?php echo $item['warranty_status'] ?></td>
							<td style="padding:10px" valign="top"><?php echo isset($item['previous_rma_number']) ? $item['previous_rma_number'] : 'N/A' ?></td>
							<td style="padding:10px" valign="top" width="250px"><?php echo nl2br($item['remarks']) ?></td>
						</tr>

					<?php endforeach; ?>
				</table>
			<?php

				break;
			case 'RMARequestPrint':
				// do something here
				$rma_id = $app->input->get('rma_id', '');
				$rma_number = $app->input->get('rma_number');

				$model = $this->getModel('Rmarequest', 'Administrator');

				if ($rma_number) {
					$rma_request_data = $model->getRMARequestData('rma_number', $rma_number);
					$rows = $model->getDataByRMANumber($rma_number);
				} else if ($rma_id) {
					$rma_request_data = $model->getRMARequestData('rma_id', $rma_id);
					$rows = $model->getDataByRMARequestId($rma_request_data->id);
				}
			?>
				<img src="../images/ATelesis_2color_web.png" alt="ATelesis_2color_web.png" /><br />
				<table style="margin-top:20px;">
					<tr>
						<td width="150px">Company</td>
						<td>: <?php echo stripslashes($rma_request_data->fullname) ?></td>
					</tr>
					<tr>
						<td>Contact</td>
						<td>: <?php echo stripslashes($rma_request_data->contact_name) ?></td>
					</tr>
					<tr>
						<td>Address</td>
						<td>: <?php echo stripslashes($rma_request_data->address) ?></td>
					</tr>
					<tr>
						<td>City</td>
						<td>: <?php echo stripslashes($rma_request_data->city) ?></td>
					</tr>
					<tr>
						<td>State/Province</td>
						<td>: <?php echo stripslashes($rma_request_data->state) ?></td>
					</tr>
					<tr>
						<td>ZIP/Postal Code</td>
						<td>: <?php echo stripslashes($rma_request_data->postal_code) ?></td>
					</tr>
					<tr>
						<td>Country/Region</td>
						<td>: <?php echo stripslashes($rma_request_data->country) ?></td>
					</tr>
					<tr>
						<td>Telephone</td>
						<td>: <?php echo stripslashes($rma_request_data->telephone) ?></td>
					</tr>
					<tr>
						<td>Fax</td>
						<td>: <?php echo stripslashes($rma_request_data->fax) ?></td>
					</tr>
					<tr>
						<td>Email</td>
						<td>: <?php echo stripslashes($rma_request_data->email) ?></td>
					</tr>
				</table>
				<style type="text/css" media="print">
					@page {
						size: landscape;
					}
				</style>

				<table style="margin-top:20px;border-collapse:collapse" border="1">
					<tr>
						<th style="padding:10px" align="left">#</th>
						<th style="padding:10px" align="left">RMA Number</th>
						<th style="padding:10px" align="left">Status</th>
						<th style="padding:10px" align="left">Part Number</th>
						<th style="padding:10px" align="left">Model Number</th>
						<th style="padding:10px" align="left">Serial Number</th>
						<th style="padding:10px" align="left">Fault Description</th>
						<th style="padding:10px" align="left">SO Number</th>
						<th style="padding:10px" align="left">Invoice Number</th>
						<th style="padding:10px" align="left">Ship Date</th>
						<th style="padding:10px" align="left">Expiry Date</th>
						<th style="padding:10px" align="left">Request Date</th>
						<th style="padding:10px" align="left">Warranty</th>
						<th style="padding:10px" align="left">Previous RMA Number</th>
						<th style="padding:10px" align="left">Remarks</th>
					</tr>
					<?php

					$stats = ATelmanHelper::getRMAStatusTextArray();

					foreach ($rows as $key => $item) :

						// expiry date
						$expiry_date = $item->expired_date;
						if ($item->expired_date_manual != '0000-00-00') $expiry_date = $item->expired_date_manual;
						if ($item->extended_expired_date != '0000-00-00') $expiry_date = $item->extended_expired_date;
					?>

						<tr>
							<td style="padding:10px" valign="top"><strong><?php echo $key + 1; ?></strong></td>
							<td style="padding:10px" valign="top" width="100px"><?php echo $item->rmacode ?></td>
							<td style="padding:10px" valign="top"><?php echo $stats[$item->status] ?></td>
							<td style="padding:10px" valign="top" width="100px"><?php echo $item->product_no ?></td>
							<td style="padding:10px" valign="top" width="250px"><?php echo $item->model_no ?></td>
							<td style="padding:10px" valign="top" width="100px"><?php echo $item->requested_sn ?></td>
							<td style="padding:10px" valign="top" width="250px"><?php echo nl2br($item->description) ?></td>
							<td style="padding:10px" valign="top"><?php echo $item->so_no ?></td>
							<td style="padding:10px" valign="top"><?php echo $item->invoice_no ?></td>
							<td style="padding:10px" valign="top"><?php echo date("d M Y", strtotime($item->purchase_date)) ?></td>
							<td style="padding:10px" valign="top"><?php echo date("d M Y", strtotime($expiry_date)) ?></td>
							<td style="padding:10px" valign="top"><?php echo date("d M Y", strtotime($item->rma_created_date)) ?></td>
							<td style="padding:10px" valign="top"><?php echo $item->warranty_status ?></td>
							<td style="padding:10px" valign="top"><?php echo isset($item->previous_rma_number) ? $item->previous_rma_number : 'N/A' ?></td>
							<td style="padding:10px" valign="top" width="250px"><?php echo nl2br($item->remarks) ?></td>
						</tr>

					<?php endforeach; ?>
				</table>
			<?php

				break;
			/* iFoundries - 9 dec 2019 */
			case 'submitServiceContractPrint':

				$data = $app->input->post->getArray();
				parse_str($data['jdata'], $output);

				$model	= $this->getModel('Servicecontract', 'Administrator');
				$customer_detail = $model->getCustomerDataByCustomerID($output['fullname_tmp']);

				# get a list of sort columns and their data to pass to array_multisort
				$sort = array();
				foreach ($output['print'] as $k => $v) {
					$sort['product_no'][$k] = @$v['product_no'];
					$sort['so_no'][$k] = @$v['so_no'];
					$sort['serial_no'][$k] = @$v['serial_no'];
				}
				# sort by event_type desc and then title asc
				array_multisort($sort['product_no'], SORT_ASC, $sort['serial_no'], SORT_ASC, $output['print']);

			?>
				<img src="../images/ATelesis_2color_web.png" alt="ATelesis_2color_web.png" /><br />
				<table style="margin-top:20px;">
					<tr>
						<td width="180px">Company</td>
						<td>: <?php echo stripslashes($output['fullname']) ?></td>
					</tr>
					<tr>
						<td>Contact</td>
						<td>: <?php echo stripslashes(@$customer_detail->contact_name) ?></td>
					</tr>
					<tr>
						<td>Address</td>
						<td>: <?php echo stripslashes(@$customer_detail->address) ?></td>
					</tr>
					<tr>
						<td>City</td>
						<td>: <?php echo @$customer_detail->city ?></td>
					</tr>
					<tr>
						<td>State/Province</td>
						<td>: <?php echo @$customer_detail->state ?></td>
					</tr>
					<tr>
						<td>ZIP/Postal Code</td>
						<td>: <?php echo @$customer_detail->postal_code ?></td>
					</tr>
					<tr>
						<td>Country/Region</td>
						<td>: <?php echo @$customer_detail->country ?></td>
					</tr>
					<tr>
						<td>Telephone</td>
						<td>: <?php echo @$customer_detail->telephone ?></td>
					</tr>
					<tr>
						<td>Fax</td>
						<td>: <?php echo stripslashes(@$customer_detail->fax) ?></td>
					</tr>
					<tr>
						<td>Email</td>
						<td>: <?php echo stripslashes(@$customer_detail->email) ?></td>
					</tr>

					<tr>
						<td>Service Contract No.</td>
						<td>: <?php echo stripslashes($output['service_contract_no']) ?></td>
					</tr>
					<tr>
						<td>Service Type</td>
						<td>: <?php echo stripslashes($output['service_type']) ?></td>
					</tr>
					<tr>
						<td>PO Number</td>
						<td>: <?php echo stripslashes($output['po_no']) ?></td>
					</tr>
					<tr>
						<td>Cover Length</td>
						<td>: <?php echo stripslashes($output['cover_length']) ?></td>
					</tr>
					<tr>
						<td>Client Name</td>
						<td>: <?php echo stripslashes($output['client_name']) ?></td>
					</tr>
					<tr>
						<td>Expiry Date</td>
						<td>: <?php echo date("d M Y", strtotime($output['expiry_date'])) ?></td>
					</tr>
					<tr>
						<td>Remarks</td>
						<td>: <?php echo stripslashes($output['remarks']) ?></td>
					</tr>

				</table>
				<style type="text/css" media="print">
					@page {
						size: landscape;
					}
				</style>

				<table style="margin-top:20px;border-collapse:collapse" border="1">
					<tr>
						<th style="padding:10px" align="left">#</th>
						<th style="padding:10px" align="left">Serial Number</th>
						<th style="padding:10px" align="left">Part Number</th>
						<th style="padding:10px" align="left">Model Number</th>
					</tr>
					<?php

					$stats = AtelmanHelper::getRMAStatusTextArray();

					foreach ($output['print'] as $key => $item) :

						if (!$item['serial_no']) continue;
					?>

						<tr>
							<td style="padding:10px" valign="top"><strong><?php echo $key + 1; ?></strong></td>
							<td style="padding:10px" valign="top" width="100px"><?php echo $item['serial_no'] ?></td>
							<td style="padding:10px" valign="top" width="100px"><?php echo $item['product_no'] ?></td>
							<td style="padding:10px" valign="top" width="250px"><?php echo $item['product_model'] ?></td>
						</tr>

					<?php endforeach; ?>
				</table>
			<?php
				break;

			/* iFoundries - 11 dec 2019 */
			case 'ServiceContractPrint':
				// do something here
				$s_id = $app->input->get('s_id', '');
				$s_number = $app->input->get('s_number');

				//$data = $app->input->post->getArray();
				//parse_str($data['jdata'], $output);

				$model	= $this->getModel('Servicecontract', 'Administrator');

				$service_contract_data = $model->getServiceContractData('service_contract_id', $s_id);
				$rows = $model->getDataByServiceContractId($service_contract_data->id);

			?>
				<img src="../images/ATelesis_2color_web.png" alt="ATelesis_2color_web.png" /><br />
				<table style="margin-top:20px;">
					<tr>
						<td width="150px">Company</td>
						<td>: <?php echo stripslashes($service_contract_data->name) ?></td>
					</tr>
					<tr>
						<td>Contact</td>
						<td>: <?php echo stripslashes($service_contract_data->contact_name) ?></td>
					</tr>
					<tr>
						<td>Address</td>
						<td>: <?php echo stripslashes($service_contract_data->address) ?></td>
					</tr>
					<tr>
						<td>City</td>
						<td>: <?php echo stripslashes($service_contract_data->city) ?></td>
					</tr>
					<tr>
						<td>State/Province</td>
						<td>: <?php echo stripslashes($service_contract_data->state) ?></td>
					</tr>
					<tr>
						<td>ZIP/Postal Code</td>
						<td>: <?php echo @stripslashes($service_contract_data->postal_code) ?></td>
					</tr>
					<tr>
						<td>Country/Region</td>
						<td>: <?php echo @stripslashes($service_contract_data->country) ?></td>
					</tr>
					<tr>
						<td>Telephone</td>
						<td>: <?php echo @stripslashes($service_contract_data->telephone) ?></td>
					</tr>
					<tr>
						<td>Fax</td>
						<td>: <?php echo stripslashes($service_contract_data->fax) ?></td>
					</tr>
					<tr>
						<td>Email</td>
						<td>: <?php echo stripslashes($service_contract_data->email) ?></td>
					</tr>

					<tr>
						<td>Service Contract No.</td>
						<td>: <?php echo stripslashes($service_contract_data->service_contract_no) ?></td>
					</tr>
					<tr>
						<td>Service Type</td>
						<td>: <?php echo stripslashes($service_contract_data->service_type) ?></td>
					</tr>
					<tr>
						<td>PO Number</td>
						<td>: <?php echo stripslashes($service_contract_data->po_no) ?></td>
					</tr>
					<tr>
						<td>Cover Length</td>
						<td>: <?php echo stripslashes($service_contract_data->cover_length) ?></td>
					</tr>
					<tr>
						<td>Client Name</td>
						<td>: <?php echo stripslashes($service_contract_data->client_name) ?></td>
					</tr>
					<tr>
						<td>Expiry Date</td>
						<td>: <?php echo date("d M Y", strtotime($service_contract_data->expiry_date)) ?></td>
					</tr>
					<tr>
						<td>Start Date</td>
						<td>: <?php echo date("d M Y", strtotime($service_contract_data->start_date)) ?></td>
					</tr>
					<tr>
						<td>Remarks</td>
						<td>: <?php echo @stripslashes($service_contract_data->remarks) ?></td>
					</tr>
				</table>
				<style type="text/css" media="print">
					@page {
						size: landscape;
					}
				</style>

				<table style="margin-top:20px;border-collapse:collapse" border="1">
					<tr>
						<th style="padding:10px" align="left">#</th>
						<th style="padding:10px" align="left">Serial Number</th>
						<th style="padding:10px" align="left">Part Number</th>
						<th style="padding:10px" align="left">Model Number</th>
					</tr>
					<?php

					$stats = ATelmanHelper::getRMAStatusTextArray();

					foreach ($rows as $key => $item) :

					?>

						<tr>
							<td style="padding:10px" valign="top"><strong><?php echo $key + 1; ?></strong></td>
							<td style="padding:10px" valign="top" width="100px"><?php echo $item->serial_no ?></td>
							<td style="padding:10px" valign="top" width="100px"><?php echo $item->part_no ?></td>
							<td style="padding:10px" valign="top" width="250px"><?php echo $item->model_no ?></td>

						</tr>

					<?php endforeach; ?>
				</table>
<?php

				break;
			default:
				break;
		}

		$app->close();
		exit;
	}



	function ajaxaddrow()
	{
		$app = \Joomla\CMS\Factory::getApplication();
		$section 	= 	$app->input->get('section', '');
		$row_id		=	$app->input->get('row_id');

		switch ($section) {
			/*case 'warranty_reg' :
				$helper 	= new ATelmanHelper();
				
				$country	= $helper->getCountryHTML($row_id);
				require('views/ajaxaddrow/warranty_reg.php');
				break;
				*/

			case 'rma_request':

				require JPATH_ADMINISTRATOR . '/components/com_atelman/tmpl/rmaitems/ajaxaddrow/rma_request.php';
				break;

			case 'service_contract':
				require JPATH_ADMINISTRATOR . '/components/com_atelman/tmpl/rmaitems/ajaxaddrow/service_contract.php';
				break;
		}
		$app->close();
		exit;
	}


	public  function submitrma()
	{
		$app = \Joomla\CMS\Factory::getApplication();
		$post	=	$app->input->post->getArray();
		$model = $this->getModel('Rmarequest', 'Administrator');
		$mails  = &$this->getModel('Mail', 'Administrator');


		if ($rma_request_id = $model->submitrma($post)) {

			$msg = Text::_('RMA Request has been saved');
			$tmp1 = $mails->submitRMAConfirmationMail($rma_request_id);
		} else {

			$msg = Text::_('RMA Request has not been saved');
		}

		$link = 'index.php?option=com_atelman&view=rmaitems';

		$this->setRedirect($link, $msg);
	}

	/*
			*
		*	RMA Import
		*
		*/
	public function rmaimport()
	{

		$app = \Joomla\CMS\Factory::getApplication();
		$post	=	$app->input->post->getArray();
		$model = $this->getModel('Csv', 'Administrator');

		if ($row = $model->CSVRMAImport()) {
			$msg = Text::_('CSV RMA Import Success');
		} else {
			$msg = Text::_('CSV RMA Import Failed');
		}
		$link = 'index.php?option=com_atelman&view=rmaitems';
		$this->setRedirect($link, $msg);
	}


	/*
		*
		*	RMA export
		*
		*/
	public function export_csv()
	{
		$app = \Joomla\CMS\Factory::getApplication();
		$model = $this->getModel('Csv', 'Administrator');
		$post	=	$app->input->post->getArray();
		$tmp = $model->exportCSV($post);
		exit;
	}



	/**
	 *	Print Function - by PDF
	 **/

	public function prints()
	{
		if (!Session::checkToken()) {
			die('Invalid Token');
		}
		$app = \Joomla\CMS\Factory::getApplication();
		$view = $app->input->get('view');
		$cid = $app->input->get('cid');
		$post = $app->input->post->getArray();

		switch ($view) {

			case 'rmaitem':
				$model = $this->getModel('Rmarequest', 'Administrator');
				$link = 'index.php?option=com_atelman&view=rmaitem&layout=edit&id=' . $cid;
				if ($post['file_id']) {
					$pdf = $model->prints($post);
					while (ob_get_level()) {
						ob_end_clean();
					}
					header('Content-Type: application/pdf');
					header('Content-Disposition: inline; filename="allied_telesis.pdf"');
					header('Cache-Control: private, max-age=0, must-revalidate');
					header('Pragma: public');
					$pdf->Output('I', 'allied_telesis.pdf');
					$app->close();
				} else {
					$msg = "You have not chosen file(s) to print.";
				}
				break;
			default:
				break;
		}

		$this->setRedirect($link, $msg);
	}


	/**
	 *	Email Function for RMA Request Detail Page (FILE)
	 **/

	public function emails()
	{

		if (!Session::checkToken()) {
			die('Invalid Token');
		}
		$app = \Joomla\CMS\Factory::getApplication();
		$view		= $app->input->get('view');
		$cid		= $app->input->get('cid');
		$post		=	$app->input->post->getArray();

		switch ($view) {

			case 'rmaitem':
				$model = $this->getModel('Rmarequest', 'Administrator');
				if ($model->emails($post)) {
					$msg = 'Email has been sent to ' . str_replace(';', ',', $post['recipients']);
				} else {
					$msg = 'Email has not been sent. Please check your email or tick your file(s) choice to be emailed.';
				}

				$link = 'index.php?option=com_atelman&view=rmaitem&layout=edit&id=' . $cid;

				break;

			default:

				break;
		}

		$this->setRedirect($link, $msg);
	}




	/**
	 *	Delete PDF Function -  RMA Request Detail Page
	 **/

	public function delete_files()
	{

		if (!Session::checkToken()) {
			die('Invalid Token');
		}
		$app = \Joomla\CMS\Factory::getApplication();
		$view		= $app->input->get('view');
		$cid		= $app->input->get('cid');
		$post		= $app->input->post->getArray();

		switch ($view) {
			case 'rmaitem':
				$model = $this->getModel('Rmarequest', 'Administrator');

				if ($row = $model->delete_files($post)) {
					$msg = Text::_('File has been removed! ');
				} else {
					$msg = Text::_('Error : File(s) Not Removed');
				}

				$link = 'index.php?option=com_atelman&view=rmaitem&layout=edit&id=' . $cid;

				break;

			default:
				break;
		}

		$this->setRedirect($link, $msg);
	}

	/**
	 *	Download  -  RMA Request edit page
	 **/
	public function download()
	{

		$app = \Joomla\CMS\Factory::getApplication();

		$action = $app->input->get('action', '');

		$db = Factory::getDBO();
		$user = Factory::getUser();

		$model = $this->getModel('Download', 'Administrator');

		switch ($action) {

			case 'download':
				$id = $app->input->get('cid');
				$row = $model->getItemDown($id);
				$result = $model->download($id);
				if (!$result) {
					$msg 	= 'Oops!! File is not exist';
					$link = 'index.php?option=com_atelman&task=rmaitem.edit&id=' . $row->rma_item_id;
					$this->setRedirect($link, $msg);
					return;
				}
				exit;
				break;

			case 'delete':

				$id = $app->input->get('cid');

				$row = $model->getItem($id);

				if ($result = $model->delete($id)) {

					$msg = $row->filename . ' has been deleted.';

					PluginHelper::importPlugin('atelesis', 'logs');
					$dispatcher = Factory::getApplication()->getDispatcher();
					$item = new \stdClass();
					$item->section = 'RMA_ITEM';
					$item->action_type = 'DELETE';
					$item->action_by = $user->id;
					$item->action_remarks = "Deleted $row->filename";

					$event = new \Joomla\Event\Event('onAfterAction', [
						'log' => $item
					]);

					// Fire the onAfterStoreUser trigger
					$dispatcher->dispatch('onAfterAction', $event);
				} else {
					$msg = $row->filename . ' has not been deleted.';
				}

				$link = 'index.php?option=com_atelman&view=rmaitem&layout=edit&cid=' . $row->rma_item_id;

				$this->setRedirect($link, $msg);

				break;
		}
	}

	public function export_logs()
	{

		$model		= &$this->getModel('csv');
		$tmp = $model->exportLogs();
		exit;
	}


	/* 
			Service Contract 
			19 Nov 2019
			iFoundries
		*/

	public function servicecontract()
	{

		$layout = JRequest::getVar('layout', 'default');

		$user = JFactory::getUser();

		if ($layout == 'edit') :
			// must check the cid, coz they have restriction on ACL based on cid
			$cid	=	JRequest::getVar('cid');

			$db = JFactory::getDBO();

			$query = " SELECT u.country_id, u.id, cp.id AS service_contract_item_id, s.customer_id FROM #__users AS u "
				.	" LEFT JOIN #__at_service_contract AS s ON s.customer_id = u.customer_id "
				.	" LEFT JOIN #__at_service_contract_product_xref AS cp ON cp.service_contract_id = s.id "
				.	" WHERE cp.id = '$cid' LIMIT 1 ";

			$db->setQuery($query);
			$userObj = $db->loadObject();

			if ($user->gid == 23) { // Manager, can view based on country_id

				if ($user->country_id != $userObj->country_id) {
					$this->setRedirect('index.php', JText::_('ALERTNOTAUTH'));
					return;
				}
			} else if ($user->gid == 24) { // Distributor, can onlyview his own data

				if ($user->customer_id !=  $userObj->customer_id) {
					$this->setRedirect('index.php', JText::_('ALERTNOTAUTH'));
					return;
				}
			}

			if (!$userObj->service_contract_item_id) {
				$this->setredirect('index.php?option=com_atelman&task=servicecontract', JText::_('ID_NOT_EXIST'));
				return;
			}

		endif;

		$view   = &$this->getView('ServiceContract');
		$view->setLayout($layout);
		//$view->setModel( $model, true );
		$view->display();
	}


	/**
		
	 **/
	public function submitcontract()
	{

		$model	= &$this->getModel('AtelServiceContract');

		$post = JRequest::get('post');

		if ($rma_request_id = $model->submitcontract($post)) {

			$msg = JText::_('Service Contract has been created');
			//$tmp1 = $model->submitRMAConfirmationMail($rma_request_id);

		} else {

			$msg = JText::_('Service Contract has not been created');
		}

		$link = 'index.php?option=com_atelman&task=servicecontract';

		$this->setRedirect($link, $msg);
	}



	public function export_csv_service_contract()
	{
		JRequest::checkToken() or die('Invalid Token');

		$model		= &$this->getModel('csvservicecontract');

		$post		=	JRequest::get('post');

		$tmp = $model->exportCSV($post);

		exit;
	}

	/**	
	 *Email Reminder for Service Contract (1 month before Contract Ended)
	 **/

	public function sendEmailReminderForExpiredServiceContract()
	{

		$model	= &$this->getModel('AtelServiceContract');

		$result = $model->sendReminderEmail();

		exit;
	}


	public function updatermapage()
	{
		$app = \Joomla\CMS\Factory::getApplication();
		$layout = $app->input->get('layout', 'updatermalist');
		$view = $this->getView('rmaitem', 'html');
		$view->setLayout($layout);
		//$view->setModel($model, true);
		$view->display();
	}


	public function ajaxLoadTotalCSV()
	{
		$app = \Joomla\CMS\Factory::getApplication();

		$db = Factory::getDBO();

		$type = $app->input->getVar('type');

		switch ($type) {
			case 'isb':
				$query = " SELECT COUNT(id) FROM #__at_warranty_items ";
				$db->setQuery($query);
				$total = $db->loadResult();
				break;
		}

		echo json_encode(array(
			"total"	=> 	$total
		));

		$app->close();
		exit;
	}

	/**
	 *
	 *	Check Warranty Status - stays in the page
	 *
	 *	
	 **/

	public function checkwarranty()
	{
		$app = \Joomla\CMS\Factory::getApplication();

		$model	= $this->getModel('Warrantyregister', 'Administrator');
		$obj = $model->checkWarranty();

		echo json_encode(array(
			"status" => $obj->status,
			"html_data"	=> 	$obj->html
		));

		$app->close();
		exit;
	}


	public function delete()
	{
		// Check for request forgeries
		$this->checkToken();

		// Get IDs from request
		$cid = $this->input->get('cid', array(), 'array');

		$cid = array_map('intval', $cid);
		if (empty($cid)) {
			$this->setMessage(Text::_('Need choose items'), 'warning');
			$this->setRedirect('index.php?option=com_atelman&view=rmaitems');
			return false;
		}

		$model = $this->getModel('Rmaitems', 'Administrator');

		try {
			$count = $model->remove($cid);
			$this->setMessage(Text::sprintf('RMA Request(s) Removed ', 'success'));
		} catch (\Exception $e) {
			$this->setMessage('Error : RMA Request(s) Not Removed', 'error');
		}

		$this->setRedirect('index.php?option=com_atelman&view=rmaitems');
		return true;
	}
}
