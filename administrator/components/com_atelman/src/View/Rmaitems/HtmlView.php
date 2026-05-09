<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Atelman
 * @author     iFoundries <wpsub@ifoundries.com>
 * @copyright  2025 iFoundries
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Atelman\Component\Atelman\Administrator\View\Rmaitems;
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
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper as JHtml;

/**
 * View class for a list of Rmaitems.
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


		$this->state = $this->get('State');
		$toolbar = Toolbar::getInstance('toolbar');
		$layout = $this->getLayout();

		switch ($layout) {
			case 'checkrma':
				ToolbarHelper::title(Text::_('Check RMA Status'), "generic");
				//ifoundires 
				$toolbar->appendButton('Link', 'cancel', Text::_('Cancel'), 'index.php?option=com_atelman&view=rmaitems');
				break;
			case 'submitrma':
				ToolbarHelper::title(Text::_('RMA Request Form'), "generic");
				//ifoundires 
				$toolbar->appendButton('Link', 'cancel', Text::_('Cancel'), 'index.php?option=com_atelman&view=rmaitems');
				$this->listCompanyHTML = AtelmanHelper::getCompanies();
				break;

			case 'importrma':
				ToolbarHelper::title(Text::_('RMA Import'), "generic");
				//ifoundires 
				$toolbar->appendButton('Link', 'cancel', Text::_('Cancel'), 'index.php?option=com_atelman&view=rmaitems');
				break;
			case 'checkwarranty':
				ToolbarHelper::title(Text::_('Check Warranty Status'), "generic");
				//ifoundires 
				$toolbar->appendButton('Link', 'cancel', Text::_('Cancel'), 'index.php?option=com_atelman&view=rmaitems');
				break;


			case 'report':
				ToolbarHelper::title(Text::_('RMA Report'), "generic");
				$from_date = "";
				$to_date = "";
				$lists['rma_report_type'] = AtelmanHelper::getSelectHTML('rma_report_type', 'rma_report_type');
				$lists['from_date'] =  JHTML::calendar($from_date, 'from_date', 'from_date', '%d/%m/%Y', ' class="validate-dates" onblur="javascript:void(0)" ');
				$lists['to_date'] =  JHTML::calendar($to_date, 'to_date', 'to_date', '%d/%m/%Y', ' class="validate-dates" onblur="javascript:void(0)" ');
				$lists['country'] = AtelmanHelper::countryCheckbox('country_id', 5);
				$this->lists = $lists;
				break;
			default:

				$this->items = $this->get('Items');
				$this->pagination = $this->get('Pagination');
				$this->filterForm = $this->get('FilterForm');
				$this->activeFilters = $this->get('ActiveFilters');
				$this->addToolbar();

				//ifoundries check permission
				$user = \Joomla\CMS\Factory::getUser();
				$app = \Joomla\CMS\Factory::getApplication();
				$allowGroup = array('33');
				if (in_array($user->gid, $allowGroup)) {
					$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
					$app->redirect('index.php');
					return false;
				}
				break;
		}



		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			throw new \Exception(implode("\n", $errors));
		}

		$this->sidebar = Sidebar::render();

		$this->status = AtelmanHelper::getRMAStatusTextArray();

		$this->user = Factory::getApplication()->getIdentity();
		$status = $this->state->get('filter.status');
		$country = $this->state->get('filter.country');
		$distributor = $this->state->get('filter.distributor');

		$this->listStatus = AtelmanHelper::statusType($status);
		$this->listCountry = AtelmanHelper::getCountryFilter($country);
		$this->listCompany = AtelmanHelper::getDistributorFilter($distributor);

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
		$this->user = Factory::getApplication()->getIdentity();
		$state = $this->get('State');
		$canDo = AtelmanHelper::getActions();

		ToolbarHelper::title(Text::_('RMA Management'), "generic");

		$toolbar = Toolbar::getInstance('toolbar');

		// Check if the form exists before showing the add/edit buttons
		$formPath = JPATH_COMPONENT_ADMINISTRATOR . '/src/View/Rmaitems';

		if ($canDo->get('core.edit', 'com_atelman')) {
			//ToolbarHelper::editList('rmaitem.edit', 'Update');
		}

		if ($this->user->gid == 25 || $this->user->gid == 8 || $this->user->gid == 31 || $this->user->gid == 32 || in_array(8, $this->user->groups)) :
			ToolbarHelper::custom('rmaitems.updatermapage', 'refresh', 'refresh', 'Update', true);
		endif;



		if ($canDo->get('core.edit.state')) {
			$dropdown = $toolbar->dropdownButton('status-group')
				->text('JTOOLBAR_CHANGE_STATUS')
				->toggleSplit(false)
				->icon('fas fa-ellipsis-h')
				->buttonClass('btn btn-action')
				->listCheck(true);

			$childBar = $dropdown->getChildToolbar();

			if (isset($this->items[0]->state)) {
				$childBar->publish('rmaitems.publish')->listCheck(true);
				$childBar->unpublish('rmaitems.unpublish')->listCheck(true);
				$childBar->archive('rmaitems.archive')->listCheck(true);
			} elseif (isset($this->items[0])) {
				// If this component does not use state then show a direct delete button as we can not trash
				if ($this->user->gid == 25 || $this->user->gid == 8 || in_array(8, $this->user->groups)) {
					$toolbar->delete('rmaitems.delete')
						->text('Delete')
						->message('JGLOBAL_CONFIRM_DELETE')
						->listCheck(true);
				}
			}

			if (isset($this->items[0]->checked_out)) {
				$childBar->checkin('rmaitems.checkin')->listCheck(true);
			}

			if (isset($this->items[0]->state)) {
				$childBar->trash('rmaitems.trash')->listCheck(true);
			}
		}



		// Show trash and delete for components that uses the state field
		if (isset($this->items[0]->state)) {

			if ($this->state->get('filter.state') == ContentComponent::CONDITION_TRASHED && $canDo->get('core.delete')) {
				$toolbar->delete('rmaitems.delete')
					->text('JTOOLBAR_EMPTY_TRASH')
					->message('JGLOBAL_CONFIRM_DELETE')
					->listCheck(true);
			}
		}

		// Set sidebar action
		Sidebar::setAction('index.php?option=com_atelman&view=rmaitems');
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
			'a.`rma_request_id`' => Text::_('COM_ATELMAN_RMAITEMS_RMA_REQUEST_ID'),
			'a.`rmacode`' => Text::_('COM_ATELMAN_RMAITEMS_RMACODE'),
			'a.`warranty_item_id`' => Text::_('COM_ATELMAN_RMAITEMS_WARRANTY_ITEM_ID'),
			'a.`product_id`' => Text::_('COM_ATELMAN_RMAITEMS_PRODUCT_ID'),
			'a.`customer_id`' => Text::_('COM_ATELMAN_RMAITEMS_CUSTOMER_ID'),
			'a.`requested_sn`' => Text::_('COM_ATELMAN_RMAITEMS_REQUESTED_SN'),
			'a.`replacement_pn`' => Text::_('COM_ATELMAN_RMAITEMS_REPLACEMENT_PN'),
			'a.`replacement_sn`' => Text::_('COM_ATELMAN_RMAITEMS_REPLACEMENT_SN'),
			'a.`warranty_status`' => Text::_('COM_ATELMAN_RMAITEMS_WARRANTY_STATUS'),
			'a.`description`' => Text::_('COM_ATELMAN_RMAITEMS_DESCRIPTION'),
			'a.`status`' => Text::_('COM_ATELMAN_RMAITEMS_STATUS'),
			'a.`shipping_duration`' => Text::_('COM_ATELMAN_RMAITEMS_SHIPPING_DURATION'),
			'a.`so_no`' => Text::_('COM_ATELMAN_RMAITEMS_SO_NO'),
			'a.`invoice_no`' => Text::_('COM_ATELMAN_RMAITEMS_INVOICE_NO'),
			'a.`remarks`' => Text::_('COM_ATELMAN_RMAITEMS_REMARKS'),
			'a.`replacement_date`' => Text::_('COM_ATELMAN_RMAITEMS_REPLACEMENT_DATE'),
			'a.`rma_assigned_date`' => Text::_('COM_ATELMAN_RMAITEMS_RMA_ASSIGNED_DATE'),
			'a.`received_date`' => Text::_('COM_ATELMAN_RMAITEMS_RECEIVED_DATE'),
			'a.`shipped_date`' => Text::_('COM_ATELMAN_RMAITEMS_SHIPPED_DATE'),
			'a.`closed_date`' => Text::_('COM_ATELMAN_RMAITEMS_CLOSED_DATE'),
			'a.`created_date`' => Text::_('COM_ATELMAN_RMAITEMS_CREATED_DATE'),
			'a.`is_import_csv`' => Text::_('COM_ATELMAN_RMAITEMS_IS_IMPORT_CSV'),
			'a.`created_by`' => Text::_('COM_ATELMAN_RMAITEMS_CREATED_BY'),
			'a.`product_no`' => Text::_('Part Number'),
			'a.`model_no`' => Text::_('Model Number'),
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
}
