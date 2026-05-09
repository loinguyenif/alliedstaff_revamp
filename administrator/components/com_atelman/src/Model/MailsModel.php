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
use Atelman\Component\Atelman\Site\Helper\AtelmanHelper as HelperAtelmanHelper;
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
class MailsModel extends AdminModel
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


	public function sendmail($section)
	{

		$app = Factory::getApplication();

		$mail = Factory::getMailer();
		$mail2 = Factory::getMailer();

		$cid		=	$app->input->getVar('cid');

		$db 			= 	Factory::getDbo();

		$attachment = '';
		$recipients = array();
		$bcc_recipients = array();
		$subject = '';
		// helper
		$helper	= new AtelmanHelper();

		$body			=	'<img src="cid:logo_id" alt="logo" /><br />';

		switch ($section) {
			case 'rmarequest':

				array_push($bcc_recipients, 'RMA-AsiaPacific@alliedtelesis.com.sg');
				//array_push($bcc_recipients, 'ata-webadmin@alliedtelesis.com.sg');
				//array_push($bcc_recipients, 'ajs6683@yahoo.com');

				$query = " SELECT r.*,rr.*,w.product_id, w.serial_no,w.serial_no_2, rs.status_name, w.so_no, w.invoice_no, w.purchase_date, w.expired_date, w.expired_date_manual, w.extended_warranty, w.extended_expired_date, rs.status_code, "
					. " ( SELECT r2.rmacode FROM #__at_rma_items AS r2 WHERE r2.customer_id = r.customer_id AND r2.so_no = r.so_no AND r2.replacement_sn = r.requested_sn ORDER BY r2.created_date DESC LIMIT 1) AS previous_rma_number "
					.	" FROM #__at_rma_items AS r "
					.	" LEFT JOIN #__at_rma_request AS rr ON rr.id = r.rma_request_id "
					.	" LEFT JOIN #__at_warranty_items AS w ON w.id = r.warranty_item_id "
					.	" LEFT JOIN #__at_rma_status AS rs ON rs.status_code = r.status "
					.	" WHERE r.id = '$cid' ";

				$db->setQuery($query);
				$row = $db->loadObject();

				// email to submitted user
				$email_array = explode(';', $row->email);
				foreach ($email_array as $ea) {
					array_push($recipients, trim($ea));
				}

				// expiry date
				$expiry_date = $row->expired_date;
				if ($row->extended_warranty > 0) $expiry_date = $row->extended_expired_date;
				if ($row->expired_date_manual != '0000-00-00') $expiry_date = $row->expired_date_manual;

				$subject 		=	$app->get('sitename') . ' - Your RMA Request has been updated - ' . (($row->rmacode) ? $row->rmacode : 'TBA');

				$body	.= '<div style="margin:10px 0;">Your RMA request has been updated</div>';
				$body	.= '<div style="margin-bottom:10px;">Requestor\'s Details:</div>';
				$body	.= '<table cellpadding=1 cellspacing=1 border=1>';
				$body	.= '<tr><td>Company</td><td>' . $row->fullname . '&nbsp;</td></tr>';
				$body	.= '<tr><td>Contact</td><td>' . $row->contact_name . '&nbsp;</td></tr>';
				$body	.= '<tr><td>Address</td><td>' . $row->address . '&nbsp;</td></tr>';
				$body	.= '<tr><td>City</td><td>' . $row->city . '&nbsp;</td></tr>';
				$body	.= '<tr><td>State/Province</td><td>' . $row->state . '&nbsp;</td></tr>';
				$body	.= '<tr><td>ZIP/Postal Code</td><td>' . $row->postal_code . '&nbsp;</td></tr>';
				$body	.= '<tr><td>Country/Region</td><td>' . $row->country . '&nbsp;</td></tr>';
				$body	.= '<tr><td>Telephone</td><td>' . $row->telephone . '&nbsp;</td></tr>';
				$body	.= '<tr><td>Fax</td><td>' . $row->fax . '&nbsp;</td></tr>';
				$body	.= '<tr><td>Email</td><td>' . $row->email . '&nbsp;</td></tr>';
				$body	.= '</table>';
				$body	.= '<div style="margin:10px 0;">Requested Item(s):</div>';

				$body	.= '<table cellpadding="1" cellspacing="1" border="1" >';
				$body .= '<tr>';
				$body .= '	<td>RMA Number</td>';
				$body .= '	<td>Status</td>';
				$body .= '	<td>Part Number</td>';
				$body .= '	<td>Model Number</td>';
				$body .= '	<td>Serial Number</td>';
				$body .= '	<td>Fault Description</td>';
				$body .= '	<td>SO Number</td>';
				$body .= '	<td>Invoice Number</td>';
				$body .= '	<td>Ship Date</td>';
				$body .= '	<td>Expiry Date</td>';
				$body .= '	<td>Request Date</td>';
				$body .= '	<td>Warranty</td>';
				$body .= '	<td>Previous RMA Number</td>';
				$body .= '	<td>Remarks</td>';
				$body .= '</tr>';

				if (!empty($row->replacement_pn)) {
					$model_no = $helper->getProductByPartNumber($row->replacement_pn)->model_no;
					$product_no = $row->replacement_pn;
				} else {
					$model_no = $helper->getItemById('products', $row->product_id)->model_no;
					$product_no = $helper->getItemById('products', $row->product_id)->product_no;
				}

				$body 	.= '<tr>';
				$body 	.= '<td>' . (($row->rmacode) ? $row->rmacode : 'TBA') . '</td>';
				$body 	.= '<td>' . $row->status_name . '</td>';
				$body 	.= '<td>' . $product_no . '&nbsp;</td>';
				$body 	.= '<td>' . $model_no . '&nbsp;</td>';
				//$body 	.= '<td>'.(($row->serial_no_2)?$row->serial_no_2:$row->serial_no).'&nbsp;</td>';
				$body 	.= '<td>' . $row->requested_sn . '&nbsp;</td>';
				$body 	.= '<td>' . $row->description . '&nbsp;</td>';
				$body 	.= '<td>' . $row->so_no . '&nbsp;</td>';
				$body 	.= '<td>' . $row->invoice_no . '&nbsp;</td>';
				$body 	.= '<td>' . date("d M Y", strtotime($row->purchase_date)) . '&nbsp;</td>';
				$body 	.= '<td>' . date("d M Y", strtotime($expiry_date)) . '&nbsp;</td>';
				$body 	.= '<td>' . date("d M Y", strtotime($row->created_date)) . '&nbsp;</td>';
				$body 	.= '<td>' . $row->warranty_status . '&nbsp;</td>';
				$body 	.= '<td>' . (isset($row->previous_rma_number) ? $row->previous_rma_number : 'N/A') . '&nbsp;</td>';
				$body 	.= '<td>' . $row->remarks . '&nbsp;</td>';
				$body 	.= '</tr>';
				$body	.= '</table>';

				if ($row->status_code == 'await') :
					$body .= '<div style="margin:10px 0;">Please do not include accessories when returning the faulty unit(s)</div>';
				endif;

				if ($row->status_code == 'receive' || $row->status_code == 'ship') {

					$query = " SELECT d.* FROM #__at_rma_items AS r "
						. " LEFT JOIN #__at_rma_downloads AS d ON d.rma_item_id = r.id AND d.status = r.status "
						. " WHERE r.id = '$cid' AND d.status = '" . $row->status_code . "' ";

					$db->setQuery($query);
					$attachments = $db->loadObjectList();

					if (!empty($attachments)) {
						foreach ($attachments as $a) {

							// Separate Mail Attachment for AirWay Bill and Documents
							if ($a->is_airway_bill) {
								$mail->addAttachment(JPATH_ADMINISTRATOR . '/atelesis_docs/' . $a->filename);
							} else {
								$mail2->addAttachment(JPATH_ADMINISTRATOR . '/atelesis_docs/' . $a->filename);
							}
						}
					}
				}

				break;
		}

		$body	.= '<div style="margin:10px 0;">Regards,</div>';
		$body	.= '<div style="margin:10px 0;">Allied Telesis Asia Pacific</div>';

		$mail->IsHTML(true);
		$mail->setSender(array('RMA-AsiaPacific@alliedtelesis.com.sg', 'RMA Admin'));
		$mail->setSubject($subject);
		$mail->setBody($body);
		$mail->AddEmbeddedImage(JPATH_SITE  . '/templates/rhuk_milkyway/images/ATelesis_2color_web.png', 'logo_id', 'ATelesis_2color_web.png', 'base64', 'image/png');
		$mail->addRecipient($recipients);
		if (!empty($bcc_recipients)) {
			$mail->addBCC($bcc_recipients);
		}
		//$result1 = $mail->Send();


		if ($row->status_code == 'receive' || $row->status_code == 'ship') {
			$mail2->IsHTML(true);
			$mail2->setSender(array('RMA-AsiaPacific@alliedtelesis.com.sg', 'RMA Admin'));
			$mail2->setSubject($subject);
			$mail2->setBody($body);
			$mail2->AddEmbeddedImage(JPATH_SITE . '/templates/rhuk_milkyway/images/ATelesis_2color_web.png', 'logo_id', 'ATelesis_2color_web.png', 'base64', 'image/png');
			$mail2->addRecipient($bcc_recipients);
			//$result2 = $mail2->Send();
		}

		return true;
	}

	/*
			From : Update Batch
		*/
	/*function sendMailByRMACodeBatch($rmacode) {
			
			global $mainframe;
			
			$mail 			=&	JFactory::getMailer();
			$db 			= 	JFactory::getDBO();	
			
			$bcc_recipients = array();
			
			// helper
			$helper	= new ATelmanHelper();
			
			array_push($bcc_recipients, 'RMA-AsiaPacific@alliedtelesis.com.sg');
			array_push($bcc_recipients, 'ata-webadmin@alliedtelesis.com.sg');
			
			$subject 						=	$mainframe->getCfg('sitename').' - Your RMA Request '.($rmacode?'(#'.$rmacode.')':'').' has been updated';
			$await_status 			= 0;
			
			$total = 0;
			$requestor_details 	= array();
			$recipients = array();
			
			// consolidate items that have same RMA Code - START
			$query = " SELECT i.*, r.fullname, r.contact_name,r.address,r.telephone,r.fax,r.email, "
			.	" rs.status_name, "
			.	" w.product_id, w.serial_no, w.serial_no_2, w.purchase_date, w.so_no,w.expired_date, w.expired_date_manual, w.extended_warranty, w.extended_expired_date "
			.	" FROM #__at_rma_items AS i "
			.	" LEFT JOIN #__at_rma_request AS r ON r.id = i.rma_request_id "
			.	" LEFT JOIN #__at_warranty_items AS w ON w.id = i.warranty_item_id "
			.	" LEFT JOIN #__at_rma_status AS rs ON rs.status_code = i.status "
			.	" WHERE i.rmacode = '$rmacode' ";
			
			$db->setQuery( $query );
			$lists = $db->loadObjectList();
			
			$body	= '<table cellpadding="1" cellspacing="1" border="1" >';
			$body .= '<tr>';
			$body .= '	<td>RMA Number</td>';
			$body .= '	<td>Status</td>';
			$body .= '	<td>Part Number</td>';
			$body .= '	<td>Model Number</td>';
			$body .= '	<td>Serial Number</td>';
			$body .= '	<td>Fault Description</td>';
			$body .= '	<td>Invoice Number</td>';
			$body .= '	<td>SO Number</td>';
			$body .= '	<td>Ship Date</td>';
			$body .= '	<td>Expiry Date</td>';
			$body .= '	<td>Request Date</td>';
			$body .= '	<td>Warranty</td>';
			$body .= '	<td>Remarks</td>';
			$body .= '</tr>';
			
			
			foreach($lists as $v) :
			
			if(!in_array($v->email, $recipients)) {
			
			$recipients[$total] = $v->email;			
			
			$requestor_details[$total]['fullname'] 			= $v->fullname;
			$requestor_details[$total]['contact_name'] 	= $v->contact_name;
			$requestor_details[$total]['address'] 			= $v->address;
			$requestor_details[$total]['telephone'] 		= $v->telephone;
			$requestor_details[$total]['fax'] 					= $v->fax;
			$requestor_details[$total]['email'] 				= $v->email;
			
			$total++;
			
			}
			
			$expiry_date = $v->expired_date;
			if($v->expired_date_manual != '0000-00-00') $expiry_date = $v->expired_date_manual;
			if($v->extended_warranty > 0) $expiry_date = $v->extended_expired_date;
			
			$body 	.= '<tr>';
			$body 	.= '<td>'.(($v->rmacode)?$v->rmacode:'TBA').'</td>';
			$body 	.= '<td>'.$v->status_name.'</td>';
			$body 	.= '<td>'.$helper->getItemById('products',$v->product_id)->product_no.'&nbsp;</td>';
			$body 	.= '<td>'.$helper->getItemById('products',$v->product_id)->model_no.'&nbsp;</td>';
			$body 	.= '<td>'.(($v->serial_no_2)?$v->serial_no_2:$v->serial_no).'&nbsp;</td>';
			$body 	.= '<td>'.$v->description.'&nbsp;</td>';
			$body 	.= '<td>'.$v->invoice_no.'&nbsp;</td>';
			$body 	.= '<td>'.$v->so_no.'&nbsp;</td>';
			$body 	.= '<td>'.date("d M Y",strtotime($v->purchase_date)).'&nbsp;</td>';
			$body 	.= '<td>'.date("d M Y",strtotime($expiry_date)).'&nbsp;</td>';
			$body 	.= '<td>'.date("d M Y",strtotime($v->created_date)).'&nbsp;</td>';
			$body 	.= '<td>'.$v->warranty_status.'&nbsp;</td>';
			$body 	.= '<td>'.$v->remarks.'&nbsp;</td>';
			$body 	.= '</tr>';
			
			if($v->status_code == 'await') {
			$await_status = 1;
			}
			
			endforeach;			
			
			$body	.= '</table>';
			
			if($await_status) :
			$body .= '<div style="margin:10px 0;">Please do not include accessories when returning the faulty unit(s)</div>';
			endif; 
			
			$body	.= '<div style="margin:10px 0;">Regards,</div>';
			$body	.= '<div style="margin:10px 0;">Allied Telesis Asia Pacific</div>';
			// consolidate - END 
			
			// Set Header - Contact Details - Start 
			foreach($recipients as $k => $em) :
			
			$bodyh	=	'<img src="cid:logo_id" alt="logo" /><br />';
			$bodyh	.= '<div style="margin:10px 0;">Your RMA Item has been updated</div>';
			$bodyh	.= '<div style="margin-bottom:10px;">Requestor\'s Details:</div>';
			$bodyh	.= '<table cellpadding=1 cellspacing=1 border=1 style="padding:10px 0;">';
			$bodyh	.= '<tr><td>Company</td><td>'.$requestor_details[$k]['fullname'].'&nbsp;</td></tr>';
			$bodyh	.= '<tr><td>Contact</td><td>'.$requestor_details[$k]['contact_name'].'&nbsp;</td></tr>';
			$bodyh	.= '<tr><td>Address</td><td>'.$requestor_details[$k]['address'].'&nbsp;</td></tr>';
			$bodyh	.= '<tr><td>Telephone</td><td>'.$requestor_details[$k]['telephone'].'&nbsp;</td></tr>';
			$bodyh	.= '<tr><td>Fax</td><td>'.$requestor_details[$k]['fax'].'&nbsp;</td></tr>';
			$bodyh	.= '<tr><td>Email</td><td>'.$requestor_details[$k]['email'].'&nbsp;</td></tr>';
			$bodyh	.= '</table>';
			$bodyh	.= '<div style="margin:10px 0;">Requested Item(s):</div>';
			
			$bodyh .= $body;
			
			$mail 			=&	JFactory::getMailer();
			
			// Send Email 
			$mail->IsHTML(true);
			$mail->setSender( array( 'RMA-AsiaPacific@alliedtelesis.com.sg', 'RMA Admin') );
			$mail->setSubject($subject);
			$mail->setBody($bodyh);
			$mail->AddEmbeddedImage( JPATH_SITE.DS.'templates'.DS.'rhuk_milkyway'.DS.'images'.DS.'ATelesis_2color_web.png', 'logo_id', 'ATelesis_2color_web.png', 'base64', 'image/png' );
			$mail->addRecipient($em);
			if(!empty($bcc_recipients)) {
			$mail->addBCC($bcc_recipients);
			}
			
			$tmps = $mail->Send();
			
			endforeach;	
			
			return true;
			}
		*/

	public function sendMailByIdBatch($cids)
	{

		$app = Factory::getApplication();

		$db = 	Factory::getDbo();

		$bcc_recipients = array();

		// helper
		$helper	= new AtelmanHelper();

		array_push($bcc_recipients, 'RMA-AsiaPacific@alliedtelesis.com.sg');
		//array_push($bcc_recipients, 'ajs6683@yahoo.com');

		$requestor_details 	= array();
		$recipients = array();

		/* consolidate items based on CIDs - START */
		$query = " SELECT i.*, r.fullname, r.contact_name,r.address,r.city, r.state,r.postal_code,r.country, r.telephone,r.fax,r.email, "
			.	" rs.status_name, "
			.	" w.product_id, w.serial_no, w.serial_no_2, w.purchase_date, w.so_no, w.expired_date, w.expired_date_manual, w.extended_warranty, w.extended_expired_date, "
			.	" p.product_no, p.model_no , "
			. " ( SELECT r2.rmacode FROM #__at_rma_items AS r2 WHERE r2.customer_id = i.customer_id AND r2.so_no = i.so_no AND r2.replacement_sn = i.requested_sn ORDER BY r2.created_date DESC LIMIT 1) AS previous_rma_number "
			.	" FROM #__at_rma_items AS i "
			.	" LEFT JOIN #__at_rma_request AS r ON r.id = i.rma_request_id "
			.	" LEFT JOIN #__at_warranty_items AS w ON w.id = i.warranty_item_id "
			.	" LEFT JOIN #__at_rma_status AS rs ON rs.status_code = i.status "
			.	" LEFT JOIN #__at_products AS p ON p.id = i.product_id "
			.	" WHERE i.id IN (" . $cids . ") "
			.	" ORDER BY p.product_no ASC, i.requested_sn ASC ";

		$db->setQuery($query);
		$lists = $db->loadObjectList();

		// must consolidate items based on RMA
		$rma_array = array();
		$body_array = array();
		$await_status_array = array();
		$receive_ship_status_array = array();

		foreach ($lists as $key => $v) {

			if (!in_array($v->rmacode, $rma_array)) {

				if ($v->rmacode) {
					array_push($rma_array, $v->rmacode);

					$recipients[$v->rmacode][$key] = $v->email;

					$requestor_details[$v->rmacode][$key]['fullname'] 			= $v->fullname;
					$requestor_details[$v->rmacode][$key]['contact_name'] 	= $v->contact_name;
					$requestor_details[$v->rmacode][$key]['address'] 				= $v->address;
					$requestor_details[$v->rmacode][$key]['city'] 					= $v->city;
					$requestor_details[$v->rmacode][$key]['state'] 					= $v->state;
					$requestor_details[$v->rmacode][$key]['postal_code'] 		= $v->postal_code;
					$requestor_details[$v->rmacode][$key]['country'] 				= $v->country;
					$requestor_details[$v->rmacode][$key]['telephone'] 			= $v->telephone;
					$requestor_details[$v->rmacode][$key]['fax'] 						= $v->fax;
					$requestor_details[$v->rmacode][$key]['email'] 					= $v->email;
				}
			}



			$expiry_date = $v->expired_date;
			if ($v->extended_warranty > 0) $expiry_date = $v->extended_expired_date;
			if ($v->expired_date_manual != '0000-00-00') $expiry_date = $v->expired_date_manual;

			$body_array[$v->rmacode] 	.= '<tr>';
			$body_array[$v->rmacode] 	.= '<td>' . (($v->rmacode) ? $v->rmacode : 'TBA') . '</td>';
			$body_array[$v->rmacode] 	.= '<td>' . $v->status_name . '</td>';
			$body_array[$v->rmacode] 	.= '<td>' . $v->product_no . '&nbsp;</td>';
			$body_array[$v->rmacode] 	.= '<td>' . $v->model_no . '&nbsp;</td>';
			//$body_array[$v->rmacode] 	.= '<td>'.(($v->serial_no_2)?$v->serial_no_2:$v->serial_no).'&nbsp;</td>';
			$body_array[$v->rmacode] 	.= '<td>' . $v->requested_sn . '&nbsp;</td>';
			$body_array[$v->rmacode] 	.= '<td>' . $v->description . '&nbsp;</td>';
			$body_array[$v->rmacode] 	.= '<td>' . $v->so_no . '&nbsp;</td>';
			$body_array[$v->rmacode] 	.= '<td>' . $v->invoice_no . '&nbsp;</td>';
			$body_array[$v->rmacode] 	.= '<td>' . date("d M Y", strtotime($v->purchase_date)) . '&nbsp;</td>';
			$body_array[$v->rmacode] 	.= '<td>' . date("d M Y", strtotime($expiry_date)) . '&nbsp;</td>';
			$body_array[$v->rmacode] 	.= '<td>' . date("d M Y", strtotime($v->created_date)) . '&nbsp;</td>';
			$body_array[$v->rmacode] 	.= '<td>' . $v->warranty_status . '&nbsp;</td>';
			$body_array[$v->rmacode] 	.= '<td>' . (isset($v->previous_rma_number) ? $v->previous_rma_number : 'N/A') . '&nbsp;</td>';
			$body_array[$v->rmacode] 	.= '<td>' . $v->remarks . '&nbsp;</td>';
			$body_array[$v->rmacode] 	.= '</tr>';

			if ($v->status == 'await') {
				$await_status_array[$v->rmacode] = 1;
			}

			if ($v->status == 'receive' || $v->status == 'ship') {
				$receive_ship_status_array[$v->rmacode] = $v->status;
			}
		}

		/* Set Header - Contact Details - Start */
		foreach ($rma_array as $key => $rma) {

			$mail 			= Factory::getMailer();
			$mail2 			= Factory::getMailer();

			foreach ($recipients[$rma] as $k => $em) {

				// accept multiple email address now
				$email_array = array();
				$tmp_array = explode(';', $em);
				foreach ($tmp_array as $ta) {
					array_push($email_array, trim($ta));
				}

				$body	= '<table cellpadding="1" cellspacing="1" border="1" >';
				$body .= '<tr>';
				$body .= '	<td>RMA Number</td>';
				$body .= '	<td>Status</td>';
				$body .= '	<td>Part Number</td>';
				$body .= '	<td>Model Number</td>';
				$body .= '	<td>Serial Number</td>';
				$body .= '	<td>Fault Description</td>';
				$body .= '	<td>SO Number</td>';
				$body .= '	<td>Invoice Number</td>';
				$body .= '	<td>Ship Date</td>';
				$body .= '	<td>Expiry Date</td>';
				$body .= '	<td>Request Date</td>';
				$body .= '	<td>Warranty</td>';
				$body .= '	<td>Previous RMA Number</td>';
				$body .= '	<td>Remarks</td>';
				$body .= '</tr>';

				$bodyh	=	'<img src="cid:logo_id" alt="logo" /><br />';
				$bodyh	.= '<div style="margin:10px 0;">Your RMA request has been updated</div>';
				$bodyh	.= '<div style="margin-bottom:10px;">Requestor\'s Details:</div>';
				$bodyh	.= '<table cellpadding=1 cellspacing=1 border=1>';
				$bodyh	.= '<tr><td>Company</td><td>' . $requestor_details[$rma][$k]['fullname'] . '&nbsp;</td></tr>';
				$bodyh	.= '<tr><td>Contact</td><td>' . $requestor_details[$rma][$k]['contact_name'] . '&nbsp;</td></tr>';
				$bodyh	.= '<tr><td>Address</td><td>' . $requestor_details[$rma][$k]['address'] . '&nbsp;</td></tr>';
				$bodyh	.= '<tr><td>City</td><td>' . $requestor_details[$rma][$k]['city'] . '&nbsp;</td></tr>';
				$bodyh	.= '<tr><td>State/Province</td><td>' . $requestor_details[$rma][$k]['state'] . '&nbsp;</td></tr>';
				$bodyh	.= '<tr><td>ZIP/Postal Code</td><td>' . $requestor_details[$rma][$k]['postal_code'] . '&nbsp;</td></tr>';
				$bodyh	.= '<tr><td>Country/Region</td><td>' . $requestor_details[$rma][$k]['country'] . '&nbsp;</td></tr>';
				$bodyh	.= '<tr><td>Telephone</td><td>' . $requestor_details[$rma][$k]['telephone'] . '&nbsp;</td></tr>';
				$bodyh	.= '<tr><td>Fax</td><td>' . $requestor_details[$rma][$k]['fax'] . '&nbsp;</td></tr>';
				$bodyh	.= '<tr><td>Email</td><td>' . $requestor_details[$rma][$k]['email'] . '&nbsp;</td></tr>';
				$bodyh	.= '</table>';
				$bodyh	.= '<div style="margin:10px 0;">Requested Item(s):</div>';

				// structure it into front
				$body .= $body_array[$rma];

				$body	.= '</table>';

				if ($await_status_array[$rma]) :
					$body .= '<div style="margin:10px 0;">Please do not include accessories when returning the faulty unit(s)</div>';
				endif;

				if ($receive_ship_status_array[$rma]) {

					// get rma_item_id based on RMA Code
					$query = " SELECT GROUP_CONCAT(DISTINCT(id) SEPARATOR ',') AS rma_id FROM #__at_rma_items WHERE rmacode = '$rma' ";
					$db->setQuery($query);
					$rma_item_ids = $db->loadResult();

					$query = " SELECT * FROM #__at_rma_downloads "
						. " WHERE rma_item_id IN (" . $rma_item_ids . ") AND status = '" . $receive_ship_status_array[$rma] . "' ";

					$db->setQuery($query);
					$attachments = $db->loadObjectList();

					if (!empty($attachments)) {
						$attachment_array = array();
						foreach ($attachments as $a) {

							if (in_array($a->filename, $attachment_array)) {
								continue;
							}

							array_push($attachment_array, trim($a->filename));

							// Separate Mail Attachment for AirWay Bill and Documents
							if ($a->is_airway_bill) {
								$mail->addAttachment(JPATH_ADMINISTRATOR . '/atelesis_docs/' . $a->filename);
							} else {
								$mail2->addAttachment(JPATH_ADMINISTRATOR . '/atelesis_docs/' . $a->filename);
							}
						}
					}
				}

				$body	.= '<div style="margin:10px 0;">Regards,</div>';
				$body	.= '<div style="margin:10px 0;">Allied Telesis Asia Pacific</div>';

				$bodyh .= $body;

				$subject = $app->get('sitename') . ' - Your RMA Request has been updated - ' . $rma;

				/* Send Email */
				$mail->IsHTML(true);
				$mail->setSender(array('RMA-AsiaPacific@alliedtelesis.com.sg', 'RMA Admin'));
				$mail->setSubject($subject);
				$mail->setBody($bodyh);
				$mail->AddEmbeddedImage(JPATH_SITE . '/templates/rhuk_milkyway/images/ATelesis_2color_web.png', 'logo_id', 'ATelesis_2color_web.png', 'base64', 'image/png');
				$mail->addRecipient($email_array);
				if (!empty($bcc_recipients)) {
					$mail->addBCC($bcc_recipients);
				}
				//$result = $mail->Send();

				if ($receive_ship_status_array[$rma]) {
					$mail2->IsHTML(true);
					$mail2->setSender(array('RMA-AsiaPacific@alliedtelesis.com.sg', 'RMA Admin'));
					$mail2->setSubject($subject);
					$mail2->setBody($bodyh);
					$mail2->AddEmbeddedImage(JPATH_SITE . '/templates/rhuk_milkyway/images/ATelesis_2color_web.png', 'logo_id', 'ATelesis_2color_web.png', 'base64', 'image/png');
					$mail2->addRecipient($bcc_recipients);
					//$result2 = $mail2->Send();
				}
			}
		}
	}
}
