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
class ReportModel extends AdminModel
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

	public function getReport($post)
	{
		$db = Factory::getDBO();

		$where = array();

		$orderby = '';
		$groupby = '';
		$content = '';
		$limit = '';

		$fieldSort = $post['fieldSort'];
		$fieldSortDir = $post['fieldSortDir'];

		if (isset($post['country_id'])) {
			$where[] = " u.country_id IN (" . implode(',', $post['country_id']) . ") ";
		}

		switch ($post['rma_report_type']) {
			case 'respond':

				if (empty($fieldSort)) {
					$fieldSort = 'respond_time';
				}
				if (empty($fieldSortDir)) {
					$fieldSortDir = 'DESC';
				}

				$where[] = " i.rma_assigned_date != '0000-00-00' ";
				$orderby = ' ORDER BY ' . $fieldSort . ' ' . $fieldSortDir;
				break;
			case 'receive':

				if (empty($fieldSort)) {
					$fieldSort = 'receive_time';
				}
				if (empty($fieldSortDir)) {
					$fieldSortDir = 'DESC';
				}

				$where[] = " i.received_date != '0000-00-00' ";
				$where[] = " i.rma_assigned_date != '0000-00-00' ";
				$orderby = ' ORDER BY ' . $fieldSort . ' ' . $fieldSortDir;

				break;
			case 'ship':

				if (empty($fieldSort)) {
					$fieldSort = 'ship_time';
				}
				if (empty($fieldSortDir)) {
					$fieldSortDir = 'DESC';
				}

				$where[] = " i.received_date != '0000-00-00' ";
				$where[] = " i.shipped_date != '0000-00-00' ";
				$orderby = ' ORDER BY ' . $fieldSort . ' ' . $fieldSortDir;
				break;
			case 'total':

				if (empty($fieldSort)) {
					$fieldSort = 'total_time';
				}
				if (empty($fieldSortDir)) {
					$fieldSortDir = 'DESC';
				}

				$where[] = " i.received_date != '0000-00-00' ";
				$where[] = " i.shipped_date != '0000-00-00' ";
				$orderby = ' ORDER BY ' . $fieldSort . ' ' . $fieldSortDir;

				break;
			case 'most-rma-country':

				if (empty($fieldSort)) {
					$fieldSort = 'country_name';
				}
				if (empty($fieldSortDir)) {
					$fieldSortDir = 'DESC';
				}

				$orderby = ' ORDER BY ' . $fieldSort . ' ' . $fieldSortDir;

				break;
			case 'most-rma-model':

				if (empty($fieldSort)) {
					$fieldSort = 'total';
				}
				if (empty($fieldSortDir)) {
					$fieldSortDir = 'DESC';
				}

				$orderby = ' ORDER BY ' . $fieldSort . ' ' . $fieldSortDir;

				break;
			default:

				break;
		}

		// From Date | To_Date
		switch ($post['rma_report_type']) {
			case 'most-rma-country':
			case 'most-rma-model':

				if ($post['from_date'] || $post['to_date']) {

					if ($post['from_date']) {
						$post['from_date'] = strtotime(str_replace('/', '-', $post['from_date']));
						$where[] = " i.created_date >= '" . date("Y-m-d 00:00:00", $post['from_date']) . "' ";
					}

					if ($post['to_date']) {
						$post['to_date'] = strtotime(str_replace('/', '-', $post['to_date']));
						$where[] = " i.created_date <= '" . date("Y-m-d 00:00:00", $post['to_date']) . "' ";
					}
				}

				break;
		}

		$where = (count($where) ? ' WHERE (' . implode(') AND (', $where) . ')' : '');

		$duration = '';
		switch ($post['rma_report_type']) {
			case 'respond':
				$duration = 'respond_time';
				break;
			case 'receive':
				$duration = 'receive_time';
				break;
			case 'ship':
				$duration = 'ship_time';
				break;
			case 'total':
				$duration = 'total_time';
				break;
		}

		switch ($post['rma_report_type']) {
			case 'respond':
			case 'receive':
			case 'ship':
			case 'total':

				$query = " SELECT i.*, c.country AS country_name , p.product_no,p.model_no, r.fullname, r.contact_name, "
					.	"	DATEDIFF(i.rma_assigned_date,i.created_date) AS respond_time , "
					.	" DATEDIFF(i.received_date, i.rma_assigned_date) AS receive_time, "
					.	" DATEDIFF(i.shipped_date, i.received_date) AS ship_time, "
					.	" DATEDIFF( IF(i.shipped_date >= i.received_date,i.shipped_date,i.received_date), i.created_date) AS total_time "
					.	" FROM #__at_rma_items AS i "
					.	" LEFT JOIN #__users AS u ON u.customer_id = i.customer_id "
					. " LEFT JOIN #__at_products AS p ON p.id = i.product_id "
					.	" LEFT JOIN #__at_rma_request AS r ON r.id = i.rma_request_id "
					. " LEFT JOIN #__at_countries AS c ON c.id = u.country_id "
					.	$where
					. $orderby
					. $limit;

				$db->setQuery($query);
				$rows = $db->loadObjectList();

				$content = '';

				if ($rows) {

					$content .= '
					<table border=1 width="100%" style="border-collapse:collapse;">
					<tr>
					<th>RMA Number</th>
					<th>Requestor</th>
					<th>Part Number</th>
					<th><a href="javascript:void(0);" onclick="javascript:sortFieldAndDirection(\'p.model_no\',\'' . (($fieldSort . ' ' . $fieldSortDir == 'p.model_no DESC') ? 'ASC' : 'DESC') . '\');">Model Number</a></th>
					<th>Requested S/N</th>
					<th><a href="javascript:void(0);" onclick="javascript:sortFieldAndDirection(\'country_name\',\'' . (($fieldSort . ' ' . $fieldSortDir == 'country_name DESC') ? 'ASC' : 'DESC') . '\');">Country</a></th>
					<th><a href="javascript:void(0);" onclick="javascript:sortFieldAndDirection(\'' . $duration . '\',\'' . (($fieldSort . ' ' . $fieldSortDir == $duration . ' DESC') ? 'ASC' : 'DESC') . '\');">Duration</a></th>
					</tr>
					';

					foreach ($rows as $r) {
						$content .= '
						<tr>
						<td>' . $r->rmacode . '</td>
						<td>' . $r->fullname . ' (' . $r->contact_name . ')</td>
						<td>' . $r->product_no . '</td>
						<td>' . $r->model_no . '</td>
						<td><a href="index.php?option=com_atelman&view=rmaitem&layout=edit&id=' . $r->id . '" target="_blank">' . $r->requested_sn . '</a></td>
						<td>' . $r->country_name . '</td>
						<td>' . $this->myduration($post['rma_report_type'], $r) . '</td>
						</tr>
						';
					}
				} else {

					$content .= 'No Report';
				}
				$content .= '</table>';
				break;


			case 'most-rma-country':
				$query = " SELECT p.product_no, p.model_no, u.country_id, c.country AS country_name "
					.	" FROM #__at_rma_items AS i "
					.	" LEFT JOIN #__users AS u ON u.customer_id = i.customer_id "
					. " LEFT JOIN #__at_products AS p ON p.id = i.product_id "
					.	" LEFT JOIN #__at_rma_request AS r ON r.id = i.rma_request_id "
					. " LEFT JOIN #__at_countries AS c ON c.id = u.country_id "
					.	$where
					. $limit;

				$db->setQuery($query);
				$rows = $db->loadObjectList();

				$content = '';

				if ($rows) {

					$array = array();

					// consolidate items
					foreach ($rows as $r) {

						if (!isset($array[$r->country_id]['total'])) {
							$array[$r->country_id]['total'] = 0;
						}

						if (!isset($array[$r->country_id]['items'][$r->product_no . '|' . $r->model_no]['total'])) {
							$array[$r->country_id]['items'][$r->product_no . '|' . $r->model_no]['total'] = 0;
						}

						$array[$r->country_id]['country_name'] = $r->country_name;
						$array[$r->country_id]['total']++;

						$array[$r->country_id]['items'][$r->product_no . '|' . $r->model_no]['total']++;
					}



					if ($fieldSort == 'total') {
						if ($fieldSortDir == 'DESC') {
							asort($array);
						} else {
							arsort($array);
						}
					} else if ($fieldSort == 'country_name') {
						if ($fieldSortDir == 'DESC') {
							$array = $this->record_sort($array, "country_name", true);
						} else {
							$array = $this->record_sort($array, "country_name", false);
						}
					}


					$content .= '
					<table border=1 width="100%" style="border-collapse:collapse;">
					<tr>
					<th><a href="javascript:void(0);" onclick="javascript:sortFieldAndDirection(\'country_name\',\'' . (($fieldSort . ' ' . $fieldSortDir == 'country_name DESC') ? 'ASC' : 'DESC') . '\');">Country</a></th>
					<th><a href="javascript:void(0);" onclick="javascript:sortFieldAndDirection(\'total\',\'' . (($fieldSort . ' ' . $fieldSortDir == 'total DESC') ? 'ASC' : 'DESC') . '\');">Total</a></th>
					</tr>
					';

					foreach ($array as $country_id => $r) {

						$content .= '
						<tr>
						<td><a href="javascript:void(0);" onclick="javascript:loadCountryDetail(' . $country_id . ')">' . $array[$country_id]['country_name'] . '</a></td>
						<td>' . $array[$country_id]['total'] . '</td>
						</tr>
						';

						if ($r['items']) {

							arsort($r['items']);

							$content .= '<tr>';
							$content .= '<td colspan="2" id="productCountryDetailTable' . $country_id . '" style="display:none;">';

							$content .= '<table border="1" style="border-collapse:collapse;margin:10px;">';
							$content .= '<tr><th>Part Number</th><th>Model Number</th><th>Quantity</th></tr>';

							foreach ($r['items'] as $prod => $item) {

								$tmp = explode("|", $prod);
								$content  .= '<tr><td>' . $tmp[0] . '</td><td>' . $tmp[1] . '</td><td>' . $item['total'] . '</td></tr>';
							}

							$content .= '</table>';
						}
					}

					$content .= '</table>';
				} else {
					$content .= 'No Report Available.';
				}

				break;
			case 'most-rma-model':
				$query = " SELECT c.country AS country_name, i.product_id , p.product_no, p.model_no "
					.	" FROM #__at_rma_items AS i "
					.	" LEFT JOIN #__users AS u ON u.customer_id = i.customer_id "
					. " LEFT JOIN #__at_products AS p ON p.id = i.product_id "
					.	" LEFT JOIN #__at_rma_request AS r ON r.id = i.rma_request_id "
					. " LEFT JOIN #__at_countries AS c ON c.id = u.country_id "
					.	$where
					. $limit;

				$db->setQuery($query);
				$rows = $db->loadObjectList();

				$content = '';

				if ($rows) {

					$array = array();

					$drop_pattern = array('/-00$/', '/-10$/', '/-20$/', '/-30$/', '/-40$/', '/-50$/', '/-60$/', '/-70$/', '/-80$/', '/-90$/');
					$replace_pattern = array('', '', '', '', '', '', '', '', '', '');

					// consolidate items
					foreach ($rows as $r) {

						$p = @preg_replace($drop_pattern, $replace_pattern, $r->product_no);
						$z = @preg_replace($drop_pattern, $replace_pattern, $r->model_no);

						$prodno = $p . '-XX';
						$modelno = $z . '-XX';

						$array[$prodno]['model_no'] = $modelno;

						if (!isset($array[$prodno]['total'])) {
							$array[$prodno]['total'] = 0;
						}

						if (!isset($array[$prodno]['items'][$r->product_no . '|' . $r->model_no . '|' . $r->country_name]['total'])) {
							$array[$prodno]['items'][$r->product_no . '|' . $r->model_no . '|' . $r->country_name]['total'] = 0;
						}

						if (!isset($array[$prodno]['country_name'])) {
							$array[$prodno]['country_name'] = array();
						}

						if (!in_array($r->country_name, $array[$prodno]['country_name'])) {
							$array[$prodno]['country_name'][] = $r->country_name;
						}

						$array[$prodno]['total']++;

						$array[$prodno]['items'][$r->product_no . '|' . $r->model_no . '|' . $r->country_name]['total']++;
					}



					if ($fieldSort == 'total') {

						$totals = array();
						foreach ($array as $key => $row) {
							$totals[$key] = $row['total'];
						}

						if ($fieldSortDir == 'DESC') {
							array_multisort($totals, SORT_DESC, $array);
						} else {
							array_multisort($totals, SORT_ASC, $array);
						}
					} else if ($fieldSort == 'model_no') {

						$modal_nos = array();
						foreach ($array as $key => $row) {
							$modal_nos[$key] = $row['model_no'];
						}

						if ($fieldSortDir == 'DESC') {
							array_multisort($modal_nos, SORT_DESC, $array);
						} else {
							array_multisort($modal_nos, SORT_ASC, $array);
						}
					}

					$content .= '
					<table border=1 width="100%" style="border-collapse:collapse;">
					<tr>
					<th>Part Number</th>
					<th><a href="javascript:void(0);" onclick="javascript:sortFieldAndDirection(\'model_no\',\'' . (($fieldSort . ' ' . $fieldSortDir == 'model_no DESC') ? 'ASC' : 'DESC') . '\');">Model Number</a></th>
					<th>Country</th>
					<th><a href="javascript:void(0);" onclick="javascript:sortFieldAndDirection(\'total\',\'' . (($fieldSort . ' ' . $fieldSortDir == 'total DESC') ? 'ASC' : 'DESC') . '\');">Total</a></th>
					</tr>
					';

					foreach ($array as $prodno_id => $r) {

						$content .= '
						<tr>
						<td><a href="javascript:void(0);" onclick="javascript:loadModelDetailTable(\'' . $prodno_id . '\')">' . $prodno_id . '</a></td>
						<td>' . $array[$prodno_id]['model_no'] . '</td>
						<td>' . implode(',', $array[$prodno_id]['country_name']) . '</td>
						<td>' . $array[$prodno_id]['total'] . '</td>
						</tr>
						';

						if ($r['items']) {

							arsort($r['items']);

							$content .= '<tr>';
							$content .= '<td colspan="3" id="partNumberDetailTable' . $prodno_id . '" style="display:none;">';

							$content .= '<table border="1" style="border-collapse:collapse;margin:10px;">';
							$content .= '<tr><th>Part Number</th><th>Model Number</th><th>Country</th><th>Quantity</th></tr>';

							foreach ($r['items'] as $prod => $item) {

								$tmp = explode("|", $prod);
								$content  .= '<tr><td>' . $tmp[0] . '</td><td>' . $tmp[1] . '</td><td>' . $tmp[2] . '</td><td>' . $item['total'] . '</td></tr>';
							}


							$content .= '</table>';
						}
					}
					$content .= '</table>';
				} else {
					$content .= 'No Report Available.';
				}

				break;
			default:

				break;
		}


		return $content;
	}


	private function myduration($type, $data)
	{

		//type cast, current time, difference in timestamps
		$timestamp      = 0;
		$current_time   = 0;
		$diff           = 0;

		$data->created_date = date("d-m-Y", strtotime($data->created_date));

		switch ($type) {
			case 'respond':

				$timestamp      = (int) strtotime($data->created_date);
				$current_time   = (int) strtotime($data->rma_assigned_date);
				$diff           = $current_time - $timestamp;

				break;
			case 'receive':

				//type cast, current time, difference in timestamps
				$timestamp      = (int) strtotime($data->rma_assigned_date);
				$current_time   = (int) strtotime($data->received_date);
				$diff           = $current_time - $timestamp;

				break;
			case 'ship':

				//type cast, current time, difference in timestamps
				$timestamp      = (int) strtotime($data->received_date);
				$current_time   = (int) strtotime($data->shipped_date);
				$diff           = $current_time - $timestamp;

				break;
			case 'total':

				$latest_date = $data->shipped_date;
				if (strtotime($data->received_date) > strtotime($data->shipped_date)) {
					$latest_date = $data->received_date;
				}

				//type cast, current time, difference in timestamps
				$timestamp      = (int) strtotime($data->created_date);
				$current_time   = (int) strtotime($latest_date);
				$diff           = $current_time - $timestamp;
				break;
		}

		//intervals in seconds
		$intervals      = array('day' => 86400); // 'year' => 31556926, 'month' => 2629744, 'hour' => 3600, 'minute'=> 60, 'week' => 604800

		//now we just find the difference
		/*if ($diff == 0){ return 'just now';}
				
				if ($diff < 60){return $diff == 1 ? $diff . ' second ago' : $diff . ' seconds ago';}
				
				if ($diff >= 60 && $diff < $intervals['hour'])
				{
				$diff = floor($diff/$intervals['minute']);
				return $diff == 1 ? $diff . ' minute ago' : $diff . ' minutes ago';
				}
				
				if ($diff >= $intervals['hour'] && $diff < $intervals['day'])
				{
				$diff = floor($diff/$intervals['hour']);
				return $diff == 1 ? $diff . ' hour ago' : $diff . ' hours ago';
				}
			*/

		//if ($diff >= $intervals['day'] && $diff < $intervals['week'])
		//{
		$diff = floor($diff / $intervals['day']);
		return $diff == 1 ? $diff . ' day' : $diff . ' days';
		//}

		/*if ($diff >= $intervals['week'] && $diff < $intervals['month'])
				{
				$diff = floor($diff/$intervals['week']);
				return $diff == 1 ? $diff . ' week ago' : $diff . ' weeks';
				}
				
				if ($diff >= $intervals['month'] && $diff < $intervals['year'])
				{
				$diff = floor($diff/$intervals['month']);
				return $diff == 1 ? $diff . ' month ago' : $diff . ' months';
				}
				
				if ($diff >= $intervals['year'])
				{
				$diff = floor($diff/$intervals['year']);
				return $diff == 1 ? $diff . ' year ago' : $diff . ' years';
			}*/
	}



	/* Function Sort */
	private function record_sort($records, $field, $reverse = false)
	{
		$hash = array();
		foreach ($records as $record) {
			$hash[$record[$field]] = $record;
		}
		($reverse) ? krsort($hash) : ksort($hash);
		$records = array();
		foreach ($hash as $record) {
			$records[] = $record;
		}
		return $records;
	}
}
