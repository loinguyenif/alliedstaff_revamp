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
class MailModel extends AdminModel
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


	public function submitRMAConfirmationMail($id)
	{

		$app = \Joomla\CMS\Factory::getApplication();
		$post	=	$app->input->post->getArray();

		$db = Factory::getContainer()->get('DatabaseDriver');

		$recipients = array();
		$bcc_recipients = array();

		// email to submitted user
		$email_array = explode(';', $post['email']);
		foreach ($email_array as $ea) {
			array_push($recipients, trim($ea));
		}

		array_push($bcc_recipients, 'RMA-AsiaPacific@alliedtelesis.com.sg');
		//array_push($bcc_recipients, 'ata-webadmin@alliedtelesis.com.sg');
		//array_push($bcc_recipients, 'ajs6683@yahoo.com');
		// helper
		$helper	= new AtelmanHelper();

		$image_embed			=	'<img src="cid:logo_id" alt="logo" /><br />';
		$image_not_embed 	=	'<img src="' . JPATH_SITE  . '/templates/rhuk_milkyway/images/ATelesis_2color_web.png" alt="logo"><br />';

		$subject =	$app->get('sitename') . ' - Your RMA Request has been submitted';
		$body = '<div style="padding:10px 0;">Thank you for your submission. We or our partner/distributor will get back to you as soon as possible.</div>';

		$body	.= '<div style="margin-bottom:10px;">Requestor\'s Details:</div>';
		$body	.= '<table cellpadding="1" cellspacing="1" border="1" >';
		$body	.= '<tr><td>Company</td><td>' . $post['fullname'] . '&nbsp;</td></tr>';
		$body	.= '<tr><td>Contact</td><td>' . $post['contact_name'] . '&nbsp;</td></tr>';
		$body	.= '<tr><td>Address</td><td>' . $post['address'] . '&nbsp;</td></tr>';
		$body	.= '<tr><td>City</td><td>' . $post['city'] . '&nbsp;</td></tr>';
		$body	.= '<tr><td>State/Province</td><td>' . $post['state'] . '&nbsp;</td></tr>';
		$body	.= '<tr><td>ZIP/Postal Code</td><td>' . $post['postal_code'] . '&nbsp;</td></tr>';
		$body	.= '<tr><td>Country/Region</td><td>' . $post['country'] . '&nbsp;</td></tr>';
		$body	.= '<tr><td>Telephone</td><td>' . $post['telephone'] . '&nbsp;</td></tr>';
		$body	.= '<tr><td>Fax</td><td>' . $post['fax'] . '&nbsp;</td></tr>';
		$body	.= '<tr><td>E-mail</td><td>' . $post['email'] . '&nbsp;</td></tr>';
		$body	.= '</table>';
		$body	.= '<div style="padding:10px 0;">Requested Item(s):</div>';

		// get status
		$stats = array();
		$query = " SELECT * FROM #__at_rma_status ";
		$db->setQuery($query);
		$statuses = $db->loadObjectList();

		foreach ($statuses as $s) :
			$stats[$s->status_code] = $s->status_name;
		endforeach;

		$rma_id = 0;

		$query = " SELECT r.*, w.product_id, w.serial_no, w.serial_no_2, w.so_no, w.expired_date, w.expired_date_manual, w.extended_expired_date, w.purchase_date , "
			.	" ( SELECT r2.rmacode FROM #__at_rma_items AS r2 WHERE r2.customer_id = r.customer_id AND r2.so_no = r.so_no AND r2.replacement_sn = r.requested_sn ORDER BY r2.created_date DESC LIMIT 1) AS previous_rma_number "
			.	" FROM #__at_rma_items AS r "
			.	" LEFT JOIN #__at_warranty_items AS w ON w.id = r.warranty_item_id "
			.	" LEFT JOIN #__at_products AS p ON p.id = w.product_id "
			.	" WHERE r.rma_request_id = '$id' "
			. " ORDER BY p.product_no ASC, r.requested_sn ASC ";

		$db->setQuery($query);
		$rows = $db->loadObjectList();

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

		$body_iw = $body; // DOA or IN body
		$body_oow = $body; // OUT body
		$body_all = $body; // ALL

		$oow_items_exist = 0;
		$iw_items_exist = 0;

		foreach ($rows as $r) :

			$rma_id = $r->id; // RMA ID

			// expiry date
			$expiry_date = $r->expired_date;
			if ($r->extended_expired_date != '0000-00-00') $expiry_date = $r->extended_expired_date;
			if ($r->expired_date_manual != '0000-00-00') $expiry_date = $r->expired_date_manual;

			if (!empty($r->replacement_pn)) {
				$model_no = $helper->getProductByPartNumber($r->replacement_pn)->model_no;
				$product_no = $r->replacement_pn;
			} else {
				$model_no = $helper->getItemById('products', $r->product_id)->model_no;
				$product_no = $helper->getItemById('products', $r->product_id)->product_no;
			}

			$tmpbody 	= '<tr>';
			$tmpbody 	.= '<td>' . (($r->status != 'open') ? $r->rmacode : 'TBA') . '</td>';
			$tmpbody 	.= '<td>' . $stats[$r->status] . '</td>';
			$tmpbody 	.= '<td>' . $product_no . '&nbsp;</td>';
			$tmpbody 	.= '<td>' . $model_no . '&nbsp;</td>';
			$tmpbody 	.= '<td>' . (($r->serial_no_2) ? $r->serial_no_2 : $r->serial_no) . '&nbsp;</td>';
			$tmpbody 	.= '<td>' . $r->description . '&nbsp;</td>';
			$tmpbody 	.= '<td>' . $r->so_no . '&nbsp;</td>';
			$tmpbody 	.= '<td>' . $r->invoice_no . '&nbsp;</td>';
			$tmpbody 	.= '<td>' . date("d M Y", strtotime($r->purchase_date)) . '&nbsp;</td>';
			$tmpbody 	.= '<td>' . date("d M Y", strtotime($expiry_date)) . '&nbsp;</td>';
			$tmpbody 	.= '<td>' . date("d M Y", strtotime($r->created_date)) . '&nbsp;</td>';
			$tmpbody 	.= '<td>' . $r->warranty_status . '&nbsp;</td>';
			$tmpbody 	.= '<td>' . (isset($r->previous_rma_number) ? $r->previous_rma_number : 'N/A') . '&nbsp;</td>';
			$tmpbody 	.= '<td>' . $r->remarks . '&nbsp;</td>';
			$tmpbody 	.= '</tr>';

			if (strtoupper($r->warranty_status) == 'IN' || strtoupper($r->warranty_status) == 'DOA') {
				$body_iw .= $tmpbody;
				$iw_items_exist = 1;
			}

			if (strtoupper($r->warranty_status) == 'OUT') {
				$body_oow .= $tmpbody;
				$oow_items_exist = 1;
			}

			$body_all .= $tmpbody;
		endforeach;

		$closebody	= '</table>';

		$closebody	.= '<div style="margin:10px 0;">Please send back only bare faulty unit to:<br /><br />Allied Telesis Asia Pacific Pte Ltd<br />RMA Store<br />4th floor<br />11 Tai Seng Link<br />Singapore 534182<br /><br />ATTN: AMY TCHIN<br />DEPT:RMA<br /></div>';

		$closebody	.= '<div style="padding:10px 0;">Regards,</div>';
		$closebody	.= '<div style="padding:10px 0;">Allied Telesis Asia Pacific</div>';

		$body_iw = $body_iw . $closebody;
		$body_oow = $body_oow . $closebody;
		$body_all = $body_all . $closebody;

		$timetext = time() . $rma_id;

		$this->createOriginalPDF($timetext, $image_not_embed . $body_all);
		foreach ($rows as $r) :
			$this->insertOriginalRMA($timetext, $r->id);
		endforeach;

		$body_iw = $image_embed . $body_iw;
		$body_oow = $image_embed . $body_oow;

		if ($iw_items_exist) {

			$mail = \Joomla\CMS\Factory::getContainer()->get(\Joomla\CMS\Mail\MailerFactoryInterface::class)->createMailer();

			// IN or DOA body
			$mail->IsHTML(true);
			$mail->setSender(array('RMA-AsiaPacific@alliedtelesis.com.sg', 'RMA Admin'));
			$mail->setSubject($subject);
			$mail->setBody($body_iw);
			$mail->AddEmbeddedImage(JPATH_SITE . '/templates/rhuk_milkyway/images/ATelesis_2color_web.png', 'logo_id', 'ATelesis_2color_web.png', 'base64', 'image/png');
			$mail->addRecipient($recipients);
			if (!empty($bcc_recipients)) {
				$mail->addBCC($bcc_recipients);
			}
			//$result = $mail->Send();
		}



		if ($oow_items_exist) {

			$mail2 = \Joomla\CMS\Factory::getContainer()->get(\Joomla\CMS\Mail\MailerFactoryInterface::class)->createMailer();

			// OUT body
			$mail2->IsHTML(true);
			$mail2->setSender(array('RMA-AsiaPacific@alliedtelesis.com.sg', 'RMA Admin'));
			$mail2->setSubject($subject);
			$mail2->setBody($body_oow);
			$mail2->AddEmbeddedImage(JPATH_SITE . '/templates/rhuk_milkyway/images/ATelesis_2color_web.png', 'logo_id', 'ATelesis_2color_web.png', 'base64', 'image/png');
			$mail2->addRecipient($recipients);
			if (!empty($bcc_recipients)) {
				$mail2->addBCC($bcc_recipients);
			}
			//$result = $mail2->Send();
		}

		return true;
	}

	private static function createOriginalPDF($txtstring, $text)
	{

		require_once JPATH_ADMINISTRATOR . '/mpdf82/vendor/autoload.php';
		$mpdf = new Mpdf();

		$mpdf->WriteHTML($text);

		$mpdf->Output(JPATH_ADMINISTRATOR . '/atelesis_docs/RMA-' . $txtstring . '.pdf', 'F');
	}

	private static function insertOriginalRMA($txtstring, $rma_id)
	{

		$user =	Factory::getApplication()->getIdentity();
		$db = Factory::getContainer()->get('DatabaseDriver');
		$atelDownload = new \stdClass();
		$atelDownload->rma_item_id 	= $rma_id;
		$atelDownload->status 		= 'RMAREQUEST';
		$atelDownload->filename 	= "RMA-" . $txtstring . ".pdf";
		$atelDownload->uploaded_by 	= $user->id;
		$db->insertObject('#__at_rma_downloads', $atelDownload);
	}
}
