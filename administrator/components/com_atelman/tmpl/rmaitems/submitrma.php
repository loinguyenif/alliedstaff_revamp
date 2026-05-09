<?php

use Atelman\Component\Atelman\Administrator\Helper\AtelmanHelper;
use Joomla\CMS\Factory;

Joomla\CMS\HTML\HTMLHelper::_('behavior.formvalidator');
defined('_JEXEC') or die('Restricted access'); ?>

<?php

$user = Factory::getApplication()->getIdentity();
?>

<?php

//JToolBarHelper::custom( 'submitrma','','', 'Submit', false );


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

		var data = jQuery('#RMARequestFormAdminForm').serialize();

		jQuery.ajax({
			url: purl,
			type: "POST",
			cache: false,
			data: {
				section: "submitRMAPrint",
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
				if (res.status) {
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

					/* i need to load this model id */

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
		jQuery('#invoice_no_' + id).html(invoice_no);
		jQuery('#so_no_' + id).html(so_no);
		jQuery('#po_no_' + id).html(po_no);
		jQuery('#ship_date_' + id).html(ship_date);
		jQuery('#expiry_date_' + id).html(expiry_date);
		jQuery('#warranty_status_' + id).html(warranty_status);

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
		jQuery('#invoice_no_' + id).val('');

		jQuery('#invoice_no_' + id).html('');
		jQuery('#so_no_' + id).html('');
		jQuery('#po_no_' + id).html('');
		jQuery('#ship_date_' + id).html('');
		jQuery('#expiry_date_' + id).html('');
		jQuery('#warranty_status_' + id).html('');

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

	function addRMARequestRow() {

		var foo = new Date; // Generic JS date object
		var unixtime_ms = foo.getTime();

		var url = 'index.php?option=com_atelman&task=rmaitems.ajaxaddrow&r=' + unixtime_ms;

		jQuery.ajax({
			type: 'POST',
			url: url,
			data: {
				'section': 'rma_request',
				'row_id': row
			},
			success: function(data) {
				// add hidden for saving purpose into systems
				var div_element = jQuery('<div>', {
					id: 'div-rma-request-' + row,
					html: data
				});

				// Append the new element to the target container
				jQuery('#ajax-addrow-rma-request').append(data);

				row = row + 1;


			}
		})

	}

	function deleteRMARequestRow(row_id) {
		jQuery('#div-rma-request-' + row_id).remove();

		row = row - 1;
	}

	function resetAJAXDropDown(id) {
		jQuery('#' + id).html('');
		jQuery('#' + id).css('display', 'none');
	}

	function myValidate(f) {
		var task = jQuery('#task').val();

		if (task == 'cancel') return true;

		if (document.formvalidator.isValid(f)) {
			f.submit();
			return true;
		} else {
			var msg = 'Please insert following fields :\n';

			if (jQuery('#fullname').hasClass('invalid')) msg += '\n- Empty Company';
			if (jQuery('#contact_name').hasClass('invalid')) msg += '\n- Empty Contact Name';
			if (jQuery('#email').hasClass('invalid')) msg += '\n- Empty / Invalid E-Mail Address';

			var stg = false;
			jQuery('#RMARequestFormAdminForm .required').each(function(klass) {
				if (!stg && jQuery('#' + klass.id).hasClass('invalid')) {
					msg += '\n- Please fill all required fields (*) on RMA Request Items';
					stg = true;
				}
			});
			alert(msg);
		}
		return false;
	}


	jQuery(function() {
		addRMARequestRow();
		loadCustomer('<?php echo $this->user->customer_id ?>');
	})
</script>

<style>
	#RMARequestFormAdminForm {
		font-family: "Segoe UI", Roboto, Arial, sans-serif;
		font-size: 14px;
		color: #333;
	}

	#RMARequestFormAdminForm fieldset {
		border: 1px solid #ddd;
		border-radius: 8px;
		padding: 20px 25px;
		margin-bottom: 25px;
		background: #fff;
		box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
	}

	#RMARequestFormAdminForm legend {
		font-size: 16px;
		font-weight: 600;
		color: #0056b3;
		padding: 0 8px;
	}

	#RMARequestFormAdminForm table.admintable {
		width: 100%;
		border-collapse: collapse;
	}

	#RMARequestFormAdminForm td.key {
		background-color: #f9f9f9;
		font-weight: 500;
		padding: 10px 12px;
		border-bottom: 1px solid #eee;
		vertical-align: top;
	}

	#RMARequestFormAdminForm td {
		padding: 10px 12px;
		border-bottom: 1px solid #eee;
	}

	/* Input, select, textarea */
	#RMARequestFormAdminForm input[type="text"],
	#RMARequestFormAdminForm input[type="email"],
	#RMARequestFormAdminForm select,
	#RMARequestFormAdminForm textarea {
		width: 100%;
		box-sizing: border-box;
		padding: 8px 10px;
		border: 1px solid #ccc;
		border-radius: 6px;
		background: #fff;
		transition: border-color 0.2s ease, box-shadow 0.2s ease;
	}

	#RMARequestFormAdminForm input:focus,
	#RMARequestFormAdminForm select:focus,
	#RMARequestFormAdminForm textarea:focus {
		border-color: #007bff;
		box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.1);
		outline: none;
	}

	/* Buttons */
	#RMARequestFormAdminForm input[type="submit"],
	#RMARequestFormAdminForm input[type="button"] {
		background: #007bff;
		color: #fff;
		border: none;
		padding: 8px 18px;
		border-radius: 6px;
		font-size: 14px;
		cursor: pointer;
		transition: background 0.2s ease;
	}

	#RMARequestFormAdminForm input[type="button"]:hover,
	#RMARequestFormAdminForm input[type="submit"]:hover {
		background: #0056b3;
	}

	#RMARequestFormAdminForm input[type="button"]:nth-child(2) {
		background: #6c757d;
	}

	#RMARequestFormAdminForm input[type="button"]:nth-child(3) {
		background: #17a2b8;
	}


	#RMARequestFormAdminForm #ajax-addrow-rma-request>div {
		border-bottom: 1px solid #ddd;
		margin-bottom: 20px;
		padding-bottom: 20px;
	}

	#RMARequestFormAdminForm small,
	#RMARequestFormAdminForm .form-text {
		color: #666;
		font-size: 12px;
	}

	div[id^="div-rma-request-"] {
		position: relative;
		border-bottom: 1px solid #ddd;
		padding: 20px;
		margin-bottom: 25px;
		background: #fafafa;
		border-radius: 10px;
		transition: box-shadow 0.2s ease;
	}

	div[id^="div-rma-request-"]:hover {
		box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
	}

	div[id^="div-rma-request-"] .action-item a.ico-delete {
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

	div[id^="div-rma-request-"] .action-item a.ico-delete:hover {
		background: #b02a37;
		box-shadow: 0 3px 6px rgba(0, 0, 0, 0.15);
		transform: translateY(-1px);
	}

	div[id^="div-rma-request-"] .action-item {
		position: absolute;
		top: -10px !important;
		right: 10px;
	}

	.invalid {
		border: 1px solid red !important;
	}
</style>
<form action="index.php?option=com_atelman&task=rmaitems.submitrma" method="post" id="RMARequestFormAdminForm" name="adminForm" class="form-validate needs-validation" onsubmit="return myValidate(this);">
	<div class="col width-70">
		<fieldset class="adminform">
			<legend><?php echo JText::_(' Requestor\'s Details ') ?></legend>
			<table class="admintable">
				<tr>
					<td width="250px" class="key"><label for="title"><?php echo JText::_('Company'); ?> <span style="color:red;">*</span> :</label></td>
					<td>
						<?php if ($user->gid == 25 || $user->gid == 23) : // Super Admin OR Manager 
						?>
							<?php echo $this->listCompanyHTML; ?>
							<input type="hidden" id="fullname" class="required" name="fullname" value="<?php //echo $this->user->name 
																										?>" />
						<?php else : ?>
							<?php echo $this->user->name ?><input type="hidden" id="fullname" class="required" name="fullname" value="<?php echo $this->user->name ?>" />
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<td width="250px" class="key"><label for="title"><?php echo JText::_('Contact'); ?> <span style="color:red;">*</span> :</label></td>
					<td><input type="text" id="contact_name" class="required" name="contact_name" value="<?php echo $this->user->contact_name ?>" /></td>
				</tr>
				<tr>
					<td width="250px" class="key" valign="top"><label for="title"><?php echo JText::_('Address'); ?>:</label></td>
					<td><textarea name="address" id="address" style="width:100%;height:70px;"><?php echo $this->user->address ?></textarea></td>
				</tr>
				<tr>
					<td width="250px" class="key"><label for="title"><?php echo JText::_('City'); ?></label></td>
					<td><input type="text" name="city" id="city" value="<?php echo $this->user->city ?>" /></td>
				</tr>
				<tr>
					<td width="250px" class="key"><label for="title"><?php echo JText::_('State/Province'); ?></label></td>
					<td><input type="text" name="state" id="state" value="<?php echo $this->user->state ?>" /></td>
				</tr>
				<tr>
					<td width="250px" class="key"><label for="title"><?php echo JText::_('ZIP/Postal Code'); ?></label></td>
					<td><input type="text" name="postal_code" id="postal_code" value="<?php echo $this->user->zipcode ?>" /></td>
				</tr>
				<tr>
					<td width="250px" class="key"><label for="title"><?php echo JText::_('Country/Region'); ?></label></td>
					<td><input type="text" name="country" id="country" value="<?php echo AtelmanHelper::getCountry($this->user->country_id) ?>" /></td>
				</tr>
				<tr>
					<td width="250px" class="key"><label for="title"><?php echo JText::_('Telephone'); ?>:</label></td>
					<td><input type="text" id="telephone" name="telephone" value="<?php echo $this->user->telephone ?>" /></td>
				</tr>
				<tr>
					<td width="250px" class="key"><label for="title"><?php echo JText::_('Fax'); ?>:</label></td>
					<td><input type="text" id="fax" name="fax" value="<?php echo $this->user->fax ?>" /></td>
				</tr>
				<tr>
					<td width="250px" class="key"><label for="title"><?php echo JText::_('Email'); ?> <span style="color:red;">*</span>:</label></td>
					<td>
						<input type="text" name="email" id="email" class="required" value="<?php echo $this->user->email ?>" /><br />
						Separate email addresses with a semicolon or semi-colon (;)<br />
						(e.g. 'username1@domain.com; username2@domain.com')
					</td>
				</tr>
			</table>
		</fieldset>

		<fieldset class="adminform">
			<legend><?php echo JText::_(' Requested Item(s): ') ?></legend>
			<div id="ajax-addrow-rma-request">
				<!-- ajax add row for RMA Request -->
			</div>
			<div style="margin-bottom:20px;">
				<!-- <input type="submit" value="Submit" /> -->
				<input type="button" value="Submit" onclick="myValidate(document.getElementById('RMARequestFormAdminForm'));" />&nbsp;&nbsp;&nbsp;
				<input type="button" onclick="javascript:addRMARequestRow();" value="Add Row" />&nbsp;&nbsp;&nbsp;
				<input type="button" onclick="javascript:print_popup();" value="Print" />
			</div>
			<div>For assistance, please email to <a href="mailto:RMA@alliedtelesis.com.sg">RMA@alliedtelesis.com.sg</a></div>
		</fieldset>
	</div>
	<div class="clr"></div>
	<input type="hidden" name="cid" value="<?php echo @$this->item->rma_id ?>" />
	<?php echo JHTML::_('form.token'); ?>
</form>