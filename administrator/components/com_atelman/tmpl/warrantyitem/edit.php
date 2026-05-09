<?php defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.formvalidator');
?>

<?php
$serialnotitle = '';
if ($this->item) {
	if (trim($this->item->serial_no_2)) {
		$serialnotitle = trim($this->item->serial_no_2);
	} else {
		if ($this->item->serial_no) {
			$serialnotitle = $this->item->serial_no;
		}
	}
}


if ($this->item) {
	JToolBarHelper::title(JText::_('View : ') . $this->item->product_no . ' :: S/No = ' . $serialnotitle, 'user.png');
} else {
	JToolBarHelper::title(JText::_('New Warranty Registration '), 'user.png');
}
// if ($this->user->gid == 25 || $this->user->gid ==8 || $this->user->gid == 34) : // admin and supervisor can write the file
// 	JToolBarHelper::save();
// 	JToolBarHelper::apply();
// endif;

$product_mainlink = JRoute::_('index.php?option=com_atelman&view=products');
?>

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

	.col.width-70 {
		width: 70%;
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
		margin-bottom: 30px;
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
		font-size: 14px;
		font-weight: bold;
	}

	table.admintable td:not(.day) {
		font-size: 14px;
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

<script language="javascript" type="text/javascript">
	document.addEventListener('DOMContentLoaded', function() {
		if (window.Joomla && Joomla.FormValidator) {
			Joomla.FormValidator.addRule('dates', function(value) {
				const regex = /\b(0?[1-9]|[12][0-9]|3[01])\/(0?[1-9]|1[012])\/\d{4}\b/;
				return regex.test(value);
			}, 'Please enter a valid date (dd/mm/yyyy)');
		}
	});


	Joomla = window.Joomla || {};

	function submitbutton(pressbutton) {
		var form = document.adminForm;

		if (pressbutton === 'cancelemployer') {
			form.task.value = pressbutton;
			form.submit();
			return;
		}

		// Sử dụng validator mới của Joomla 5
		if (Joomla && Joomla.FormValidator && Joomla.FormValidator.isValid(form)) {
			form.task.value = pressbutton;
			form.submit();
		} else {
			alert('<?php echo JText::_('Please correct the fields below : Date Format'); ?>');
		}
	}
	//-->

	/*function setExpiryDateForThisProduct(inputx, value) {
		if(!value) {
			inputx.setProperty('value','<?php echo $this->lists['warranty_based_on_product'] ?>');
		}
	}*/
</script>
<style type="text/css">
	table tr td {
		vertical-align: top;
	}

	input.invalid {
		border: 1px solid #ff0000 !important;
	}
</style>

<form action="index.php" method="post" name="adminForm" id="adminForm" class="form-validate">
	<div class="col width-70">
		<?php if ($this->item): ?>
			<fieldset class="adminform">
				<legend><?php echo JText::_(' Warranty Registrant ') ?></legend>
				<table class="admintable">
					<tr>
						<td width="200px" class="key"><label for="title"><?php echo JText::_('First Name'); ?>:</label></td>
						<td><?php echo ($this->item->first_name) ? $this->item->first_name : 'N/A' ?></td>
					</tr>
					<tr>
						<td width="200px" class="key"><label for="title"><?php echo JText::_('Last Name'); ?>:</label></td>
						<td><?php echo $this->item->last_name ? $this->item->last_name : 'N/A' ?></td>
					</tr>
					<tr>
						<td width="200px" class="key"><label for="title"><?php echo JText::_('Address'); ?>:</label></td>
						<td><?php echo $this->item->address ? $this->item->address : 'N/A' ?></td>
					</tr>
					<tr>
						<td width="200px" class="key"><label for="title"><?php echo JText::_('City'); ?>:</label></td>
						<td><?php echo $this->item->city ? $this->item->city : 'N/A' ?></td>
					</tr>
					<tr>
						<td width="200px" class="key"><label for="title"><?php echo JText::_('Postal Code'); ?>:</label></td>
						<td><?php echo $this->item->postal_code ? $this->item->postal_code : 'N/A' ?></td>
					</tr>
					<tr>
						<td width="200px" class="key"><label for="title"><?php echo JText::_('Country'); ?>:</label></td>
						<td><?php echo $this->item->country_name_3 ? $this->item->country_name_3 : 'N/A' ?></td>
					</tr>
					<tr>
						<td width="200px" class="key"><label for="title"><?php echo JText::_('Telephone'); ?>:</label></td>
						<td><?php echo $this->item->telephone ? $this->item->telephone : 'N/A' ?></td>
					</tr>
					<tr>
						<td width="200px" class="key"><label for="title"><?php echo JText::_('Fax'); ?>:</label></td>
						<td><?php echo $this->item->fax ? $this->item->fax : 'N/A' ?></td>
					</tr>
					<tr>
						<td width="200px" class="key"><label for="title"><?php echo JText::_('Email'); ?>:</label></td>
						<td><?php echo $this->item->email ? $this->item->email : 'N/A' ?></td>
					</tr>
					<tr>
						<td width="200px" class="key"><label for="title"><?php echo JText::_('Company Name'); ?>:</label></td>
						<td><?php echo $this->item->company_name ? $this->item->company_name : 'N/A' ?></td>
					</tr>
					<tr>
						<td width="200px" class="key"><label for="title"><?php echo JText::_('Job Title'); ?>:</label></td>
						<td><?php echo $this->item->job_title ? $this->item->job_title : 'N/A' ?></td>
					</tr>
				</table>
			</fieldset>
		<?php endif; ?>
		<fieldset class="adminform">
			<?php if ($this->item): ?>
				<legend><?php echo JText::_('View : ') . $this->item->product_no . ' :: S/No = ' . $this->item->serial_no ?></legend>
			<?php else : ?>
				<legend><?php echo JText::_('New Warranty Registration ') ?></legend>
			<?php endif; ?>
			<table class="admintable">
				<tr>
					<td width="200px" class="key">
						<label for="title">
							<?php echo JText::_('COM_ATELMAN_PRODUCT_NO') ?>:
						</label>
					</td>
					<td>
						<?php //echo $this->item->product_no 
						?>
						<?php echo $this->lists['product_no'] ?><br />
						<?php if ($this->user->gid == 25 || $this->user->gid == 8 || $this->user->gid == 34) : ?>
							* Part Number must be exist in <a href="<?php echo $product_mainlink ?>" target="_blank">Product</a>. Otherwise, it will not save this field.
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<td width="200px" class="key">
						<label for="title">
							<?php echo JText::_('COM_ATELMAN_MODEL_NO') ?>:
						</label>
					</td>
					<td>
						<?php echo $this->lists['model_no'] ?><br />
						<!--* Model Number is linked to Part Number.-->
					</td>
				</tr>
				<tr>
					<td width="200px" class="key">
						<label for="title">
							<?php echo JText::_('COM_ATELMAN_SERIAL_NO') ?>:
						</label>
					</td>
					<td>
						<?php echo $this->lists['serial_no'] ?>
					</td>
				</tr>
				<tr>
					<td width="200px" class="key">
						<label for="title">
							<?php echo JText::_('COM_ATELMAN_SERIAL_NO_2') ?>:
						</label>
					</td>
					<td>
						<?php echo $this->lists['serial_no_2'] ?><br />
						<?php if ($this->user->gid == 25 || $this->user->gid == 8 || $this->user->gid == 34) : ?>
							* If this exist, <b>Serial Number</b> (Above) will be ignored and not searchable.
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<td width="200px" class="key">
						<label for="title">
							<?php echo JText::_('COM_ATELMAN_REPLACEMENT_PART_NUMBER') ?>:
						</label>
					</td>
					<td>
						<?php echo $this->lists['replacement_pn'] ?><br />
						<?php if ($this->user->gid == 25 || $this->user->gid == 8 || $this->user->gid == 34) : ?>
							*If Replacement P/N is not exist in Product DB, value will be empty
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<td width="200px" class="key">
						<label for="title">
							<?php echo JText::_('COM_ATELMAN_CUSTOMER_NO') ?>:
						</label>
					</td>
					<td>
						<?php echo $this->lists['customer_id'] ?>
						<?php if (@$this->item->customer_id) : ?>
							&nbsp;<a href="index.php?option=com_users&view=user&task=edit&cid[]=<?php echo $this->item->customer_user_id ?>">Edit <?php echo $this->item->customer_name ?></a>
						<?php endif; ?>
						<br />
						<?php if ($this->user->gid == 25 || $this->user->gid == 8 || $this->user->gid == 34) : ?>
							* This Customer links to <a href="<?php echo JRoute::_('index.php?option=com_users'); ?>">User Management Customer ID</a>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<td width="200px" class="key">
						<label for="title">
							<?php echo JText::_('COM_ATELMAN_PO_NO') ?>:
						</label>
					</td>
					<td>
						<?php echo $this->lists['po_no'] ?>
					</td>
				</tr>
				<tr>
					<td width="200px" class="key">
						<label for="title">
							<?php echo JText::_('COM_ATELMAN_SO_NO') ?>:
						</label>
					</td>
					<td>
						<?php echo $this->lists['so_no'] ?>
					</td>
				</tr>
				<tr>
					<td width="200px" class="key">
						<label for="title">
							<?php echo JText::_('COM_ATELMAN_INVOICE_NO') ?>:
						</label>
					</td>
					<td>
						<?php echo $this->lists['invoice_no'] ?>
					</td>
				</tr>
				<tr>
					<td width="200px" class="key">
						<label for="title">
							<?php echo JText::_('Purchase Date'); ?>:
						</label>
					</td>
					<td>
						<?php echo $this->lists['purchase_date'] ?>
						<?php //echo date("d-m-Y",strtotime($this->item->purchase_date)) 
						?>
					</td>
				</tr>
				<tr>
					<td width="200px" class="key">
						<label for="title">
							<?php echo JText::_('Expiry Date'); ?>:
						</label>
					</td>
					<td>
						<?php echo $this->lists['warranty_based_on_product'] ?>
					</td>
				</tr>
				<tr>
					<td width="200px" class="key">
						<label for="title">
							<?php echo JText::_('Expiry Date (Manual) '); ?>:
						</label>
					</td>
					<td>
						<?php echo $this->lists['expired_date'] ?>
					</td>
				</tr>
				<tr>
					<td width="200px" class="key">
						<label for="title">
							<?php echo JText::_('Extended Warranty (Months)'); ?>:
						</label>
					</td>
					<td>
						<?php echo $this->lists['extended_warranty'] ?>
					</td>
				</tr>
				<?php if ($this->user->gid != 25 && $this->user->gid != 34) : ?>
					<tr>
						<td width="200px" class="key">
							<label for="title">
								<?php echo JText::_('Extended Warranty Expiry Date'); ?>:
							</label>
						</td>
						<td>
							<?php echo $this->lists['extended_warranty_date'] ?><br />
						</td>
					</tr>
				<?php endif; ?>
				<tr>
					<td width="200px" class="key">
						<label for="title">
							<?php echo JText::_('Comments'); ?>:
						</label>
					</td>
					<td>
						<?php if ($this->user->gid == 25 || $this->user->gid == 8 || $this->user->gid == 34) : ?>
							<textarea name="comments"><?php echo @$this->item->comments ?></textarea>
						<?php else : ?>
							<?php echo (trim(@$this->item->comments)) ? @$this->item->comments : 'N/A' ?>
						<?php endif; ?>
					</td>
				</tr>
			</table>
		</fieldset>
	</div>
	<div class="clr"></div>
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="view" value="warrantyitem" />
	<input type="hidden" name="cid" value="<?php echo @$this->item->warranty_item_id ?>" />
	<input type="hidden" name="option" value="com_atelman" />
	<?php echo JHTML::_('form.token'); ?>
</form>