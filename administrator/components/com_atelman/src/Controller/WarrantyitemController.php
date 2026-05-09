<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Atelman
 * @author     iFoundries <wpsub@ifoundries.com>
 * @copyright  2025 iFoundries
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Atelman\Component\Atelman\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Language\Text;
use \Joomla\CMS\Factory;

/**
 * Warrantyitem controller class.
 *
 * @since  1.0.0
 */
class WarrantyitemController extends FormController
{
	protected $view_list = 'warrantyitems';

	public function save($key = null, $urlVar = null)
	{

		$task = $this->getTask();
		$app   = Factory::getApplication();
		$post		=	$app->input->post->getArray();
		$model 		= $this->getModel('warrantyitem', 'Administrator');

		if ($row = $model->save($post)) {
			$msg = Text::_('Warranty Registration(s) Saved ! ');
		} else {
			$msg = Text::_('Error : Warranty Registration(s) Not Saved OR Product No. does not exist');
		}

		if ($task == 'apply') {
			$link = 'index.php?option=com_atelman&view=warrantyitem&layout=edit&id=' . $row->id;
			$this->setRedirect($link, $msg);
			return;
		}
		$link = 'index.php?option=com_atelman&view=warrantyitems';
		$this->setRedirect($link, $msg);
	}
}
