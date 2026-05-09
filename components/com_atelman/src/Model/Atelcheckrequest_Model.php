<?php
/**
 * @version		$Id: search.php 19343 2010-11-03 18:12:02Z ian $
 * @package		Joomla
 * @subpackage	Search
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport( 'joomla.application.component.model' );

/**
 * @package		Joomla
 * @subpackage	Search
 */
class AtelmanModelAtelCheckRequest extends JModel
{

	var $lists = '';

	/**
	 * Overridden constructor
	 * @access	protected
	 */
	function __construct()
	{
		parent::__construct();
	}

	function getRMAStatus($rmacode = '') {
		
		if(!$rmacode)
			return false;
		
		$db =& JFactory::getDBO();
		
		$query = " SELECT i.`status`, s.status_name, i.shipping_duration, i.received_date, i.shipped_date, i.closed_date FROM #__at_rma_items AS i "
						.	" LEFT JOIN #__at_rma_status AS s ON s.status_code = i.status "
						.	" WHERE i.rmacode = ". $db->Quote($rmacode,false)
						.	" LIMIT 1 ";
			$db->setQuery( $query );
			$status = $db->loadObject();
		
		return $status;
	}
	
	function getWarrantyStatus($post) {
	
		// helper
		$helper					= 	new ATelmanHelper();
		$db =& JFactory::getDBO();
		
		$po_no 		= strtolower($post['po_no']);
		$so_no 		= strtolower($post['so_no']);
		$invoice_no = strtolower($post['invoice_no']);
		$serial_no	= strtolower($post['serial_no']);
		
		if(!$po_no && !$so_no && !$invoice_no && !$serial_no) 
			return false;
		
		if($po_no) {
			$where[] = ' LOWER(w.po_no) = '. $db->Quote($po_no);
		}
		
		if($so_no) {
			$where[] = ' LOWER(w.so_no) = '. $db->Quote($so_no);
		}
		
		if($invoice_no) {
			$where[] = ' LOWER(w.invoice_no) = '. $db->Quote($invoice_no);
		}
		
		if($serial_no) {
			
			$snoquery = '';
			
			$serial_no = $db->Quote($serial_no); 
			
			$query = 'SELECT COUNT(id)'
						. ' FROM #__at_warranty_items '
						. ' WHERE serial_no_2 = '. $serial_no
						. ' LIMIT 1 ';
						
					$db->setQuery( $query );
				
			if($db->loadResult()) {
				$snoquery = ' w.serial_no_2 = '. $serial_no;
			} else {
				$snoquery = ' ( w.serial_no = '. $serial_no . ' AND w.serial_no_2 = "" ) ';
			}
				
			$where[] = $snoquery;
		}
		
		$where = ( count( $where ) ? ' WHERE (' . implode( ') AND (', $where ) . ')' : '' );
		
		$query = " SELECT w.* FROM #__at_warranty_items AS w "
				.	$where
				. " ORDER BY w.purchase_date DESC ";
			$db->setQuery( $query );
			$rows = $db->loadObjectList();
		
		/* Create Table */
		if(!empty($rows)) :
		$html = '<div style="color:#000">';
		$html .= '<table border="1" cellspacing="1" cellpadding="1" style="border-collapse:collapse">';
		$html .= '<tr>';
		$html .= '<th width="160px;">'.JText::_('COM_ATELMAN_PO_NO').'</th>';
		$html .= '<th width="0px;">'.JText::_('COM_ATELMAN_SO_NO').'</th>';
		$html .= '<th width="70px;">'.JText::_('COM_ATELMAN_INVOICE_NO').'</th>';
		$html .= '<th width="170px;">'.JText::_('COM_ATELMAN_MODEL_NO').'</th>';
		$html .= '<th width="150px;">'.JText::_('COM_ATELMAN_SERIAL_NO').'</th>';
		$html .= '<th width="80px;">'.JText::_('COM_ATELMAN_PURCHASE_DATE').'</th>';
		$html .= '<th width="80px;">'.JText::_('COM_ATELMAN_EXPIRY_DATE').'</th>';
		$html .= '</tr>';
		
		$arrSerialNo = array();
		
		foreach($rows as $r) :
		
			$serial_no = (($r->serial_no_2)?$r->serial_no_2:$r->serial_no);
			
			if (in_array($serial_no, $arrSerialNo)) {
				continue;
			}

			array_push($arrSerialNo, $serial_no);
			
			$html .= '<tr>';
			$html .= '<td width="160px;">'.$r->po_no.'</td>';
			$html .= '<td width="70px;">'.$r->so_no.'</td>';
			$html .= '<td width="70px;">'.$r->invoice_no.'</td>';
			$html .= '<td width="170px">'.((!empty($r->replacement_pn))?$helper->getProductByPartNumber($r->replacement_pn)->model_no:$helper->getItemById('products',$r->product_id)->model_no).'</td>';
			$html .= '<td width="150px;">'.$serial_no.'</td>';
			$html .= '<td width="80px;">'.date("d M Y", strtotime($r->purchase_date)).'</td>';
			$html .= '<td width="80px;">'.(($r->expired_date_manual != '0000-00-00')? date("d M Y", strtotime($r->expired_date_manual)): (($r->extended_warranty > 0)?date("d M Y", strtotime($r->extended_expired_date)) : date("d M Y", strtotime($r->expired_date))) ).'</td>';
			$html .= '</tr>';
		endforeach;
		$html .= '</table>';
		$html .= '</div>';
		else:
		$html = 'Warranty does not exist';
		endif;
		
		return $html;
	}
}
