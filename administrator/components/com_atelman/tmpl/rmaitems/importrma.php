<?php

defined('_JEXEC') or die('Restricted access');

use Atelman\Component\Atelman\Administrator\Helper\AtelmanHelper;
use Joomla\CMS\Factory;

Joomla\CMS\HTML\HTMLHelper::_('behavior.formvalidator');


if ($this->user->gid == 25 || $this->user->gid == 8) :
//JToolBarHelper::save();
//JToolBarHelper::apply();
endif;


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
		vertical-align: middle;
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
			<legend><?php echo JText::_('RMA Import') ?></legend>
			<div style="margin-bottom:20px;">Note : Files must be in CSV Format</div>
			<table class="admintable" style="margin-bottom:20px;padding-bottom:20px;border-bottom:1px solid #ccc;">
				<tr id="fileUploadStatus">
					<td class="key" valign="top">
						<label for="title">
							<?php echo JText::_('Files'); ?>:
						</label>
					</td>
					<td>
						<input type="file" id="" name="rmafile" value="" /><br /><br />
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
	<input type="hidden" name="task" id="task" value="rmaitems.rmaimport" />
	<input type="hidden" name="option" value="com_atelman" />
	<?php echo JHTML::_('form.token'); ?>
</form>