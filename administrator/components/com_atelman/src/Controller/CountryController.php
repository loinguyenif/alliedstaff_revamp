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
 * Country controller class.
 *
 * @since  1.0.0
 */
class CountryController extends FormController
{
	protected $view_list = 'countries';

	public function save($key = null, $urlVar = null)
	{
		$task = $this->getTask();
		$model 		= $this->getModel('Country', 'Administrator');
		$app   = Factory::getApplication();
		$data = $this->input->get('jform', [], 'array');
		$keyId = $this->input->getInt('id');
		if ($keyId) {
			$data['id'] = $keyId;
		}

		if ($row = $model->save($data)) {
			$msg = Text::_('Country Manager Saved ! ');
		} else {
			$msg = Text::_('Error : Product Manager Not Saved');
		}
		if ($task == 'apply') {
			$link = 'index.php?option=com_atelman&view=country&layout=edit&id=' . $row->id;
			$this->setRedirect($link, $msg);
			return;
		}
		$link = 'index.php?option=com_atelman&view=countries';
		$this->setRedirect($link, $msg);
	}
}
