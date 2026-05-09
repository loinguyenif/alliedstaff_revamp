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

/**
 * Rmaitem controller class.
 *
 * @since  1.0.0
 */
class RmaitemController extends FormController
{
	protected $view_list = 'rmaitems';

	public function save($key = null, $urlVar = null)
	{
		$task = $this->getTask();
		$app   = Factory::getApplication();
		$post		=	$app->input->post->getArray();

		$model = $this->getModel('Rmaitem', 'Administrator');
		$mails  = $this->getModel('Mails', 'Administrator');

		if ($result = $model->save($post)) {
			$msg = $app->enqueueMessage('RMA Request(s) Saved!', 'message');
			if ($task != "apply") {
				//send email
				$tmp = $mails->sendmail('rmarequest');
			}
		} else {
			$msg = $model->getError();
			$this->setMessage($msg, 'error');
		}
		if ($task == "apply") {
			$link = 'index.php?option=com_atelman&view=rmaitem&layout=edit&id=' . $post['cid'];
		} else {
			$link = 'index.php?option=com_atelman&view=rmaitems';
		}

		$this->setRedirect($link, $msg);
	}

	public function saveUpdate($key = null)
	{
		$app   = Factory::getApplication();
		$post		=	$app->input->post->getArray();

		$model = $this->getModel('Rmarequest', 'Administrator');
		$mails 		= $this->getModel('Mails', 'Administrator');

		if ($model->updaterma($post)) {
			// send email per CIDs
			$tmp = $mails->sendMailByIdBatch($post['cid']);
			$msg = $app->enqueueMessage('RMA Item(s) has been updated and Notification Email(s) have been sent', 'message');
		} else {

			$msg = Text::_('Error : RMA Item(s) has not been updated!');
			$this->setMessage($msg, 'error');
		}

		$link = 'index.php?option=com_atelman&view=rmaitems';
		$this->setRedirect($link, $msg);
	}

	public function applyUpdate($key = null)
	{
		$app   = Factory::getApplication();
		$post =	$app->input->post->getArray();
		$model = $this->getModel('Rmarequest', 'Administrator');

		if ($model->updaterma($post)) {
			$msg = $app->enqueueMessage('RMA Item(s) has been updated');
		} else {
			$msg = Text::_('Error : RMA Item(s) has not been updated!');
			$this->setMessage($msg, 'error');
		}

		$link = 'index.php?option=com_atelman&view=rmaitem&layout=updatermalist&cid=' . $post['cid'];
		$this->setRedirect($link, $msg);
	}
}
