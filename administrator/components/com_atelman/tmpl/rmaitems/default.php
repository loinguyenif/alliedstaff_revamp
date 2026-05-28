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

use Atelman\Component\Atelman\Administrator\Helper\AtelmanHelper;
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
$wa->registerAndUseStyle('custom.searchtools', 'media/templates/administrator/atum/css/system/searchtools/searchtools.css');
$wa->useStyle('com_atelman.admin')
	->useScript('com_atelman.admin');

$user      = Factory::getApplication()->getIdentity();
$userId    = $user->get('id');
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$search    = $this->state->get('filter.search');
$canOrder  = $user->authorise('core.edit.state', 'com_atelman');



if (!empty($saveOrder)) {
	$saveOrderingUrl = 'index.php?option=com_atelman&task=rmaitems.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';
	HTMLHelper::_('draggablelist.draggable');
}

$export_link = 'index.php';
$submit_rma_req_link	= 'index.php?option=com_atelman&view=rmaitems&layout=submitrma';
$check_rma_req_link	= 'index.php?option=com_atelman&view=rmaitems&layout=checkrma';

?>
<script type="text/javascript">
	function exportToXLS() {
		jQuery('#task').val('rmaitems.export_csv');
		jQuery('#view').val('rmarequest');
		document.adminForm.submit();
		jQuery('#task').val('');
		jQuery('#view').val('rmaitems');
	}

	jQuery(document).ready(function() {
		jQuery('#resetAllBtn').click(function() {
			jQuery('#filter_search').val('');
			jQuery('select[name="filter_status"]').prop('selectedIndex', 0);
			<?php if ($this->user->gid == 25 || $this->user->gid == 8 || $this->user->gid == 31 || $this->user->gid == 32 || $this->user->gid == 34 || $this->user->gid == 35) { ?>
				jQuery('select[name="filter_country"]').prop('selectedIndex', 0);
			<?php }  ?>
			<?php if ($this->user->gid == 25 || $this->user->gid == 8 || $this->user->gid == 23 || $this->user->gid == 31 || $this->user->gid == 32 || $this->user->gid == 34 || $this->user->gid == 35) { ?>
				jQuery('select[name="filter_company"]').prop('selectedIndex', 0);
			<?php } ?>
			jQuery('select[name="filter_distributor"]').prop('selectedIndex', 0);
			jQuery('#adminForm').submit();
		});
	});
</script>
<style>
/*	.search-box-custom {
		display: flex;
	}

	.search-box-custom .filter-search-actions {
		margin-inline-start: 8px;
	}

	.js-stools {
		display: none;
	}

	#sidebar-wrapper {
		display: none;
	}
    */    
</style>
<form action="<?php echo Route::_('index.php?option=com_atelman&view=rmaitems'); ?>" method="post"
	name="adminForm" id="adminForm">
	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">
				<div style="margin:10px 0;font-weight:bold;font-size:14px;float:left">
					<?php if ($this->user->gid == 25 || $this->user->gid == 8 || $this->user->gid == 33 || $this->user->gid == 34) : ?>
						<a href="javascript:void(0);" onclick="javascript:exportToXLS();">Export XLS</a>
					<?php endif; ?>
					<?php if ($this->user->gid == 25 || $this->user->gid == 8 || $this->user->gid == 33) : ?>
						&nbsp;/&nbsp;
						<a href="index.php?option=com_atelman&view=rmaitems&layout=importrma">Import CSV</a>
					<?php endif; ?>
				</div>
				<?php if ($this->user->gid < 31 || $this->user->gid == 34) : ?>
					<div style="float:right;margin:10px 0;font-weight:bold;font-size:14px;">
						<a href="<?php echo $submit_rma_req_link ?>">Submit RMA Request</a>&nbsp;/&nbsp;<a href="<?php echo $check_rma_req_link ?>">Check RMA Request</a>
					</div>
				<?php endif; ?>
				<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
				?>

				<div class="clearfix"></div>

				<div class="js-stools-container-bar box-filter">
					<div class="btn-toolbar">
						<div class="ordering-select">
							<div class="search js-stools-field-list search-box-custom">
								<div class="filter-search-bar btn-group">
									<div class="input-group">
										<input type="text" name="filter[search]" id="filter_search" value="<?php echo $search ?>" placeholder="RMA Number/Part Number/Model Number/Serial Number" class="form-control js-stools-search-string" aria-describedby="filter_search-desc">
										<div role="tooltip" id="filter_search-desc" class="filter-search-bar__description">
											Search </div>
										<button type="submit" class="filter-search-bar__button btn btn-primary" aria-label="Search">
											<span class="filter-search-bar__button-icon icon-search" aria-hidden="true"></span>
										</button>
									</div>
								</div>
								<div class="filter-search-actions btn-group">
									<button type="button"
										class="filter-search-actions__button btn btn-primary"
										id="resetAllBtn">
										<span class="icon-refresh"></span> Reset
									</button>
								</div>
							</div>
							<div class="filter_status js-stools-field-list">
								<?php echo $this->listStatus; ?>
							</div>
							<?php if ($this->user->gid == 25 || $this->user->gid == 8 || $this->user->gid == 31 || $this->user->gid == 32 || $this->user->gid == 34 || $this->user->gid == 35) { ?>
								<div class="filter_country js-stools-field-list">
									<?php echo $this->listCountry; ?>
								</div>
							<?php }  ?>

							<?php if ($this->user->gid == 25 || $this->user->gid == 8 || $this->user->gid == 23 || $this->user->gid == 31 || $this->user->gid == 32 || $this->user->gid == 34 || $this->user->gid == 35) { ?>
								<div class="filter_company js-stools-field-list">
									<?php echo $this->listCompany  ?>
								</div>
							<?php } ?>
						</div>
					</div>
				</div>

				<div class="clearfix"></div>
				<table class="table table-striped" id="rmaitemList">
					<thead>
						<tr>
							<th>#</th>
							<th class="w-1 text-center">
								<input type="checkbox" autocomplete="off" class="form-check-input" name="checkall-toggle" value=""
									title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
							</th>
							<th class='left'>
								<?php echo HTMLHelper::_('searchtools.sort',  'RMA Number', 'a.rmacode', $listDirn, $listOrder); ?>
							</th>
							<th class='left'>
								<?php echo HTMLHelper::_('searchtools.sort',  'Part Number', 'a.product_no', $listDirn, $listOrder); ?>
							</th>
							<th class='left'>
								<?php echo HTMLHelper::_('searchtools.sort',  'Model Number', 'a.model_no', $listDirn, $listOrder); ?>
							</th>
							<th class='left'>
								<?php echo HTMLHelper::_('searchtools.sort',  'Serial Number', 'a.requested_sn', $listDirn, $listOrder); ?>
							</th>
							<th class='left'>
								<?php echo HTMLHelper::_('searchtools.sort',  'Status', 'a.status', $listDirn, $listOrder); ?>
							</th>

							<?php if ($this->user->gid == 25 || $this->user->gid == 8 || $this->user->gid == 8 || $this->user->gid == 31 || $this->user->gid == 32 || $this->user->gid == 34 || $this->user->gid == 35) : // if super admin  or Store or Logistics or Supervisor or Finance 
							?>
								<th width="25%" class="title">
									<?php echo HTMLHelper::_('searchtools.sort',  'Company', 'a.customer_name', $listDirn, $listOrder); ?>
								</th>
							<?php endif; ?>


							<?php if ($this->user->gid == 23) : // if manager 
							?>
								<th width="25%" class="title">
									<?php echo HTMLHelper::_('searchtools.sort',  'Distributor', 'distributor_name', $listDirn, $listOrder); ?>
								</th>
							<?php endif; ?>

							<th class="title">
								<?php echo HTMLHelper::_('searchtools.sort',  'Request Date', 'a.created_date', $listDirn, $listOrder); ?>
							</th>

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
						?>
							<tr class="row<?php echo $i % 2; ?>" data-draggable-group='1' data-transition>
								<td><?php echo $i + 1; ?></td>
								<td class="text-center">
									<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
								</td>
								<td>
									<?php if (isset($item->checked_out) && $item->checked_out && ($canEdit || $canChange)) : ?>
										<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->uEditor, $item->checked_out_time, 'rmaitems.', $canCheckin); ?>
									<?php endif; ?>
									<?php if ($canEdit) : ?>
										<a href="<?php echo Route::_('index.php?option=com_atelman&task=rmaitem.edit&id=' . (int) $item->id); ?>">
											<?php echo $item->rmacode ? $this->escape($item->rmacode) : 'N/A'; ?>
										</a>
									<?php else : ?>
										<?php echo $item->rmacode ? $this->escape($item->rmacode) : 'N/A'; ?>
									<?php endif; ?>
								</td>
								<td>
									<?php echo $item->product_no ? $item->product_no : 'N/A'; ?>
								</td>
								<td>
									<?php echo $item->model_no ? $item->model_no : 'N/A'; ?>
								</td>
								<td>
									<?php echo $item->requested_sn ? $item->requested_sn : 'N/A'; ?>
								</td>

								<td>
									<?php echo $this->status[$item->status]; ?>
								</td>

								<?php if ($this->user->gid == 25 || $this->user->gid == 8 || $this->user->gid == 8 || $this->user->gid == 31 || $this->user->gid == 32 || $this->user->gid == 34 || $this->user->gid == 35) : // if super admin  or Store or Logistics or Supervisor or Finance 
								?>
									<td>
										<?php echo $item->customer_name; ?>
									</td>
								<?php endif; ?>

								<?php if ($this->user->gid == 23) : // if manager 
								?>
									<td>
										<?php echo $item->distributor_name; ?>
									</td>
								<?php endif; ?>



								<td>
									<?php
									$date = $item->created_date;
									echo $date > 0 ? date('d/m/Y', strtotime($date)) : '-';
									?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>

				<input type="hidden" name="task" id="task" value="" />
				<input type="hidden" name="view" id="view" value="rmaitems">
				<input type="hidden" name="option" value="com_atelman">
				<input type="hidden" name="boxchecked" value="0" />
				<input type="hidden" name="list[fullorder]" value="<?php echo $listOrder; ?> <?php echo $listDirn; ?>" />
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>