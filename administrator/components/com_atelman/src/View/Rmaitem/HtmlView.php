<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Atelman
 * @author     iFoundries <wpsub@ifoundries.com>
 * @copyright  2025 iFoundries
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Atelman\Component\Atelman\Administrator\View\Rmaitem;
// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use \Joomla\CMS\Toolbar\ToolbarHelper;
use \Joomla\CMS\Factory;
use \Atelman\Component\Atelman\Administrator\Helper\AtelmanHelper;
use \Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Toolbar\Toolbar;

/**
 * View class for a single Rmaitem.
 *
 * @since  1.0.0
 */
class HtmlView extends BaseHtmlView
{
	protected $state;

	protected $item;

	protected $form;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  Template name
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function display($tpl = null)
	{
		$app = \Joomla\CMS\Factory::getApplication();
		$layout = $this->getLayout();
		$toolbar = Toolbar::getInstance('toolbar');
		$this->user = Factory::getApplication()->getIdentity();
		$this->layout = $layout;

		// Check for errors.
		switch ($layout) {
			case 'updatermalist':

				if ($this->user->gid == 25 || $this->user->gid == 8 || $this->user->gid == 31 || $this->user->gid == 32 || $this->user->gid == 34) :
					$toolbar->save('rmaitem.saveUpdate', 'Save');
					$toolbar->apply('rmaitem.applyUpdate', 'Apply');
				endif;
				$toolbar->appendButton('Link', 'cancel', Text::_('Cancel'), 'index.php?option=com_atelman&view=rmaitems');

				$post = $app->input->post->getArray();

				if (empty($post)) {
					$get = $app->input->get->getArray();
					$cid = $get['cid'];
				} else {
					$cid = implode(",", $post['cid']);
				}

				$db = Factory::getDbo();
				$query = " SELECT * FROM #__at_rma_downloads WHERE rma_item_id IN(" . $cid . ") ";
				$db->setQuery($query);
				$files = $db->loadObjectList();

				$this->cid = $cid;
				$this->files = $files;
				break;

			case 'edit':
				$this->state = $this->get('State');
				$this->item  = $this->get('Item');
				$this->form  = $this->get('Form');
				$this->addToolbar();
				$currentUser	= Factory::getUser();
				$modelRmaItem = $this->getModel('Rmaitem', 'Administrator');

				$row = $this->item;

				if ($currentUser->gid == 24) { // distributor, must see from groups management first
					$group = $modelRmaItem->itemGroupXref($currentUser->id);
					$this->group = $group;
				}

				/* FILES */

				$filenames = $modelRmaItem->fileNameRMA($this->item->id);

				$filenameArr = array();

				foreach ($filenames as $f) :
					$filenameArr[$f->status_code . '-' . $f->is_airway_bill]['is_awb'] = $f->is_airway_bill;
					$filenameArr[$f->status_code . '-' . $f->is_airway_bill]['title'] = $f->status_name . (($f->is_airway_bill == 1) ? ' - Airway Bill' : '');
					$filenameArr[$f->status_code . '-' . $f->is_airway_bill]['object_file'][] = $f->id . "|" . date("d/m/Y H:i", strtotime($f->created_date)) . "|" . $f->filename;
				endforeach;

				/* FILES - RMA ORDER / REQUEST */
				$filenames = $modelRmaItem->fileNameRMAOrderRequest($this->item->id);

				$filenameRMAArr = array();
				if (!empty($filenames)) {
					foreach ($filenames as $f) :
						$status = '';
						switch ($f->status) {
							case 'RMAORDER':
								$status = 'RMA Order';
								break;
							case 'RMAREQUEST':
								$status = 'RMA Request';
								break;
						}
						$filenameRMAArr[$f->status]['title'] = $status;
						$filenameRMAArr[$f->status]['object_file'][] = $f->id . "|" . date("d/m/Y H:i", @strtotime(@$f->created_date)) . "|" . $f->filename;
					endforeach;
				}
				if ($row->replacement_date != '0000-00-00 00:00:00') $replacement_date = date("d/m/Y", @strtotime($row->replacement_date));
				//if($row->rma_assigned_date != '0000-00-00') $rma_assigned_date = date("d/m/Y",strtotime($row->rma_assigned_date));
				if ($row->received_date != '0000-00-00 00:00:00') $received_date = date("d/m/Y", @strtotime($row->received_date));
				if ($row->shipped_date != '0000-00-00 00:00:00') $shipped_date = date("d/m/Y", @strtotime($row->shipped_date));
				if ($row->closed_date != '0000-00-00 00:00:00') $closed_date = date("d/m/Y", @strtotime($row->closed_date));

				if ($currentUser->gid == 25 || $currentUser->gid == 8 || $currentUser->gid == 32 || $currentUser->gid == 34) {
					$lists['replacement_date'] 	=  HTMLHelper::_('calendar', $row->replacement_date, 'replacement_date', 'replacement_date', '%d/%m/%Y', ' class="validate-dates" onblur="javascript:void(0)" ') . "<br />Date Format : DD/MM/YYYY ";
					//$lists['rma_assigned_date'] 	=  JHTML::calendar($rma_assigned_date, 'rma_assigned_date', 'rma_assigned_date', '%d/%m/%Y', ' class="validate-dates" onblur="javascript:void(0)" ')."<br />Date Format : DD/MM/YYYY ";
					$lists['received_date'] 	=  HTMLHelper::_('calendar', $row->received_date, 'received_date', 'received_date', '%d/%m/%Y', ' class="validate-dates" onblur="javascript:void(0)" ') . "<br />Date Format : DD/MM/YYYY ";
					$lists['shipped_date'] 		=  HTMLHelper::_('calendar', $row->shipped_date, 'shipped_date', 'shipped_date', '%d/%m/%Y', ' class="validate-dates" onblur="javascript:void(0)" ') . "<br />Date Format : DD/MM/YYYY ";
					$lists['closed_date'] 		=  HTMLHelper::_('calendar', $row->closed_date, 'closed_date', 'closed_date', '%d/%m/%Y', ' class="validate-dates" onblur="javascript:void(0)" ') . "<br />Date Format : DD/MM/YYYY ";
				}

				$row->real_expired_date = '-';

				if ($row->expired_date_manual != '0000-00-00 00:00:00' && isset($row->expired_date_manual)):
					$row->real_expired_date = date("m/Y", strtotime($row->expired_date_manual));
				elseif ($row->extended_expired_date != '0000-00-00 00:00:00' && isset($row->extended_expired_date)) :
					$row->real_expired_date = date("d/m/Y", strtotime($row->extended_expired_date));
				elseif ($row->expired_date != '0000-00-00 00:00:00' && isset($row->expired_date)) :
					$row->real_expired_date = date("d/m/Y", strtotime($row->expired_date));
				endif;

				$row->purchase_date = date("d/m/Y", @strtotime($row->purchase_date));
				$row->rma_request_date = date("d/m/Y", @strtotime($row->rma_request_date));


				$this->item = $row;
				$this->layout = $layout;
				$this->user = $currentUser;
				$this->downloads = $filenameArr;
				$this->rmas = $filenameRMAArr;
				$this->lists = @$lists;

				if ($currentUser->gid == 25 || $currentUser->gid == 8 || $currentUser->gid == 32 || $currentUser->gid == 34 || $currentUser->gid == 31) {
					// rma statuses
					$statusesHTML = AtelmanHelper::statusTypeUpdate($row->status);
				} else {
					$statusesHTML = AtelmanHelper::statusItem($row->status);
				}

				$this->statusesHTML = $statusesHTML;
				break;
		}

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	protected function addToolbar()
	{
		Factory::getApplication()->input->set('hidemainmenu', true);
		$layout = $this->getLayout();
		$user  = Factory::getApplication()->getIdentity();
		$isNew = ($this->item->id == 0);

		if (isset($this->item->checked_out)) {
			$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
		} else {
			$checkedOut = false;
		}

		$canDo = AtelmanHelper::getActions();

		ToolbarHelper::title(Text::_('RMA Order : ' . $this->item->rmacode), "generic");

		// If not checked out, can save the item.
		if (!$checkedOut && ($canDo->get('core.edit') || ($canDo->get('core.create')))) {
			if (
				$user->gid == 8 ||
				$user->gid == 25 ||
				$user->gid == 32 ||
				$user->gid == 34 ||
				($user->gid == 31 && ($this->item->status == 'await' || $this->item->status == 'ship' || $this->item->status == 'ship_close' || $this->item->status == 'receive' || $this->item->status == 'receive_close'))
			) {
				ToolbarHelper::save('rmaitem.save', 'Save');
				if ($layout == 'edit') {
					ToolbarHelper::apply('rmaitem.apply', 'Apply');
				}
			}
		}

		$toolbar = Toolbar::getInstance('toolbar');
		$toolbar->appendButton('Link', 'cancel', Text::_('Cancel'), 'index.php?option=com_atelman&view=rmaitems');
	}
}
