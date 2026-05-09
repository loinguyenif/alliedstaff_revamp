<?php
defined('_JEXEC') or die('Restricted access');

use Atelman\Component\Atelman\Administrator\Helper\AtelmanHelper;
use Joomla\CMS\Factory;

Joomla\CMS\HTML\HTMLHelper::_('behavior.formvalidator');
$user = Factory::getUser();


?>

<script type="text/javascript">
	jQuery(function() {
		jQuery('#rma_report_type').val('');
		jQuery('.rma-report-table .toggle1 td').css('display', 'none');

		jQuery('.rma-report-table input.all-country-chxbox').prop('checked', true);
		jQuery('.rma-report-table input.country-chxbox').prop('disabled', 'true');

		jQuery('#rma_report_type').change(function() {

			jQuery('input#fieldSort').val('');
			jQuery('input#fieldSortDir').val('');

			switch (this.value) {
				case 'most-rma-country':
				case 'most-rma-model':
					jQuery('.rma-report-table .toggle1 td').css('display', '');
					break;
				default:
					jQuery('.rma-report-table .toggle1 td').css('display', 'none');
					break;
			}
		});

		jQuery('#rma-all-countries').click(function() {
			if (this.checked) {
				jQuery('.rma-report-table input.country-chxbox').prop('disabled', 'true');
				jQuery('.rma-report-table input.country-chxbox').prop('checked', '');
			} else {
				jQuery('.rma-report-table input.country-chxbox').prop('disabled', '');
				jQuery('.rma-report-table input.country-chxbox').prop('checked', '');
			}
		});

	});

	function sortFieldAndDirection(sortField, sortFieldDir) {

		jQuery('input#fieldSort').val(sortField);
		jQuery('input#fieldSortDir').val(sortFieldDir);

		generateReport();

	}

	function generateReport() {
		// ajax report here
		var foo = new Date; // Generic JS date object
		var unixtime_ms = foo.getTime();

		var purl = 'index.php?option=com_atelman&task=index.php?option=com_atelman&task=rmaitems.ajax&r=' + unixtime_ms;

		var data = jQuery('#RMAReportAdminForm').serialize();

		jQuery.ajax({
			url: purl,
			type: "POST",
			cache: false,
			data: {
				section: "RMAReport",
				jdata: data
			}
		}).done(function(data) {
			jQuery('#report_result').html(data);
		});

		return false;
	}

	function loadCountryDetail(country_id) {
		jQuery('#productCountryDetailTable' + country_id).toggle();
	}

	function loadModelDetailTable(product_no_xx) {
		jQuery('#partNumberDetailTable' + product_no_xx).toggle();
	}

	function exportToXLS() {
		jQuery('#task').val('rmaitems.export_csv');
		jQuery('#view').val('reportrma');
		document.adminForm.submit();
	}
</script>
<style type="text/css">
	.adminform {
		background: #f9f9f9;
		border: 1px solid #ddd;
		border-radius: 6px;
		padding: 25px 30px;
		max-width: 1200px;
		margin: 30px auto;
		box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
		font-family: "Segoe UI", Arial, sans-serif;
		color: #333;
	}

	.adminform legend {
		font-weight: bold;
		font-size: 1.3rem;
		color: #333;
		margin-bottom: 15px;
		border-bottom: 1px solid #e0e0e0;
		padding-bottom: 5px;
	}

	.adminform .note {
		font-size: 0.9rem;
		color: #555;
		margin-bottom: 20px;
		line-height: 1.4;
	}

	.admintable {
		width: 100%;
		border-collapse: collapse;
	}

	.admintable tr {
		border-bottom: 1px solid transparent;
	}

	.admintable td.key {
		width: 25%;
		text-align: right;
		font-weight: 500;
		padding: 8px 15px;
		color: #333;
		vertical-align: middle;
	}

	.admintable td {
		padding: 8px 10px;
		vertical-align: middle;
	}

	/* Input và select */
	.inputbox,
	select,
	input[type="text"],
	input[type="date"] {
		width: 60%;
		max-width: 320px;
		padding: 6px 8px;
		border: 1px solid #ccc;
		border-radius: 4px;
		font-size: 0.9rem;
		background: #fff;
		transition: all 0.2s ease;
		height: 42px;
	}

	.inputbox:focus,
	select:focus,
	input[type="text"]:focus {
		border-color: #66afe9;
		box-shadow: 0 0 3px rgba(102, 175, 233, 0.5);
		outline: none;
	}

	/* Lịch */
	.field-calendar input {
		width: 140px;
	}

	table.rma-report-table tr td .column {
		float: left;
	}


	/* Nút hành động */
	button.action,
	input[type="submit"],
	input[type="button"] {
		background: #666;
		color: white;
		border: none;
		border-radius: 4px;
		padding: 6px 15px;
		margin-top: 10px;
		margin-right: 5px;
		font-size: 0.9rem;
		height: 40px;
		cursor: pointer;
		transition: background 0.2s ease;
	}

	button.action:hover,
	input[type="submit"]:hover,
	input[type="button"]:hover {
		background: #444;
	}

	/****report_result */
	#report_result table {
		width: 100%;
		border-collapse: collapse;
		font-family: "Segoe UI", Arial, sans-serif;
		font-size: 14px;
		color: #333;
		border: 1px solid #ccc;
		border-radius: 8px;
		overflow: hidden;
	}

	#report_result th,
	#report_result td {
		border: 1px solid #ddd;
		padding: 8px 10px;
		text-align: left;
	}

	#report_result th {
		background-color: #f2f4f7;
		font-weight: 600;
		color: #222;
	}

	#report_result tr:nth-child(even) {
		background-color: #fafafa;
	}

	#report_result tr:hover {
		background-color: #eef4ff;
		transition: 0.2s;
	}

	#report_result {
		margin-top: 10px;
		border-radius: 10px;
		overflow-x: auto;
		box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
	}
</style>
<div class="col" style="width:100%;">
	<form action="index.php?option=com_atelman&view=rmaitems&layout=report" method="post" name="adminForm" enctype="multipart/form-data" id="RMAReportAdminForm">
		<fieldset class="adminform">
			<legend><?php echo JText::_('RMA Report') ?></legend>
			<div style="margin-bottom:20px;">
				Note :<br />
				1. "From (date)" field and "To (date)" field are disabled for <b>Respond</b>, <b>Receive</b>, <b>Ship</b> and <b>Total Time</b>.
			</div>
			<table class="admintable rma-report-table">
				<tr>
					<td class="key"><label for="title"><?php echo JText::_('RMA Report Type'); ?>:</label></td>
					<td>
						<?php echo $this->lists['rma_report_type']; ?>
					</td>
				</tr>
				<tr class="toggle1">
					<td class="key"><label for="title"><?php echo JText::_('From (DD/MM/YYYY)'); ?>:</label></td>
					<td>
						<?php echo $this->lists['from_date']; ?>
					</td>
				</tr>
				<tr class="toggle1">
					<td class="key"><label for="title"><?php echo JText::_('To (DD/MM/YYYY)'); ?>:</label></td>
					<td>
						<?php echo $this->lists['to_date']; ?>
					</td>
				</tr>
				<tr>
					<td class="key"><label for="title"><?php echo JText::_('Country'); ?>:</label></td>
					<td>
						<?php echo $this->lists['country']; ?>
					</td>
				</tr>
				<tr>
					<td class="key" valign="top">&nbsp;</td>
					<td>
						<button type="button" class="action" onclick="javascript:generateReport();">Generate Report</button>
						<button type="button" class="action" onclick="javascript:exportToXLS();">Export Report</button>
					</td>
				</tr>
			</table>
			<div id="report_result"></div>
		</fieldset>
		<input type="hidden" name="task" id="task" value="" />
		<input type="hidden" name="view" id="view" value="rmaitems" />
		<input type="hidden" name="option" value="com_atelman" />
		<input type="hidden" id="fieldSort" name="fieldSort" value="" />
		<input type="hidden" id="fieldSortDir" name="fieldSortDir" value="" />
		<?php echo JHTML::_('form.token'); ?>
	</form>

</div>

<div class="clr"></div>