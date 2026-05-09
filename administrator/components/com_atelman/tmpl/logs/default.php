<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Atelman
 * @author     iFoundries <wpsub@ifoundries.com>
 * @copyright  2025 iFoundries
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;


use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Layout\LayoutHelper;
use \Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');

// Import CSS
$wa =  $this->document->getWebAssetManager();
$wa->useStyle('com_atelman.admin')
	->useScript('com_atelman.admin');

$user      = Factory::getApplication()->getIdentity();
$userId    = $user->get('id');
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$canOrder  = $user->authorise('core.edit.state', 'com_atelman');



if (!empty($saveOrder)) {
	$saveOrderingUrl = 'index.php?option=com_atelman&task=logs.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';
	HTMLHelper::_('draggablelist.draggable');
}

?>
<style>
	.js-stools-container-bar {
		margin-inline-start: inherit !important;
	}

	.btn-toolbar {
		justify-content: inherit !important;
	}

	.btn-toolbar .ordering-select {
		display: none !important;
	}

	#j-main-container {
		position: relative;
	}

	.export-log {
		position: absolute;
		top: 10px;
		right: 10px;
	}

	.export-log a {
		font-weight: bold;
		padding: 5px 15px;
	}
</style>

<form action="<?php echo Route::_('index.php?option=com_atelman&view=logs'); ?>" method="post"
	name="adminForm" id="adminForm">
	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">
				<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
				<div class="clearfix"></div>
				<div class="export-log">
					<a class="btn btn-success" href="index.php?option=com_atelman&task=logs.export_logs&filter_order=<?php echo $listOrder; ?>&filter_order_Dir=<?php echo $listDirn; ?>">Export</a>
				</div>
				<table class="table table-striped" id="logList">
					<thead>
						<tr>
							<th>#</th>
							<!-- <th class="w-1 text-center">
								<input type="checkbox" autocomplete="off" class="form-check-input" name="checkall-toggle" value=""
									title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
							</th> -->
							<th class='left'>
								<?php echo HTMLHelper::_('searchtools.sort',  'Action Date', 'a.action_date', $listDirn, $listOrder); ?>
							</th>
							<th class='left'>
								<?php echo HTMLHelper::_('searchtools.sort',  'Module', 'a.section', $listDirn, $listOrder); ?>
							</th>
							<th class='left'>
								<?php echo HTMLHelper::_('searchtools.sort',  'Action Type', 'a.action_type', $listDirn, $listOrder); ?>
							</th>
							<th class='left'>
								<?php echo HTMLHelper::_('searchtools.sort',  'Action By', 'u.name', $listDirn, $listOrder); ?>
							</th>
							<th class='left'>
								<?php echo HTMLHelper::_('searchtools.sort',  'COM_ATELMAN_LOGS_REMARKS', 'a.remarks', $listDirn, $listOrder); ?>
							</th>
							<th></th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<td colspan="<?php echo isset($this->items[0]) ? count(get_object_vars($this->items[0])) : 10; ?>">
								<?php echo $this->pagination->getListFooter(); ?>
							</td>
						</tr>
					</tfoot>
					<tbody <?php if (!empty($saveOrder)) : ?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>" <?php endif; ?>>
						<?php foreach ($this->items as $i => $item) :
							$ordering   = ($listOrder == 'a.ordering');
							$canCreate  = $user->authorise('core.create', 'com_atelman');
							$canEdit    = $user->authorise('core.edit', 'com_atelman');
							$canCheckin = $user->authorise('core.manage', 'com_atelman');
							$canChange  = $user->authorise('core.edit.state', 'com_atelman');
							$detail_link = Route::_('index.php?option=com_atelman&task=log.edit&id=' . (int) $item->id);
							if ($item->action_type == "DELETE") {
								$link = "";
							}
							if ($item->which_id > 0) {
								switch ($item->section) {
									case 'RMA_ITEM':
										$link 	= 'index.php?option=com_atelman&view=rmaitem&layout=edit&id=' . $item->which_id . '';
										break;

									case 'WARRANTY_REG_ITEM':
										$link 	= 'index.php?option=com_atelman&view=warrantyitem&layout=edit&id=' . $item->which_id;
										break;

									case 'PRODUCT_ITEM':
										$link 	= 'index.php?option=com_atelman&view=product&layout=edit&id=' . $item->which_id;
										break;

									case 'CUSTOMER':
										$link 	=	'index.php?option=com_users&view=user&layout=edit&id=' . $item->which_id;
										break;

									case 'COUNTRY_ITEM':
										$link		=	'index.php?option=com_atelman&view=country&layout=edit&id=' . $item->which_id;
										break;
									default:
										$link		= "";
										break;
								}
							}
						?>
							<tr class="row<?php echo $i % 2; ?>" data-draggable-group='1' data-transition>
								<td><?php echo $i + 1 ?></td>
								<!-- <td class="text-center">
									<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
								</td> -->
								<td>
									<?php echo ($item->action_date) ? $item->action_date : ''; ?>
								</td>
								<td>
									<a href="<?php echo $detail_link; ?>"><?php echo $item->section; ?></a>
								</td>
								<td>
									<?php echo $item->action_type; ?>
								</td>
								<td>
									<?php echo $item->name ?>
								</td>
								<td>
									<?php echo $item->remarks ?>
								</td>

								<td>
									<?php if ($link) : ?>
										<a href="<?php echo $link ?>">Details</a>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>

				<input type="hidden" name="task" value="" />
				<input type="hidden" name="boxchecked" value="0" />
				<input type="hidden" name="list[fullorder]" value="<?php echo $listOrder; ?> <?php echo $listDirn; ?>" />
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>