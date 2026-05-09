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

use \Joomla\CMS\Table\Table;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Plugin\PluginHelper;
use \Joomla\CMS\MVC\Model\AdminModel;
use \Joomla\CMS\Helper\TagsHelper;
use \Joomla\CMS\Filter\OutputFilter;
use \Joomla\CMS\Event\Model;
use Joomla\CMS\Event\AbstractEvent;


/**
 * Product model.
 *
 * @since  1.0.0
 */
class ProductModel extends AdminModel
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
	public $typeAlias = 'com_atelman.product';

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
	public function getTable($type = 'Product', $prefix = 'Administrator', $config = array())
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
			'com_atelman.product',
			'product',
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



	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since   1.0.0
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = Factory::getApplication()->getUserState('com_atelman.edit.product.data', array());

		if (empty($data)) {
			if ($this->item === null) {
				$this->item = $this->getItem();
			}

			$data = $this->item;
		}

		return $data;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed    Object on success, false on failure.
	 *
	 * @since   1.0.0
	 */
	public function getItem($pk = null)
	{

		if ($item = parent::getItem($pk)) {
			if (isset($item->params)) {
				$item->params = json_encode($item->params);
			}

			// Do any procesing on fields here if needed
		}

		return $item;
	}

	/**
	 * Method to duplicate an Product
	 *
	 * @param   array  &$pks  An array of primary key IDs.
	 *
	 * @return  boolean  True if successful.
	 *
	 * @throws  Exception
	 */
	public function duplicate(&$pks)
	{
		$app = Factory::getApplication();
		$user = $app->getIdentity();
		$dispatcher = $this->getDispatcher();

		// Access checks.
		if (!$user->authorise('core.create', 'com_atelman')) {
			throw new \Exception(Text::_('JERROR_CORE_CREATE_NOT_PERMITTED'));
		}

		$context    = $this->option . '.' . $this->name;

		// Include the plugins for the save events.
		PluginHelper::importPlugin($this->events_map['save']);

		$table = $this->getTable();

		foreach ($pks as $pk) {

			if ($table->load($pk, true)) {
				// Reset the id to create a new record.
				$table->id = 0;

				if (!$table->check()) {
					throw new \Exception($table->getError());
				}


				// Create the before save event.
				$beforeSaveEvent = AbstractEvent::create(
					$this->event_before_save,
					[
						'context' => $context,
						'subject' => $table,
						'isNew'   => true,
						'data'    => $table,
					]
				);

				// Trigger the before save event.
				$dispatchResult = Factory::getApplication()->getDispatcher()->dispatch($this->event_before_save, $beforeSaveEvent);

				// Check if dispatch result is an array and handle accordingly
				$result = isset($dispatchResult['result']) ? $dispatchResult['result'] : [];

				// Proceed with your logic
				if (in_array(false, $result, true) || !$table->store()) {
					throw new \Exception($table->getError());
				}

				// Trigger the after save event.
				Factory::getApplication()->getDispatcher()->dispatch(
					$this->event_after_save,
					AbstractEvent::create(
						$this->event_after_save,
						[
							'context'    => $context,
							'subject'    => $table,
							'isNew'      => true,
							'data'       => $table,
						]
					)
				);
			} else {
				throw new \Exception($table->getError());
			}
		}

		// Clean cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @param   Table  $table  Table Object
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	protected function prepareTable($table)
	{
		jimport('joomla.filter.output');

		if (empty($table->id)) {
			// Set ordering to the last item if not set
			if (@$table->ordering === '') {
				$db = $this->getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__at_products');
				$max             = $db->loadResult();
				$table->ordering = $max + 1;
			}
		}
	}


	public function save($post)
	{
		$db = Factory::getDBO();
		$user = Factory::getUser();
		$atelproduct = $this->getTable('Product');

		if (!$atelproduct->bind($post)) {
			return false;
		}

		$atelproduct->id = $post['id'];

		$item = array();

		if ($post['id']) {
			$query = " SELECT * FROM #__at_products WHERE id = " . $db->Quote($post['id'], false);
			$db->setQuery($query);
			$item = $db->loadObject();
		}

		if ($atelproduct->store()) {
			PluginHelper::importPlugin('atelesis', 'logs');
			$dispatcher = Factory::getApplication()->getDispatcher();
			$log = new \stdClass();
			$log->section = 'PRODUCT_ITEM';
			if ($post['id']) {
				$log->action_type = 'EDIT_SAVE';
			} else {
				$log->action_type = 'NEW_SAVE';
			}
			$log->action_by = $user->id;
			$log->action_remarks = 'Saving Product Item on Part Number : ' . (($post['product_no']) ? $post['product_no'] : 'N/A');
			$log->id = (($item->id) ? $item->id : $atelproduct->id);

			$before_update = json_encode($item);
			$after_update = json_encode($post);

			$event = new \Joomla\Event\Event('onAfterAction', [
				'log' => $log,
				'before' => $before_update,
				'after' => $after_update,
			]);

			// Fire the onAfterStoreUser trigger
			$dispatcher->dispatch('onAfterAction', $event);

			return $atelproduct;
		}
		return false;
	}
}
