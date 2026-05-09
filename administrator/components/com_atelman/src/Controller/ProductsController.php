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

use Atelman\Component\Atelman\Administrator\Helper\AtelmanHelper;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Utilities\ArrayHelper;
use stdClass;

/**
 * Products list controller class.
 *
 * @since  1.0.0
 */
class ProductsController extends AdminController
{
	/**
	 * Method to clone existing Products
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

		$this->setRedirect('index.php?option=com_atelman&view=products');
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
	public function getModel($name = 'Product', $prefix = 'Administrator', $config = array())
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




	public function CSVProductInsert($file)
	{
		$db = Factory::getContainer()->get('DatabaseDriver');
		setlocale(LC_ALL, 'en_US.UTF-8');
		set_time_limit(0);

		if (!$file)
			return false;

		$src = $file['tmp_name'];
		$header = array();
		$fp = fopen($src, 'r');
		$line = fgetcsv($fp, 4096); // database header;

		foreach ($line as $t) :
			$header[] = $t;
		endforeach;

		$text = '';

		while (($line = fgetcsv($fp, 5000, ',')) !== FALSE) {
			if ($line) {
				$empty	=	false;

				foreach ($line as $t) :
					if (!empty($t)) {
						$notempty = true;
						break;
					}
				endforeach;

				if ($notempty) {
					$data = array();
					for ($i = 0; $i < count($header); $i++) {
						$dt		=	str_replace("'", "\'", $line[$i]);
						$dt		=	str_replace('"', '\"', $dt);

						$data[] = "'" . $dt . "'";
					}


					$fieldcount			= count($data);

					if ($fieldcount != 5)
						return false;

					$atelproduct = new stdClass();
					$atelproduct->product_no 	= $line[0];
					$atelproduct->model_no 		= $line[1];
					$atelproduct->product_name 	= $line[2];
					$atelproduct->warranty 		= $line[3];
					$atelproduct->is_previous3years = $line[4];

					// check row
					$rowProduct = AtelmanHelper::checkRowProduct($atelproduct->product_no);
					if ($rowProduct) {
						//update
						$atelproduct->id = $rowProduct;
						$db->updateObject('#__at_products', $atelproduct, 'id');
					} else {
						//insert
						$db->insertObject('#__at_products', $atelproduct);
					}
				}
			}
		}
		fclose($fp);

		return true;
	}

	public function importCSV()
	{

		$app   = \Joomla\CMS\Factory::getApplication();
		$input = $app->input;
		$file  = $input->files->get('csvfile', null, 'array');

		if ($file && $file['error'] === UPLOAD_ERR_OK) {
			$this->CSVProductInsert($file);
			$app->enqueueMessage('CSV uploaded and processed successfully');
		} else {
			$app->enqueueMessage('File upload failed', 'error');
		}

		$this->setRedirect('index.php?option=com_atelman&view=products');
	}



	public function delete()
	{
		// Check for request forgeries
		$this->checkToken();

		// Get IDs from request
		$cid = $this->input->get('cid', array(), 'array');

		$cid = array_map('intval', $cid);
		if (empty($cid)) {
			$this->setMessage(Text::_('COM_PRODUCTS_NO_ITEM_SELECTED'), 'warning');
			$this->setRedirect('index.php?option=com_products&view=products');
			return false;
		}

		$model = $this->getModel('Products', 'Administrator');

		try {
			$count = $model->remove($cid);
			$this->setMessage(Text::sprintf('Product(s) Removed !', 'success'));
		} catch (\Exception $e) {
			$this->setMessage('Error : Product(s) Not Removed', 'error');
		}

		$this->setRedirect('index.php?option=com_atelman&view=products');
		return true;
	}
}
