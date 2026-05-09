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

$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
	->useScript('form.validate');
HTMLHelper::_('bootstrap.tooltip');
?>

<form
	action="<?php echo Route::_('index.php?option=com_atelman&layout=edit&id=' . (int) $this->item->id); ?>"
	method="post" enctype="multipart/form-data" name="adminForm" id="warrantyitem-form" class="form-validate form-horizontal">

	
	<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'item')); ?>
	<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'item', Text::_('COM_ATELMAN_TAB_ITEM', true)); ?>
	<div class="row-fluid">
		<div class="col-md-12 form-horizontal">
			<fieldset class="adminform">
				<legend><?php echo Text::_('COM_ATELMAN_FIELDSET_ITEM'); ?></legend>
				<?php echo $this->form->renderField('id'); ?>
				<?php echo $this->form->renderField('warranty_id'); ?>
				<?php echo $this->form->renderField('customer_id'); ?>
				<?php echo $this->form->renderField('product_id'); ?>
				<?php echo $this->form->renderField('serial_no'); ?>
				<?php echo $this->form->renderField('serial_no_2'); ?>
				<?php echo $this->form->renderField('replacement_pn'); ?>
				<?php echo $this->form->renderField('po_no'); ?>
				<?php echo $this->form->renderField('so_no'); ?>
				<?php echo $this->form->renderField('invoice_no'); ?>
				<?php echo $this->form->renderField('purchase_date'); ?>
				<?php echo $this->form->renderField('comments'); ?>
				<?php echo $this->form->renderField('expired_date'); ?>
				<?php echo $this->form->renderField('expired_date_manual'); ?>
				<?php echo $this->form->renderField('extended_warranty'); ?>
				<?php echo $this->form->renderField('extended_expired_date'); ?>
				<?php echo $this->form->renderField('created_date'); ?>
			</fieldset>
		</div>
	</div>
	<?php echo HTMLHelper::_('uitab.endTab'); ?>

	
	<?php echo HTMLHelper::_('uitab.endTabSet'); ?>

	<input type="hidden" name="task" value=""/>
	<?php echo HTMLHelper::_('form.token'); ?>

</form>
