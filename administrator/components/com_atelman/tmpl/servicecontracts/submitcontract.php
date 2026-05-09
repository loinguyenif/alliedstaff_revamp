<?php
defined('_JEXEC') or die('Restricted access');

use \Joomla\CMS\Factory;

?>

<?php
$user = Factory::getUser();
?>

<style type="text/css">
	input[type="text"],
	textarea {
		width: 400px;
	}

	.serial_no_listing {
		background-color: #FFFFFF;
		border: 1px solid #333333;
		height: 150px;
		overflow-x: hidden;
		overflow-y: scroll;
		position: absolute;
		z-index: 200;
	}

	.serial_no_listing a {
		display: block;
		padding: 0 10px;
	}
</style>
<script type="text/javascript">
	function resetProductNo(id) {
		jQuery('#product_complete_' + id).html('N/A');
		jQuery('#product_id_' + id).val('');
	}

	function print_popup() {

		var foo = new Date; // Generic JS date object
		var unixtime_ms = foo.getTime();

		var purl = 'index.php?option=com_atelman&task=rmaitems.ajax&r=' + unixtime_ms;

		var data = jQuery('#ServiceContractFormAdminForm').serialize();

		jQuery.ajax({
			url: purl,
			type: "POST",
			cache: false,
			data: {
				section: "submitServiceContractPrint",
				jdata: data
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

	function loadCustomer(customer_id) {
		var foo = new Date; // Generic JS date object
		var unixtime_ms = foo.getTime();

		var url = 'index.php?option=com_atelman&task=rmaitems.ajax&r=' + unixtime_ms;
		jQuery.ajax({
			type: 'POST',
			url: url,
			data: {
				'section': 'loadCompany',
				'customer_id': customer_id
			},
			dataType: 'json',
			success: function(res) {
				if (res.status) { // true, load the data in
					jQuery('#fullname').val(res.data.company_name);
				}
			}
		})
	}

	/*
		Search Serial No, Every Serial No has Product ID
	*/

	function searchSerialNo(value, id) {

		var foo = new Date; // Generic JS date object
		var unixtime_ms = foo.getTime();

		jQuery('#serial_no_list_' + id).css('display', 'none');

		var url = 'index.php?option=com_atelman&task=rmaitems.ajax&r=' + unixtime_ms;

		jQuery.ajax({
			type: 'POST',
			url: url,
			data: {
				'section': 'getSerialNo',
				'row_id': id,
				'keyword': value
			},
			dataType: 'json',
			success: function(res) {
				if (res.status) { // true, load the data in
					jQuery('#serial_no_list_' + id).html(res.data);
					jQuery('#serial_no_list_' + id).css('display', 'block');
				} else {
					jQuery('#serial_no_list_' + id).html('');
				}
			}
		})


	}

	function chooseSerialNo(id, strText, product_id, product_name, product_no, warranty_status, invoice_no, so_no, po_no, ship_date, expiry_date, warranty_id, warranty_customer_id, previous_rma_number) {

		jQuery('#search_serial_no_' + id).css('display', 'none');
		jQuery('#search_serial_no_reset_' + id).css('display', '');
		jQuery('#serial_no_' + id).val(strText);
		jQuery('#serial_no_' + id).removeClass('invalid');

		jQuery('#serial_no_complete_' + id).html(strText);
		jQuery('#serial_no_list_' + id).css('display', 'none');
		jQuery('#serial_no_list_' + id).html('');

		jQuery('#product_id_' + id).val(product_id);
		jQuery('#product_id_' + id).removeClass('invalid');

		jQuery('#product_complete_' + id).html(product_name + ' ( ' + product_no + ' ) ');

		jQuery('#product_model_' + id + 'a').val(product_name);
		jQuery('#product_no_' + id + 'a').val(product_no);
		jQuery('#warranty_id_' + id + 'a').val(warranty_id);
		jQuery('#warranty_customer_id_' + id + 'a').val(warranty_customer_id);
		jQuery('#invoice_no_' + id + 'a').val(invoice_no);
		jQuery('#so_no_' + id + 'a').val(so_no);
		jQuery('#po_no_' + id + 'a').val(po_no);
		jQuery('#ship_date_' + id + 'a').val(ship_date);
		jQuery('#expiry_date_' + id + 'a').val(expiry_date);
		jQuery('#warranty_status_' + id + 'a').val(warranty_status);
		jQuery('#previous_rma_number_' + id + 'a').val(previous_rma_number);
	}

	function resetSerialNo(id) {
		jQuery('#search_serial_no_' + id).css('display', '');
		jQuery('#search_serial_no_reset_' + id).css('display', 'none');
		jQuery('#serial_no_complete_' + id).html('');
		jQuery('#serial_no_' + id).val('');

		jQuery('#product_model_' + id + 'a').val('');
		jQuery('#product_no_' + id + 'a').val('');
		jQuery('#warranty_status_' + id + 'a').val('');
		jQuery('#invoice_no_' + id + 'a').val('');
		jQuery('#so_no_' + id + 'a').val('');
		jQuery('#po_no_' + id + 'a').val('');
		jQuery('#ship_date_' + id + 'a').val('');
		jQuery('#expiry_date_' + id + 'a').val('');
		jQuery('#warranty_status_' + id + 'a').val('');
		jQuery('#previous_rma_number_' + id + 'a').val('');

		resetProductNo(id);

		jQuery('#search_serial_no_input_' + id).val('');
	}

	var row = 1;

	function addServiceContractRow() {

		var foo = new Date; // Generic JS date object
		var unixtime_ms = foo.getTime();
		var url = 'index.php?option=com_atelman&task=rmaitems.ajaxaddrow&r=' + unixtime_ms;
		jQuery.ajax({
			type: 'POST',
			url: url,
			data: {
				'section': 'service_contract',
				'row_id': row
			},
			//dataType: 'json',
			success: function(data) {
				// add hidden for saving purpose into systems
				var div_element = jQuery('<div>', {
					id: 'div-service-contract-' + row,
					html: data
				});

				// Append the new element to the target container
				jQuery('#ajax-addrow-service-contract').append(data);
				row = row + 1;
			}
		})
	}

	function deleteServiceContractRow(row_id) {
		jQuery('#div-service-contract-' + row_id).remove();

		row = row - 1;
	}

	function resetAJAXDropDown(id) {
		jQuery('#'.id).html('');
		jQuery('#'.id).css('display', 'none');
	}

	function myValidate(f) {

		var task = jQuery('#task').val();

		if (task == 'cancel') return true;

		if (document.formvalidator.isValid(f)) {
			return true;
		} else {
			var msg = 'Please insert following fields :\n';

			if (jQuery('#service_contract_no').hasClass('invalid')) {
				msg += '\n- Empty Service Contract No.';
			}

			var stg = false;
			jQuery('#ServiceContractFormAdminForm .required').each(function(klass) {
				if (!stg) {

					if (jQuery(klass.id).hasClass('invalid')) {
						msg += '\n- Please fill all required fields (*) on Service Contract Form';
						stg = true;
					}
				}
			});
			alert(msg);
		}
		return false;
	}

	jQuery(function() {
		addServiceContractRow();
		/*loadCustomer('<?php echo $this->user->customer_id ?>');*/
	});
</script>
<style>
	#ServiceContractFormAdminForm {
		font-family: "Segoe UI", Roboto, Arial, sans-serif;
		font-size: 14px;
		color: #333;
	}

	#ServiceContractFormAdminForm fieldset {
		border: 1px solid #ddd;
		border-radius: 8px;
		padding: 20px 25px;
		margin-bottom: 25px;
		background: #fff;
		box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
	}

	#ServiceContractFormAdminForm legend {
		font-size: 16px;
		font-weight: 600;
		color: #0056b3;
		padding: 0 8px;
	}

	#ServiceContractFormAdminForm table.admintable {
		width: 100%;
		border-collapse: collapse;
	}

	#ServiceContractFormAdminForm td.key {
		background-color: #f9f9f9;
		font-weight: 500;
		padding: 10px 12px;
		border-bottom: 1px solid #eee;
		vertical-align: top;
	}

	#ServiceContractFormAdminForm td {
		padding: 10px 12px;
		border-bottom: 1px solid #eee;
	}

	/* Input, select, textarea */
	#ServiceContractFormAdminForm input[type="text"],
	#ServiceContractFormAdminForm input[type="email"],
	#ServiceContractFormAdminForm select,
	#ServiceContractFormAdminForm textarea {
		width: 100%;
		box-sizing: border-box;
		padding: 8px 10px;
		border: 1px solid #ccc;
		border-radius: 6px;
		background: #fff;
		transition: border-color 0.2s ease, box-shadow 0.2s ease;
	}

	#ServiceContractFormAdminForm input:focus,
	#ServiceContractFormAdminForm select:focus,
	#ServiceContractFormAdminForm textarea:focus {
		border-color: #007bff;
		box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.1);
		outline: none;
	}

	/* Buttons */
	#ServiceContractFormAdminForm input[type="submit"],
	#ServiceContractFormAdminForm input[type="button"] {
		background: #007bff;
		color: #fff;
		border: none;
		padding: 8px 18px;
		border-radius: 6px;
		font-size: 14px;
		cursor: pointer;
		transition: background 0.2s ease;
	}

	#ServiceContractFormAdminForm input[type="button"]:hover,
	#ServiceContractFormAdminForm input[type="submit"]:hover {
		background: #0056b3;
	}

	#ServiceContractFormAdminForm input[type="button"]:nth-child(2) {
		background: #6c757d;
	}

	#ServiceContractFormAdminForm input[type="button"]:nth-child(3) {
		background: #17a2b8;
	}


	#ServiceContractFormAdminForm #ajax-addrow-rma-request>div {
		border-bottom: 1px solid #ddd;
		margin-bottom: 20px;
		padding-bottom: 20px;
	}

	#ServiceContractFormAdminForm small,
	#ServiceContractFormAdminForm .form-text {
		color: #666;
		font-size: 12px;
	}

	div[id^="div-service-contract-"] {
		position: relative;
		border-bottom: 1px solid #ddd;
		padding: 20px;
		margin-bottom: 25px;
		background: #fafafa;
		border-radius: 10px;
		transition: box-shadow 0.2s ease;
	}

	div[id^="div-service-contract-"]:hover {
		box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
	}

	div[id^="div-service-contract-"] .action-item a.ico-delete {
		display: inline-block;
		padding: 6px 14px;
		background: #dc3545;
		color: #fff;
		font-size: 13px;
		font-weight: 500;
		border-radius: 6px;
		text-decoration: none;
		transition: all 0.25s ease;
		box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
	}

	div[id^="div-service-contract-"] .action-item a.ico-delete:hover {
		background: #b02a37;
		box-shadow: 0 3px 6px rgba(0, 0, 0, 0.15);
		transform: translateY(-1px);
	}

	div[id^="div-service-contract-"] .action-item {
		position: absolute;
		top: -10px !important;
		right: 10px;
	}

	.invalid {
		border: 1px solid red !important;
	}

	#expiry_date_btn {
		position: absolute;
		right: 0px;
	}
</style>
<form action="index.php" method="post" id="ServiceContractFormAdminForm" name="adminForm" class="form-validate" onSubmit="return myValidate(this);">
	<div class="col width-70">
		<fieldset class="adminform">
			<legend><?php echo JText::_(' Service Contract Detail ') ?></legend>
			<table class="admintable">
				<tr>
					<td width="200px" class="key"><label for="title"><?php echo JText::_('Company'); ?> <span style="color:red;">*</span> :</label></td>
					<td>
						<?php if ($user->gid == 25 || $user->gid == 8 || $user->gid == 23) : // Super Admin OR Manager 
						?>
							<?php echo $this->companiesHTML; ?>
							<input type="hidden" id="fullname" class="required" name="fullname" value="<?php //echo $this->user->name 
																										?>" />
						<?php else : ?>
							<?php echo $this->user->name ?><input type="hidden" id="fullname" class="required" name="fullname" value="<?php echo $this->user->name ?>" />
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<td width="200px" class="key"><label for="title"><?php echo JText::_('Service Contract #'); ?> <span style="color:red;">*</span> :</label></td>
					<td><input type="text" name="service_contract_no" id="service_contract_no" class="required" value="" /></td>
				</tr>
				<tr>
					<td width="200px" class="key"><label for="title"><?php echo JText::_('Expiry Date'); ?></label></td>
					<td><?php echo $this->lists['expiry_date'] ?></td>
				</tr>
				<tr>
					<td width="200px" class="key"><label for="title"><?php echo JText::_('PO Number'); ?></label></td>
					<td><input type="text" name="po_no" id="po_no" value="" /></td>
				</tr>
				<tr>
					<td width="200px" class="key"><label for="title"><?php echo JText::_('Cover Length'); ?></label></td>
					<td><input type="text" name="cover_length" id="cover_length" value="" /></td>
				</tr>
				<tr>
					<td width="200px" class="key"><label for="title"><?php echo JText::_('Service Type'); ?></label></td>
					<td>
						<select name="service_type" id="service_type">
							<option value="">-- Please select Service Type --</option>
							<option value="ELITE">ELITE</option>
						</select>
					</td>
				</tr>
				<tr>
					<td width="200px" class="key"><label for="title"><?php echo JText::_('Client Name'); ?></label></td>
					<td><input type="text" name="client_name" id="client_name" value="" /></td>
				</tr>
				<tr>
					<td width="200px" class="key"><label for="title"><?php echo JText::_('Remarks'); ?></label></td>
					<td><textarea name="remarks" id="remarks" value=""></textarea></td>
				</tr>
			</table>
		</fieldset>

		<fieldset class="adminform">
			<legend><?php echo JText::_(' Please include Product ') ?></legend>
			<div id="ajax-addrow-service-contract">
				<!-- ajax add row for RMA Request -->
			</div>
			<div style="margin-bottom:20px;"><input type="submit" value="Submit" />&nbsp;&nbsp;&nbsp;<input type="button" onclick="javascript:addServiceContractRow();" value="Add Row" />&nbsp;&nbsp;&nbsp;<input type="button" onclick="javascript:print_popup();" value="Print" /></div>
			<div>For assistance, please email to <a href="mailto:RMA@alliedtelesis.com.sg">RMA@alliedtelesis.com.sg</a></div>
		</fieldset>
	</div>
	<div class="clr"></div>
	<input type="hidden" name="task" id="task" value="servicecontract.submitcontract" />
	<!-- <input type="hidden" name="view" value="servicecontracts" /> -->
	<input type="hidden" name="cid" value="" />
	<input type="hidden" name="option" value="com_atelman" />
	<?php echo JHTML::_('form.token'); ?>
</form>