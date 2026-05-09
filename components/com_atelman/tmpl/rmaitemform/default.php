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
use \Atelman\Component\Atelman\Site\Helper\AtelmanHelper;

$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
	->useScript('form.validate');
HTMLHelper::_('bootstrap.tooltip');

// Load admin language file
$lang = Factory::getLanguage();
$lang->load('com_atelman', JPATH_SITE);

$user    = Factory::getApplication()->getIdentity();
$canEdit = AtelmanHelper::canUserEdit($this->item, $user);


?>

<div class="rmaitem-edit front-end-edit">

<?php if ($this->params->get('show_page_heading')) : ?>
    <div class="page-header">
        <h1> <?php echo $this->escape($this->params->get('page_heading')); ?> </h1>
    </div>
    <?php endif;?>
	<?php if (!$canEdit) : ?>
		<h3>
		<?php throw new \Exception(Text::_('COM_ATELMAN_ERROR_MESSAGE_NOT_AUTHORISED'), 403); ?>
		</h3>
	<?php else : ?>
		<?php if (!empty($this->item->id)): ?>
			<h1><?php echo Text::sprintf('COM_ATELMAN_EDIT_ITEM_TITLE', $this->item->id); ?></h1>
		<?php else: ?>
			<h1><?php echo Text::_('COM_ATELMAN_ADD_ITEM_TITLE'); ?></h1>
		<?php endif; ?>

		<form id="form-rmaitem"
			  action="<?php echo Route::_('index.php?option=com_atelman&task=rmaitemform.save'); ?>"
			  method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
			
	<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'item')); ?>
	<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'item', Text::_('COM_ATELMAN_TAB_ITEM', true)); ?>
	<?php echo $this->form->renderField('id'); ?>

	<?php echo $this->form->renderField('rma_request_id'); ?>

	<?php echo $this->form->renderField('rmacode'); ?>

	<?php echo $this->form->renderField('warranty_item_id'); ?>

	<?php echo $this->form->renderField('product_id'); ?>

	<?php echo $this->form->renderField('customer_id'); ?>

	<?php echo $this->form->renderField('requested_sn'); ?>

	<?php echo $this->form->renderField('replacement_pn'); ?>

	<?php echo $this->form->renderField('replacement_sn'); ?>

	<?php echo $this->form->renderField('warranty_status'); ?>

	<?php echo $this->form->renderField('description'); ?>

	<?php echo $this->form->renderField('status'); ?>

	<?php echo $this->form->renderField('shipping_duration'); ?>

	<?php echo $this->form->renderField('so_no'); ?>

	<?php echo $this->form->renderField('invoice_no'); ?>

	<?php echo $this->form->renderField('remarks'); ?>

	<?php echo $this->form->renderField('replacement_date'); ?>

	<?php echo $this->form->renderField('rma_assigned_date'); ?>

	<?php echo $this->form->renderField('received_date'); ?>

	<?php echo $this->form->renderField('shipped_date'); ?>

	<?php echo $this->form->renderField('closed_date'); ?>

	<?php echo $this->form->renderField('created_date'); ?>

	<?php echo $this->form->renderField('is_import_csv'); ?>

	<?php echo $this->form->renderField('created_by'); ?>

	<?php echo HTMLHelper::_('uitab.endTab'); ?>
			<div class="control-group">
				<div class="controls">

					<?php if ($this->canSave): ?>
						<button type="submit" class="validate btn btn-primary">
							<span class="fas fa-check" aria-hidden="true"></span>
							<?php echo Text::_('JSUBMIT'); ?>
						</button>
					<?php endif; ?>
					<a class="btn btn-danger"
					   href="<?php echo Route::_('index.php?option=com_atelman&task=rmaitemform.cancel'); ?>"
					   title="<?php echo Text::_('JCANCEL'); ?>">
					   <span class="fas fa-times" aria-hidden="true"></span>
						<?php echo Text::_('JCANCEL'); ?>
					</a>
				</div>
			</div>

			<input type="hidden" name="option" value="com_atelman"/>
			<input type="hidden" name="task"
				   value="rmaitemform.save"/>
			<?php echo HTMLHelper::_('form.token'); ?>
		</form>
	<?php endif; ?>
</div>
