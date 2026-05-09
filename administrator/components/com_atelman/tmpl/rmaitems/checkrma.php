<?php defined('_JEXEC') or die('Restricted access'); ?>

<?php

?>
<script type="text/javascript">
	function checkRMAStatus() {
		var foo = new Date; // Generic JS date object
		var unixtime_ms = foo.getTime();

		var rma_number = jQuery('#rma_number').val();

		var url = 'index.php?option=com_atelman&task=rmaitems.checkrma&r=' + unixtime_ms;

		jQuery.ajax({
			url: url,
			type: 'POST',
			dataType: 'json',
			data: {
				rma_number: rma_number
			},
			success: function(response) {
				var status_code = response.status_code;
				var status = response.status;

				if (status == 1) {
					jQuery('#results').html(status_code);
				} else {
					jQuery('#results').html('RMA Number does not exist');
				}
			},

		});
	}
</script>
<form action="index.php" method="post" name="adminForm">

	<fieldset class="adminform" style="border: 1px solid #ddd; border-radius: 10px; padding: 20px; background: #fafafa; box-shadow: 0 2px 6px rgba(0,0,0,0.05);">
		<legend style="font-size: 1.2rem; font-weight: 600; color: #007bff; padding: 0 10px; border-left: 4px solid #007bff; margin-bottom: 15px;">
			RMA Status Check
		</legend>

		<table class="admintable" style="width: 100%; border-collapse: separate; border-spacing: 10px;">
			<tbody>
				<tr>
					<td width="200px" class="key" style="text-align: right; vertical-align: middle;">
						<label for="rma_number">RMA Number:</label>
					</td>
					<td>
						<input
							type="text"
							name="rma_number"
							id="rma_number"
							class="form-control"
							placeholder="Enter RMA number..."
							style="width: 250px; padding: 8px; border: 1px solid #ccc; border-radius: 6px;" />
					</td>
				</tr>
				<tr>
					<td></td>
					<td>
						<input
							type="button"
							onclick="javascript:checkRMAStatus();return false;"
							value="Check This RMA Number"
							class="btn btn-primary"
							style="padding: 8px 16px; border-radius: 6px; background-color: #007bff; border: none; color: #fff; cursor: pointer;" />
					</td>
				</tr>
				<tr>
					<td></td>
					<td>
						<div id="results" style="color:red;"></div>
					</td>
				</tr>
			</tbody>
		</table>
	</fieldset>
</form>