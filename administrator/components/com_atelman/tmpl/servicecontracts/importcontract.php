<?php

defined('_JEXEC') or die('Restricted access');

use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;

$user = Factory::getUser();
?>
<style>
	/* ===== RMA Import Form Styling ===== */
	fieldset.adminform {
		background: #fff;
		border: 1px solid #e0e0e0;
		border-radius: 8px;
		padding: 20px 25px;
		box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
		margin: 20px auto;
		font-size: 14px;
	}

	fieldset.adminform legend {
		font-size: 18px;
		font-weight: 600;
		color: #2c3e50;
	}

	fieldset.adminform .admintable {
		width: 100%;
		border-collapse: collapse;
	}

	fieldset.adminform .admintable td.key {
		width: 120px;
		font-weight: 500;
		color: #333;
		padding: 10px 0;
	}

	fieldset.adminform .admintable td {
		padding: 10px 0;
		vertical-align: top;
	}

	fieldset.adminform input[type="file"] {
		padding: 6px;
		border: 1px solid #ccc;
		border-radius: 4px;
		background: #fff;
	}

	fieldset.adminform input[type="submit"],
	fieldset.adminform .btn-cancel {
		display: inline-block;
		padding: 8px 22px;
		border-radius: 6px;
		font-weight: 500;
		color: #fff;
		border: none;
		cursor: pointer;
		margin-right: 10px;
	}

	fieldset.adminform input[type="submit"] {
		background-color: #3b7354;
	}

	fieldset.adminform input[type="submit"]:hover {
		background-color: #2f5d44;
	}

	fieldset.adminform .btn-cancel {
		background-color: #345680;
		text-decoration: none;
	}

	fieldset.adminform .btn-cancel:hover {
		background-color: #2b486c;
	}

	fieldset.adminform .form-actions {
		border-top: 1px solid #f0f0f0;
		margin-top: 20px;
		padding-top: 15px;
	}
</style>
<?php if ($this->user->gid == 25 || $this->user->gid == 8) :  ?>
	<div style="margin-bottom:20px;">

	</div>
<?php endif; ?>
<form action="index.php" method="post" name="adminForm" enctype="multipart/form-data" class="form-validate">
	<div class="col width-55">

		<fieldset class="adminform">
			<legend><?php echo Text::_('Service Contract Import') ?></legend>
			<div style="margin-bottom:20px;">Note : Files must be in CSV Format</div>
			<div style="margin-bottom:20px;">
				Column A : Service Contract No. <br />
				Column B : PO Number <br />
				Column C : Client Name <br />
				Column D : Service Type <br />
				Column E : Length Cover <br />
				Column F : Model No <br />
				Column G : Serial No <br />
				Column H : Expiry Date <br />
				Column I : Start Date <br /><br />

				System will check "Column A", <br />
				If not exist, it will insert record from A-G,I Column<br />
				Otherwise, it will update record from B-G,I Column <br />
				<br />
				For Column G, it will always insert record.<br />
			</div>
			<table class="admintable" style="margin-bottom:20px;padding-bottom:20px;border-bottom:1px solid #ccc;">
				<tr id="fileUploadStatus">
					<td class="key" valign="top">
						<label for="title">
							<?php echo Text::_('Files'); ?>:
						</label>
					</td>
					<td>
						<input type="file" id="" name="servicecontractfile" value="" /><br /><br />
					</td>
				</tr>

				<tr>
					<td class="key">
						<label for="title">
							&nbsp;
						</label>
					</td>
					<td>
						<input type="submit" value="Import!" />
					</td>
				</tr>
			</table>

		</fieldset>

	</div>
	<div class="clr"></div>
	<input type="hidden" id="task" name="task" value="servicecontracts.servicecontractimport" />
	<input type="hidden" name="option" value="com_atelman" />
	<?php echo JHTML::_('form.token'); ?>
</form>