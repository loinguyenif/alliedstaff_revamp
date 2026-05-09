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
use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;

/**
 * Servicecontract controller class.
 *
 * @since  1.0.0
 */
class ServicecontractController extends FormController
{
	protected $view_list = 'servicecontracts';



	public function submitcontract()
	{
		$app   = Factory::getApplication();
		$post =	$app->input->post->getArray();
		$model	= $this->getModel('ServiceContract', 'Administrator');
		if ($sv_request_id = $model->submitcontract($post)) {
			$msg = Text::_('Service Contract has been created');
			//$tmp1 = $model->submitRMAConfirmationMail($rma_request_id);

		} else {
			$msg = Text::_('Service Contract has not been created');
		}

		$link = 'index.php?option=com_atelman&view=servicecontracts';

		$this->setRedirect($link, $msg);
	}

	public function save($key = null, $urlVar = null)
	{
		$task = $this->getTask();
		$app   = Factory::getApplication();
		$post		=	$app->input->post->getArray();
		$model 	= $this->getModel('Servicecontract', 'Administrator');

		if ($model->updateservicecontract($post)) {
			$msg = Text::_('Service Contract has been updated');
		} else {
			$msg = Text::_('Error : Service Contract has not been updated!');
		}

		if ($task == "apply") {
			$link = 'index.php?option=com_atelman&view=servicecontract&layout=edit&id=' . $post['cid'];
		} else {
			$link = 'index.php?option=com_atelman&view=servicecontracts';
		}

		$this->setRedirect($link, $msg);
	}
}
