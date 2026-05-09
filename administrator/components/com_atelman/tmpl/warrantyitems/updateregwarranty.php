<?php defined('_JEXEC') or die('Restricted access'); ?>

<style>
	/* ===== RMA Import Form Styling ===== */
	fieldset.adminform {
		background: #fff;
		border: 1px solid #e0e0e0;
		border-radius: 8px;
		padding: 20px 25px;
		box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
		margin: 20px auto;
		font-size: 14px;
	}

	fieldset.adminform legend {
		font-size: 18px;
		font-weight: 600;
		color: #2c3e50;
	}

	fieldset.adminform .admintable {
		width: 100%;
		border-collapse: collapse;
	}

	fieldset.adminform .admintable td.key {
		width: 120px;
		font-weight: 500;
		color: #333;
		padding: 10px 0;
	}

	fieldset.adminform .admintable td {
		padding: 10px 0;
		vertical-align: middle;
	}

	fieldset.adminform input[type="file"] {
		padding: 6px;
		border: 1px solid #ccc;
		border-radius: 4px;
		background: #fff;
	}

	fieldset.adminform input[type="submit"],
	fieldset.adminform .btn-cancel {
		display: inline-block;
		padding: 8px 22px;
		border-radius: 6px;
		font-weight: 500;
		color: #fff;
		border: none;
		cursor: pointer;
		margin-right: 10px;
	}

	fieldset.adminform input[type="submit"] {
		background-color: #3b7354;
	}

	fieldset.adminform input[type="submit"]:hover {
		background-color: #2f5d44;
	}

	fieldset.adminform .btn-cancel {
		background-color: #345680;
		text-decoration: none;
	}

	fieldset.adminform .btn-cancel:hover {
		background-color: #2b486c;
	}

	fieldset.adminform .form-actions {
		border-top: 1px solid #f0f0f0;
		margin-top: 20px;
		padding-top: 15px;
	}
</style>
<form action="index.php" method="post" name="adminForm" class="form-validate" enctype="multipart/form-data">
	<div class="col width-70" style="width:100%">
		<fieldset class="adminform">
			<legend><?php echo JText::_(' Update Warranty Registration ') ?></legend>
			<div style="margin:0 0 10px 0;"><strong>CSV file format:</strong></div>
			<div>1<sup>st</sup> column is SO Number (this field will be ignored)</div>
			<div>2<sup>nd</sup> column is Serial Number (if does not match then skip this row)</div>
			<div>3<sup>rd</sup> column is Replacement S/N </div>
			<div>4<sup>th</sup> column is Replacement P/N (must exist in Product database)</div>
			<div>5<sup>th</sup> column is Customer ID </div>
			<div>6<sup>th</sup> column is Manual Expiry Date (DD/MM/YYYY) </div>
			<div>7<sup>th</sup> column is Extended Warranty (Months) </div>
			<div>8<sup>th</sup> column is Comments </div>
			<br />
			<div>
				<strong><u>NB1</u>:</strong> 2<sup>nd</sup> columns are mandatory; Serial Number , i.e. S/N exist in the sales order.<br />
				<strong><u>NB2</u>:</strong> 3<sup>rd</sup> to 8<sup>th</sup> columns are optional. Enter a dash (-) if no update is required; otherwise, leave blank to clean-up this field.
			</div>
			<div style="margin-top:20px;">
				<table class="admintable">
					<tr>
						<td width="100px" class="key"><label for="title"><?php echo JText::_('File'); ?>:</label></td>
						<td><input type="file" name="csvfile" /></td>
					</tr>
					<tr>
						<td width="200px" class="key"></td>
						<td><input type="submit" value="Update" /></td>
					</tr>
				</table>
			</div>
		</fieldset>
	</div>
	<div class="clr"></div>
	<input type="hidden" name="task" value="warrantyitem.csv_update" />
	<input type="hidden" name="view" value="warrantyitem" />
	<input type="hidden" name="option" value="com_atelman" />
	<?php echo JHTML::_('form.token'); ?>
</form>