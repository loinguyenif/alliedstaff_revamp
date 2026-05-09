<?php

use Atelman\Component\Atelman\Administrator\Helper\AtelmanHelper;
use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Layout\LayoutHelper;
use \Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;


defined('_JEXEC') or die('Restricted access');
?>

<?php
$user = Factory::getUser();
?>
<script type="text/javascript">
	jQuery(function() {
		<?php if (
			$this->item->status == 'receive' ||
			$this->item->status == 'receive_close' ||
			$this->item->status == 'ship' ||
			$this->item->status == 'ship_close'
		) : ?>
			jQuery('#fileUploadStatus').css('display', '');
		<?php endif; ?>

		<?php if ($this->user->gid == 25 || $this->user->gid == 8 || $this->user->gid == 32 || $this->user->gid == 34 || ($this->user->gid == 31 && ($this->item->status == 'await' || $this->item->status == 'ship' || $this->item->status == 'receive'))) :  ?>
			jQuery('#status').on('change', function() {

				jQuery('#document_file').val('');
				if (this.value == 'receive' || this.value == 'receive_close' || this.value == 'ship' || this.value == 'ship_close') {
					jQuery('#fileUploadStatus').css('display', '');
				} else {
					jQuery('#fileUploadStatus').css('display', 'none');
				}

			});
		<?php endif; ?>

	});

	function fileForm(form, task) {
		jQuery('#' + form + ' input[name="task"]').val(task);
		jQuery('#' + form).submit();
	}

	function hideshowId(id) {
		var status = jQuery('#' + id).css('display');
		if (status == 'none') {
			jQuery('#' + id).css('display', 'block');
		} else {
			jQuery('#' + id).css('display', 'none');
		}
	}

	function print_popup(rma_id, rma_number) {

		var foo = new Date; // Generic JS date object
		var unixtime_ms = foo.getTime();

		var purl = 'index.php?option=com_atelman&task=rmaitems.ajax&r=' + unixtime_ms;

		jQuery.ajax({
			url: purl,
			type: "POST",
			cache: false,
			data: {
				section: "RMARequestPrint",
				rma_id: rma_id,
				rma_number: rma_number
			}
		}).done(function(data) {
			var win = window.open('', 'name' + unixtime_ms, 'height=500,width=500,scrollbars=yes,menubar=yes');
			win.document.write(data);
			setTimeout(function() {
				win.print();
			}, 500);
			if (window.focus) {
				win.focus();
			}
		});

		return false;

	}


	function files_checkbox(value) {

		if (value) {
			jQuery('.rma_detail_files_checkbox input[type="checkbox"]').prop('checked', true);
		} else {
			jQuery('.rma_detail_files_checkbox input[type="checkbox"]').prop('checked', false);
		}

	}
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

	/* table.admintable tr:not(.daysrow) td:not(.day) {
		padding: 8px 5px;
		vertical-align: top;
	}

	table.admintable td:first-child:not(.day) {
		width: 160px;
		font-weight: 600;
		color: #444;
		text-align: right;
		padding-right: 12px;
	}

	table.admintable td:last-child:not(.day) {
		text-align: left;
	} */

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
		/* hoặc reset lại để tránh kế thừa */
		display: table-cell;
		/* giữ layout bảng RMARequestFormAdminForm */
	}
</style>
<?php if ($this->user->gid == 25 || $this->user->gid == 8 || $this->user->gid == 34) :  ?>
	<div style="margin-bottom:20px; font-size:14px;">
		Note : <br />
		1. Save => Save RMA Order and send notification to requestor<br />
		2. Apply => Save RMA Order and no notification send to requestor<br />
		3. Close => Operation Cancelled
	</div>
<?php endif; ?>
<div style="margin-bottom:20px;">
	<button type="button" href="javascript:void(0);" onclick="javascript:print_popup(<?php echo $this->item->rma_id ?>,'<?php echo $this->item->rmacode ?>');">Print RMA Request</button>
</div>

<div class="col width-55">
	<form action="index.php" method="post" name="adminForm" enctype="multipart/form-data" id="adminForm">
		<fieldset class="adminform">
			<legend><?php echo JText::_("Requestor's Details") ?></legend>
			<table class="admintable">
				<tr>
					<td width="200px" class="key"><label for="title"><?php echo JText::_('Company'); ?>:</label></td>
					<td>
						<?php if ($this->user->gid == 25 || $this->user->gid == 8 || $this->user->gid == 32 || $this->user->gid == 34) :  ?>
							<input name="fullname" class="inputbox" type="text" size="34" value="<?php echo $this->item->fullname ?>" />
						<?php else : ?>
							<?php echo (($this->item->fullname) ? $this->item->fullname : '-'); ?>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<td width="200px" class="key"><label for="title"><?php echo JText::_('Contact'); ?>:</label></td>
					<td>
						<?php if ($this->user->gid == 25 || $this->user->gid == 8 || $this->user->gid == 32 || $this->user->gid == 34) :  ?>
							<input name="contact_name" class="inputbox" type="text" size="34" value="<?php echo $this->item->contact_name ?>" />
						<?php else : ?>
							<?php echo (($this->item->contact_name) ? $this->item->contact_name : '-'); ?>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<td width="200px" class="key" valign="top"><label for="title"><?php echo JText::_('Address'); ?>:</label></td>
					<td>
						<?php if ($this->user->gid == 25 || $this->user->gid == 8 || $this->user->gid == 32 || $this->user->gid == 34) :  ?>
							<textarea name="address" class="inputbox" style="height:100px;"><?php echo $this->item->address ?></textarea>
						<?php else : ?>
							<?php echo (($this->item->address) ? nl2br($this->item->address) : '-'); ?>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<td width="200px" class="key" valign="top"><label for="title"><?php echo JText::_('City'); ?>:</label></td>
					<td>
						<?php if ($this->user->gid == 25 || $this->user->gid == 8 || $this->user->gid == 32 || $this->user->gid == 34) :  ?>
							<input name="city" class="inputbox" type="text" size="34" value="<?php echo $this->item->city ?>" />
						<?php else : ?>
							<?php echo (($this->item->city) ? $this->item->city : '-'); ?>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<td width="200px" class="key" valign="top"><label for="title"><?php echo JText::_('State/Province'); ?>:</label></td>
					<td>
						<?php if ($this->user->gid == 25 || $this->user->gid == 8 || $this->user->gid == 32 || $this->user->gid == 34) :  ?>
							<input name="state" class="inputbox" type="text" size="34" value="<?php echo $this->item->state ?>" />
						<?php else : ?>
							<?php echo (($this->item->state) ? $this->item->state : '-'); ?>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<td width="200px" class="key" valign="top"><label for="title"><?php echo JText::_('ZIP/Postal Code'); ?>:</label></td>
					<td>
						<?php if ($this->user->gid == 25 || $this->user->gid == 8 || $this->user->gid == 32 || $this->user->gid == 34) :  ?>
							<input name="postal_code" class="inputbox" type="text" size="34" value="<?php echo $this->item->postal_code ?>" />
						<?php else : ?>
							<?php echo (($this->item->postal_code) ? $this->item->postal_code : '-'); ?>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<td width="200px" class="key" valign="top"><label for="title"><?php echo JText::_('Country/Region'); ?>:</label></td>
					<td>
						<?php if ($this->user->gid == 25 || $this->user->gid == 8 || $this->user->gid == 32 || $this->user->gid == 34) :  ?>
							<input name="country" class="inputbox" type="text" size="34" value="<?php echo $this->item->country ?>" />
						<?php else : ?>
							<?php echo (($this->item->country) ? $this->item->country : '-'); ?>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<td width="200px" class="key"><label for="title"><?php echo JText::_('Telephone'); ?>:</label></td>
					<td>
						<?php if ($this->user->gid == 25 || $this->user->gid == 8 || $this->user->gid == 32 || $this->user->gid == 34) :  ?>
							<input name="telephone" class="inputbox" type="text" size="34" value="<?php echo $this->item->telephone ?>" />
						<?php else : ?>
							<?php echo (($this->item->telephone) ? $this->item->telephone : '-'); ?>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<td width="200px" class="key"><label for="title"><?php echo JText::_('Fax'); ?>:</label></td>
					<td>
						<?php if ($this->user->gid == 25 || $this->user->gid == 8 || $this->user->gid == 32 || $this->user->gid == 34) :  ?>
							<input name="fax" class="inputbox" type="text" size="34" value="<?php echo $this->item->fax ?>" />
						<?php else : ?>
							<?php echo (($this->item->fax) ? $this->item->fax : '-'); ?>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<td width="200px" class="key" valign="top"><label for="title"><?php echo JText::_('Email'); ?>:</label></td>
					<td>
						<?php if ($this->user->gid == 25 || $this->user->gid == 8 || $this->user->gid == 32 || $this->user->gid == 34) :  ?>
							<textarea name="email" style="height:100px;"><?php echo $this->item->email ?></textarea><br />
							Separate email addresses with a semicolon or semi-colon (;)<br />
							(e.g. 'username1@domain.com; username2@domain.com')
						<?php else : ?>
							<?php echo (($this->item->email) ? $this->item->email : '-'); ?>
						<?php endif; ?>

					</td>
				</tr>
			</table>
			<?php if ($this->user->gid == 25 || $this->user->gid == 8 || $this->user->gid == 32 || $this->user->gid == 34) :  ?>
				<input type="hidden" name="rma_request_id" value="<?php echo $this->item->rma_request_id ?>" />
			<?php endif; ?>
		</fieldset>
		<fieldset class="adminform">
			<?php if ($this->layout == 'edit') : ?>
				<legend><?php echo JText::_('COM_ATELMAN_RMA_REQUEST_RMA_DETAIL') ?></legend>
			<?php else : ?>
				<legend><?php echo JText::_('Add RMA') ?></legend>
			<?php endif; ?>

			<table class="admintable">
				<tr>
					<td colspan="2">
						<b>Note :</b><br />
						1. If the file not appear on Right Side, please check you file extension.<br />
						2. These files are based on "Status"
					</td>
				</tr>
				<tr>
					<td class="key" width="150px">
						<label for="title">
							<?php echo JText::_('COM_ATELMAN_FIELD_CUSTOMER_ID'); ?>:
						</label>
					</td>
					<td>
						<?php echo $this->item->customer_id ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="title">
							<?php echo JText::_('COM_ATELMAN_FIELD_CUSTOMER_NAME'); ?>:
						</label>
					</td>
					<td>
						<?php echo $this->item->customer_name ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="title">
							<?php echo JText::_('COM_ATELMAN_PRODUCT_NO'); ?>:
						</label>
					</td>
					<td>
						<?php echo $this->item->product_no ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="title">
							<?php echo JText::_('COM_ATELMAN_MODEL_NO'); ?>:
						</label>
					</td>
					<td>
						<?php echo $this->item->model_no ?>
					</td>
				</tr>
				<?php if ($this->user->gid == 25 || $this->user->gid == 8) :  ?>
					<tr>
						<td class="key">
							<label for="title">
								<?php echo JText::_('Update Original S/N'); ?>:
							</label>
						</td>
						<td style="position:relative;">
							<input class="inputbox" name="update_sn" type="text" size="34" value="" />
						</td>
					</tr>
				<?php endif; ?>
				<tr>
					<td class="key">
						<label for="title">
							<?php echo JText::_('COM_ATELMAN_SERIAL_NO'); ?>:
						</label>
					</td>
					<td>
						<?php echo (($this->item->serial_no) ? $this->item->serial_no : '-'); ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="title">
							<?php echo JText::_('COM_ATELMAN_REQUESTED_SERIAL_NO'); ?>:
						</label>
					</td>
					<td>
						<?php if ($this->user->gid == 25 || $this->user->gid == 8 || $this->user->gid == 32 || $this->user->gid == 34) :  ?>
							<input name="requested_sn" class="inputbox" type="text" size="34" value="<?php echo $this->item->requested_sn ?>" />
						<?php else : ?>
							<?php echo (($this->item->requested_sn) ? $this->item->requested_sn : '-'); ?>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="title">
							<?php echo JText::_('COM_ATELMAN_SERIAL_NO_2'); ?>:
						</label>
					</td>
					<td>
						<?php if ($this->user->gid == 25 || $this->user->gid == 8 || $this->user->gid == 32 || $this->user->gid == 34) :  ?>
							<input name="replacement_sn" class="inputbox" type="text" size="34" value="<?php echo $this->item->replacement_sn ?>" />
						<?php else : ?>
							<?php echo (($this->item->replacement_sn) ? $this->item->replacement_sn : '-'); ?>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<td class="key" valign="top">
						<label for="title">
							<?php echo JText::_('COM_ATELMAN_REPLACEMENT_PART_NUMBER'); ?>:
						</label>
					</td>
					<td>
						<?php if ($this->user->gid == 25 || $this->user->gid == 8 || $this->user->gid == 32 || $this->user->gid == 34) :  ?>
							<input name="replacement_pn" class="inputbox" type="text" size="34" value="<?php echo $this->item->replacement_pn ?>" /><br />
							*If Replacement P/N is not exist in Product DB, value will be empty
						<?php else : ?>
							<?php echo (($this->item->replacement_pn) ? $this->item->replacement_pn : '-'); ?>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="title">
							<?php echo JText::_('COM_ATELMAN_SO_NO'); ?>:
						</label>
					</td>
					<td>
						<?php echo (($this->item->so_no) ? $this->item->so_no : '-'); ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="title">
							<?php echo JText::_('COM_ATELMAN_INVOICE_NO'); ?>:
						</label>
					</td>
					<td>
						<?php echo (($this->item->invoice_no) ? $this->item->invoice_no : '-'); ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="title">
							<?php echo JText::_('COM_ATELMAN_REQUEST_DATE'); ?>:
						</label>
					</td>
					<td>
						<?php echo $this->item->rma_request_date ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="title">
							<?php echo JText::_('COM_ATELMAN_EXPIRY_DATE'); ?>:
						</label>
					</td>
					<td>
						<?php //echo $this->item->real_expired_date 
						?>
					</td>
				</tr>
				<tr>
					<td class="key" valign="top">
						<label for="title">
							<?php echo JText::_('COM_ATELMAN_FIELD_FAULT_DESCRIPTION'); ?>:
						</label>
					</td>
					<td>
						<?php if ($this->user->gid == 25 || $this->user->gid == 8 || $this->user->gid == 32 || $this->user->gid == 34) :  ?>
							<textarea name="description" rows="10" cols="33"><?php echo $this->item->description ?></textarea>
						<?php else : ?>
							<?php echo $this->item->description ?>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<td class="key" valign="top">
						<label for="title">
							<?php echo JText::_('Remarks'); ?>:
						</label>
					</td>
					<td>

						<?php if ($this->user->gid == 25 || $this->user->gid == 8 || $this->user->gid == 32 || $this->user->gid == 34) : ?>
							<textarea name="remarks" rows="10" cols="33"><?php echo $this->item->remarks ?></textarea>
						<?php else : ?>
							<?php echo $this->item->remarks ?>
						<?php endif; ?>
					</td>
				</tr>

				<tr>
					<td class="key">
						<label for="title">
							<?php echo JText::_('Previous RMA Number'); ?>:
						</label>
					</td>
					<td>
						<?php echo isset($this->item->previous_rma_number) ? $this->item->previous_rma_number : 'N/A'; ?>
					</td>
				</tr>
				<?php if ($this->user->gid == 25 || $this->user->gid == 8 || $this->user->gid == 32 || $this->user->gid == 34) : ?>
					<tr>
						<td class="key">
							<label for="title">
								<?php echo JText::_('RMA Request'); ?>:
							</label>
						</td>
						<td>
							<input type="file" class="rma_request_file" name="rma_request_file" value="" /><br />
						</td>
					</tr>
				<?php endif; ?>
				<tr>
					<td class="key">
						<label for="title">
							<?php echo JText::_('COM_ATELMAN_RMA_NO'); ?>:
						</label>
					</td>
					<td>

						<?php if ($this->user->gid == 25 || $this->user->gid == 8 || $this->user->gid == 32 || $this->user->gid == 34) : ?>
							<input class="inputbox" type="text" name="rmacode" id="title" size="60" value="<?php echo $this->item->rmacode ?>" />
						<?php else : ?>
							<?php echo $this->item->rmacode ?>
						<?php endif; ?>
					</td>
				</tr>
				<?php if ($this->user->gid == 25 || $this->user->gid == 8 || $this->user->gid == 32 || $this->user->gid == 34) : ?>
					<tr>
						<td class="key">
							<label for="title">
								<?php echo JText::_('RMA Order'); ?>:
							</label>
						</td>
						<td>
							<input type="file" class="rma_order_file" name="rma_order_file" value="" /><br />
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
						<?php echo $this->item->status_name; ?>
					</td>
				</tr>
				<?php if ($this->user->gid == 25 || $this->user->gid == 8 || $this->user->gid == 32 || $this->user->gid == 31 || $this->user->gid == 34) : ?>
					<tr>
						<td class="key">
							<label for="title">
								<?php echo JText::_('Status (New)'); ?>:
							</label>
						</td>
						<td>
							<?php echo $this->statusesHTML ?>
						</td>
					</tr>
				<?php endif; ?>

				<?php if ($this->user->gid == 25 || $this->user->gid == 8 || $this->user->gid == 32 || $this->user->gid == 31 || $this->user->gid == 34) : ?>
					<tr id="fileUploadStatus" style="display:none;">
						<td class="key" valign="top">
							<label for="title">
								<?php echo JText::_('Files'); ?>:
							</label>
						</td>
						<td>
							<input type="file" class="document_file" name="document_file[0]" value="" /><br />
							<input type="checkbox" name="is_airway_bill[0]" value="1" />Is this file airway bill?<br />
							<br />
							<input type="file" class="document_file" name="document_file[1]" value="" /><br />
							<input type="checkbox" name="is_airway_bill[1]" value="1" />Is this file airway bill?<br />
							<br />
							<input type="file" class="document_file" name="document_file[2]" value="" /><br />
							<input type="checkbox" name="is_airway_bill[2]" value="1" />Is this file airway bill?<br />
							<br />
							<input type="file" class="document_file" name="document_file[3]" value="" /><br />
							<input type="checkbox" name="is_airway_bill[3]" value="1" />Is this file airway bill?<br />
							<br />
							<input type="file" class="document_file" name="document_file[4]" value="" /><br />
							<input type="checkbox" name="is_airway_bill[4]" value="1" />Is this file airway bill?<br />
							<br />
							*** Extensions *** : <br />
							<b>.pdf, .jpg, .gif, .png</b>
						</td>
					</tr>
				<?php endif; ?>
				<tr>
					<td class="key">
						<label for="title">
							<?php echo JText::_('COM_ATELMAN_FIELD_SHIPPING_DURATION_FOR_REPLACEMENT'); ?>:
						</label>
					</td>
					<td>
						<?php if ($this->user->gid == 25 || $this->user->gid == 8 || $this->user->gid == 32 || $this->user->gid == 34) : ?>
							<input class="inputbox" type="text" name="shipping_duration" id="title" size="60" value="<?php echo $this->item->shipping_duration ?>" />
						<?php else : ?>
							<?php echo $this->item->shipping_duration ?>
						<?php endif; ?>

					</td>
				</tr>
				<tr>
					<td class="key" valign="top">
						<label for="title">
							<?php echo JText::_('COM_ATELMAN_FIELD_REPLACEMENT_DATE'); ?>:
						</label>
					</td>
					<td>
						<?php if ($this->user->gid == 25 || $this->user->gid == 8 || $this->user->gid == 32 || $this->user->gid == 34) : ?>
							<?php echo $this->lists['replacement_date']; ?>
						<?php else : ?>
							<?php echo ($this->item->replacement_date == '0000-00-00') ? 'N/A' : date("d/m/Y", strtotime($this->item->replacement_date)); ?>
							<input type="hidden" name="replacement_date" value="<?php echo ($this->item->replacement_date == '0000-00-00') ? '' : date("d/m/Y", strtotime($this->item->replacement_date)); ?>" />
						<?php endif; ?>

					</td>
				</tr>
				<?php /*<tr>
					<td class="key" valign="top">
						<label for="title">
							<?php echo JText::_( 'COM_ATELMAN_FIELD_RMA_ASSIGNED_DATE' ); ?>:
						</label>
					</td>
					<td>
						<?php if($this->user->gid == 25 || $this->user->gid ==8 || $this->user->gid == 32 ) : ?>
						<?php echo $this->lists['rma_assigned_date']; ?>
						<?php else : ?>
						<?php echo ($this->item->rma_assigned_date == '0000-00-00')?'N/A':date("d/m/Y",strtotime($this->item->rma_assigned_date)); ?>
						<?php endif; ?>
						
					</td>
				</tr>*/ ?>
				<tr>
					<td class="key" valign="top">
						<label for="title">
							<?php echo JText::_('COM_ATELMAN_FIELD_RECEIVE_DATE'); ?>:
						</label>
					</td>
					<td>
						<?php if ($this->user->gid == 25 || $this->user->gid == 8 || $this->user->gid == 32 || $this->user->gid == 34) : ?>
							<?php echo $this->lists['received_date']; ?>
						<?php else : ?>
							<?php echo ($this->item->received_date == '0000-00-00') ? 'N/A' : date("d/m/Y", strtotime($this->item->received_date)); ?>
							<input type="hidden" name="received_date" value="<?php echo ($this->item->received_date == '0000-00-00') ? '' : date("d/m/Y", strtotime($this->item->received_date)); ?>" />
						<?php endif; ?>

					</td>
				</tr>
				<tr>
					<td class="key" valign="top">
						<label for="title">
							<?php echo JText::_('COM_ATELMAN_FIELD_SHIP_DATE'); ?>:
						</label>
					</td>
					<td>
						<?php if ($this->user->gid == 25 || $this->user->gid == 8 || $this->user->gid == 32 || $this->user->gid == 34) : ?>
							<?php echo $this->lists['shipped_date']; ?>
						<?php else : ?>
							<?php echo ($this->item->shipped_date == '0000-00-00') ? 'N/A' : date("d/m/Y", strtotime($this->item->shipped_date)); ?>
							<input type="hidden" name="shipped_date" value="<?php echo ($this->item->shipped_date == '0000-00-00') ? '' : date("d/m/Y", strtotime($this->item->shipped_date)); ?>" />
						<?php endif; ?>

					</td>
				</tr>
				<tr>
					<td class="key" valign="top">
						<label for="title">
							<?php echo JText::_('COM_ATELMAN_FIELD_CLOSE_DATE'); ?>:
						</label>
					</td>
					<td>
						<?php if ($this->user->gid == 25 || $this->user->gid == 8 || $this->user->gid == 32 || $this->user->gid == 34) : ?>
							<?php echo $this->lists['closed_date']; ?>
						<?php else : ?>
							<?php echo ($this->item->closed_date == '0000-00-00') ? 'N/A' : date("d/m/Y", strtotime($this->item->closed_date)); ?>
						<?php endif; ?>

					</td>
				</tr>
			</table>
		</fieldset>
		<input type="hidden" name="warranty_status" value="<?php echo $this->item->warranty_status ?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="view" value="rmarequest" />
		<input type="hidden" name="cid" value="<?php echo $this->item->rma_id ?>" />
		<input type="hidden" id="warranty_item_id" name="warranty_item_id" value="<?php echo $this->item->warranty_item_id ?>" />
		<input type="hidden" name="option" value="com_atelman" />
		<?php echo JHTML::_('form.token'); ?>
	</form>
</div>
<div class="col width-45">
	<form action="index.php" method="post" id="RMADownloadForm" name="RMADownloadForm" enctype="multipart/form-data" style="position:relative;">
		<fieldset class="adminform">
			<legend><?php echo JText::_(' Files ') ?></legend>
			<?php if ($user->gid == 25 || $this->user->gid == 23 || $this->user->gid == 24 || $this->user->gid == 32 || $this->user->gid == 31 || $this->user->gid == 34 || $this->user->gid == 35) : ?>
				<?php if ($user->gid == 25 || $user->gid == 34) { ?>
					<button type="button" onclick="javascript:fileForm('RMADownloadForm', 'rmaitems.delete_files');">Delete</button>
				<?php } ?>
				<button type="button" onclick="javascript:fileForm('RMADownloadForm', 'rmaitems.prints');">Print</button>
				<button type="button" onclick="javascript:hideshowId('emailRMARequestForm')">Email</button>
				<br /><br />
			<?php endif; ?>
			<div style="margin-bottom:10px;">
				<input type="checkbox" value="1" onclick="javascript:files_checkbox(this.checked);" /> Select / Deselect all
			</div>
			<div class="rma_detail_files_checkbox">
				<?php if (!empty($this->downloads) || !empty($this->rmas)) : ?>

					<?php
					// DOWNLOADS
					foreach ($this->downloads as $key => $value) :
						// Airway bill can be seen by any group
						// Internal Docs cannot be seen by Distributor / Manager 
						if (!$value['is_awb']) {
							if ($this->user->gid == 23 || $this->user->gid == 24) :
								continue;
							endif;
						}
					?>
						<div style="margin-bottom:10px;font-weight:bold;font-size:16px;"><?php echo $value['title'] ?></div>
						<div style="margin-bottom:20px;">
							<?php
							foreach ($value['object_file'] as $obj) :
								$tmp = explode('|', $obj);
								$id = $tmp[0];
								$timestamp = $tmp[1];
								$filename = $tmp[2];

								$download_link = 'index.php?option=com_atelman&task=rmaitems.download&action=download&cid=' . $id;
							?>

								<?php if ($user->gid == 25 || $this->user->gid == 23 || $this->user->gid == 24 || $this->user->gid == 32 || $this->user->gid == 31  || $this->user->gid == 34 || $this->user->gid == 35) : ?>
									<div style="float:left;width:20px;margin-right:5px;"><input type="checkbox" value="<?php echo $id ?>" name="file_id[]" /></div>
								<?php endif; ?>

								<div style="float:left;width:120px;"><?php echo $timestamp ?></div>
								<div style="float:left;margin-left:10px;width:400px;"><a href="<?php echo $download_link ?>"><?php echo $filename ?></a></div>
								<div class="clr" style="clear:both;"></div>
							<?php endforeach; ?>
						</div>
					<?php endforeach; ?>


					<?php
					// RMA ORDERS
					foreach ($this->rmas as $value) :
					?>
						<div style="margin-bottom:10px;font-weight:bold;font-size:16px;"><?php echo $value['title'] ?></div>
						<div style="margin-bottom:20px;">
							<?php
							foreach ($value['object_file'] as $obj) :
								$tmp = explode('|', $obj);
								$id = $tmp[0];
								$timestamp = $tmp[1];
								$filename = $tmp[2];

								$download_link = 'index.php?option=com_atelman&task=rmaitems.download&action=download&cid=' . $id;
							?>

								<?php if ($user->gid == 25 || $this->user->gid == 23 || $this->user->gid == 24 || $this->user->gid == 32 || $this->user->gid == 31  || $this->user->gid == 34 || $this->user->gid == 35) : ?>
									<div style="float:left;width:20px;margin-right:5px;"><input type="checkbox" value="<?php echo $id ?>" name="file_id[]" /></div>
								<?php endif; ?>

								<div style="float:left;width:120px;"><?php echo $timestamp ?></div>
								<div style="float:left;margin-left:10px;width:400px;"><a href="<?php echo $download_link ?>"><?php echo $filename ?></a></div>
								<div class="clr" style="clear:both;"></div>
							<?php endforeach; ?>
						</div>
					<?php endforeach; ?>


				<?php else : ?>
					<div>No Documents</div>
				<?php endif; ?>
			</div>
		</fieldset>
		<!-- print, email, delete -->
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="view" value="rmaitem" />
		<input type="hidden" name="cid" value="<?php echo $this->item->rma_id ?>" />
		<input type="hidden" name="option" value="com_atelman" />
		<?php echo JHTML::_('form.token'); ?>

		<div id="emailRMARequestForm" style="position:absolute;background:#fff;padding:20px;border:1px solid #ccc;top:0;right:0;display:none;width:320px;">
			<div style="position:absolute;top:10px;right:10px;"><a href="javascript:void(0);" onclick="javascript:$('emailRMARequestForm').setStyle('display','none');">X</a></div>
			Email : <input type="text" class="required email" name="recipients" value="" /><br /><br />
			&nbsp;<button type="button" onclick="javascript:fileForm('RMADownloadForm', 'rmaitems.emails');">Send</button><br /><br />
			Please separate with ';' to send more than 1 email(s)
		</div>
	</form>
</div>
<div class="clr"></div>