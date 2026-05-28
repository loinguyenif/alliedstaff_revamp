<?php defined('_JEXEC') or die('Restricted access'); ?>

<script type="text/javascript">
	function checkRMABegin() {
		jQuery('#status-rma-code').html('<div id="ajaxloader">Loading</div>');
		var form = jQuery('#ATelesisCheckRMAStatusForm');
		var formData = form.serialize();
		var url = 'index.php?option=com_atelman&task=rmaitems.ajax&format=json&section=getRMAStatus';
		jQuery.ajax({
			url: url,
			type: 'POST',
			data: formData,
			dataType: 'json',
			success: function(response) {
				var status = response.status;
				var statusTxt = response.statusTxt;
				jQuery('#status-rma-code').html(statusTxt);
			},
			error: function(xhr, status, error) {
				console.error('Error:', error);
				jQuery('#status-rma-code').html('An error occurred. Please try again.');
			}
		});

	}

	function checkWarrantyBegin() {
		jQuery('#status-warranty-reg').html('<div id="ajaxloader">Loading</div>');

		var form = jQuery('#ATelesisCheckWarrantyStatusForm');
		var formData = form.serialize();
		var url = 'index.php?option=com_atelman&task=rmaitems.ajax&format=json&section=getWarrantyStatus';
		jQuery.ajax({
			url: url,
			type: 'POST',
			data: formData,
			dataType: 'json',
			success: function(response) {
				var status = response.status;
				var statusTxt = response.statusTxt;
				jQuery('#status-warranty-reg').html(statusTxt);
			},
			error: function(xhr, status, error) {
				console.error('Error:', error);
				jQuery('#status-warranty-reg').html('An error occurred. Please try again.');
			}
		});
	}
</script>
<div class="header">
	<?php echo $this->item->title ?>
</div>
<div id="ATCheckRequest" class="ATFormFormat">
	<div class="ATCheckRequestForm">
		<form action="index.php" method="post" name="adminForm" id="ATelesisCheckRMAStatusForm">
			<div class="left">
				<div class="fields">
					<div class="label">RMA Number :</div>
					<div class="inputs"><input type="text" name="rmacode" value="" /></div>
					<div class="clear"></div>
				</div>
				<div class="fields">
					<div class="label">&nbsp;</div>
					<div class="inputs"><input type="button" class="button" value="Check RMA Status" onclick="javascript:checkRMABegin();return false;" /></div>
					<div class="clear"></div>
				</div>

				<input type="hidden" name="option" value="com_atelman" />
				<input type="hidden" name="task" value="rmaitems.ajax" />
				<input type="hidden" name="section" value="getRMAStatus" />
				<?php echo JHTML::_('form.token'); ?>
			</div>
		</form>
		<div class="right">
			<div id="status-rma-code" style="color:red;">&nbsp;</div>
		</div>
		<div class="clear"></div>
	</div>
	<!-- -->
	<!-- WARRANTY STATUS -->
	<!-- -->
	<style type="text/css">
		#ATelesisCheckWarrantyStatusForm table tr td {
			padding: 5px 0;
		}
	</style>

	<div class="ATCheckRequestForm">
		<form action="index.php" method="post" name="adminForm" id="ATelesisCheckWarrantyStatusForm">
			<div class="left">
				<div class="fields">
					<div class="label">PO Number :</div>
					<div class="inputs"><input type="text" name="po_no" value="" maxlength="20" /></div>
					<div class="clear"></div>
				</div>
				<div class="fields">
					<div class="label">SO Number :</div>
					<div class="inputs"><input type="text" name="so_no" value="" maxlength="8" /></div>
					<div class="clear"></div>
				</div>
			</div>
			<div class="right">
				<div class="fields">
					<div class="label">Invoice Number :</div>
					<div class="inputs"><input type="text" name="invoice_no" value="" maxlength="8" /></div>
					<div class="clear"></div>
				</div>
				<div class="fields">
					<div class="label">Serial Number :</div>
					<div class="inputs"><input type="text" name="serial_no" value="" maxlength="18" /></div>
					<div class="clear"></div>
				</div>
			</div>
			<div class="clear"></div>
			<div style="text-align:right">
				<input type="button" class="button" value="Check Warranty Status" onclick="javascript:checkWarrantyBegin();return false;" />
			</div>
			<input type="hidden" name="option" value="com_atelman" />
			<input type="hidden" name="task" value="rmaitems.ajax" />
			<input type="hidden" name="section" value="getWarrantyStatus" />
			<?php echo JHTML::_('form.token'); ?>
		</form>

		<div id="status-warranty-reg" style="color:red;margin-top:15px;">&nbsp;</div>
		<div class="clear"></div>
	</div>
</div>