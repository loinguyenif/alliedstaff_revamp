<?php defined('_JEXEC') or die('Restricted access'); ?>
<div id="div-rma-request-<?php echo $row_id ?>" style="position:relative;border-bottom:1px solid #ccc;padding-bottom:20px;margin-bottom:20px;">
	<?php if ($row_id > 1) : ?>
		<div class="action-item" style="position:absolute;top:0;right:0;">
			<a class="ico-delete" href="javascript:void(0);" onclick="javascript:deleteRMARequestRow(<?php echo $row_id ?>)">Delete</a>
			<div class="clear"></div>
		</div>
	<?php endif; ?>

	<table class="admintable">
		<tr>
			<td width="200px" class="key"><label for="title"><?php echo JText::_('Serial Number'); ?> <span style="color:red">*</span>:</label></td>
			<td>
				<div class="model_input1" id="search_serial_no_<?php echo $row_id ?>">
					<input type="text" id="search_serial_no_input_<?php echo $row_id ?>" placeholder="Search" onkeyup="javascript:searchSerialNo(this.value, <?php echo $row_id ?>);" value="" autocomplete="off" />
				</div>
				<div class="model_input2" id="serial_no_complete_<?php echo $row_id ?>"><!-- ajax serial no complete name --></div>
				<div class="model_input3" id="search_serial_no_reset_<?php echo $row_id ?>" style="display:none;"><a href="javascript:void(0);" onclick="javascript:resetSerialNo(<?php echo $row_id ?>)">Reset</a></div>
				<div class="clear"></div>
				<input type="hidden" name="print[<?php echo $row_id ?>][serial_no]" class="required" id="serial_no_<?php echo $row_id ?>" value="" />
				<div class="serial_no_listing" id="serial_no_list_<?php echo $row_id ?>" style="display:none;"><!-- ajax serial no listing --></div>
			</td>
		</tr>
		<tr>
			<td width="200px" class="key"><label for="title"><?php echo JText::_('Model Number'); ?>:</label></td>
			<td>
				<div class="model_input2" id="product_complete_<?php echo $row_id ?>">N/A</div>
				<div class="clear"></div>
				<input type="hidden" name="print[<?php echo $row_id ?>][product_id]" class="required" id="product_id_<?php echo $row_id ?>" value="" />
				<input type="hidden" name="print[<?php echo $row_id ?>][product_model]" id="product_model_<?php echo $row_id ?>a" value="" />
				<input type="hidden" name="print[<?php echo $row_id ?>][product_no]" id="product_no_<?php echo $row_id ?>a" value="" />
				<input type="hidden" name="print[<?php echo $row_id ?>][warranty_id]" id="warranty_id_<?php echo $row_id ?>a" value="" />
				<input type="hidden" name="print[<?php echo $row_id ?>][warranty_customer_id]" id="warranty_customer_id_<?php echo $row_id ?>a" value="" />
				<input type="hidden" name="print[<?php echo $row_id ?>][previous_rma_number]" id="previous_rma_number_<?php echo $row_id ?>a" value="" />
			</td>
		</tr>
		<tr>
			<td width="200px" class="key"><label for="title"><?php echo JText::_('PO Number'); ?>:</label></td>
			<td>
				<span id="po_no_<?php echo $row_id ?>"></span>
				<input id="po_no_<?php echo $row_id ?>a" name="print[<?php echo $row_id ?>][po_no]" type="hidden" value="" />
			</td>
		</tr>
		<tr>
			<td width="200px" class="key"><label for="title"><?php echo JText::_('SO Number'); ?>:</label></td>
			<td>
				<span id="so_no_<?php echo $row_id ?>"></span>
				<input id="so_no_<?php echo $row_id ?>a" name="print[<?php echo $row_id ?>][so_no]" type="hidden" value="" />
			</td>
		</tr>
		<tr>
			<td width="200px" class="key"><label for="title"><?php echo JText::_('Invoice Number'); ?>:</label></td>
			<td>
				<span id="invoice_no_<?php echo $row_id ?>"></span>
				<input id="invoice_no_<?php echo $row_id ?>a" name="print[<?php echo $row_id ?>][invoice_no]" type="hidden" value="" />
			</td>
		</tr>
		<tr>
			<td width="200px" class="key"><label for="title"><?php echo JText::_('Ship Date'); ?>:</label></td>
			<td>
				<span id="ship_date_<?php echo $row_id ?>"></span>
				<input id="ship_date_<?php echo $row_id ?>a" type="hidden" name="print[<?php echo $row_id ?>][ship_date]" value="" />
			</td>
		</tr>
		<tr>
			<td width="200px" class="key"><label for="title"><?php echo JText::_('Expiry Date'); ?>:</label></td>
			<td>
				<span id="expiry_date_<?php echo $row_id ?>"></span>
				<input id="expiry_date_<?php echo $row_id ?>a" type="hidden" name="print[<?php echo $row_id ?>][expiry_date]" value="" />
			</td>
		</tr>
		<tr>
			<td width="200px" class="key"><label for="title"><?php echo JText::_('Warranty Status'); ?>:</label></td>
			<td>
				<span id="warranty_status_<?php echo $row_id ?>"></span>
				<input id="warranty_status_<?php echo $row_id ?>a" type="hidden" name="print[<?php echo $row_id ?>][warranty_status]" value="" />
			</td>
		</tr>
		<tr>
			<td width="200px" class="key"><label for="title"><?php echo JText::_('Detailed Fault Description'); ?> <span style="color:red">*</span>:</label></td>
			<td><textarea name="print[<?php echo $row_id ?>][description]" id="description_<?php echo $row_id ?>" class="required"></textarea></td>
		</tr>
		<tr>
			<td width="200px" class="key"><label for="title"><?php echo JText::_('Remarks'); ?>:</label></td>
			<td><textarea name="print[<?php echo $row_id ?>][remarks]"></textarea></td>
		</tr>
	</table>
</div>
<?php /*
<div class="warranty-item">
	
	<div class="left">
		<div class="addrow">
			<div class="label">Serial Number&nbsp;<span class="red">*</span></div>
			<div class="field" id="serial_no_box_<?php echo $row_id ?>" style="position:relative;">
				<div class="model_input1" id="search_serial_no_<?php echo $row_id ?>">
					<input type="text" id="search_serial_no_input_<?php echo $row_id ?>" placeholder="Search" onkeyup="javascript:searchSerialNo(this.value, <?php echo $row_id ?>);" value="" />
				</div>
        <div class="model_input2" id="serial_no_complete_<?php echo $row_id ?>"><!-- ajax serial no complete name --></div>
        <div class="model_input3" id="search_serial_no_reset_<?php echo $row_id ?>" style="display:none;"><a href="javascript:void(0);" onclick="javascript:resetSerialNo(<?php echo $row_id ?>)">Reset</a></div>
        <div class="clear"></div>
        <input type="hidden" name="serial_no[]" class="required" id="serial_no_<?php echo $row_id ?>" value="" />
        <div class="serial_no_listing" id="serial_no_list_<?php echo $row_id ?>" style="display:none;"><!-- ajax serial no listing --></div>
			</div>
			<div class="clear"></div>
		</div>
		
		<div class="addrow">
			<div class="label">Invoice or SO Number</div>
			<div class="field"><input type="text" id="invoice_no_<?php echo $row_id ?>"" name="invoice_no[]" class="inputbox" value="" /></div>
			<div class="clear"></div>
		</div>
		
		<div class="addrow">
			<div class="label">Remarks</div>
			<div class="field"><textarea name="remarks[]"></textarea></div>
			<div class="clear"></div>
		</div>
	</div>
	
	<div class="right">
		
		<div class="addrow">
			<div class="label">Model Number</div>
			<div class="field" style="position:relative;">
        <div class="model_input2" id="product_complete_<?php echo $row_id ?>">N/A</div>
        <div class="clear"></div>
        <input type="hidden" name="product_id[]" class="required" id="product_id_<?php echo $row_id ?>" value="" />
			</div>
			<div class="clear"></div>
		</div>
		
		<div class="addrow">
			<div class="label">Detailed Fault Description&nbsp;<span class="red">*</span></div>
			<div class="field"><textarea name="description[]" class="required"></textarea></div>
			<div class="clear"></div>
		</div>
		
	</div>
	<div class="clear"></div>
</div>
*/ ?>