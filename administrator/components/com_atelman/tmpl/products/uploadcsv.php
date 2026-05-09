<?php
defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('formbehavior.chosen', 'select');
?>
<form action="index.php" method="post" name="adminForm" enctype="multipart/form-data">
	<div class="card">
		<div class="card-header">
			<h3><?php echo Text::_('Add Product by CSV'); ?></h3>
		</div>
		<div class="card-body">
			<div class="control-group">
				<div class="control-label">
					<label for="csvfile"><?php echo Text::_('CSV File:'); ?></label>
				</div>
				<div class="controls">
					<input type="file" name="csvfile" id="csvfile" accept=".csv" required />
				</div>
			</div>
		</div>
		<div class="card-footer">
			<button type="submit" class="btn btn-success">
				<?php echo Text::_('Import'); ?>
			</button>
			<a class="btn btn-secondary" href="<?php echo Route::_('index.php?option=com_atelman&view=products'); ?>">
				<?php echo Text::_('JCANCEL'); ?>
			</a>
		</div>
	</div>

	<div class="clr"></div>
	<input type="hidden" name="task" value="products.importCSV" />
	<input type="hidden" name="option" value="com_atelman" />
	<?php echo JHTML::_('form.token'); ?>
</form>