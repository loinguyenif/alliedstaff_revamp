<?php defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Factory;
?>

<?php
HTMLHelper::_('behavior.formvalidator');
JToolBarHelper::title(JText::_('User Account Request'), 'user.png');
JToolBarHelper::custom('user.sendAcctRequest', 'save', '', 'Send Request', false);

?>
<style>
	/* ====== Layout ====== */
	.col {
		float: left;
		padding: 10px;
		box-sizing: border-box;
	}

	.col.width-70 {
		width: 70%;
	}

	.col.width-55 {
		width: 55%;
	}

	.col.width-45 {
		width: 45%;
	}

	/* Clearfix */
	.col::after {
		content: "";
		display: table;
		clear: both;
	}

	/* ====== Form Container ====== */
	form.adminform,
	form#RMARequestFormAdminForm,
	form#RMADownloadForm {
		background: #fff;
		border: 1px solid #ddd;
		border-radius: 6px;
		padding: 20px;
		box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
		margin-bottom: 15px;
	}

	fieldset.adminform {
		border: none;
		padding: 0;
	}

	legend {
		font-size: 1.2em;
		font-weight: bold;
		color: #333;
		margin-bottom: 15px;
		border-bottom: 3px solid #1976d2;
		padding-bottom: 6px;
	}


	/* ====== Table form fields ====== */
	table.admintable {
		width: 100%;
		border-collapse: collapse;
	}

	table.admintable label {
		font-size: 14px;
		font-weight: bold;
	}

	table.admintable td:not(.day) {
		font-size: 14px;
		padding: 8px 5px;
		vertical-align: top;
	}

	/* ====== Input, textarea, select styling ====== */
	table.admintable input[type="text"],
	table.admintable input[type="email"],
	table.admintable input[type="tel"],
	table.admintable input[type="number"],
	table.admintable textarea,
	table.admintable select {
		width: 100%;
		max-width: 350px;
		padding: 6px 10px;
		border: 1px solid #ccc;
		border-radius: 5px;
		font-size: 13px;
		line-height: 1.4em;
		background-color: #fafafa;
		transition: border-color 0.2s, background-color 0.2s;
	}

	/* Hover + focus effect */
	table.admintable input:focus,
	table.admintable textarea:focus,
	table.admintable select:focus {
		outline: none;
		border-color: #66afe9;
		background-color: #fff;
		box-shadow: 0 0 2px rgba(102, 175, 233, 0.6);
	}

	/* Textarea resize */
	table.admintable textarea {
		resize: vertical;
		height: 70px;
	}

	/* ====== Buttons ====== */
	button[type="button"] {
		background-color: #f5f5f5;
		border: 1px solid #ccc;
		border-radius: 4px;
		color: #333;
		padding: 6px 14px;
		font-size: 13px;
		cursor: pointer;
		margin-right: 5px;
		transition: background 0.2s;
	}

	button[type="button"]:hover {
		background-color: #e6e6e6;
	}

	/* ====== Files section ====== */
	#RMADownloadForm legend {
		margin-bottom: 10px;
	}

	.rma_detail_files_checkbox {
		border-top: 1px solid #ddd;
		margin-top: 10px;
		padding-top: 8px;
	}

	#RMADownloadForm {
		background: #fafafa;
	}

	/* ====== Notes (top area) ====== */
	.note-section {
		font-size: 13px;
		color: #555;
		margin-bottom: 10px;
		line-height: 1.5;
	}

	.note-section strong {
		color: #000;
	}

	.rma_detail_files_checkbox {
		display: grid;
		background: #fafafa;
		border: 1px solid #ddd;
		border-radius: 10px;
		padding: 16px;
	}

	.rma_detail_files_checkbox>div[style*="font-weight:bold"] {
		font-size: 16px !important;
		font-weight: 600 !important;
		color: #222;
		margin-bottom: 12px !important;
		border-bottom: 1px solid #eee;
		padding-bottom: 6px;
	}

	.rma_detail_files_checkbox>div {
		font-size: 14px;
		color: #333;
	}

	.rma_detail_files_checkbox input[type="checkbox"] {
		transform: scale(1.2);
		margin-right: 6px;
	}

	.rma_detail_files_checkbox a {
		color: #0073aa;
		text-decoration: none;
		font-weight: 500;
	}

	.rma_detail_files_checkbox a:hover {
		text-decoration: underline;
	}

	.rma_detail_files_checkbox span,
	.rma_detail_files_checkbox div {
		line-height: 1.6;
	}

	@media (max-width: 768px) {
		.rma_detail_files_checkbox {
			grid-template-columns: 1fr;
		}
	}


	/* ====== Responsive ====== */
	@media (max-width: 900px) {

		.col.width-55,
		.col.width-45,
		.col.width-70 {
			width: 100%;
			float: none;
		}

		table.admintable td:first-child {
			text-align: left;
			padding-bottom: 4px;
		}

		table.admintable td {
			display: block;
			width: 100%;
		}
	}

	.field-calendar td,
	.field-calendar tr td {
		all: unset;
		display: table-cell;
	}

	.item-file {
		display: flex;
		align-items: center;
		gap: 8px;
		padding: 8px 12px;
		margin-bottom: 6px;
		border: 1px solid #ddd;
		border-radius: 6px;
		background: #fafafa;
		transition: all 0.2s ease-in-out;
	}

	.item-file:hover {
		background: #f0f8ff;
		border-color: #bbb;
	}

	.item-file input[type="checkbox"] {
		transform: scale(1.2);
		cursor: pointer;
	}

	.item-file .filename {
		font-size: 14px;
		color: #333;
		word-break: break-word;
		font-weight: bold;
	}

	input.invalid {
		border: 1px solid red !important;
	}
</style>
<script>
	document.addEventListener('DOMContentLoaded', function() {
		Joomla.submitbutton = function(task) {
			var form = document.getElementById('adminForm');
			// Custom validation
			if (document.formvalidator && !document.formvalidator.isValid(form)) {
				alert('Please complete all required fields');
				return false;
			}

			Joomla.submitform(task, form);
		};

	});
</script>

<style type="text/css">
	input[type="text"],
	input[type="password"],
	textarea {
		width: 500px;
	}
</style>
<form action="index.php" class="form-validate" method="post" name="adminForm" id="adminForm" autocomplete="off">
	<div class="col width-70">
		<fieldset class="adminform">
			<legend><?php echo JText::_('User Account Request'); ?></legend>
			<table class="admintable" cellspacing="1">
				<tr>
					<td width="150" class="key"><label for="first_name"><?php echo JText::_('First Name'); ?> * </label></td>
					<td><input type="text" name="first_name" id="first_name" class="inputbox required " size="40" value="" /></td>
				</tr>
				<tr>
					<td class="key"><label for="last_name"><?php echo JText::_('Last Name'); ?> * </label></td>
					<td><input type="text" name="last_name" id="last_name" class="inputbox required " size="40" value="" autocomplete="off" /></td>
				</tr>
				<tr>
					<td class="key"><label for="email"><?php echo JText::_('Email'); ?> *</label></td>
					<td><input class="inputbox required validate-email" type="text" name="email" id="email" size="40" value="" /></td>
				</tr>
				<tr>
					<td class="key"><label for="job_title"><?php echo JText::_('Job Title'); ?></label></td>
					<td><input type="text" name="job_title" id="job_title" class="inputbox" size="40" value="" autocomplete="off" /></td>
				</tr>
				<tr>
					<td class="key"><label for="company_name"><?php echo JText::_('Company Name'); ?></label></td>
					<td><input type="text" name="company_name" id="company_name" class="inputbox" size="40" value="" autocomplete="off" /></td>
				</tr>
				<tr>
					<td class="key"><label for="website"><?php echo JText::_('Website URL'); ?></label></td>
					<td><input type="text" name="website" id="website" class="inputbox" size="40" value="" autocomplete="off" /></td>
				</tr>
				<tr>
					<td class="key"><label for="address"><?php echo JText::_('Address'); ?></label></td>
					<td><textarea name="address" id="address" autocomplete="off"></textarea></td>
				</tr>
				<tr>
					<td class="key"><label for="city"><?php echo JText::_('City'); ?></label></td>
					<td><input type="text" name="city" id="city" class="inputbox" size="40" value="" autocomplete="off" /></td>
				</tr>
				<tr>
					<td class="key"><label for="state"><?php echo JText::_('State'); ?></label></td>
					<td><input type="text" name="state" id="state" class="inputbox" size="40" value="" autocomplete="off" /></td>
				</tr>
				<tr>
					<td class="key"><label for="postalcode"><?php echo JText::_('Postal Code'); ?></label></td>
					<td><input type="text" name="postalcode" id="postalcode" class="inputbox" size="40" value="" autocomplete="off" /></td>
				</tr>
				<tr>
					<td class="key"><label for="country"><?php echo JText::_('Country'); ?></label></td>
					<td><input type="text" name="country" id="country" class="inputbox" size="40" value="" autocomplete="off" /></td>
				</tr>
				<tr>
					<td class="key"><label for="phone"><?php echo JText::_('Phone'); ?></label></td>
					<td><input type="text" name="phone" id="phone" class="inputbox" size="40" value="" autocomplete="off" /></td>
				</tr>
			</table>
		</fieldset>
	</div>
	<div class="clr"></div>

	<input type="hidden" name="option" value="com_users" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="section" value="" />
	<?php echo JHTML::_('form.token'); ?>
</form>