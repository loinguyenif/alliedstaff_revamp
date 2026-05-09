<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Atelman
 * @author     iFoundries <wpsub@ifoundries.com>
 * @copyright  2025 iFoundries
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Atelman\Component\Atelman\Administrator\View\Logs;
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
 * View class for a list of Logs.
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
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->filterForm = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

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
		$state = $this->get('State');
		$canDo = AtelmanHelper::getActions();

		ToolbarHelper::title(Text::_('System Logs'), "generic");

		$toolbar = Toolbar::getInstance('toolbar');

		// Check if the form exists before showing the add/edit buttons
		$formPath = JPATH_COMPONENT_ADMINISTRATOR . '/src/View/Logs';



		if ($canDo->get('core.edit.state')) {
			$dropdown = $toolbar->dropdownButton('status-group')
				->text('JTOOLBAR_CHANGE_STATUS')
				->toggleSplit(false)
				->icon('fas fa-ellipsis-h')
				->buttonClass('btn btn-action')
				->listCheck(true);

			$childBar = $dropdown->getChildToolbar();

			if (isset($this->items[0]->state)) {
				$childBar->publish('logs.publish')->listCheck(true);
				$childBar->unpublish('logs.unpublish')->listCheck(true);
				$childBar->archive('logs.archive')->listCheck(true);
			} elseif (isset($this->items[0])) {
				// If this component does not use state then show a direct delete button as we can not trash

			}
		}






		// Set sidebar action
		Sidebar::setAction('index.php?option=com_atelman&view=logs');
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
			'a.`section`' => Text::_('COM_ATELMAN_LOGS_SECTION'),
			'a.`action_type`' => Text::_('COM_ATELMAN_LOGS_ACTION_TYPE'),
			'a.`action_by`' => Text::_('COM_ATELMAN_LOGS_ACTION_BY'),
			'a.`which_id`' => Text::_('COM_ATELMAN_LOGS_WHICH_ID'),
			'a.`remarks`' => Text::_('COM_ATELMAN_LOGS_REMARKS'),
			'a.`before_update`' => Text::_('COM_ATELMAN_LOGS_BEFORE_UPDATE'),
			'a.`after_update`' => Text::_('COM_ATELMAN_LOGS_AFTER_UPDATE'),
			'a.`action_date`' => Text::_('COM_ATELMAN_LOGS_ACTION_DATE'),
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
