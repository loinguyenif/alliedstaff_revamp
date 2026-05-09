<?php defined('_JEXEC') or die('Restricted access'); ?>

<?php //JHTML::_('behavior.tooltip');  
?>
<script type="text/javascript">
	function checkServiceContractBegin() {
		jQuery('#status-service-contract-reg').html('<div id="ajaxloader">Loading</div>');
		var form = jQuery('#ATelesisCheckServiceContractForm');
		var formData = form.serialize();
		var url = 'index.php?option=com_atelman&task=rmaitems.ajax&format=json&section=getServiceContract';
		jQuery.ajax({
			url: url,
			type: 'POST',
			data: formData,
			dataType: 'json',
			success: function(response) {
				var status = response.status;
				var statusTxt = response.statusTxt;
				jQuery('#status-service-contract-reg').html(statusTxt);
			},
			error: function(xhr, status, error) {
				jQuery('#status-service-contract-reg').html('An error occurred. Please try again.');
			}
		});

	}


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
</script>
<div class="header">
	<?php echo @$this->item->name ?>
</div>
<div id="ATCheckRequest" class="ATFormFormat">
	<!-- -->
	<!-- SERVICE CONTRACT STATUS -->
	<!-- -->
	<style type="text/css">
		#ATelesisCheckServiceContractForm table tr td {
			padding: 5px 0;
		}
	</style>

	<div class="ATCheckRequestForm">
		<form action="index.php" method="post" name="adminForm" id="ATelesisCheckServiceContractForm">
			<div class="left">
				<div class="fields">
					<div class="label">Service Contract Number :</div>
					<div class="inputs"><input type="text" name="service_contract_no" value="" maxlength="20" /></div>
					<div class="clear"></div>
				</div>
			</div>
			<div class="right">
				<div class="fields">
					<div class="label">Serial Number :</div>
					<div class="inputs"><input type="text" name="serial_no" value="" maxlength="16" /></div>
					<div class="clear"></div>
				</div>
			</div>
			<div class="clear"></div>
			<div style="text-align:right">
				<input type="button" class="button" value="Check Service Contract" onclick="javascript:checkServiceContractBegin();return false;" />
			</div>
			<input type="hidden" name="option" value="com_atelman" />
			<input type="hidden" name="task" value="rmaitems.ajax" />
			<input type="hidden" name="section" value="getServiceContract" />
			<?php echo JHTML::_('form.token'); ?>
		</form>

		<div id="status-service-contract-reg" style="color:red;margin-top:15px;">&nbsp;</div>
		<div class="clear"></div>
	</div>

	<!-- -->
	<!-- CHECK RMA STATUS -->
	<!-- -->
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
				<?php //echo JHTML::_( 'form.token' ); 
				?>
			</div>
		</form>
		<div class="right">
			<div id="status-rma-code" style="color:red;">&nbsp;</div>
		</div>
		<div class="clear"></div>
	</div>


</div>