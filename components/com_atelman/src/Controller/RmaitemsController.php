<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Atelman
 * @author     iFoundries <wpsub@ifoundries.com>
 * @copyright  2025 iFoundries
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Atelman\Component\Atelman\Site\Controller;

\defined('_JEXEC') or die;

use Atelman\Component\Atelman\Administrator\Helper\AtelmanHelper;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\HTML\HTMLHelper as JHtml;

/**
 * Rmaitems class.
 *
 * @since  1.0.0
 */
class RmaitemsController extends FormController
{
	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional
	 * @param   array   $config  Configuration array for model. Optional
	 *
	 * @return  object	The model
	 *
	 * @since   1.0.0
	 */
	public function getModel($name = 'Rmaitems', $prefix = 'Site', $config = array())
	{
		return parent::getModel($name, $prefix, array('ignore_request' => true));
	}

	public function ajax()
	{
		$app = \Joomla\CMS\Factory::getApplication();
		$helper					= 	new AtelmanHelper();
		$section = $app->input->get('section', '');
		$helper 	= new AtelmanHelper();

		switch ($section) {
			case 'getCompany':

				$model	= &$this->getModel('AtelEndUser');
				$post	=	$app->input->getArray();

				if ($company_list = $model->getCompanyList($post)) {
					$status = true;
				} else {
					$status = false;
					$statusTxt = 'Company does not exist';
				}

				echo json_encode(array(
					"status"	=> 	$status,
					"statusTxt" => 	$statusTxt,
					"data" => $company_list
				));

				break;
			case 'getWarrantyStatus':
				$model	= $this->getModel('Atelcheckrequest');
				$post	=	$app->input->getArray();

				if ($statusTxt = $model->getWarrantyStatus($post)) {
					$status = true;
				} else {
					$status = false;
					$statusTxt = 'Warranty does not exist';
				}

				echo json_encode(array(
					"status"	=> 	$status,
					"statusTxt" => 	$statusTxt
				));

				break;
			case 'getRMAStatus':
				$model	= $this->getModel('Atelcheckrequest');
				$post	=	$app->input->getArray();

				if ($statusObj = $model->getRMAStatus($post['rmacode'])) {
					$status = true;

					$dates = NULL;

					if ($statusObj->status == 'receive') {
						$dates = $statusObj->received_date;
					}
					if ($statusObj->status == 'ship') {
						$dates = $statusObj->shipped_date;
					}
					if ($statusObj->status == 'close') {
						$dates = $statusObj->closed_date;
					}

					$statusTxt = $statusObj->status_name . (($dates != NULL) ? ' on ' . date("d M Y", strtotime($dates)) : '') . (($statusObj->status == 'receive') ? '; replacement unit(s) will be shipped in approximately ' . $statusObj->shipping_duration . ' day(s).' : '.');
				} else {
					$status = false;
					$statusTxt = 'RMA Number does not exist.';
				}

				echo json_encode(array(
					"status"	=> 	$status,
					"statusTxt" => 	$statusTxt
				));
				break;
			case 'getProductListing':

				$model	= &$this->getModel('AtelProduct');
				$post	=	$app->input->getArray();

				$rows = $model->getProductsByKeyword($post['keyword']);

				$product_list = '';

				if (!empty($rows)) {

					$status = true;

					//$product_list .= '<a href="javascript:void(0)" onclick="javascript:chooseProductNo('.$post['row_id'].', 0)">None</a>';
					foreach ($rows as $r) :
						$product_complete = $r->model_no . ' (' . $r->product_no . ') ';
						$product_list .= '<a href="javascript:void(0)" onclick="javascript:chooseProductNo(' . $post['row_id'] . ', ' . $r->id . ', \'' . $product_complete . '\')">' . $product_complete . ' </div>';
					endforeach;
				} else {
					$status = false;
				}


				echo json_encode(array(
					"status"	=> 	$status,
					"data" => 	$product_list
				));
				break;

			/**Get Serial Number plus Product Number**/
			case 'getSerialNo':
				$model	= $this->getModel('AtelProduct');
				$post	=	$app->input->getArray();

				$rows = $model->getSerialNoByKeyword($post['keyword']);

				$serial_no_list = '';

				if (!empty($rows)) {

					$status = true;

					//$serial_no_list .= '<a href="javascript:void(0)" onclick="javascript:chooseProductNo('.$post['row_id'].', 0)">None</a>';
					foreach ($rows as $r) :

						$serial_no_complete = $r->serial_no;
						$product_id = $r->product_id;
						$product_model = $r->model_no;
						$product_number = $r->product_no;

						$serial_no_list .= '<a href="javascript:void(0)" onclick="javascript:chooseSerialNo(' . $post['row_id'] . ', \'' . $serial_no_complete . '\',\'' . $product_id . '\',\'' . $product_model . ' (' . $product_number . ') \')">' . $serial_no_complete . ' </div>';
					endforeach;
				} else {
					$status = false;
				}


				echo json_encode(array(
					"status"	=> 	$status,
					"data" => 	$serial_no_list
				));
				break;


			/**Get Serial Number for End User**/
			case 'getSerialNoEndUser':
				$model	= &$this->getModel('AtelEndUser');
				$post	=	$app->input->getArray();

				$rows = $model->getSerialNoEndUser($post);

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
						$strtotime_expiry_date 	= strtotime($expiry_date) + (24 * 60 * 60);
						$expiry_date 						= date('d/m/Y', $strtotime_expiry_date - 1);

						if (time() <= $strtotime_expiry_date) {
							$warranty = 'IN';
						}

						if (time() > $strtotime_expiry_date) {
							$warranty = 'OUT';
						}

						// Purchase date : 01-10-2014...DOA latest date : 31-12-2014
						$tmp = split("-", $r->purchase_date); // 2014-12-31
						$year = $tmp[0];
						$month = $tmp[1] + 3;
						$day = $tmp[2];

						$doa_time = date("Y-m-d", mktime(0, 0, 0, $month, $day, $year));
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

			case 'submitRMAPrint':

				$data = JRequest::getVar('jdata', '');

				parse_str($data, $output);

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
				<img src="images/ATelesis_2color_web.png" alt="ATelesis_2color_web.png" /><br />
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

			case 'getServiceContract':

				$model	= $this->getModel('Atelcheckrequest');
				$post	=	$app->input->getArray();

				if ($statusTxt = $model->getServiceContract($post)) {
					$status = true;
				} else {
					$status = false;
					$statusTxt = 'Service Contract does not exist';
				}

				echo json_encode(array(
					"status"	=> 	$status,
					"statusTxt" => 	$statusTxt
				));

				break;

			default:
				break;
		}

		$app->close();
		exit;
	}




	public function ajaxaddrow()
	{
		$app = \Joomla\CMS\Factory::getApplication();

		$section = $app->input->get('section', '');
		$row_id		=	$app->input->get('row_id', '');

		switch ($section) {
			case 'warranty_reg':
				$helper = 	new AtelmanHelper();
				$country	= $helper->getCountryHTML($row_id);
				require JPATH_COMPONENT . '/tmpl/ajaxaddrow/warranty_reg.php';
				break;
			case 'rma_request':
				$helper 	= new AtelmanHelper();
				require JPATH_COMPONENT . '/tmpl/ajaxaddrow/rma_request.php';
				break;
			case 'rma_request_enduser':
				$helper 	= new AtelmanHelper();
				require JPATH_COMPONENT . '/tmpl/ajaxaddrow/rma_request_enduser.php';
				break;
		}
		$app->close();
		exit;
	}

	public function ajaxDistributor()
	{
		$app = \Joomla\CMS\Factory::getApplication();
		$db = Factory::getDBO();

		$country_id 	= 	$app->input->getVar('country_id', '');

		if ($country_id) {
			$query = " SELECT * FROM #__users WHERE gid = 24 AND country_id = '$country_id' ORDER BY name ASC ";
			$db->setQuery($query);
			$items = $db->loadObjectList();
		}
		$company = array();
		$company[] = JHTML::_('select.option',  '',  '-- Select Customer / Distributor --');

		if (!empty($items)) {
			foreach ($items as $i) :
				$company[] = JHTML::_('select.option',  $i->customer_id,  JText::_($i->name));
			endforeach;
		} else {
		}

		echo JHTML::_('select.genericlist',   $company, 'customer_id[]', '  ', 'value', 'text');
		$app->close();
		exit;
	}



	public function save($key = null, $urlVar = null)
	{
		$app = \Joomla\CMS\Factory::getApplication();

		$post = $app->input->getArray();

		$section = $post['section'];
		$Itemid = $post['Itemid'];
		switch ($section) {
			case 'warranty_reg':
				$model	= &$this->getModel('AtelWarrantyReg');
				$mails  = &$this->getModel('Atelmails');

				if ($warranty_reg_id = $model->save($post)) {
					$msg = Text::_('Warranty Registration has been saved');
					$tmp = $mails->sendmail($section, $warranty_reg_id, 'admin');
				} else {
					$msg = Text::_('Warranty Registration has not been saved');
				}
				$link = 'index.php?option=com_atelman&task=warrantyreg&Itemid=' . $Itemid;

				break;

			case 'rma_request':

				$model	= $this->getModel('Rmaitem');
				$mails  = $this->getModel('Atelmails');
				if ($rma_request_id = $model->save($post)) {
					$msg = Text::_('RMA Request has been saved');
					//$tmp = $mails->sendmail($section, $rma_request_id, 'admin'); // send to super admin
					//$tmp1 = $mails->sendmail($section, $rma_request_id, 'submitter'); // send to submitter
					//$tmp2 = $mails->sendmail($section, $rma_request_id, 'distributor'); // send to distributor if available(by cust. id)
				} else {
					$msg = Text::_('RMA Request has not been saved');
				}

				$link = 'index.php?option=com_atelman&view=rmarequest&Itemid=' . $Itemid;

				break;
		}
		$this->setRedirect($link, $msg);
	}



	public function exportServiceContract()
	{
		$app = \Joomla\CMS\Factory::getApplication();

		$servicecontractno 	= 	$app->input->get('servicecontractno', '');
		$serialno			=	$app->input->get('serialno', '');
		$model	= $this->getModel('Atelcheckrequest');
		$result = $model->exportServiceContract($servicecontractno, $serialno);
		exit;
	}
}
