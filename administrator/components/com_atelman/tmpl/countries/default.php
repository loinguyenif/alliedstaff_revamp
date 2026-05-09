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
	$saveOrderingUrl = 'index.php?option=com_atelman&task=countries.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';
	HTMLHelper::_('draggablelist.draggable');
}

?>
<style>
	.js-stools {
		display: none;
	}
</style>

<form action="<?php echo Route::_('index.php?option=com_atelman&view=countries'); ?>" method="post"
	name="adminForm" id="adminForm">
	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">
				<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>

				<div class="clearfix"></div>
				<table class="table table-striped" id="country_managementList">
					<thead>
						<tr>
							<th class="w-1">#</th>
							<th class="w-1 text-center">
								<input type="checkbox" autocomplete="off" class="form-check-input" name="checkall-toggle" value=""
									title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
							</th>

							<th class='left'>
								<?php echo HTMLHelper::_('searchtools.sort',  'Country Name', 'a.country', $listDirn, $listOrder); ?>
							</th>

							<th scope="col" class="w-3 d-none d-lg-table-cell">

								<?php echo HTMLHelper::_('searchtools.sort',  'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?> </th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<td colspan="4">
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
									<?php echo  HTMLHelper::_('grid.id', $i, $item->id); ?>
								</td>
								<td>
									<?php if (isset($item->checked_out) && $item->checked_out && ($canEdit || $canChange)) : ?>
										<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->uEditor, $item->checked_out_time, 'countries.', $canCheckin); ?>
									<?php endif; ?>
									<?php if ($canEdit) : ?>
										<a href="<?php echo Route::_('index.php?option=com_atelman&task=country.edit&id=' . (int) $item->id); ?>">
											<?php echo $this->escape($item->country); ?>
										</a>
									<?php else : ?>
										<?php echo $this->escape($item->country); ?>
									<?php endif; ?>
								</td>

								<td class="d-none d-lg-table-cell">
									<?php echo $item->id; ?>

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