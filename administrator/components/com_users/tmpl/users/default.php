<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   (C) 2007 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Atelman\Component\Atelman\Administrator\Helper\AtelmanHelper;
use Joomla\CMS\Access\Access;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\String\PunycodeHelper;

/** @var \Joomla\Component\Users\Administrator\View\Users\HtmlView $this */


/** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('table.columns')
    ->useScript('multiselect');

$listOrder  = $this->escape($this->state->get('list.ordering'));
$listDirn   = $this->escape($this->state->get('list.direction'));
$loggeduser = $this->getCurrentUser();
$mfa        = PluginHelper::isEnabled('multifactorauth');

?>
<form action="<?php echo Route::_('index.php?option=com_users&view=users'); ?>" method="post" name="adminForm" id="adminForm">
    <div class="row">
        <div class="col-md-12">
            <div id="j-main-container" class="j-main-container">
                <?php
                // Search tools bar
                echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]);
                ?>
                <?php if (empty($this->items)) : ?>
                    <div class="alert alert-info">
                        <span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
                        <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
                    </div>
                <?php else : ?>
                    <table class="table" id="userList">
                        <caption class="visually-hidden">
                            <?php echo Text::_('COM_USERS_USERS_TABLE_CAPTION'); ?>,
                            <span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
                            <span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
                        </caption>
                        <thead>
                            <tr>
                                <td class="w-1 text-center">
                                    <?php echo HTMLHelper::_('grid.checkall'); ?>
                                </td>
                                <th scope="col">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'Company', 'a.name', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col">
                                    Contact
                                </th>
                                <th scope="col" class="w-10 d-none d-md-table-cell">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_USERNAME', 'a.username', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col" class="w-12 d-none d-xl-table-cell">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_EMAIL', 'a.email', $listDirn, $listOrder); ?>
                                </th>

                                <th scope="col">
                                    Customer ID
                                </th>
                                <th scope="col">
                                    Country
                                </th>
                                <th scope="col">
                                    Customer Type
                                </th>
                                <th width="10%" scope="col">
                                    <?php echo JText::_('Group'); ?>
                                </th>
                                <th scope="col" class="w-5 text-center d-md-table-cell">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_USERS_HEADING_ENABLED', 'a.block', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col" class="w-12 d-none d-xl-table-cell">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_USERS_HEADING_LAST_VISIT_DATE', 'a.lastvisitDate', $listDirn, $listOrder); ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($this->items as $i => $item) :
                                $canEdit   = $this->canDo->get('core.edit');
                                $canChange = $loggeduser->authorise('core.edit.state', 'com_users');

                                // If this group is super admin and this user is not super admin, $canEdit is false
                                if ((!$loggeduser->authorise('core.admin')) && Access::check($item->id, 'core.admin')) {
                                    $canEdit   = false;
                                    $canChange = false;
                                }
                            ?>
                                <tr class="row<?php echo $i % 2; ?>">
                                    <td class="text-center">
                                        <?php if ($canEdit || $canChange) : ?>
                                            <?php echo HTMLHelper::_('grid.id', $i, $item->id, false, 'cid', 'cb', $item->name); ?>
                                        <?php endif; ?>
                                    </td>
                                    <th scope="row">
                                        <div class="name break-word">
                                            <?php if ($canEdit) : ?>
                                                <a href="<?php echo Route::_('index.php?option=com_users&task=user.edit&id=' . (int) $item->id); ?>" title="<?php echo Text::sprintf('COM_USERS_EDIT_USER', $this->escape($item->name)); ?>">
                                                    <?php echo $this->escape($item->name); ?></a>
                                            <?php else : ?>
                                                <?php echo $this->escape($item->name); ?>
                                            <?php endif; ?>
                                        </div>

                                        <?php echo HTMLHelper::_('users.notesModal', $item->note_count, $item->id); ?>
                                        <?php if ($item->requireReset == '1') : ?>
                                            <span class="badge bg-warning"><?php echo Text::_('COM_USERS_PASSWORD_RESET_REQUIRED'); ?></span>
                                        <?php endif; ?>
                                    </th>

                                    <td class="break-word">
                                        <?php echo $this->escape($item->contact_name); ?>
                                    </td>
                                    <td class="break-word">
                                        <?php echo $this->escape($item->username); ?>
                                    </td>
                                    <td class="hidden-phone break-word hidden-tablet">
                                        <?php echo JStringPunycode::emailToUTF8($this->escape($item->email)); ?>
                                    </td>
                                    <td class="break-word">
                                        <?php echo $this->escape($item->customer_id); ?>
                                    </td>
                                    <td class="break-word">
                                        <?php
                                        echo AtelmanHelper::getCountryUser($item->country_id);
                                        ?>
                                    </td>
                                    <td class="break-word">
                                        <?php echo $item->is_internal == 0 ? "External" : "Internal"; ?>
                                    </td>
                                    <td>
                                        <?php if (substr_count($item->group_names, "\n") > 1) : ?>
                                            <span class="hasTooltip" title="<?php echo JHtml::_('tooltipText', JText::_('COM_USERS_HEADING_GROUPS'), nl2br($item->group_names), 0); ?>"><?php echo JText::_('COM_USERS_USERS_MULTIPLE_GROUPS'); ?></span>
                                        <?php else : ?>
                                            <?php echo nl2br($item->group_names); ?>
                                        <?php endif; ?>
                                    </td>
                                    <td class="center">
                                        <?php
                                        $self = $loggeduser->id == $item->id;

                                        if ($canChange) :
                                            echo JHtml::_('jgrid.state', JHtml::_('users.blockStates', $self), $item->block, $i, 'users.', !$self);
                                        else :
                                            echo JHtml::_('jgrid.state', JHtml::_('users.blockStates', $self), $item->block, $i, 'users.', false);
                                        endif; ?>
                                    </td>



                                    <td class="hidden-phone hidden-tablet">
                                        <?php if ($item->lastvisitDate != $this->db->getNullDate()) : ?>
                                            <?php echo JHtml::_('date', $item->lastvisitDate, JText::_('DATE_FORMAT_LC6')); ?>
                                        <?php else : ?>
                                            <?php echo JText::_('JNEVER'); ?>
                                        <?php endif; ?>
                                    </td>


                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <?php // load the pagination. 
                    ?>
                    <?php echo $this->pagination->getListFooter(); ?>

                    <?php // Load the batch processing form if user is allowed 
                    ?>
                    <?php
                    if (
                        $loggeduser->authorise('core.create', 'com_users')
                        && $loggeduser->authorise('core.edit', 'com_users')
                        && $loggeduser->authorise('core.edit.state', 'com_users')
                    ) : ?>
                        <template id="joomla-dialog-batch"><?php echo $this->loadTemplate('batch_body'); ?></template>
                    <?php endif; ?>
                <?php endif; ?>

                <input type="hidden" name="task" value="">
                <input type="hidden" name="boxchecked" value="0">
                <?php echo HTMLHelper::_('form.token'); ?>
            </div>
        </div>
    </div>
</form>