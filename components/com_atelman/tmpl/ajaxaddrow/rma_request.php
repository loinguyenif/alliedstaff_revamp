<?php defined('_JEXEC') or die('Restricted access'); ?>

<div class="warranty-item">
	<?php if($row_id > 1) : ?>
	<div class="action-item">
		<a class="ico-delete" href="javascript:void(0);" onclick="javascript:deleteRMARequestRow(<?php echo $row_id ?>)">&nbsp;</a>
		<div class="clear"></div>
	</div>
	<?php endif; ?>
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