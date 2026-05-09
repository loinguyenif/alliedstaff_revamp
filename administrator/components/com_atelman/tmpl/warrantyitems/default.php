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

$search  = $this->state->get('filter.search');



if (!empty($saveOrder)) {
	$saveOrderingUrl = 'index.php?option=com_atelman&task=warrantyitems.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';
	HTMLHelper::_('draggablelist.draggable');
}

?>
<script type="text/javascript">
	function exportToXLS() {
		jQuery('#task').val('export_csv');
		document.adminForm.submit();
		jQuery('#task').val('warrantyreg');
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
	table tr td {
		vertical-align: top;
	}

	.search-box-custom {
		display: flex;
	}

	.search-box-custom .filter-search-actions {
		margin-inline-start: 8px;
	}

	.js-stools {
		display: none;
	}
</style>
<?php if ($this->user->gid != 24 && $this->user->gid != 23) : ?>
	<?php
	$importisb = Route::_('index.php?option=com_atelman&view=warrantyitems&layout=importisb');
	$importisbdata = Route::_('index.php?option=com_atelman&view=warrantyitems&layout=importisbdata');
	$updatewarrantyreg = Route::_('index.php?option=com_atelman&view=warrantyitems&layout=updateregwarranty');
	?>
	<div style="border-bottom:1px solid #ccc;">
		<!--<div style="float:left;margin:0 0 10px 0;font-weight:bold;font-size:14px;"><a href="javascript:exportToXLS();">Export XLS</a></div>-->
		<div style="float:right;margin:0 0 10px 0;font-weight:bold;font-size:14px;"><a href="<?php echo $importisb ?>">Import isb.csv</a></div>
		<div style="float:right;margin:0 10px 10px 0;font-weight:bold;font-size:14px;"><a href="<?php echo $importisbdata ?>">Import isbdata.csv</a></div>
		<div style="float:right;margin:0 10px 10px 0;font-weight:bold;font-size:14px;"><a href="<?php echo $updatewarrantyreg ?>">Update Warranty Registration</a></div>
		<div style="clear:both"></div>
	</div>
<?php endif; ?>
<form action="<?php echo Route::_('index.php?option=com_atelman&view=warrantyitems'); ?>" method="post"
	name="adminForm" id="adminForm">
	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">
				<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
				<div class="clearfix"></div>
				<div class="js-stools-container-bar box-filter">
					<div class="btn-toolbar">
						<div class="ordering-select">
							<div class="search js-stools-field-list search-box-custom">
								<div class="filter-search-bar btn-group">
									<div class="input-group">
										<input type="text" name="filter[search]" id="filter_search" value="<?php echo $search ?>" placeholder="PO Number / SO Number/ Part Number / Model Number / Serial Number" class="form-control js-stools-search-string" aria-describedby="filter_search-desc">
										<div role="tooltip" id="filter_search-desc" class="filter-search-bar__description">
											PO Number / SO Number/ Part Number / Model Number / Serial Number </div>
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

							<?php if ($this->user->gid == 25 || $this->user->gid == 8 || $this->user->gid == 34) { ?>
								<div class="filter_country js-stools-field-list">
									<?php echo $this->lists['countries'] ?>
								</div>
							<?php }  ?>

							<?php if ($this->user->gid == 25 || $this->user->gid == 8 || $this->user->gid == 34) { ?>
								<div class="filter_company js-stools-field-list">
									<?php echo $this->lists['companies'] ?>
								</div>
							<?php } ?>
							<div class="filter_status js-stools-field-list">
								<?php echo $this->lists['expiry_month']; ?>
							</div>
						</div>
					</div>
				</div>

				<div class="clearfix"></div>

				<table class="table table-striped" id="warrantyitemList">
					<thead>
						<tr>
							<th>#</th>
							<th class="w-1 text-center">
								<input type="checkbox" autocomplete="off" class="form-check-input" name="checkall-toggle" value=""
									title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
							</th>

							<th class='left'>
								<?php echo HTMLHelper::_('searchtools.sort',  'Part Number', 'a.product_no', $listDirn, $listOrder); ?>
							</th>
							<th class='left'>
								<?php echo HTMLHelper::_('searchtools.sort',  'Model Number', 'a.model_no', $listDirn, $listOrder); ?>
							</th>
							<th class='left'>
								<?php echo HTMLHelper::_('searchtools.sort',  'Serial Number', 'a.serial_no', $listDirn, $listOrder); ?>
							</th>
							<th class='left'>
								<?php echo HTMLHelper::_('searchtools.sort',  'Replacement S/N', 'a.serial_no_2', $listDirn, $listOrder); ?>
							</th>
							<th class='left'>
								<?php echo HTMLHelper::_('searchtools.sort',  'COM_ATELMAN_FIELD_EXTENDED_WARRANTY', 'a.extended_warranty', $listDirn, $listOrder); ?>
							</th>
							<th class='left'>
								<?php echo HTMLHelper::_('searchtools.sort',  'Expiry Date	', 'a.real_expiry_date', $listDirn, $listOrder); ?>
							</th>
							<th class='left'>
								<?php echo HTMLHelper::_('searchtools.sort',  'Customer', 'a.username', $listDirn, $listOrder); ?>
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
							$link = Route::_('index.php?option=com_atelman&view=warrantyitem&layout=edit&id=' . $item->id);
						?>
							<tr class="row<?php echo $i % 2; ?>" data-draggable-group='1' data-transition>
								<td><?php echo $i + 1; ?></td>
								<td class="text-center">
									<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
								</td>
								<td>
									<?php echo (($item->replacement_pn) ? $item->replacement_pn : $item->product_no); ?>
								</td>
								<td>
									<?php echo (($item->replacement_pn) ? $item->model_no2 : $item->model_no); ?>
								</td>
								<td>
									<a href="<?php echo $link ?>"><?php echo ($item->serial_no) ? $item->serial_no : 'N/A'; ?></a>
								</td>
								<td>
									<?php echo ($item->serial_no_2) ? $item->serial_no_2 : 'N/A'; ?>
								</td>
								<td>
									<?php echo $item->extended_warranty; ?>
								</td>
								<td>
									<?php echo date("d/m/Y", strtotime($item->real_expiry_date)) ?>
								</td>
								<td>
									<?php if ($item->customer_id) : ?>
										<?php echo ($item->company_name) ? $item->company_name : 'No Company Name' ?>
									<?php else : ?>
										N/A
									<?php endif; ?>
								</td>

							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>

				<input type="hidden" name="option" value="com_atelman" />
				<input type="hidden" id="task" name="task" value="" />
				<input type="hidden" name="view" value="warrantyitems" />
				<input type="hidden" name="boxchecked" value="0" />
				<input type="hidden" name="list[fullorder]" value="<?php echo $listOrder; ?> <?php echo $listDirn; ?>" />
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>