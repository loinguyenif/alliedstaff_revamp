<?php defined('_JEXEC') or die('Restricted access'); ?>

<script type="text/javascript">
	function checkWarrantyStatus() {
		var foo = new Date; // Generic JS date object
		var unixtime_ms = foo.getTime();

		var po_no = jQuery('#po_no').val();
		var so_no = jQuery('#so_no').val();
		var invoice_no = jQuery('#invoice_no').val();
		var serial_no = jQuery('#serial_no').val();

		var url = 'index.php?option=com_atelman&task=rmaitems.checkwarranty&r=' + unixtime_ms;

		jQuery.ajax({
			type: 'POST',
			url: url,
			data: {
				'po_no': po_no,
				'so_no': so_no,
				'invoice_no': invoice_no,
				'serial_no': serial_no
			},
			dataType: 'json',
			success: function(res) {
				if (res.status == 1) {
					jQuery('#results').css('color', 'black');
					jQuery('#results').html(res.html_data);
				} else {
					jQuery('#results').css('color', 'red');
					jQuery('#results').html('Warranty does not exist');
				}
			}
		})

	}
</script>
<style>
	.admintable {
		width: 100%;
		border-collapse: separate;
		border-spacing: 10px;
	}

	.admintable td.key {
		text-align: right;
		vertical-align: middle;
	}

	.admintable td input {
		width: 250px;
		padding: 8px;
		border: 1px solid #ccc;
		border-radius: 6px;
	}

	.admintable input[type="button"] {
		padding: 8px 16px;
		border-radius: 6px;
		background-color: #007bff;
		border: none;
		color: #fff;
		cursor: pointer;
	}

	/****result */
	#results table {
		width: 100%;
		border-collapse: collapse;
		font-family: "Segoe UI", Arial, sans-serif;
		font-size: 14px;
		color: #333;
		border: 1px solid #000;
		overflow: hidden;
	}

	#results th,
	#results td {
		border: 1px solid #ddd;
		padding: 8px 10px;
		text-align: left;
	}

	#results th {
		background-color: #f2f4f7;
		font-weight: 600;
		color: #222;
	}

	#results tr:nth-child(even) {
		background-color: #fafafa;
	}

	#results tr:hover {
		background-color: #eef4ff;
		transition: 0.2s;
	}

	#results {
		margin-top: 10px;
		border-radius: 10px;
		overflow-x: auto;
		box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
	}

	#sidebar-wrapper {
		display: none;
	}
</style>
<form action="index.php" method="post" name="adminForm" style="border: 1px solid #ddd; border-radius: 10px; padding: 20px; background: #fafafa; box-shadow: 0 2px 6px rgba(0,0,0,0.05);">
	<div class="">
		<fieldset class="adminform">
			<legend style="font-size: 1.2rem; font-weight: 600; color: #007bff; padding: 0 10px; border-left: 4px solid #007bff; margin-bottom: 15px;"><?php echo JText::_(' Warranty Status ') ?></legend>

			<table class="admintable">
				<tr>
					<td width="200px" class="key"><label for="title"><?php echo JText::_('PO Number'); ?>:</label></td>
					<td>
						<input type="text" id="po_no" name="po_no" value="" />
					</td>
				</tr>
				<tr>
					<td width="200px" class="key"><label for="title"><?php echo JText::_('SO Number'); ?>:</label></td>
					<td>
						<input type="text" id="so_no" name="so_no" value="" />
					</td>
				</tr>
				<tr>
					<td width="200px" class="key"><label for="title"><?php echo JText::_('Invoice Number'); ?>:</label></td>
					<td>
						<input type="text" id="invoice_no" name="invoice_no" value="" />
					</td>
				</tr>
				<tr>
					<td width="200px" class="key"><label for="title"><?php echo JText::_('Serial Number'); ?>:</label></td>
					<td>
						<input type="text" id="serial_no" name="serial_no" value="" />
					</td>
				</tr>
				<tr>
					<td width="200px" class="key"><label for="title">&nbsp;</label></td>
					<td><input type="button" onclick="javascript:checkWarrantyStatus();return false;" value="Check" /></td>
				</tr>
			</table>
			<div id="results" style="color:red;"></div>
		</fieldset>

	</div>
	<div class="clr"></div>
	<input type="hidden" name="task" value="checkwarranty" />
	<input type="hidden" name="view" value="rmarequest" />
	<input type="hidden" name="option" value="com_atelman" />
	<?php echo JHTML::_('form.token'); ?>
</form>