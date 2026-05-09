<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Atelman
 * @author     iFoundries <wpsub@ifoundries.com>
 * @copyright  2025 iFoundries
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Atelman\Component\Atelman\Administrator\View\Warrantyitem;
// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use \Joomla\CMS\Toolbar\ToolbarHelper;
use \Joomla\CMS\Factory;
use \Atelman\Component\Atelman\Administrator\Helper\AtelmanHelper;
use \Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper as JHtml;

/**
 * View class for a single Warrantyitem.
 *
 * @since  1.0.0
 */
class HtmlView extends BaseHtmlView
{
	protected $state;

	protected $item;

	protected $form;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  Template name
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function display($tpl = null)
	{
		$this->state = $this->get('State');
		$this->item  = $this->get('Item');
		$this->form  = $this->get('Form');
		$currentUser	= Factory::getUser();
		$row = $this->item;

		if ($currentUser->gid == 24) { // distributor, must see from groups management first

			/*$query = " SELECT gx.group_id, ga.`access` "
						.	" FROM #__at_group_xref AS gx "
						.	" LEFT JOIN #__at_group_access AS ga ON ga.group_id = gx.group_id "
						.	" WHERE gx.user_id = ".$currentUser->id
						.	" ";
						
					$db->setQuery( $query );
					$group = $db->loadObject();
				//echo print_r($group);
				$this->assignRef('group', $group);
				*/
		}

		if ($currentUser->gid == 23) {
		}

		$helper = new AtelmanHelper();
		$lists = array();
		// this portion to set Expiry Date (Automatically) based on Purchase Date July 1st 2010, and Is_Internal Customer.
		// Nothing to do, just to display it.
		$expired_date_based_on_product = date("d/m/Y", @strtotime($row->expired_date));
		$lists['warranty_based_on_product'] = $expired_date_based_on_product . '<input type="hidden" name="expired_date" value="' . $expired_date_based_on_product . '" ';

		if ($currentUser->gid == 25 || $currentUser->gid == 8 || $currentUser->gid == 34) { // if Admin or Supervisor, can edit

			$lists['customer_id'] = $this->getCustomer(@$row->customer_id);
			$lists['so_no'] = '<input type="text" name="so_no" value="' . @$row->so_no . '">';
			$lists['po_no'] = '<input type="text" name="po_no" value="' . @$row->po_no . '">';
			$lists['invoice_no'] = '<input type="text" name="invoice_no" value="' . @$row->invoice_no . '">';
			$lists['serial_no'] = '<input type="text" name="serial_no" value="' . @$row->serial_no . '">';
			$lists['serial_no_2'] = '<input type="text" name="serial_no_2" value="' . @$row->serial_no_2 . '">';
			$lists['replacement_pn'] = '<input type="text" name="replacement_pn" value="' . @$row->replacement_pn . '">';
			$lists['product_no'] = '<input type="text" name="product_no" value="' . @$row->product_no . '">';
			$lists['model_no'] = (@$row->model_no) ? @$row->model_no : 'N/A';

			if (@$row->purchase_date != NULL && @$row->purchase_date != '0000-00-00 00:00:00') $purchase_date = $row->purchase_date; //date("d/m/Y", strtotime($row->purchase_date));
			if (@$row->expired_date_manual != NULL && @$row->expired_date_manual != '0000-00-00 00:00:00') $expired_date = $row->expired_date_manual; //date("d/m/Y", strtotime($row->expired_date_manual));
			// purchase date

			$lists['purchase_date'] =  JHTML::calendar(@$purchase_date, 'purchase_date', 'purchase_date', '%d/%m/%Y', ' class="validate-dates" onblur="javascript:void(0)" ') . " Date Format : DD/MM/YYYY ";

			// expired_date
			$lists['expired_date'] =  JHTML::calendar(@$expired_date, 'expired_date_manual', 'expired_date_manual', '%d/%m/%Y', ' class="validate-dates" ') . " Date Format : DD/MM/YYYY ";

			// extended_warranty
			$lists['extended_warranty'] = '<input type="text" name="extended_warranty" value="' . @$row->extended_warranty . '">&nbsp;' . (@$row->extended_warranty ? date("d/m/Y", strtotime($row->extended_expired_date)) : '');
		} else { // else, cannot edit certain field.

			$lists['customer_id'] = @$row->customer_id ? @$row->customer_id : 'N/A';
			$lists['so_no'] = @$row->so_no ? @$row->so_no : 'N/A';
			$lists['po_no'] = @$row->po_no ? @$row->po_no : 'N/A';
			$lists['invoice_no'] = (@$row->invoice_no) ? $row->invoice_no : 'N/A';
			$lists['serial_no'] = (@$row->serial_no) ? $row->serial_no : 'N/A';
			$lists['serial_no_2'] = (@$row->serial_no_2) ? $row->serial_no_2 : 'N/A';
			$lists['replacement_pn'] = (@$row->replacement_pn) ? $row->replacement_pn : 'N/A';
			$lists['product_no'] = (@$row->product_no) ? $row->product_no : 'N/A';
			$lists['model_no'] = (@$row->model_no) ? $row->model_no : 'N/A';

			// expired_date
			$lists['purchase_date'] = (@$row->purchase_date != '0000-00-00 00:00:00' && $row->purchase_date != NULL) ? date("d/m/Y", strtotime($row->purchase_date)) : 'N/A';

			// expired_date
			$lists['expired_date'] = (@$row->expired_date_manual != '0000-00-00 00:00:00' && $row->expired_date_manual != NULL) ? date("d/m/Y", strtotime($row->expired_date_manual)) : 'N/A';

			// extended_warranty
			$lists['extended_warranty'] = @$row->extended_warranty;
			$lists['extended_warranty_date'] = (@$row->extended_expired_date != '0000-00-00 00:00:00' && $row->extended_expired_date != NULL) ? date("d/m/Y", strtotime($row->extended_expired_date)) : 'N/A';
		}

		$this->lists = $lists;
		$this->user = $currentUser;

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			throw new \Exception(implode("\n", $errors));
		}
		$this->addToolbar();

		//ifoundries check permission
		$user = \Joomla\CMS\Factory::getUser();
		$app = \Joomla\CMS\Factory::getApplication();
		$allowGroup = array('8', '23', '24', '34', '25');
		if (!in_array($user->gid, $allowGroup)) {
			$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
			$app->redirect('index.php');
			return false;
		}

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	protected function addToolbar()
	{
		Factory::getApplication()->input->set('hidemainmenu', true);

		$user  = Factory::getApplication()->getIdentity();
		$isNew = (@$this->item->id == 0);

		if (isset($this->item->checked_out)) {
			$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
		} else {
			$checkedOut = false;
		}

		$canDo = AtelmanHelper::getActions();

		//ToolbarHelper::title(Text::_('COM_ATELMAN_TITLE_WARRANTYITEM'), "generic");

		// If not checked out, can save the item.
		// if ($this->user->gid == 25 || $this->user->gid == 34) : // admin and supervisor can write the file
		// 	JToolBarHelper::save();
		// 	JToolBarHelper::apply();
		// endif;
		if ($user->gid == 25 || $user->gid == 8 || $user->gid == 34) {
			if (!$checkedOut && ($canDo->get('core.edit') || ($canDo->get('core.create')))) {
				ToolbarHelper::save('warrantyitem.save', 'Save');
				ToolbarHelper::apply('warrantyitem.apply', 'Apply');
			}
		}




		if (empty($this->item->id)) {
			ToolbarHelper::cancel('warrantyitem.cancel', 'JTOOLBAR_CANCEL');
		} else {
			ToolbarHelper::cancel('warrantyitem.cancel', 'JTOOLBAR_CLOSE');
		}
	}



	public function getCustomer($customer_id)
	{
		$db = Factory::getDBO();
		$arry = array();
		$arry[] = JHTML::_('select.option',  0,  '-- Select Customer / Distributor --');
		$query = " SELECT u.customer_id, u.name AS name "
			.	" FROM #__users AS u "
			//.	" RIGHT JOIN #__at_companies AS c ON c.customer_id = u.customer_id "
			.	" WHERE u.gid = 24 OR u.gid = 23 "
			.	" GROUP BY u.customer_id ORDER BY u.name, u.customer_id ASC ";

		$db->setQuery($query);
		$rows = $db->loadObjectList();

		if (!empty($rows)) :
			foreach ($rows as $r):
				$arry[] = JHTML::_('select.option',  $r->customer_id,  Text::_($r->name . ' [' . $r->customer_id . ']'));
			endforeach;
		endif;

		return JHTML::_('select.genericlist',   $arry, 'customer_id', 'class="inputbox" size="1" ', 'value', 'text', $customer_id);
		return '';
	}
}
