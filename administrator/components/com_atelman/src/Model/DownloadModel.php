<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Atelman
 * @author     iFoundries <wpsub@ifoundries.com>
 * @copyright  2025 iFoundries
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Atelman\Component\Atelman\Administrator\Model;
// No direct access.
defined('_JEXEC') or die;

use Atelman\Component\Atelman\Administrator\Helper\AtelmanHelper;
use \Joomla\CMS\Table\Table;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Plugin\PluginHelper;
use \Joomla\CMS\MVC\Model\AdminModel;
use \Joomla\CMS\Helper\TagsHelper;
use \Joomla\CMS\Filter\OutputFilter;
use \Joomla\CMS\Event\Model;
use Joomla\CMS\Event\AbstractEvent;
use Joomla\CMS\Mail\MailFactoryInterface;
use Mpdf\Mpdf;


/**
 * Log model.
 *
 * @since  1.0.0
 */
class DownloadModel extends AdminModel
{
	/**
	 * @var    string  The prefix to use with controller messages.
	 *
	 * @since  1.0.0
	 */
	protected $text_prefix = 'COM_ATELMAN';

	/**
	 * @var    string  Alias to manage history control
	 *
	 * @since  1.0.0
	 */
	public $typeAlias = 'com_atelman.log';

	/**
	 * @var    null  Item data
	 *
	 * @since  1.0.0
	 */
	protected $item = null;




	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   string  $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  Table    A database object
	 *
	 * @since   1.0.0
	 */
	public function getTable($type = 'Log', $prefix = 'Administrator', $config = array())
	{
		return parent::getTable($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      An optional array of data for the form to interogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  \JForm|boolean  A \JForm object on success, false on failure
	 *
	 * @since   1.0.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app = Factory::getApplication();

		// Get the form.
		$form = $this->loadForm(
			'com_atelman.log',
			'log',
			array(
				'control' => 'jform',
				'load_data' => $loadData
			)
		);



		if (empty($form)) {
			return false;
		}

		return $form;
	}

	/*
			*
			*	Delete 
			*
		*/
	public function deleteFileRequest($id)
	{
		if (empty($id)) return false;

		$db = JFactory::getDBO();

		$query = " SELECT d.filename, d.rma_item_id, i.rmacode, w.serial_no FROM #__at_rma_downloads AS d "
			.	" LEFT JOIN #__at_rma_items AS i ON i.id = d.rma_item_id "
			. " LEFT JOIN #__at_warranty_items AS w ON w.id = i.warranty_item_id "
			.	" WHERE d.id = '$id' LIMIT 1 ";

		$db->setQuery($query);
		$data = $db->loadObject();

		$filename = $data->filename;
		$rma_item_id = $data->rma_item_id;
		$rmacode = $data->rmacode;
		$serial_no = $data->serial_no;

		/* check whether this file is the only one in #__rma_downloads, if so, delete the file */
		if ($this->checkFilename($filename)) {

			$path = JPATH_ADMINISTRATOR . '/atelesis_docs';
			$direct_to_file = $path . '/' . $filename;

			unlink($direct_to_file);
		}

		$query = " DELETE FROM #__at_rma_downloads WHERE id = '$id' ";
		$db->setQuery($query);
		$result = $db->query();

		return $result;
	}

	public function download($id)
	{
		$db = Factory::getDBO();

		$row = $this->getItemDown($id);

		$filename = $row->filename;

		$path = JPATH_ADMINISTRATOR . '/atelesis_docs';
		$direct_to_file = $path . '/' . $filename;

		if (!file_exists($direct_to_file)) return false;

		$now = gmdate("D, d M Y H:i:s");

		header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
		header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
		header("Last-Modified: {$now} GMT");

		// force download
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");

		// disposition / encoding on response body
		header("Content-Disposition: attachment;filename={$filename}");
		header("Content-Transfer-Encoding: binary");

		// Send the file contents.
		readfile($direct_to_file);
		exit;
	}

	public function getItemDown($id)
	{

		$db = Factory::getDBO();
		$query = " SELECT * FROM #__at_rma_downloads WHERE id = '$id' LIMIT 1 ";
		$db->setQuery($query);
		$row = $db->loadObject();
		return $row;
	}

	/*
		*
		* If Filename Result > 1, return false ( do not delete file )
		*
		*/
	public function checkFilename($filename)
	{

		$db = JFactory::getDBO();

		$query = " SELECT COUNT(id) FROM #__at_rma_downloads WHERE filename = '$filename' ";
		$db->setQuery($query);
		$result = $db->loadResult();

		if ($result > 1) {
			return false;
		} else {
			return true;
		}
	}
}
