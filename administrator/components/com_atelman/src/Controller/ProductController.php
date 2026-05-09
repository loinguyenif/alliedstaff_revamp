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
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Factory;

/**
 * Product controller class.
 *
 * @since  1.0.0
 */
class ProductController extends FormController
{
	protected $view_list = 'products';

	public function save($key = null, $urlVar = null)
	{
		$task = $this->getTask();
		$model 		= $this->getModel('Product', 'Administrator');
		$app   = Factory::getApplication();
		$data = $this->input->get('jform', [], 'array');
		$keyId = $this->input->getInt('id');
		if ($keyId) {
			$data['id'] = $keyId;
		}

		if ($row = $model->save($data)) {
			$msg = Text::_('Product Saved ! ');
		} else {
			$msg = Text::_('Error : Product Not Saved');
		}
		if ($task == 'apply') {
			$link = 'index.php?option=com_atelman&view=product&layout=edit&id=' . $row->id;
			$this->setRedirect($link, $msg);
			return;
		}
		$link = 'index.php?option=com_atelman&view=products';
		$this->setRedirect($link, $msg);


		// $app = \Joomla\CMS\Factory::getApplication();
		// $data = $this->input->get('jform', [], 'array');

		// $table = $this->getTable('Product');

		// if (!$table->bind($data)) {
		// 	$app->enqueueMessage($table->getError(), 'error');
		// 	return false;
		// }

		// if (!$table->check()) {
		// 	$app->enqueueMessage($table->getError(), 'error');
		// 	return false;
		// }

		// if (!$table->store()) {
		// 	$app->enqueueMessage($table->getError(), 'error');
		// 	return false;
		// }

		// $app->enqueueMessage('Product saved successfully!', 'message');
		// return true;
	}
}
