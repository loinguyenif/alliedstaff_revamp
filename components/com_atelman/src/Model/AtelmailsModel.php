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

use \Joomla\CMS\Factory;
use \Joomla\Utilities\ArrayHelper;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Table\Table;
use \Joomla\CMS\MVC\Model\ItemModel;
use \Joomla\CMS\Helper\TagsHelper;
use \Joomla\CMS\Object\CMSObject;
use \Joomla\CMS\User\UserFactoryInterface;
use \Atelman\Component\Atelman\Site\Helper\AtelmanHelper;

/**
 * Atelman model.
 *
 * @since  1.0.0
 */
class AtelmailsModel extends ItemModel
{
	public $_item;

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 *
	 * @throws Exception
	 */
	protected function populateState()
	{
		$app  = Factory::getApplication('com_atelman');
		$user = $app->getIdentity();

		// Check published state
		if ((!$user->authorise('core.edit.state', 'com_atelman')) && (!$user->authorise('core.edit', 'com_atelman'))) {
			$this->setState('filter.published', 1);
			$this->setState('filter.archived', 2);
		}

		// Load state from the request userState on edit or from the passed variable on default
		if (Factory::getApplication()->input->get('layout') == 'edit') {
			$id = Factory::getApplication()->getUserState('com_atelman.edit.rmaitem.id');
		} else {
			$id = Factory::getApplication()->input->get('id');
			Factory::getApplication()->setUserState('com_atelman.edit.rmaitem.id', $id);
		}

		$this->setState('rmaitem.id', $id);

		// Load the parameters.
		$params       = $app->getParams();
		$params_array = $params->toArray();

		if (isset($params_array['item_id'])) {
			$this->setState('rmaitem.id', $params_array['item_id']);
		}

		$this->setState('params', $params);
	}

	public function getItem($id = null)
	{
		if ($this->_item === null) {
			$this->_item = false;

			if (empty($id)) {
				$id = $this->getState('rmaitem.id');
			}

			// Get a level row instance.
			$table = $this->getTable();

			// Attempt to load the row.
			if ($table && $table->load($id)) {


				// Check published state.
				if ($published = $this->getState('filter.published')) {
					if (isset($table->state) && $table->state != $published) {
						throw new \Exception(Text::_('COM_ATELMAN_ITEM_NOT_LOADED'), 403);
					}
				}

				// Convert the Table to a clean CMSObject.
				$properties  = $table->getProperties(1);
				$this->_item = ArrayHelper::toObject($properties, CMSObject::class);
			}

			if (empty($this->_item)) {
				throw new \Exception(Text::_('COM_ATELMAN_ITEM_NOT_LOADED'), 404);
			}
		}



		$container = \Joomla\CMS\Factory::getContainer();

		$userFactory = $container->get(UserFactoryInterface::class);

		if (isset($this->_item->created_by)) {
			$user = $userFactory->loadUserById($this->_item->created_by);
			$this->_item->created_by_name = $user->name;
		}

		return $this->_item;
	}



	/***** send mail*************/

	function sendmail($section,  $id, $whois = 'admin')
	{

		$app = \Joomla\CMS\Factory::getApplication();
		$post = $app->input->getArray();
		$mail = Factory::getMailer();
		$db = Factory::getDBO();

		$recipients = array();
		$bcc_recipients = array();

		switch ($whois) {
			case 'admin':
				//get all super administrator
				$query = 'SELECT name, email, sendEmail' .
					' FROM #__users' .
					' WHERE LOWER( usertype ) = "super administrator"';
				$db->setQuery($query);
				$super_administrator = $db->loadObjectList();

				// get superadministrators id
				foreach ($super_administrator as $r) {
					if ($r->sendEmail) {
						array_push($recipients, $r->email);
					}
				}

				if ($section == 'rma_request') {
					array_push($bcc_recipients, 'RMA-AsiaPacific@alliedtelesis.com.sg');
				}

				break;
			case 'submitter':
				// email to submitted user
				array_push($recipients, $post['email']);
				break;
			case 'distributor': // must by customer_id , see below on rma request

				if ($section == 'rma_request') {

					$query = " SELECT u.email FROM #__at_rma_items AS r "
						.	" LEFT JOIN #__at_warranty_items AS w ON w.id = r.warranty_item_id "
						.	" LEFT JOIN #__users AS u ON w.customer_id = u.customer_id "
						.	" WHERE r.rma_request_id = '$id' ";

					$db->setQuery($query);
					$distemails = $db->loadObjectList();

					foreach ($distemails as $em) :
						array_push($recipients, $em->email);
					endforeach;
				}
				break;
			default:
				return;
				break;
		}

		// helper
		$helper	= new AtelmanHelper();

		$body			=	'<img src="cid:logo_id" alt="logo" /><br />';

		switch ($section) {
			case 'warranty_reg':

				$query = " SELECT country_name FROM #__at_world_countries WHERE country_code = '" . $post['country'] . "' ";
				$db->setQuery($query);
				$country_nm = $db->loadResult();

				$subject 		=	$app->get('sitename') . ' - Your Submission Warranty has been submitted';

				$body	.= '<div style="margin:10px 0;">Your Warranty Register as :</div>';
				$body	.= '<table cellpadding=1 cellspacing=1 border=1 style="padding:10px 0;">';
				$body	.= '<tr><td>First Name</td><td>' . $post['first_name'] . '</td></tr>';
				$body	.= '<tr><td>Last Name</td><td>' . $post['last_name'] . '</td></tr>';
				$body	.= '<tr><td>Address</td><td>' . $post['address'] . '</td></tr>';
				$body	.= '<tr><td>City</td><td>' . $post['city'] . '</td></tr>';
				$body	.= '<tr><td>Postal Code</td><td>' . $post['postal_code'] . '</td></tr>';
				$body	.= '<tr><td>Country</td><td>' . $country_nm . '</td></tr>';
				$body	.= '<tr><td>Telephone</td><td>' . $post['telephone'] . '</td></tr>';
				$body	.= '<tr><td>Fax</td><td>' . $post['fax'] . '</td></tr>';
				$body	.= '<tr><td>Email</td><td>' . $post['email'] . '</td></tr>';
				$body	.= '<tr><td>Company Name</td><td>' . $post['company_name'] . '</td></tr>';
				$body	.= '<tr><td>Job Title</td><td>' . $post['job_title'] . '</td></tr>';
				$body	.= '</table>';
				$body	.= '<div style="margin:10px 0;">Your Warranty Items are</div>';

				$products_total = count($post['product_id']);

				$query = " SELECT COUNT(id) FROM #__at_warranty_items WHERE warranty_id = '$id' ";
				$db->setQuery($query);
				$products_total = $db->loadResult();

				for ($i = 0; $i < $products_total; $i++) {

					$body	.= '<table cellpadding=1 cellspacing=1 border=1 style="padding:10px 0;">';
					$body 	.= '<tr><td>Part Number</td><td>' . $helper->getItemById('products', $post['product_id'][$i])->product_no . '</td></tr>';
					$body 	.= '<tr><td>Serial Number</td><td>' . $post['serial_no'][$i] . '</td></tr>';
					$body 	.= '<tr><td>Purchase Date</td><td>' . $post['purchase_date'][$i] . '</td></tr>';
					$body 	.= '<tr><td>Purchase From</td><td>' . $helper->getItemById('companies', $post['purchase_from'][$i])->company_name . '</td></tr>';
					$body 	.= '<tr><td>Purchase Country</td><td>' . $helper->getItemById('countries', $post['purchase_country'][$i])->country . '</td></tr>';
					$body 	.= '<tr><td>Comments</td><td>' . $post['comments'][$i] . '</td></tr>';
					$body	.= '</table>';
				}

				break;
			case 'rma_request':

				if ($whois == 'submitter') {
					$subject =	$app->get('sitename') . ' - Your RMA Request has been submitted';
					$body .= '<div style="margin:10px 0;">Thank you for your submission. Our Partner / Distributor will get you back as soon as possible.</div>';
				} else if ($whois == 'admin') {
					$subject =	$app->get('sitename') . ' Administrator - ' . $post['fullname'] . '(' . $post['email'] . ') sends RMA requests';
					$body .= '<div style="margin:10px 0">Dear Admin,</div>';
					$body .= '<div style="margin-bottom:10px">' . $post['fullname'] . '(' . $post['email'] . ') sends RMA Request.</div>';
				} else { // distributor
					$subject =	$app->get('sitename') . ' Partners - ' . $post['fullname'] . '(' . $post['email'] . ') sends RMA requests';
					$body .= '<div style="margin:10px 0">Dear Partner / Distributor,</div>';
					$body .= '<div style="margin-bottom:10px">' . $post['fullname'] . '(' . $post['email'] . ') sends RMA Request.</div>';
				}

				$body	.= '<div style="margin-bottom:10px;">Requestor\'s Details:</div>';
				$body	.= '<table cellpadding=1 cellspacing=1 border=1 style="padding:10px 0;">';
				$body	.= '<tr><td>Full Name</td><td>' . $post['fullname'] . '&nbsp;</td></tr>';
				$body	.= '<tr><td>Address</td><td>' . $post['address'] . '&nbsp;</td></tr>';
				$body	.= '<tr><td>Telephone</td><td>' . $post['telephone'] . '&nbsp;</td></tr>';
				$body	.= '<tr><td>Fax</td><td>' . $post['fax'] . '&nbsp;</td></tr>';
				$body	.= '<tr><td>Email</td><td>' . $post['email'] . '&nbsp;</td></tr>';
				$body	.= '</table>';
				$body	.= '<div style="margin:10px 0;">Requested Items:</div>';

				$query = " SELECT r.*, w.product_id, w.serial_no FROM #__at_rma_items AS r "
					.	" LEFT JOIN #__at_warranty_items AS w ON w.id = r.warranty_item_id "
					.	" LEFT JOIN #__users AS u ON u.customer_id = w.customer_id "
					.	" WHERE r.rma_request_id = '$id' ";

				$db->setQuery($query);
				$rows = $db->loadObjectList();

				foreach ($rows as $r) :

					$body	.= '<table cellpadding=1 cellspacing=1 border=1 style="padding:10px 0;">';
					$body 	.= '<tr><td>RMA Number</td><td>' . $r->rmacode . '&nbsp;</td></tr>';
					$body 	.= '<tr><td>Part Number</td><td>' . $helper->getItemById('products', $r->product_id)->product_no . '&nbsp;</td></tr>';
					$body 	.= '<tr><td>Model Number</td><td>' . $helper->getItemById('products', $r->product_id)->model_no . '&nbsp;</td></tr>';
					$body 	.= '<tr><td>Serial Number</td><td>' . $r->serial_no . '&nbsp;</td></tr>';
					$body 	.= '<tr><td>Fault Description</td><td>' . $r->description . '&nbsp;</td></tr>';
					$body 	.= '<tr><td>Status</td><td>' . $r->status . '&nbsp;</td></tr>';
					$body 	.= '<tr><td>Invoice Number</td><td>' . $r->invoice_no . '&nbsp;</td></tr>';
					$body 	.= '<tr><td>Remarks</td><td>' . $r->remarks . '&nbsp;</td></tr>';
					$body	.= '</table>';

				endforeach;

				break;
		}

		$body	.= '<div style="margin:10px 0;">Regards,</div>';
		$body	.= '<div style="margin:10px 0;">Allied Telesis Asia Pacific</div>';

		$mail->IsHTML(true);
		$mail->setSender(array($app->get('mailfrom'), $app->get('fromname')));

		if ($section == 'rma_request' && $whois == 'admin') {
			$mail->setSender(array('RMA-AsiaPacific@alliedtelesis.com.sg', 'RMA Admin'));
		}

		$mail->setSubject($subject);
		$mail->setBody($body);
		$mail->AddEmbeddedImage(JPATH_SITE . DS . 'templates' . DS . 'rhuk_milkyway' . DS . 'images' . DS . 'ATelesis_2color_web.png', 'logo_id', 'ATelesis_2color_web.png', 'base64', 'image/png');
		//$mail->addCC($cc);
		$mail->addRecipient($recipients);
		if (!empty($bcc_recipients)) {
			$mail->addBCC($bcc_recipients);
		}
		return $mail->Send();
	}
}
