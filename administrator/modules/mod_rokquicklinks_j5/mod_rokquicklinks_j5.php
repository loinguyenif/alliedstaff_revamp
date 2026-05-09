<?php
defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Factory;

$user   = Factory::getUser();
$levels = $user->getAuthorisedViewLevels();

// Nếu user không có quyền xem module này → dừng render
if (!in_array($module->access, $levels)) {
    return;
}

require_once __DIR__ . '/helper.php';

$links = ModRokQuicklinksHelper::getLinks($params);

// Load layout
require ModuleHelper::getLayoutPath('mod_rokquicklinks_j5', $params->get('layout', 'default'));
