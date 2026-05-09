<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Atelman
 * @author     iFoundries <wpsub@ifoundries.com>
 * @copyright  2025 iFoundries
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Atelman\Component\Atelman\Administrator\View\Servicecontracts;
// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use \Atelman\Component\Atelman\Administrator\Helper\AtelmanHelper;
use \Joomla\CMS\Toolbar\Toolbar;
use \Joomla\CMS\Toolbar\ToolbarHelper;
use \Joomla\CMS\Language\Text;
use \Joomla\Component\Content\Administrator\Extension\ContentComponent;
use \Joomla\CMS\Form\Form;
use \Joomla\CMS\HTML\Helpers\Sidebar;
use \Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper as JHtml;

/**
 * View class for a list of Servicecontracts.
 *
 * @since  1.0.0
 */
class HtmlView extends BaseHtmlView
{
	protected $items;

	protected $pagination;

	protected $state;

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
		$layout = $this->getLayout();
		$toolbar = Toolbar::getInstance('toolbar');
		$currentUser	= Factory::getUser();

		if (!$currentUser->authorise('core.admin', 'com_atelman')) {
			throw new \Exception(\JText::_('JERROR_ALERTNOAUTHOR'), 403);
		}


		switch ($layout) {
			case 'importcontract':
				ToolbarHelper::title(Text::_('Service Contract Import'), "generic");
				//ifoundires 
				$toolbar->appendButton('Link', 'cancel', Text::_('Cancel'), 'index.php?option=com_atelman&view=servicecontracts');
				break;

			case 'submitcontract':
				ToolbarHelper::title(Text::_('Service Contact Submission Form'), "generic");
				//ifoundires 
				$toolbar->appendButton('Link', 'cancel', Text::_('Cancel'), 'index.php?option=com_atelman&view=servicecontracts');

				$currentUser->country_name = AtelmanHelper::getCountry($currentUser->country_id);
				$lists['expiry_date'] 	=  JHTML::calendar('', 'expiry_date', 'expiry_date', '%d/%m/%Y', ' class="validate-dates" onblur="javascript:void(0)" ') . "<br />Date Format : DD/MM/YYYY ";

				$this->user = $currentUser;
				$this->lists = $lists;
				$this->companiesHTML = $this->getCompanies();

				break;
			default:
				$this->items = $this->get('Items');
				$this->pagination = $this->get('Pagination');
				$this->filterForm = $this->get('FilterForm');
				$this->activeFilters = $this->get('ActiveFilters');
				$this->addToolbar();
				break;
		}

		$this->user = Factory::getApplication()->getIdentity();

		//ifoundries check permission
		$allowGroup = array('8', '23', '24', '34', '25');
		if (!in_array($this->user->gid, $allowGroup)) {
			$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
			$app->redirect('index.php');
			return false;
		}

		$helper = new AtelmanHelper();
		// expiry
		$filter_expiry = $this->state->get('filter.expiry');
		$country = $this->state->get('filter.country');
		$distributor = $this->state->get('filter.distributor');
		$lists['expiry'] = $this->getMostExpiry($filter_expiry);
		$lists['companies'] = $helper->getDistributorFilter($distributor, $country);
		$lists['countries'] = $helper->getCountryFilter($country);
		$this->lists = $lists;

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			//throw new \Exception(implode("\n", $errors));
		}


		$this->sidebar = Sidebar::render();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	protected function addToolbar()
	{
		$user = Factory::getUser();
		$state = $this->get('State');
		$canDo = AtelmanHelper::getActions();

		ToolbarHelper::title(Text::_('Net.cover Service Contract Management'), "generic");

		$toolbar = Toolbar::getInstance('toolbar');

		// Check if the form exists before showing the add/edit buttons
		$formPath = JPATH_COMPONENT_ADMINISTRATOR . '/src/View/Servicecontracts';


		if ($canDo->get('core.edit.state')) {
			$dropdown = $toolbar->dropdownButton('status-group')
				->text('JTOOLBAR_CHANGE_STATUS')
				->toggleSplit(false)
				->icon('fas fa-ellipsis-h')
				->buttonClass('btn btn-action')
				->listCheck(true);

			$childBar = $dropdown->getChildToolbar();

			if (isset($this->items[0]->state)) {
				$childBar->publish('servicecontracts.publish')->listCheck(true);
				$childBar->unpublish('servicecontracts.unpublish')->listCheck(true);
				$childBar->archive('servicecontracts.archive')->listCheck(true);
			} elseif (isset($this->items[0])) {
				// If this component does not use state then show a direct delete button as we can not trash
				if ($user->gid == 25 || in_array(8, $user->groups)) {
					$toolbar->delete('servicecontracts.delete')
						->text('Delete')
						->message('JGLOBAL_CONFIRM_DELETE')
						->listCheck(true);
				}
			}



			if (isset($this->items[0]->checked_out)) {
				$childBar->checkin('servicecontracts.checkin')->listCheck(true);
			}

			if (isset($this->items[0]->state)) {
				$childBar->trash('servicecontracts.trash')->listCheck(true);
			}
		}



		// Show trash and delete for components that uses the state field
		if (isset($this->items[0]->state)) {

			if ($this->state->get('filter.state') == ContentComponent::CONDITION_TRASHED && $canDo->get('core.delete')) {
				$toolbar->delete('servicecontracts.delete')
					->text('JTOOLBAR_EMPTY_TRASH')
					->message('JGLOBAL_CONFIRM_DELETE')
					->listCheck(true);
			}
		}



		// Set sidebar action
		Sidebar::setAction('index.php?option=com_atelman&view=servicecontracts');
	}

	/**
	 * Method to order fields 
	 *
	 * @return void 
	 */
	protected function getSortFields()
	{
		return array(
			'a.`id`' => Text::_('JGRID_HEADING_ID'),
			'a.`user_id`' => Text::_('COM_ATELMAN_SERVICECONTRACTS_USER_ID'),
			'a.`service_type`' => Text::_('COM_ATELMAN_SERVICECONTRACTS_SERVICE_TYPE'),
			'a.`expiry_date`' => Text::_('COM_ATELMAN_SERVICECONTRACTS_EXPIRY_DATE'),
			'a.`service_contract_no`' => Text::_('COM_ATELMAN_SERVICECONTRACTS_SERVICE_CONTRACT_NO'),
			'a.`cover_length`' => Text::_('COM_ATELMAN_SERVICECONTRACTS_COVER_LENGTH'),
			'a.`po_no`' => Text::_('COM_ATELMAN_SERVICECONTRACTS_PO_NO'),
			'a.`client_name`' => Text::_('COM_ATELMAN_SERVICECONTRACTS_CLIENT_NAME'),
			'a.`customer_id`' => Text::_('COM_ATELMAN_SERVICECONTRACTS_CUSTOMER_ID'),
			'a.`remarks`' => Text::_('COM_ATELMAN_SERVICECONTRACTS_REMARKS'),
			'a.`reminder1`' => Text::_('COM_ATELMAN_SERVICECONTRACTS_REMINDER1'),
			'a.`start_date`' => Text::_('COM_ATELMAN_SERVICECONTRACTS_START_DATE'),
			'a.`created_date`' => Text::_('COM_ATELMAN_SERVICECONTRACTS_CREATED_DATE'),
		);
	}

	/**
	 * Check if state is set
	 *
	 * @param   mixed  $state  State
	 *
	 * @return bool
	 */
	public function getState($state)
	{
		return isset($this->state->{$state}) ? $this->state->{$state} : false;
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

	public function getMostExpiry($filter_expiry)
	{
		$expiry = array();
		$expiry[] = JHTML::_('select.option',  '',  '-- due to 6 month expired --');
		$expiry[] = JHTML::_('select.option',  0,  'No');
		$expiry[] = JHTML::_('select.option',  1,  'Yes');

		return JHTML::_('select.genericlist',   $expiry, 'filter_expiry', 'class="inputbox form-select" size="1" onchange="document.adminForm.submit( );"', 'value', 'text', "$filter_expiry");
	}
}
