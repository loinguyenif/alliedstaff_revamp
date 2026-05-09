<?php defined('_JEXEC') or die('Restricted access'); ?>

<?php JHTML::_('behavior.tooltip');  ?>

<?php

JToolBarHelper::title(JText::_('Import CSV - Warranty Registration : '), 'user.png');

if ($this->user->gid == 25 || $this->user->gid == 8 || $this->group->access == 'r,w') : // admin, and read/write access can save this
	JToolBarHelper::save();
	JToolBarHelper::apply();
endif;

JToolBarHelper::cancel('cancel', 'Close');

?>

<form action="index.php" method="post" name="adminForm" enctype="multipart/form-data">
	<div class="col width-45">
		<fieldset class="adminform">
			<legend><?php echo JText::_('Add Warranty Registration by CSV'); ?></legend>
			<div>
				<div>1st column : Customer ID</div>
				<div>2nd column : PO Number</div>
				<div>3rd column : SO Number</div>
				<div>4th column : Invoice Number</div>
				<div>5th column : Part Number</div>
				<div>6th column : Model Number</div>
				<div>7th column : Serial Number</div>
				<div>8th column : Ship Date (Purchase Date)</div>

				<div style="margin:10px 0;">
					Note : <br />
					1. Part Number and Model Number must be exist in product, otherwise, it does not get the Product ID<br />
					2. Default : "Allied Telesis" Company, and "Singapore" Country<br />
				</div>
			</div>
			<table class="admintable">
				<tr>
					<td width="110" class="key">
						<label for="title">
							<?php echo JText::_('CSV File'); ?>:
						</label>
					</td>
					<td>
						<input class="inputbox" type="file" name="csvfile" id="title" size="60" value="" />
					</td>
				</tr>
				<tr>
					<td width="120" class="key">&nbsp;

					</td>
					<td>
						<input type="submit" value="Import" />
					</td>
				</tr>
			</table>
		</fieldset>
	</div>
	<div class="clr"></div>
	<input type="hidden" name="task" value="import_csv" />
	<input type="hidden" name="option" value="com_atelman" />
	<input type="hidden" name="section" value="warrantyreg" />
	<?php echo JHTML::_('form.token'); ?>
</form>