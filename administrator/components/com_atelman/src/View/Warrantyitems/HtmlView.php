<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Atelman
 * @author     iFoundries <wpsub@ifoundries.com>
 * @copyright  2025 iFoundries
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Atelman\Component\Atelman\Administrator\View\Warrantyitems;
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

/**
 * View class for a list of Warrantyitems.
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
			case 'updateregwarranty':
				ToolbarHelper::title(Text::_('Update Warranty Registration'), "generic");
				$toolbar->appendButton('Link', 'cancel', Text::_('Close'), 'index.php?option=com_atelman&view=warrantyitems');
				break;

			case 'importisbdata':

				$toolbar->appendButton('Link', 'cancel', Text::_('Close'), 'index.php?option=com_atelman&view=warrantyitems');
				break;
			case 'importisb':

				$toolbar->appendButton('Link', 'cancel', Text::_('Close'), 'index.php?option=com_atelman&view=warrantyitems');
				break;


			default:
				ToolbarHelper::title(Text::_('Warranty Registration Manager'), "generic");
				$this->items = $this->get('Items');
				$this->pagination = $this->get('Pagination');
				$this->filterForm = $this->get('FilterForm');
				$this->activeFilters = $this->get('ActiveFilters');
				$expiry_month = $this->state->get('filter.expiry_month');
				$country = $this->state->get('filter.country');
				$distributor = $this->state->get('filter.distributor');

				$helper = new AtelmanHelper();
				$lists['companies'] = $helper->getDistributorFilter($distributor, $country);
				$lists['countries'] = $helper->getCountryFilter($country);
				$lists['expiry_month'] =  $helper->getExpiryMonthFilter($expiry_month);


				$this->user = Factory::getUser();
				$this->lists = $lists;
				break;
		}



		//ifoundries check permission
		$user = \Joomla\CMS\Factory::getUser();
		$app = \Joomla\CMS\Factory::getApplication();
		$allowGroup = array('8', '23', '24', '25', '34');
		if (!in_array($user->gid, $allowGroup)) {
			$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
			$app->redirect('index.php');
			return false;
		}
		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			throw new \Exception(implode("\n", $errors));
		}

		$this->addToolbar();

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
		$layout = $this->getLayout();

		ToolbarHelper::title(Text::_('Warranty Registration Manager'), "generic");

		$toolbar = Toolbar::getInstance('toolbar');

		// Check if the form exists before showing the add/edit buttons
		$formPath = JPATH_COMPONENT_ADMINISTRATOR . '/src/View/Warrantyitems';
		if ($layout == "default") {
			if (file_exists($formPath)) {
				if ($user->gid == 25 || in_array(8, $user->groups)) {
					if ($canDo->get('core.create')) {
						$toolbar->addNew('warrantyitem.add', 'Add');
					}
				}
			}
		}

		if ($canDo->get('core.edit.state')) {
			$dropdown = $toolbar->dropdownButton('status-group')
				->text('JTOOLBAR_CHANGE_STATUS')
				->toggleSplit(false)
				->icon('fas fa-ellipsis-h')
				->buttonClass('btn btn-action')
				->listCheck(true);

			$childBar = $dropdown->getChildToolbar();

			if (isset($this->items[0]->state)) {
				$childBar->publish('warrantyitems.publish')->listCheck(true);
				$childBar->unpublish('warrantyitems.unpublish')->listCheck(true);
				$childBar->archive('warrantyitems.archive')->listCheck(true);
			} elseif (isset($this->items[0])) {
				// If this component does not use state then show a direct delete button as we can not trash
				if ($user->gid == 25 || in_array(8, $user->groups)) {
					$toolbar->delete('warrantyitems.delete')
						->text('Delete')
						->message('JGLOBAL_CONFIRM_DELETE')
						->listCheck(true);
				}
			}
		}


		// Set sidebar action
		Sidebar::setAction('index.php?option=com_atelman&view=warrantyitems');
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
			'a.`warranty_id`' => Text::_('COM_ATELMAN_WARRANTYITEMS_WARRANTY_ID'),
			'a.`customer_id`' => Text::_('COM_ATELMAN_WARRANTYITEMS_CUSTOMER_ID'),
			'a.`product_id`' => Text::_('COM_ATELMAN_WARRANTYITEMS_PRODUCT_ID'),
			'a.`serial_no`' => Text::_('COM_ATELMAN_WARRANTYITEMS_SERIAL_NO'),
			'a.`serial_no_2`' => Text::_('COM_ATELMAN_WARRANTYITEMS_SERIAL_NO_2'),
			'a.`replacement_pn`' => Text::_('COM_ATELMAN_WARRANTYITEMS_REPLACEMENT_PN'),
			'a.`po_no`' => Text::_('COM_ATELMAN_WARRANTYITEMS_PO_NO'),
			'a.`so_no`' => Text::_('COM_ATELMAN_WARRANTYITEMS_SO_NO'),
			'a.`invoice_no`' => Text::_('COM_ATELMAN_WARRANTYITEMS_INVOICE_NO'),
			'a.`purchase_date`' => Text::_('COM_ATELMAN_WARRANTYITEMS_PURCHASE_DATE'),
			'a.`comments`' => Text::_('COM_ATELMAN_WARRANTYITEMS_COMMENTS'),
			'a.`expired_date`' => Text::_('COM_ATELMAN_WARRANTYITEMS_EXPIRED_DATE'),
			'a.`expired_date_manual`' => Text::_('COM_ATELMAN_WARRANTYITEMS_EXPIRED_DATE_MANUAL'),
			'a.`extended_warranty`' => Text::_('COM_ATELMAN_WARRANTYITEMS_EXTENDED_WARRANTY'),
			'a.`extended_expired_date`' => Text::_('COM_ATELMAN_WARRANTYITEMS_EXTENDED_EXPIRED_DATE'),
			'a.`created_date`' => Text::_('COM_ATELMAN_WARRANTYITEMS_CREATED_DATE'),
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
