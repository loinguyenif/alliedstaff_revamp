<?php

/**
 * @version		$Id: view.php 17299 2010-05-27 16:06:54Z ian $
 * @package		Joomla
 * @subpackage	Search
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;

/**
 * @package		Joomla
 * @subpackage	Search
 * @since 1.5
 */
class HtmlView extends BaseHtmlView
{
	function display($tpl = null)
	{
		echo 'ccc';
		die;
		global $mainframe, $option;

		$itemid = JRequest::getVar('Itemid');
		$application = JFactory::getApplication();
		$menu = $application->getMenu();
		$item = $menu->getItem($itemid);

		$helper = new ATelmanHelper();

		$doc = &JFactory::getDocument();

		$mainframe->setPageTitle('Allied Telesis - Warranty Registration'); //- creates custom title as it should

		$this->assignRef('pagination',	$pagination);
		$this->assignRef('country', $helper->getWorldCountryHTML());
		$this->assignRef('item', $item);
		parent::display($tpl);
	}
	function getCountryHTML() {}
}
