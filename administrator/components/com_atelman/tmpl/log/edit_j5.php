<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Atelman
 * @author     iFoundries <wpsub@ifoundries.com>
 * @copyright  2025 iFoundries
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Language\Text;

$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
	->useScript('form.validate');
HTMLHelper::_('bootstrap.tooltip');
?>

<form
	action="<?php echo Route::_('index.php?option=com_atelman&layout=edit&id=' . (int) $this->item->id); ?>"
	method="post" enctype="multipart/form-data" name="adminForm" id="log-form" class="form-validate form-horizontal">

	
	<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'log')); ?>
	<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'log', Text::_('COM_ATELMAN_TAB_LOG', true)); ?>
	<div class="row-fluid">
		<div class="col-md-12 form-horizontal">
			<fieldset class="adminform">
				<legend><?php echo Text::_('COM_ATELMAN_FIELDSET_LOG'); ?></legend>
				<?php echo $this->form->renderField('id'); ?>
				<?php echo $this->form->renderField('section'); ?>
				<?php echo $this->form->renderField('action_type'); ?>
				<?php echo $this->form->renderField('action_by'); ?>
				<?php echo $this->form->renderField('which_id'); ?>
				<?php echo $this->form->renderField('remarks'); ?>
				<?php echo $this->form->renderField('before_update'); ?>
				<?php echo $this->form->renderField('after_update'); ?>
				<?php echo $this->form->renderField('action_date'); ?>
			</fieldset>
		</div>
	</div>
	<?php echo HTMLHelper::_('uitab.endTab'); ?>

	
	<?php echo HTMLHelper::_('uitab.endTabSet'); ?>

	<input type="hidden" name="task" value=""/>
	<?php echo HTMLHelper::_('form.token'); ?>

</form>
