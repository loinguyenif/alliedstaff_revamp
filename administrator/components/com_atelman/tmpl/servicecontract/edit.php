<?php defined('_JEXEC') or die('Restricted access');

use Atelman\Component\Atelman\Administrator\Helper\AtelmanHelper;
use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Layout\LayoutHelper;
use \Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
?>

<?php



$user = Factory::getUser();

?>
<script type="text/javascript">
	function hideshowId(id) {
		var status = jQuery('#'.id).getStyle('display');
		if (status == 'none') {
			jQuery('#'.id).setStyle('display', 'block');
		} else {
			jQuery('#'.id).setStyle('display', 'none');
		}
	}

	function print_popup(service_contract_id, service_contract_number) {

		var foo = new Date; // Generic JS date object
		var unixtime_ms = foo.getTime();

		var purl = 'index.php?option=com_atelman&task=rmaitems.ajax&r=' + unixtime_ms;

		jQuery.ajax({
			url: purl,
			type: "POST",
			cache: false,
			data: {
				section: "ServiceContractPrint",
				s_id: service_contract_id,
				s_number: service_contract_number
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

	.col.width-70 {
		width: 70%;
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
	<div style="margin-bottom:20px;  font-size:14px; padding-left:15px">
		Note : <br />
		1. Save => Save Service Contract and go back to Service Contract Listing<br />
		2. Apply => Save Service Contract and stay in the page<br />
		3. Close => Operation Cancelled
	</div>
<?php endif; ?>
<div style="margin-bottom:20px; padding-left:15px">
	<button type="button" href="javascript:void(0);" onclick="javascript:print_popup(<?php echo $this->item->id ?>,'<?php echo $this->item->service_contract_no ?>');">Print Service Contract : <?php echo $this->item->service_contract_no ?></button>
</div>

<div class="col width-70">
	<form action="index.php" method="post" name="adminForm" enctype="multipart/form-data" id="adminForm">
		<fieldset class="adminform">
			<legend><?php echo JText::_('Service Contract Detail') ?></legend>
			<table class="admintable">
				<tr>
					<td width="200px" class="key"><label for="title"><?php echo JText::_('Company'); ?>:</label></td>
					<td>
						<?php if ($user->gid == 25) : // Super Admin 
						?>
							<?php echo $this->companiesHTML; ?><br /><?php echo $this->item->distributor_name; ?>
						<?php else : ?>
							<?php echo $this->item->distributor_name; ?>
						<?php endif; ?>
					</td>
				</tr>

				<tr>
					<td width="200px" class="key" valign="top"><label for="title"><?php echo JText::_('Service Contract #'); ?>:</label></td>
					<td>
						<?php if ($this->user->gid == 25 || $this->user->gid == 8 || $this->user->gid == 32 || $this->user->gid == 34) :  ?>
							<input name="service_contract_no" type="text" class="inputbox" value="<?php echo $this->item->service_contract_no ?>" />
						<?php else : ?>
							<?php echo (($this->item->service_contract_no) ? $this->item->service_contract_no : '-'); ?>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<td width="200px" class="key" valign="top"><label for="title"><?php echo JText::_('Start Date'); ?>:</label></td>
					<td>
						<?php if ($this->user->gid == 25 || $this->user->gid == 8 || $this->user->gid == 32 || $this->user->gid == 34) :  ?>
							<?php echo $this->lists['start_date']; ?>
						<?php else : ?>
							<?php echo (($this->item->start_date) ? $this->item->start_date : '-'); ?>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<td width="200px" class="key" valign="top"><label for="title"><?php echo JText::_('Expiry Date'); ?>:</label></td>
					<td>
						<?php if ($this->user->gid == 25 || $this->user->gid == 8 || $this->user->gid == 32 || $this->user->gid == 34) :  ?>
							<?php echo $this->lists['expiry_date']; ?>
						<?php else : ?>
							<?php echo (($this->item->expiry_date) ? $this->item->expiry_date : '-'); ?>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<td width="200px" class="key" valign="top"><label for="title"><?php echo JText::_('PO Number'); ?>:</label></td>
					<td>
						<?php if ($this->user->gid == 25 || $this->user->gid == 8 || $this->user->gid == 32 || $this->user->gid == 34) :  ?>
							<input name="po_no" class="inputbox" type="text" size="34" value="<?php echo $this->item->po_no ?>" />
						<?php else : ?>
							<?php echo (($this->item->po_no) ? $this->item->po_no : '-'); ?>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<td width="200px" class="key" valign="top"><label for="title"><?php echo JText::_('Cover Length'); ?>:</label></td>
					<td>
						<?php if ($this->user->gid == 25 || $this->user->gid == 8 || $this->user->gid == 32 || $this->user->gid == 34) :  ?>
							<input name="cover_length" class="inputbox" type="text" size="34" value="<?php echo $this->item->cover_length ?>" />
						<?php else : ?>
							<?php echo (($this->item->cover_length) ? $this->item->cover_length : '-'); ?>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<td width="200px" class="key" valign="top"><label for="title"><?php echo JText::_('Service Type'); ?>:</label></td>
					<td>
						<?php if ($this->user->gid == 25 || $this->user->gid == 8 || $this->user->gid == 32 || $this->user->gid == 34) :  ?>
							<input name="service_type" class="inputbox" type="text" size="34" value="<?php echo $this->item->service_type ?>" />
						<?php else : ?>
							<?php echo (($this->item->service_type) ? $this->item->service_type : '-'); ?>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<td width="200px" class="key"><label for="title"><?php echo JText::_('Client Name'); ?>:</label></td>
					<td>
						<?php if ($this->user->gid == 25 || $this->user->gid == 8 || $this->user->gid == 32 || $this->user->gid == 34) :  ?>
							<input name="client_name" class="inputbox" type="text" size="34" value="<?php echo $this->item->client_name ?>" />
						<?php else : ?>
							<?php echo (($this->item->client_name) ? $this->item->client_name : '-'); ?>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<td width="200px" class="key"><label for="title"><?php echo JText::_('Remarks'); ?>:</label></td>
					<td>
						<?php if ($this->user->gid == 25 || $this->user->gid == 8 || $this->user->gid == 32 || $this->user->gid == 34) :  ?>
							<textarea name="remarks" class="inputbox"><?php echo $this->item->remarks ?></textarea>
						<?php else : ?>
							<?php echo (($this->item->remarks) ? $this->item->remarks : '-'); ?>
						<?php endif; ?>
					</td>
				</tr>
			</table>

		</fieldset>
		<fieldset class="adminform">
			<legend><?php echo JText::_('Product') ?></legend>

			<table class="admintable">
				<tr>
					<td colspan="2">
				<tr>
					<td width="200px" class="key"><label for="title"><?php echo JText::_('Serial No'); ?>:</label></td>
					<td>
						<?php if ($this->user->gid == 25 || $this->user->gid == 8 || $this->user->gid == 32 || $this->user->gid == 34) :  ?>
							<input name="serial_no" class="inputbox" type="text" size="34" value="<?php echo $this->item->serial_no ?>" /><br />
						<?php else : ?>
							<?php echo (($this->item->serial_no) ? $this->item->serial_no : '-'); ?>
						<?php endif; ?>
					</td>
				</tr>
				<td colspan="2">
					<tr>
						<td width="200px" class="key"><label for="title"><?php echo JText::_('Model No.'); ?>:</label></td>
						<td>
							<?php if ($this->user->gid == 25 || $this->user->gid == 8 || $this->user->gid == 32 || $this->user->gid == 34) :  ?>
								<input name="model_no" class="inputbox" type="text" size="34" value="<?php echo $this->item->model_no ?>" /><br />
							<?php else : ?>
								<?php echo (($this->item->model_no) ? $this->item->model_no : '-'); ?>
							<?php endif; ?>
						</td>
					</tr>
				</td>
				<td colspan="2">
					<tr>
						<td width="200px" class="key"><label for="title"><?php echo JText::_('Part No.'); ?>:</label></td>
						<td>
							<?php if ($this->user->gid == 25 || $this->user->gid == 8 || $this->user->gid == 32 || $this->user->gid == 34) :  ?>
								<input name="part_no" class="inputbox" type="text" size="34" value="<?php echo $this->item->part_no ?>" /><br />
							<?php else : ?>
								<?php echo (($this->item->part_no) ? $this->item->part_no : '-'); ?>
							<?php endif; ?>
						</td>
					</tr>
				</td>
				</tr>
			</table>
		</fieldset>
		<input type="hidden" name="service_contract_id" value="<?php echo $this->item->id ?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="view" value="servicecontract" />
		<input type="hidden" name="cid" value="<?php echo $this->item->service_contract_item_id ?>" />
		<input type="hidden" name="option" value="com_atelman" />
		<?php echo JHTML::_('form.token'); ?>
	</form>
</div>

<div class="clr"></div>