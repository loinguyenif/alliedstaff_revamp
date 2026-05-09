<?php defined('_JEXEC') or die('Restricted access'); ?>
<div id="div-service-contract-<?php echo $row_id ?>" style="position:relative;border-bottom:1px solid #ccc;padding-bottom:20px;margin-bottom:20px;">
	<?php if($row_id > 1) : ?>
	<div class="action-item" style="position:absolute;top:0;right:0;">
		<a class="ico-delete" href="javascript:void(0);" onclick="javascript:deleteRMARequestRow(<?php echo $row_id ?>)">Delete</a>
		<div class="clear"></div>
	</div>
	<?php endif; ?>
	
	<table class="admintable">
	<tr>
		<td width="200px" class="key"><label for="title"><?php echo JText::_( 'Serial Number' ); ?> <span style="color:red">*</span>:</label></td>
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
		<td width="200px" class="key"><label for="title"><?php echo JText::_( 'Model Number' ); ?>:</label></td>
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
	</table>
</div>	
	