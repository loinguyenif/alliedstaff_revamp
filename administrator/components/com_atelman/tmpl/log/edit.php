<?php defined('_JEXEC') or die('Restricted access'); ?>

<?php
JToolBarHelper::title(JText::_('COM_ATELMAN_LOGS_ID') . ' : ' . $this->item->id, 'user.png');

$before_update 	= json_decode($this->item->before_update);
$after_update 	= json_decode($this->item->after_update);

?>
<style>
	.col {
		float: left;
		padding: 10px;
		box-sizing: border-box;
	}

	.col.width-70 {
		width: 70%;
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

	/* table.admintable tr:not(.daysrow) td:not(.day) {
		padding: 8px 5px;
		vertical-align: top;
	}

	table.admintable td:first-child:not(.day) {
		width: 160px;
		font-weight: 600;
		color: #444;
		text-align: right;
		padding-right: 12px;
	}

	table.admintable td:last-child:not(.day) {
		text-align: left;
	} */

	table.admintable label {
		font-size: 14px;
		font-weight: bold;
	}

	table.admintable td:not(.day) {
		font-size: 14px;
		padding: 8px 5px;
		vertical-align: top;
	}
</style>

<form action="index.php" method="post" name="adminForm" enctype="multipart/form-data" class="form-validate" id="adminform">
	<div class="col width-70">
		<fieldset class="adminform">
			<legend><?php echo JText::_('ID # : ') . $this->item->id ?></legend>
			<table class="admintable">
				<tr>
					<td class="key">
						<label for="title">
							<?php echo JText::_('COM_ATELMAN_ACTION_BY'); ?>
						</label>
					</td>
					<td>
						<?php echo $this->item->name ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="title">
							<?php echo JText::_('COM_ATELMAN_SECTION'); ?>
						</label>
					</td>
					<td>
						<?php echo $this->item->section ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="title">
							<?php echo JText::_('COM_ATELMAN_ACTION_TYPE'); ?>
						</label>
					</td>
					<td>
						<?php echo $this->item->action_type ?>
					</td>
				</tr>
				<tr>
					<td width="100px" class="key">
						<label for="title">
							<?php echo JText::_('COM_ATELMAN_REMARKS'); ?>:
						</label>
					</td>
					<td>
						<?php echo $this->item->remarks ?>
					</td>
					<?php /*if($this->item->section == 'RMA_ITEM') : ?>
			<tr>
				<td colspan="2">
					<table width="100%">
					<tr>
						<td width="25%">
							RMA Number : 
							Fault Description : 
							Replacement (Days) : 
							Remarks : 
							Status : 
							Received Date : <?php echo $before_update->received_date ?><br />
							Shipped Date : <?php echo $before_update->shipped_date ?><br />
							Closed Date : <?php echo $before_update->closed_date ?><br />
							
						</td>
						<td width="25%">
							RMA Number : 
							Fault Description : 
							Replacement (Days) : 
							Remarks : 
							Status : 
							Received Date : <?php echo $after_update->received_date ?><br />
							Shipped Date : <?php echo $after_update->shipped_date ?><br />
							Closed Date : <?php echo $after_update->closed_date ?><br />
						</td>
				</td>
			</tr>
			<?php endif;*/ ?>
				</tr>

			</table>
		</fieldset>
	</div>
	<div class="clr"></div>
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="view" value="logs" />
	<input type="hidden" name="cid" value="<?php echo $this->item->id ?>" />
	<input type="hidden" name="option" value="com_atelman" />
	<?php echo JHTML::_('form.token'); ?>
</form>