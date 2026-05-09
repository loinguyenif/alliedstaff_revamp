<?php

use Atelman\Component\Atelman\Administrator\Helper\AtelmanHelper;
use Atelman\Component\Atelman\Site\Helper\AtelmanHelper as HelperAtelmanHelper;
use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Layout\LayoutHelper;
use \Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;

defined('_JEXEC') or die('Restricted access');
JToolBarHelper::title(Text::_('RMA Update List'), 'user.png');

$user = Factory::getUser();
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

<style>
	/* ====== Layout ====== */
	.col {
		float: left;
		padding: 10px;
		box-sizing: border-box;
	}

	.col.width-55 {
		width: 55%;
	}

	.col.width-45 {
		width: 45%;
	}

	/* Clearfix */
	.col::after {
		content: "";
		display: table;
		clear: both;
	}

	/* ====== Form Container ====== */
	form.adminform,
	form#RMARequestFormAdminForm,
	form#RMADownloadForm {
		background: #fff;
		border: 1px solid #ddd;
		border-radius: 6px;
		padding: 20px;
		box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
		margin-bottom: 15px;
	}

	fieldset.adminform {
		border: none;
		padding: 0;
	}

	legend {
		font-size: 1.2em;
		font-weight: bold;
		color: #333;
		margin-bottom: 15px;
		border-bottom: 3px solid #1976d2;
		padding-bottom: 6px;
	}


	/* ====== Table form fields ====== */
	table.admintable {
		width: 100%;
		border-collapse: collapse;
	}

	/*table.admintable tr td {
		padding: 8px 5px;
		vertical-align: top;
	}

	table.admintable td:first-child {
		width: 160px;
		font-weight: 600;
		color: #444;
		text-align: right;
		padding-right: 12px;
	}

	table.admintable td:last-child {
		text-align: left;
	}*/

	table.admintable label {
		font-size: 13px;
		font-weight: bold;
	}

	table.admintable td:not(.day) {
		font-size: 13px;
		padding: 8px 5px;
		vertical-align: top;
	}

	/* ====== Input, textarea, select styling ====== */
	table.admintable input[type="text"],
	table.admintable input[type="email"],
	table.admintable input[type="tel"],
	table.admintable input[type="number"],
	table.admintable textarea,
	table.admintable select {
		width: 100%;
		max-width: 350px;
		padding: 6px 10px;
		border: 1px solid #ccc;
		border-radius: 5px;
		font-size: 13px;
		line-height: 1.4em;
		background-color: #fafafa;
		transition: border-color 0.2s, background-color 0.2s;
	}

	/* Hover + focus effect */
	table.admintable input:focus,
	table.admintable textarea:focus,
	table.admintable select:focus {
		outline: none;
		border-color: #66afe9;
		background-color: #fff;
		box-shadow: 0 0 2px rgba(102, 175, 233, 0.6);
	}

	/* Textarea resize */
	table.admintable textarea {
		resize: vertical;
		height: 70px;
	}

	/* ====== Buttons ====== */
	button[type="button"] {
		background-color: #f5f5f5;
		border: 1px solid #ccc;
		border-radius: 4px;
		color: #333;
		padding: 6px 14px;
		font-size: 13px;
		cursor: pointer;
		margin-right: 5px;
		transition: background 0.2s;
	}

	button[type="button"]:hover {
		background-color: #e6e6e6;
	}

	/* ====== Files section ====== */
	#RMADownloadForm legend {
		margin-bottom: 10px;
	}

	.rma_detail_files_checkbox {
		border-top: 1px solid #ddd;
		margin-top: 10px;
		padding-top: 8px;
	}

	#RMADownloadForm {
		background: #fafafa;
	}

	/* ====== Notes (top area) ====== */
	.note-section {
		font-size: 13px;
		color: #555;
		margin-bottom: 10px;
		line-height: 1.5;
	}

	.note-section strong {
		color: #000;
	}

	.rma_detail_files_checkbox {
		display: grid;
		background: #fafafa;
		border: 1px solid #ddd;
		border-radius: 10px;
		padding: 16px;
	}

	.rma_detail_files_checkbox>div[style*="font-weight:bold"] {
		font-size: 16px !important;
		font-weight: 600 !important;
		color: #222;
		margin-bottom: 12px !important;
		border-bottom: 1px solid #eee;
		padding-bottom: 6px;
	}

	.rma_detail_files_checkbox>div {
		font-size: 14px;
		color: #333;
	}

	.rma_detail_files_checkbox input[type="checkbox"] {
		transform: scale(1.2);
		margin-right: 6px;
	}

	.rma_detail_files_checkbox a {
		color: #0073aa;
		text-decoration: none;
		font-weight: 500;
	}

	.rma_detail_files_checkbox a:hover {
		text-decoration: underline;
	}

	.rma_detail_files_checkbox span,
	.rma_detail_files_checkbox div {
		line-height: 1.6;
	}

	@media (max-width: 768px) {
		.rma_detail_files_checkbox {
			grid-template-columns: 1fr;
		}
	}


	/* ====== Responsive ====== */
	@media (max-width: 900px) {

		.col.width-55,
		.col.width-45 {
			width: 100%;
			float: none;
		}

		table.admintable td:first-child {
			text-align: left;
			padding-bottom: 4px;
		}

		table.admintable td {
			display: block;
			width: 100%;
		}
	}

	.field-calendar td,
	.field-calendar tr td {
		all: unset;
		display: table-cell;
	}

	.item-file {
		display: flex;
		align-items: center;
		gap: 8px;
		padding: 8px 12px;
		margin-bottom: 6px;
		border: 1px solid #ddd;
		border-radius: 6px;
		background: #fafafa;
		transition: all 0.2s ease-in-out;
	}

	.item-file:hover {
		background: #f0f8ff;
		border-color: #bbb;
	}

	.item-file input[type="checkbox"] {
		transform: scale(1.2);
		cursor: pointer;
	}

	.item-file .filename {
		font-size: 14px;
		color: #333;
		word-break: break-word;
		font-weight: bold;
	}
</style>
<div style="margin-bottom:20px; font-size:14px; color:#555; line-height:1.4;">
	Note : <br />
	1. Save => Save RMA Order and send notification to requestor<br />
	2. Apply => Save RMA Order and no notification send to requestor<br />
	3. Close => Operation Cancelled
</div>
<form action="index.php" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
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
						$helper = new AtelmanHelper();
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
						<div class="item-file">
							<input type="checkbox" name="files_rma_item_id[]" value="<?php echo $f->id ?>" />
							<span class="filename"><?php echo $f->filename; ?></span>
						</div>
					<?php } ?>
				<?php else : ?>
					No Files
				<?php endif; ?>
			</div>
		</fieldset>
	</div>
	<div class="clr"></div>
	<input type="hidden" id="task" name="task" value="" />
	<input type="hidden" name="view" value="rmaitem" />
	<input type="hidden" name="cid" value="<?php echo $this->cid ?>" />
	<input type="hidden" name="option" value="com_atelman" />
	<?php echo JHTML::_('form.token'); ?>
</form>