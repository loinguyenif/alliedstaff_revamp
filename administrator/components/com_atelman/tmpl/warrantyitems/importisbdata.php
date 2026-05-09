<?php defined('_JEXEC') or die('Restricted access');

use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Layout\LayoutHelper;
use \Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
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
<?php

JToolBarHelper::title(JText::_('Import isbdata.csv - Warranty Registration : '), 'user.png');

	/*if($this->user->gid == 25 || $this->user->gid ==8 || $this->group->access == 'r,w') : // admin, and read/write access can save this
		JToolBarHelper::save();
		JToolBarHelper::apply();
		endif;
	*/
	//JToolBarHelper::cancel( 'cancel', 'Close' );

	?>
<script type="text/javascript">
	function proceeding() {
		var r = confirm("Are you sure you want to import current isbdata.csv?");
		if (r == true) {
			document.isbdataForm.submit();
		} else {
			alert("Nothing happened! Cancel it")
		}
	}

	function cancellation(redirect_to) {
		window.location = redirect_to;
	}
</script>
<form action="index.php" method="post" name="isbdataForm" enctype="multipart/form-data">
	<div class="col width-70">
		<fieldset class="adminform">
			<legend><?php echo JText::_('IMPORTANT NOTE'); ?></legend>
			<div>
				<div style="margin:0 0 10px 0"><i><b>Before proceed, please read the note below</b></i></div>
				<div>1. Please make sure you already have isbdata.csv in your folder ( Current folder is : <b>*root*/atelftp/isbdata.csv</b></div>
				<div>2. This will only ADD-ing warranty registration (non-Cumulative) , basically, only doing INSERTION based on CSV you have</div>
				<div>3. If you understand this, please proceed...</div>
				<div style="margin:20px 0;">
					<input type="button" class="btn btn-success" value="OK" onclick="javascript:proceeding();" />
					<input type="button" class="btn btn-danger" value="Cancel" onclick="javascript:cancellation('<?php echo JRoute::_('index.php?option=com_atelman&view=warrantyitems') ?>');" />
				</div>
			</div>
		</fieldset>
	</div>
	<div class="clr"></div>
	<input type="hidden" name="task" value="import_isbdata" />
	<input type="hidden" name="option" value="com_atelman" />
	<?php echo JHTML::_('form.token'); ?>
</form>