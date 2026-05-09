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

// no direct access
namespace Atelman\Component\Atelman\Site\View\Rmarequest;
// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;

/**
 * View class for a list of Atelman.
 *
 * @since  1.0.0
 */
class HtmlView extends BaseHtmlView
{
	function display($tpl = null)
	{
		$app  = Factory::getApplication();
		$user = $app->getIdentity();
		$db				= Factory::getDBO();
		$currentUser	= Factory::getUser();

		$itemid = $app->input->getInt('Itemid');
		$menu = $app->getMenu();
		$item = $menu->getItem($itemid);
		$this->item = $item;
		$this->user = $user;

		$doc = Factory::getDocument();

		$doc->setTitle('Allied Telesis - RMA Request'); //- creates custom title as it should

		parent::display($tpl);
	}
}
