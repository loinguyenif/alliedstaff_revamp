<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_cpanel
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Atelman\Component\Atelman\Administrator\Helper\AtelmanHelper;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Registry\Registry;

/** @var \Joomla\Component\Cpanel\Administrator\View\Cpanel\HtmlView $this */

// Load JavaScript message titles
Text::script('ERROR');
Text::script('WARNING');
Text::script('NOTICE');
Text::script('MESSAGE');

Text::script('COM_CPANEL_UNPUBLISH_MODULE_SUCCESS');
Text::script('COM_CPANEL_UNPUBLISH_MODULE_ERROR');

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('com_cpanel.admin-cpanel')
    ->useScript('joomla.dialog-autocreate');

$user = $this->getCurrentUser();
$levels = AtelmanHelper::getRealViewLevels($user);

// Set up the modal options that will be used for module editor
$popupOptions = [
    'popupType'  => 'iframe',
    'src'        => Route::_('index.php?option=com_cpanel&task=addModule&position=' . $this->position, false),
    'textHeader' => Text::_('COM_CPANEL_ADD_MODULE_MODAL_TITLE'),
];
$user = \Joomla\CMS\Factory::getUser();
$allowGroup = array('8', '25');
if (!in_array($user->gid, $allowGroup)) { ?>
    <style>
        .module-actions.dropdown {
            display: none;
        }
    </style>
<?php } ?>
<div id="cpanel-modules">
    <div class="cpanel-modules <?php echo $this->position; ?>">
        <div class="card-columns">
            <?php if ($this->quickicons) :
                foreach ($this->quickicons as $iconmodule) {
                    $modParams = new Registry($iconmodule->params);

                    echo ModuleHelper::renderModule($iconmodule, [
                        'style' => 'well',
                        'class' => 'quickicons-for-' . $modParams->get('context', ''),
                    ]);
                }
            endif;
            foreach ($this->modules as $module) {
                if (in_array($module->access, $levels)) :
                    echo ModuleHelper::renderModule($module, ['style' => 'well']);
                endif;
            }
            ?>
            <?php if ($user->authorise('core.admin', 'com_modules') && $user->authorise('core.create', 'com_modules')) : ?>

            <?php endif; ?>
        </div>
    </div>
</div>