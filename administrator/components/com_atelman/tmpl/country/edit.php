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
if ($this->item->id) {
	$name = "Country: " . $this->item->country;
} else {
	$name = "Add Country";
}
?>

<form
	action="<?php echo Route::_('index.php?option=com_atelman&layout=edit&id=' . (int) $this->item->id); ?>"
	method="post" enctype="multipart/form-data" name="adminForm" id="country-form" class="form-validate form-horizontal">


	<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'countrie')); ?>
	<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'countrie', Text::_($name, true)); ?>
	<div class="row-fluid">
		<div class="col-md-12 form-horizontal">
			<fieldset class="adminform">
				<?php echo $this->form->renderField('country'); ?>
			</fieldset>
		</div>
	</div>
	<?php echo HTMLHelper::_('uitab.endTab'); ?>


	<?php echo HTMLHelper::_('uitab.endTabSet'); ?>

	<input type="hidden" name="task" value="" />
	<?php echo HTMLHelper::_('form.token'); ?>

</form>