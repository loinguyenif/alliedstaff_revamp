<?php

defined('_JEXEC') or die('Restricted access');
JHTML::_('behavior.tooltip');
JHTML::_('behavior.formvalidation');

JToolBarHelper::title(JText::_('RMA Update List'), 'user.png');

if ($this->user->gid == 25 || $this->user->gid == 8 || $this->user->gid == 31 || $this->user->gid == 32 || $this->user->gid == 34) :
	JToolBarHelper::save();
	JToolBarHelper::apply();
endif;

JToolBarHelper::cancel();

$user = JFactory::getUser();
?>

<script type="text/javascript">
	window.addEvent('domready', function() {

		<?php if ($this->item->status == 'receive' || $this->item->status == 'ship') : ?>
			//$('fileUploadStatus').setStyle('display','');
		<?php endif; ?>

		<?php if ($this->user->gid == 25 || $this->user->gid == 8 || $this->user->gid == 31 || $this->user->gid == 32 || $this->user->gid == 34) :  ?>
			$('status').addEvent('change', function() {

				$('document_file').setProperty('value', '');
				if (this.value == 'receive' || this.value == 'ship') {
					$('fileUploadStatus').setStyle('display', '');
				} else {
					$('fileUploadStatus').setStyle('display', 'none');
				}

			});
		<?php endif; ?>

	});
</script>
<div style="margin-bottom:20px;">
	Note : <br />
	1. Save => Save RMA Order and send notification to requestor<br />
	2. Apply => Save RMA Order and no notification send to requestor<br />
	3. Close => Operation Cancelled
</div>
<form action="index.php" method="post" name="adminForm" enctype="multipart/form-data">
	<div class="col width-55">

		<fieldset class="adminform">
			<legend><?php echo JText::_('RMA Update List') ?></legend>

			<table class="admintable" style="margin-bottom:20px;padding-bottom:20px;">
				<?php if ($this->user->gid == 25 || $this->user->gid == 8 || $this->user->gid == 32 || $this->user->gid == 34) : ?>
					<tr>
						<td class="key">
							<label for="title">
								<?php echo JText::_('RMA Request'); ?>:
							</label>
						</td>
						<td>
							<input class="inputbox" type="file" name="rma_request_file" id="rma_request_file" size="60" value="" />
						</td>
					</tr>
					<tr>
						<td class="key">
							<label for="title">
								<?php echo JText::_('COM_ATELMAN_RMA_NO'); ?>:
							</label>
						</td>
						<td>
							<input class="inputbox required" type="text" id="rmacode" name="rmacode" id="title" size="60" value="" />
						</td>
					</tr>
					<tr>
						<td class="key">
							<label for="title">
								<?php echo JText::_('RMA Order'); ?>:
							</label>
						</td>
						<td>
							<input class="inputbox" type="file" name="rma_order_file" id="rma_order_file" size="60" value="" />
						</td>
					</tr>
				<?php endif; ?>
				<tr>
					<td class="key">
						<label for="title">
							<?php echo JText::_('Status'); ?>:
						</label>
					</td>
					<td>
						<?php
						$helper = new ATelmanHelper();
						$statusesHTML = $helper->statusTypeUpdate();
						echo $statusesHTML;
						?>
					</td>
				</tr>
				<tr id="fileUploadStatus" style="display:none;">
					<td class="key" valign="top">
						<label for="title">
							<?php echo JText::_('Files'); ?>:
						</label>
					</td>
					<td>
						<input type="file" id="document_file" name="document_file" value="" /><br />
						<input type="checkbox" name="is_airway_bill" value="1" />Is this file airway bill?<br />
						<br />
						*** Extensions *** : <br />
						<b>.xls, .doc, .txt, .pdf, .jpg, .gif, .png</b>
					</td>
				</tr>
				<?php if ($this->user->gid == 25 || $this->user->gid == 8 || $this->user->gid == 32 || $this->user->gid == 34) : // Super Admin or Logistics or Supervisor 
				?>
					<tr>
						<td class="key">
							<label for="title">
								<?php echo JText::_('COM_ATELMAN_FIELD_SHIPPING_DURATION_FOR_REPLACEMENT'); ?>:
							</label>
						</td>
						<td>
							<input class="inputbox" type="text" name="shipping_duration" id="title" size="60" value="" />
						</td>
					</tr>
					<tr>
						<td class="key">
							<label for="title">
								<?php echo JText::_('COM_ATELMAN_FIELD_RECEIVE_DATE'); ?>:
							</label>
						</td>
						<td>
							<?php echo JHTML::calendar('', 'received_date', 'received_date', '%d/%m/%Y', ' class="validate-dates" onblur="javascript:void(0)" ') . "<br />Date Format : DD/MM/YYYY "; ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<label for="title">
								<?php echo JText::_('COM_ATELMAN_FIELD_SHIP_DATE'); ?>:
							</label>
						</td>
						<td>
							<?php echo JHTML::calendar('', 'shipped_date', 'shipped_date', '%d/%m/%Y', ' class="validate-dates" onblur="javascript:void(0)" ') . "<br />Date Format : DD/MM/YYYY "; ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<label for="title">
								<?php echo JText::_('COM_ATELMAN_FIELD_CLOSE_DATE'); ?>:
							</label>
						</td>
						<td>
							<?php echo JHTML::calendar('', 'closed_date', 'closed_date', '%d/%m/%Y', ' class="validate-dates" onblur="javascript:void(0)" ') . "<br />Date Format : DD/MM/YYYY "; ?>
						</td>
					</tr>
				<?php endif; ?>
			</table>

		</fieldset>

	</div>
	<div class="col width-40">
		<fieldset class="adminform">
			<legend><?php echo JText::_('Files') ?></legend>
			<div>
				Please tick the files you want to remove :
				<br /><br />
				<?php if (!empty($this->files)) : ?>
					<?php foreach ($this->files as $f) { ?>
						<input type="checkbox" name="files_rma_item_id[]" value="<?php echo $f->id ?>" /><?php echo $f->filename; ?><br />
					<?php } ?>
				<?php else : ?>
					No Files
				<?php endif; ?>
			</div>
		</fieldset>
	</div>
	<div class="clr"></div>
	<input type="hidden" id="task" name="task" value="" />
	<input type="hidden" name="view" value="updatermalist" />
	<input type="hidden" name="cid" value="<?php echo $this->cid ?>" />
	<input type="hidden" name="option" value="com_atelman" />
	<?php echo JHTML::_('form.token'); ?>
</form>