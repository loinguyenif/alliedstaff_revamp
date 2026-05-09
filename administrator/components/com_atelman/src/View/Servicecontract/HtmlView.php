<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Atelman
 * @author     iFoundries <wpsub@ifoundries.com>
 * @copyright  2025 iFoundries
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Atelman\Component\Atelman\Administrator\View\Servicecontract;
// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use \Joomla\CMS\Toolbar\ToolbarHelper;
use \Joomla\CMS\Factory;
use \Atelman\Component\Atelman\Administrator\Helper\AtelmanHelper;
use \Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper as JHtml;
use \Joomla\CMS\Toolbar\Toolbar;

/**
 * View class for a single Servicecontract.
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
		$app = Factory::getApplication();
		$this->state = $this->get('State');
		$this->item  = $this->get('Item');
		$this->form  = $this->get('Form');
		$this->user = Factory::getUser();
		if ($this->user->gid == 24) { // distributor, must see from groups management first
			$group = AtelmanHelper::getGroupXref($this->user->id);
			$this->group = $group;
		}

		//if ($this->item->replacement_date != '0000-00-00 00:00:00' && $this->item->replacement_date != NULL) $replacement_date = date("d/m/Y", strtotime($this->item->replacement_date));
		//if($row->rma_assigned_date != '0000-00-00') $rma_assigned_date = date("d/m/Y",strtotime($row->rma_assigned_date));

		if ($this->user->gid == 25 || $this->user->gid == 8 || $this->user->gid == 32 || $this->user->gid == 34) {
			$lists['start_date'] 		=  JHTML::calendar($this->item->start_date, 'start_date', 'start_date', '%d/%m/%Y', ' class="validate-dates" onblur="javascript:void(0)" ') . "<br />Date Format : DD/MM/YYYY ";
			$lists['expiry_date'] 		=  JHTML::calendar($this->item->expiry_date, 'expiry_date', 'expiry_date', '%d/%m/%Y', ' class="validate-dates" onblur="javascript:void(0)" ') . "<br />Date Format : DD/MM/YYYY ";
			$this->lists = $lists;
		}


		$this->companiesHTML = $this->getCompanies();


		//ifoundries check permission
		$allowGroup = array('8', '23', '24', '34', '25');
		if (!in_array($this->user->gid, $allowGroup)) {
			$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
			$app->redirect('index.php');
			return false;
		}


		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			//throw new \Exception(implode("\n", $errors));
		}
		$this->addToolbar();

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
		$layout = $this->getLayout();
		$user  = Factory::getApplication()->getIdentity();
		$isNew = ($this->item->id == 0);

		if (isset($this->item->checked_out)) {
			$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
		} else {
			$checkedOut = false;
		}

		$canDo = AtelmanHelper::getActions();

		ToolbarHelper::title(Text::_('Service Contract : ' . $this->item->service_contract_no), "generic");

		// If not checked out, can save the item.
		if (!$checkedOut && ($canDo->get('core.edit') || ($canDo->get('core.create')))) {
			if (
				$user->gid == 25 || $user->gid == 8 ||
				$user->gid == 32 ||
				$user->gid == 34 ||
				$user->gid == 31
			) {
				ToolbarHelper::save('Servicecontract.save', 'Save');
				if ($layout == 'edit') {
					ToolbarHelper::apply('Servicecontract.apply', 'Apply');
				}
			}
		}

		$toolbar = Toolbar::getInstance('toolbar');
		$toolbar->appendButton('Link', 'cancel', Text::_('Cancel'), 'index.php?option=com_atelman&view=servicecontracts');
	}


	public function getCompanies()
	{

		$db = Factory::getDBO();
		$user = Factory::getUser();

		$where = array();
		if ($user->gid == 23) { // Manager
			$where[] = ' u.country_id = ' . $db->Quote($user->country_id, false);
			$where[] = ' u.country_id != \'\' ';
			$where[] = ' u.customer_id != \'\' ';
			$where[] = ' u.gid != 25 '; // not Super Administrator
		}

		$where = (count($where) ? ' WHERE ' . implode(' AND ', $where) : '');

		$query = " SELECT c.* FROM #__users AS u "
			.	" RIGHT JOIN #__at_companies AS c ON c.customer_id = u.customer_id "
			.	$where
			. " GROUP BY u.customer_id "
			. " ORDER BY c.company_name ASC ";

		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$company = array();

		$company[] = JHTML::_('select.option',  '',  '-- Select Company --');
		foreach ($rows as $r):
			$company[] = JHTML::_('select.option',  $r->customer_id,  Text::_($r->company_name . ' [' . $r->customer_id . ']'));
		endforeach;

		return JHTML::_('select.genericlist',   $company, 'fullname_tmp', 'class="inputbox required" size="1" style="width:100%;" onchange="javascript:loadCustomer(this.value)" ', 'value', 'text', $user->customer_id);
	}
}
