<?php $row_id = $_GET['row_id']; ?>
<?php $productObj = $_GET['productObj']; $productObj = str_replace('\\', '', $productObj);$productObj = json_decode($productObj);?>
<div id="div-rma-request-<?php echo $row_id ?>" style="position:relative;border-bottom:1px solid #ccc;padding-bottom:20px;margin-bottom:20px;">
	<?php if($row_id > 1) : ?>
	<div class="action-item" style="position:absolute;top:0;right:0;">
		<a class="ico-delete" href="javascript:void(0);" onclick="javascript:deleteRMARequestRow(<?php echo $row_id ?>)">Delete</a>
		<div class="clear"></div>
	</div>
	<?php endif; ?>
	
	<table class="admintable">
	<tr>
		<td width="200px" class="key"><label for="title"><?php echo 'Serial Number'; ?> <span style="color:red">*</span>:</label></td>
		<td>
			<div class="model_input1" id="search_serial_no_<?php echo $row_id ?>" style="<?php echo $productObj->serial_no?'display:none;':''?>">
				<input type="text" id="search_serial_no_input_<?php echo $row_id ?>" placeholder="Search" onkeyup="javascript:searchSerialNo(this.value, <?php echo $row_id ?>);" value="" autocomplete="off" />
			</div>
      <div class="model_input2" id="serial_no_complete_<?php echo $row_id ?>"><?php echo $productObj->serial_no?><!-- ajax serial no complete name --></div>
      <div class="model_input3" id="search_serial_no_reset_<?php echo $row_id ?>" style="<?php echo $productObj->serial_no?'':'display:none;'?>"><a href="javascript:void(0);" onclick="javascript:resetSerialNo(<?php echo $row_id ?>)">Reset</a></div>
      <div class="clear"></div>
      <input type="hidden" name="print[<?php echo $row_id ?>][serial_no]" class="required" id="serial_no_<?php echo $row_id ?>" value="<?php echo $productObj->serial_no?>" />
      <div class="serial_no_listing" id="serial_no_list_<?php echo $row_id ?>" style="display:none;"><!-- ajax serial no listing --></div>
    </td>
	</tr>
  <tr>
		<td width="200px" class="key"><label for="title"><?php echo 'Model Number'; ?>:</label></td>
		<td>
			<div class="model_input2" id="product_complete_<?php echo $row_id ?>"><?php echo (!empty($productObj->product_model)?$productObj->product_model:'N/A') ?></div>
      <div class="clear"></div>
      <input type="hidden" name="print[<?php echo $row_id ?>][product_id]" class="required" id="product_id_<?php echo $row_id ?>" value="<?php echo $productObj->product_id?>" />
			<input type="hidden" name="print[<?php echo $row_id ?>][product_model]" id="product_model_<?php echo $row_id ?>a" value="<?php echo $productObj->product_model?>" />
			<input type="hidden" name="print[<?php echo $row_id ?>][product_no]" id="product_no_<?php echo $row_id ?>a" value="<?php echo $productObj->product_no ?>" />
			<input type="hidden" name="print[<?php echo $row_id ?>][warranty_id]" id="warranty_id_<?php echo $row_id ?>a" value="<?php echo $productObj->warranty_id?>" />
			<input type="hidden" name="print[<?php echo $row_id ?>][warranty_customer_id]" id="warranty_customer_id_<?php echo $row_id ?>a" value="<?php echo $productObj->warranty_customer_id?>" />
			<input type="hidden" name="print[<?php echo $row_id ?>][previous_rma_number]" id="previous_rma_number_<?php echo $row_id ?>a" value="<?php echo $productObj->previous_rma_number?>" />
		</td>
	</tr>
	<tr style="display:none;">
		<td width="200px" class="key"><label for="title"><?php echo 'PO Number'; ?>:</label></td>
		<td>
			<span id="po_no_<?php echo $row_id ?>" ></span>
			<input id="po_no_<?php echo $row_id ?>a" name="print[<?php echo $row_id ?>][po_no]" type="hidden" value="<?php echo $productObj->po_no?>" />
		</td>
	</tr>
	 <tr style="display:none;">
		<td width="200px" class="key"><label for="title"><?php echo 'SO Number'; ?>:</label></td>
		<td>
			<span id="so_no_<?php echo $row_id ?>" ></span>
			<input id="so_no_<?php echo $row_id ?>a" name="print[<?php echo $row_id ?>][so_no]" type="hidden" value="<?php echo $productObj->so_no?>" />
		</td>
	</tr>
  <tr style="display:none;">
		<td width="200px" class="key"><label for="title"><?php echo 'Invoice Number'; ?>:</label></td>
		<td>
			<span id="invoice_no_<?php echo $row_id ?>" ></span>
			<input id="invoice_no_<?php echo $row_id ?>a" name="print[<?php echo $row_id ?>][invoice_no]" type="hidden" value="<?php echo $productObj->invoice_no?>" />
		</td>
	</tr>
	<tr style="display:none;">
		<td width="200px" class="key"><label for="title"><?php echo 'Ship Date'; ?>:</label></td>
		<td>
			<span id="ship_date_<?php echo $row_id ?>" ></span>
			<input id="ship_date_<?php echo $row_id ?>a" type="hidden" name="print[<?php echo $row_id ?>][ship_date]" value="<?php echo $productObj->ship_date?>" />
		</td>
	</tr>
	<tr style="display:none;">
		<td width="200px" class="key"><label for="title"><?php echo 'Expiry Date'; ?>:</label></td>
		<td>
			<span id="expiry_date_<?php echo $row_id ?>"  ></span>
			<input id="expiry_date_<?php echo $row_id ?>a" type="hidden" name="print[<?php echo $row_id ?>][expiry_date]" value="<?php echo $productObj->expiry_date?>" />
		</td>
	</tr>
	<tr>
		<td width="200px" class="key"><label for="title"><?php echo 'Warranty Status' ; ?>:</label></td>
		<td>
			<span id="warranty_status_<?php echo $row_id ?>"  ><?php echo $productObj->warranty_status?></span>
			<input id="warranty_status_<?php echo $row_id ?>a" type="hidden" name="print[<?php echo $row_id ?>][warranty_status]" value="<?php echo $productObj->warranty_status?>" />
		</td>
	</tr>
  <tr>
		<td width="200px" class="key"><label for="title"><?php echo 'Detailed Fault Description' ; ?> <span style="color:red">*</span>:</label></td>
		<td><textarea name="print[<?php echo $row_id ?>][description]" id="description_<?php echo $row_id ?>" class="required"><?php echo $productObj->description?></textarea></td>
	</tr>
  <tr>
		<td width="200px" class="key"><label for="title"><?php echo 'Remarks'; ?>:</label></td>
		<td><textarea name="print[<?php echo $row_id ?>][remarks]"><?php echo $productObj->remarks?></textarea></td>
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