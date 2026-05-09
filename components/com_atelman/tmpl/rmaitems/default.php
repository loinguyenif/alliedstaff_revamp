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
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Layout\LayoutHelper;
use \Joomla\CMS\Session\Session;
use \Joomla\CMS\User\UserFactoryInterface;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('formbehavior.chosen', 'select');

$user       = Factory::getApplication()->getIdentity();
$userId     = $user->get('id');
$listOrder  = $this->state->get('list.ordering');
$listDirn   = $this->state->get('list.direction');
$canCreate  = $user->authorise('core.create', 'com_atelman') && file_exists(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'rmaitemform.xml');
$canEdit    = $user->authorise('core.edit', 'com_atelman') && file_exists(JPATH_COMPONENT .  DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'rmaitemform.xml');
$canCheckin = $user->authorise('core.manage', 'com_atelman');
$canChange  = $user->authorise('core.edit.state', 'com_atelman');
$canDelete  = $user->authorise('core.delete', 'com_atelman');

// Import CSS
$wa = $this->document->getWebAssetManager();
$wa->useStyle('com_atelman.list');
?>

<?php if ($this->params->get('show_page_heading')) : ?>
    <div class="page-header">
        <h1> <?php echo $this->escape($this->params->get('page_heading')); ?> </h1>
    </div>
<?php endif;?>
<form action="<?php echo htmlspecialchars(Uri::getInstance()->toString()); ?>" method="post"
	  name="adminForm" id="adminForm">
	
	<div class="table-responsive">
		<table class="table table-striped" id="rmaitemList">
			<thead>
			<tr>
				
					<th class=''>
						<?php echo HTMLHelper::_('grid.sort',  'COM_ATELMAN_RMAITEMS_ID', 'a.id', $listDirn, $listOrder); ?>
					</th>

					<th class=''>
						<?php echo HTMLHelper::_('grid.sort',  'COM_ATELMAN_RMAITEMS_RMA_REQUEST_ID', 'a.rma_request_id', $listDirn, $listOrder); ?>
					</th>

					<th class=''>
						<?php echo HTMLHelper::_('grid.sort',  'COM_ATELMAN_RMAITEMS_RMACODE', 'a.rmacode', $listDirn, $listOrder); ?>
					</th>

					<th class=''>
						<?php echo HTMLHelper::_('grid.sort',  'COM_ATELMAN_RMAITEMS_WARRANTY_ITEM_ID', 'a.warranty_item_id', $listDirn, $listOrder); ?>
					</th>

					<th class=''>
						<?php echo HTMLHelper::_('grid.sort',  'COM_ATELMAN_RMAITEMS_PRODUCT_ID', 'a.product_id', $listDirn, $listOrder); ?>
					</th>

					<th class=''>
						<?php echo HTMLHelper::_('grid.sort',  'COM_ATELMAN_RMAITEMS_CUSTOMER_ID', 'a.customer_id', $listDirn, $listOrder); ?>
					</th>

					<th class=''>
						<?php echo HTMLHelper::_('grid.sort',  'COM_ATELMAN_RMAITEMS_REQUESTED_SN', 'a.requested_sn', $listDirn, $listOrder); ?>
					</th>

					<th class=''>
						<?php echo HTMLHelper::_('grid.sort',  'COM_ATELMAN_RMAITEMS_REPLACEMENT_PN', 'a.replacement_pn', $listDirn, $listOrder); ?>
					</th>

					<th class=''>
						<?php echo HTMLHelper::_('grid.sort',  'COM_ATELMAN_RMAITEMS_REPLACEMENT_SN', 'a.replacement_sn', $listDirn, $listOrder); ?>
					</th>

					<th class=''>
						<?php echo HTMLHelper::_('grid.sort',  'COM_ATELMAN_RMAITEMS_WARRANTY_STATUS', 'a.warranty_status', $listDirn, $listOrder); ?>
					</th>

					<th class=''>
						<?php echo HTMLHelper::_('grid.sort',  'COM_ATELMAN_RMAITEMS_DESCRIPTION', 'a.description', $listDirn, $listOrder); ?>
					</th>

					<th class=''>
						<?php echo HTMLHelper::_('grid.sort',  'COM_ATELMAN_RMAITEMS_STATUS', 'a.status', $listDirn, $listOrder); ?>
					</th>

					<th class=''>
						<?php echo HTMLHelper::_('grid.sort',  'COM_ATELMAN_RMAITEMS_SHIPPING_DURATION', 'a.shipping_duration', $listDirn, $listOrder); ?>
					</th>

					<th class=''>
						<?php echo HTMLHelper::_('grid.sort',  'COM_ATELMAN_RMAITEMS_SO_NO', 'a.so_no', $listDirn, $listOrder); ?>
					</th>

					<th class=''>
						<?php echo HTMLHelper::_('grid.sort',  'COM_ATELMAN_RMAITEMS_INVOICE_NO', 'a.invoice_no', $listDirn, $listOrder); ?>
					</th>

					<th class=''>
						<?php echo HTMLHelper::_('grid.sort',  'COM_ATELMAN_RMAITEMS_REMARKS', 'a.remarks', $listDirn, $listOrder); ?>
					</th>

					<th class=''>
						<?php echo HTMLHelper::_('grid.sort',  'COM_ATELMAN_RMAITEMS_REPLACEMENT_DATE', 'a.replacement_date', $listDirn, $listOrder); ?>
					</th>

					<th class=''>
						<?php echo HTMLHelper::_('grid.sort',  'COM_ATELMAN_RMAITEMS_RMA_ASSIGNED_DATE', 'a.rma_assigned_date', $listDirn, $listOrder); ?>
					</th>

					<th class=''>
						<?php echo HTMLHelper::_('grid.sort',  'COM_ATELMAN_RMAITEMS_RECEIVED_DATE', 'a.received_date', $listDirn, $listOrder); ?>
					</th>

					<th class=''>
						<?php echo HTMLHelper::_('grid.sort',  'COM_ATELMAN_RMAITEMS_SHIPPED_DATE', 'a.shipped_date', $listDirn, $listOrder); ?>
					</th>

					<th class=''>
						<?php echo HTMLHelper::_('grid.sort',  'COM_ATELMAN_RMAITEMS_CLOSED_DATE', 'a.closed_date', $listDirn, $listOrder); ?>
					</th>

					<th class=''>
						<?php echo HTMLHelper::_('grid.sort',  'COM_ATELMAN_RMAITEMS_CREATED_DATE', 'a.created_date', $listDirn, $listOrder); ?>
					</th>

					<th class=''>
						<?php echo HTMLHelper::_('grid.sort',  'COM_ATELMAN_RMAITEMS_IS_IMPORT_CSV', 'a.is_import_csv', $listDirn, $listOrder); ?>
					</th>

					<th class=''>
						<?php echo HTMLHelper::_('grid.sort',  'COM_ATELMAN_RMAITEMS_CREATED_BY', 'a.created_by', $listDirn, $listOrder); ?>
					</th>

						<?php if ($canEdit || $canDelete): ?>
					<th class="center">
						<?php echo Text::_('COM_ATELMAN_RMAITEMS_ACTIONS'); ?>
					</th>
					<?php endif; ?>

			</tr>
			</thead>
			<tfoot>
			<tr>
				<td colspan="<?php echo isset($this->items[0]) ? count(get_object_vars($this->items[0])) : 10; ?>">
					<div class="pagination">
						<?php echo $this->pagination->getPagesLinks(); ?>
					</div>
				</td>
			</tr>
			</tfoot>
			<tbody>
			<?php foreach ($this->items as $i => $item) : ?>
				<?php $canEdit = $user->authorise('core.edit', 'com_atelman'); ?>
				<?php if (!$canEdit && $user->authorise('core.edit.own', 'com_atelman')): ?>
				<?php $canEdit = Factory::getApplication()->getIdentity()->id == $item->created_by; ?>
				<?php endif; ?>

				<tr class="row<?php echo $i % 2; ?>">
					
					<td>
						<?php echo $item->id; ?>
					</td>
					<td>
						<?php echo $item->rma_request_id; ?>
					</td>
					<td>
						<?php echo $item->rmacode; ?>
					</td>
					<td>
						<?php echo $item->warranty_item_id; ?>
					</td>
					<td>
						<?php echo $item->product_id; ?>
					</td>
					<td>
						<?php echo $item->customer_id; ?>
					</td>
					<td>
						<?php echo $item->requested_sn; ?>
					</td>
					<td>
						<?php echo $item->replacement_pn; ?>
					</td>
					<td>
						<?php echo $item->replacement_sn; ?>
					</td>
					<td>
						<?php echo $item->warranty_status; ?>
					</td>
					<td>
						<?php echo $item->description; ?>
					</td>
					<td>
						<?php echo $item->status; ?>
					</td>
					<td>
						<?php echo $item->shipping_duration; ?>
					</td>
					<td>
						<?php echo $item->so_no; ?>
					</td>
					<td>
						<?php echo $item->invoice_no; ?>
					</td>
					<td>
						<?php echo $item->remarks; ?>
					</td>
					<td>
						<?php
						$date = $item->replacement_date;
						echo $date > 0 ? HTMLHelper::_('date', $date, Text::_('DATE_FORMAT_LC4')) : '-';
						?>
					</td>
					<td>
						<?php
						$date = $item->rma_assigned_date;
						echo $date > 0 ? HTMLHelper::_('date', $date, Text::_('DATE_FORMAT_LC4')) : '-';
						?>
					</td>
					<td>
						<?php
						$date = $item->received_date;
						echo $date > 0 ? HTMLHelper::_('date', $date, Text::_('DATE_FORMAT_LC4')) : '-';
						?>
					</td>
					<td>
						<?php
						$date = $item->shipped_date;
						echo $date > 0 ? HTMLHelper::_('date', $date, Text::_('DATE_FORMAT_LC4')) : '-';
						?>
					</td>
					<td>
						<?php
						$date = $item->closed_date;
						echo $date > 0 ? HTMLHelper::_('date', $date, Text::_('DATE_FORMAT_LC4')) : '-';
						?>
					</td>
					<td>
						<?php
						$date = $item->created_date;
						echo $date > 0 ? HTMLHelper::_('date', $date, Text::_('DATE_FORMAT_LC4')) : '-';
						?>
					</td>
					<td>
						<?php echo $item->is_import_csv; ?>
					</td>
					<td>
								<?php $container = \Joomla\CMS\Factory::getContainer();
								$userFactory = $container->get(UserFactoryInterface::class);?>
								<?php $user = $userFactory->loadUserById($item->created_by); ?>
								<?php echo $user->name; ?>
					</td>
					<?php if ($canEdit || $canDelete): ?>
						<td class="center">
							<?php if ($canEdit): ?>
								<a href="<?php echo Route::_('index.php?option=com_atelman&task=rmaitem.edit&id=' . $item->id, false, 2); ?>" class="btn btn-mini" type="button"><i class="icon-edit" ></i></a>
							<?php endif; ?>
							<?php if ($canDelete): ?>
								<a href="<?php echo Route::_('index.php?option=com_atelman&task=rmaitemform.remove&id=' . $item->id, false, 2); ?>" class="btn btn-mini delete-button" type="button"><i class="icon-trash" ></i></a>
							<?php endif; ?>
						</td>
					<?php endif; ?>

				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php if ($canCreate) : ?>
		<a href="<?php echo Route::_('index.php?option=com_atelman&task=rmaitemform.edit&id=0', false, 0); ?>"
		   class="btn btn-success btn-small"><i
				class="icon-plus"></i>
			<?php echo Text::_('COM_ATELMAN_ADD_ITEM'); ?></a>
	<?php endif; ?>

	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="boxchecked" value="0"/>
	<input type="hidden" name="filter_order" value=""/>
	<input type="hidden" name="filter_order_Dir" value=""/>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>

<?php
	if($canDelete) {
		$wa->addInlineScript("
			jQuery(document).ready(function () {
				jQuery('.delete-button').click(deleteItem);
			});

			function deleteItem() {

				if (!confirm(\"" . Text::_('COM_ATELMAN_DELETE_MESSAGE') . "\")) {
					return false;
				}
			}
		", [], [], ["jquery"]);
	}
?>