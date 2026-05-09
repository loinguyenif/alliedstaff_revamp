<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Atelman
 * @author     iFoundries <wpsub@ifoundries.com>
 * @copyright  2025 iFoundries
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Atelman\Component\Atelman\Administrator\View\Products;
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

/**
 * View class for a list of Products.
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


		$layout = $this->getLayout();

		switch ($layout) {
			case 'uploadcsv':
				break;

			default:
				$this->state = $this->get('State');
				$this->items = $this->get('Items');
				$this->pagination = $this->get('Pagination');
				$this->filterForm = $this->get('FilterForm');
				$this->activeFilters = $this->get('ActiveFilters');
				$this->addToolbar();
				break;
		}

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			throw new \Exception(implode("\n", $errors));
		}
		//ifoundries check permission
		$user = \Joomla\CMS\Factory::getUser();
		$app = \Joomla\CMS\Factory::getApplication();
		$allowGroup = array('8', '25');
		if (!in_array($user->gid, $allowGroup)) {
			$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
			$app->redirect('index.php');
			return false;
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
		$state = $this->get('State');
		$canDo = AtelmanHelper::getActions();

		ToolbarHelper::title(Text::_('Product Manager'), "generic");

		$toolbar = Toolbar::getInstance('toolbar');

		// Check if the form exists before showing the add/edit buttons
		$formPath = JPATH_COMPONENT_ADMINISTRATOR . '/src/View/Products';

		if (file_exists($formPath)) {
			if ($canDo->get('core.create')) {
				$toolbar->addNew('product.add', 'Add');
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
				$childBar->publish('products.publish')->listCheck(true);
				$childBar->unpublish('products.unpublish')->listCheck(true);
				$childBar->archive('products.archive')->listCheck(true);
			} elseif (isset($this->items[0])) {
				// If this component does not use state then show a direct delete button as we can not trash
				$toolbar->delete('products.delete')
					->text('Delete')
					->message('JGLOBAL_CONFIRM_DELETE')
					->listCheck(true);
			}


			if (isset($this->items[0]->checked_out)) {
				$childBar->checkin('products.checkin')->listCheck(true);
			}

			if (isset($this->items[0]->state)) {
				$childBar->trash('products.trash')->listCheck(true);
			}
		}



		// Show trash and delete for components that uses the state field

		// if ($canDo->get('core.admin')) {
		// 	$toolbar->preferences('com_atelman');
		// }

		//ifoundires 
		$toolbar->appendButton('Link', 'upload', Text::_('Upload CSV'), 'index.php?option=com_atelman&view=products&layout=uploadcsv');

		// Set sidebar action
		Sidebar::setAction('index.php?option=com_atelman&view=products');
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
			'a.`product_no`' => Text::_('COM_ATELMAN_PRODUCTS_PRODUCT_NO'),
			'a.`model_no`' => Text::_('COM_ATELMAN_PRODUCTS_MODEL_NO'),
			'a.`product_name`' => Text::_('COM_ATELMAN_PRODUCTS_PRODUCT_NAME'),
			'a.`warranty`' => Text::_('COM_ATELMAN_PRODUCTS_WARRANTY'),
			'a.`is_previous3years`' => Text::_('COM_ATELMAN_PRODUCTS_IS_PREVIOUS3YEARS'),
			'a.`created_date`' => Text::_('COM_ATELMAN_PRODUCTS_CREATED_DATE'),
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
