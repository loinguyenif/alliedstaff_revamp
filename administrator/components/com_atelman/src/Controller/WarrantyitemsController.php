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

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Utilities\ArrayHelper;

/**
 * Warrantyitems list controller class.
 *
 * @since  1.0.0
 */
class WarrantyitemsController extends AdminController
{
	/**
	 * Method to clone existing Warrantyitems
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 */
	public function duplicate()
	{
		// Check for request forgeries
		$this->checkToken();

		// Get id(s)
		$pks = $this->input->post->get('cid', array(), 'array');

		try {
			if (empty($pks)) {
				throw new \Exception(Text::_('COM_ATELMAN_NO_ELEMENT_SELECTED'));
			}

			ArrayHelper::toInteger($pks);
			$model = $this->getModel();
			$model->duplicate($pks);
			$this->setMessage(Text::_('COM_ATELMAN_ITEMS_SUCCESS_DUPLICATED'));
		} catch (\Exception $e) {
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
		}

		$this->setRedirect('index.php?option=com_atelman&view=warrantyitems');
	}

	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    Optional. Model name
	 * @param   string  $prefix  Optional. Class prefix
	 * @param   array   $config  Optional. Configuration array for model
	 *
	 * @return  object	The Model
	 *
	 * @since   1.0.0
	 */
	public function getModel($name = 'Warrantyitem', $prefix = 'Administrator', $config = array())
	{
		return parent::getModel($name, $prefix, array('ignore_request' => true));
	}



	/**
	 * Method to save the submitted ordering values for records via AJAX.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 *
	 * @throws  Exception
	 */
	public function saveOrderAjax()
	{
		// Get the input
		$pks   = $this->input->post->get('cid', array(), 'array');
		$order = $this->input->post->get('order', array(), 'array');

		// Sanitize the input
		ArrayHelper::toInteger($pks);
		ArrayHelper::toInteger($order);

		// Get the model
		$model = $this->getModel();

		// Save the ordering
		$return = $model->saveorder($pks, $order);

		if ($return) {
			echo "1";
		}

		// Close the application
		Factory::getApplication()->close();
	}

	public function csv_update()
	{
		$this->checkToken();

		$app = \Joomla\CMS\Factory::getApplication();
		$post	=	$app->input->post->getArray();

		$model		= $this->getModel('Csv', 'Administrator');

		if ($row = $model->CSVWarrantyRegistrationUpdate()) {
			$msg = Text::_('Warranty Registration Updated');
		} else {
			$msg = Text::_('Warranty Registration is not updated. Please follow the note below', 'error');
		}
		$link = 'index.php?option=com_atelman&view=warrantyitem&layout=updateregwarranty';

		$this->setRedirect($link, $msg);
	}


	public function import_isb()
	{

		$this->checkToken();


		$model		= $this->getModel('Csv', 'Administrator');

		if ($model->CSVimportfile('isb')) {
			$msg = Text::_('Import isbdata.csv has been successfull !! ');
		} else {
			$msg = Text::_('Error : Import isbdata.csv has been successfull !');
		}

		$link = 'index.php?option=com_atelman&view=warrantyitems';

		$this->setRedirect($link, $msg);
	}

	public function import_isbdata()
	{

		$this->checkToken();

		$model		= $this->getModel('Csv', 'Administrator');

		if ($model->CSVimportfile('isbdata')) {
			$msg = Text::_('Import isbdata.csv has been successfull ! ');
		} else {
			$msg = Text::_('Error : Import isbdata.csv has been failed !');
		}

		$link = 'index.php?option=com_atelman&view=warrantyitems';

		$this->setRedirect($link, $msg);
	}


	public function delete()
	{
		// Check for request forgeries
		$this->checkToken();

		// Get IDs from request
		$cid = $this->input->get('cid', array(), 'array');

		$cid = array_map('intval', $cid);
		if (empty($cid)) {
			$this->setMessage(Text::_('Need choose items'), 'warning');
			$this->setRedirect('index.php?option=com_atelman&view=warrantyitems');
			return false;
		}

		$model = $this->getModel('Warrantyitems', 'Administrator');

		try {
			$count = $model->remove($cid);
			$this->setMessage(Text::sprintf('Warranty Registration(s) Removed ', 'success'));
		} catch (\Exception $e) {
			$this->setMessage('Error : Warranty Registration(s) Not Removed', 'error');
		}

		$this->setRedirect('index.php?option=com_atelman&view=warrantyitems');
		return true;
	}
}
