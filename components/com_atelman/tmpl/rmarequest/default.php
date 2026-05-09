<?php
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Factory;
?>

<?php
HTMLHelper::_('behavior.formvalidator');
?>

<script type="text/javascript">
	var icon_err = '<img src="<?php echo Uri::root() . 'images/icon-error.png' ?>" />';
	var icon_good = '<img src="<?php echo Uri::root() . 'images/icon-correct.png' ?>" />';

	/*function searchProductListing(value, id) {
		var foo = new Date; // Generic JS date object
		var unixtime_ms = foo.getTime();
		
		jQuery('#product_list_'+id).css('display','none');
		
		var url = 'index.php?option=com_atelman&task=ajax&r=' + unixtime_ms;	
		
		var req = new Ajax(url, {
			data		: 	
			{
				'section' 	: 	'getProductListing',
				'row_id'	:	id,
				'keyword'	:	value
			},
			method		: "post",
			onSuccess	: function(data) {
				
				var obj1 		= 	Json.evaluate(data);
				var status		=	obj1.status;
				var data		=	obj1.data;
				
				if(status) { // true, load the data in
					jQuery('#product_list_'+id).html(data);
					jQuery('#product_list_'+id).css('display','block');
					} else {
				}
				
			},
			evalScripts : true
		}).request();		
	}
	*/
	/*function chooseProductNo(id, product_id, strText) {
		jQuery('#product_id_'+id).val(product_id);
		jQuery('#product_complete_'+id).html(strText);
		jQuery('#product_list_'+id).css('display','none');
		jQuery('#product_list_'+id).html('');
		
		jQuery('#search_product_no_'+id).css('display','none');
		jQuery('#search_product_no_reset_'+id).css('display','');
		
		// serial_no search displayed
		jQuery('#serial_no_box_'+id).css('display','block');
		
		// reset serial no
		jQuery('#serial_no_'+id).val('');
		jQuery('#serial_no_complete_'+id).html('');
	}
	*/

	function resetProductNo(id) {
		//jQuery('#search_product_no_'+id).css('display','');
		//jQuery('#search_product_no_reset_'+id).css('display','none');
		jQuery('#product_complete_' + id).html('N/A');
		jQuery('#product_id_' + id).val('');
		//jQuery('#search_product_no_input_'+id).val('');

		//resetSerialNo(id);

		// serial_no search not displayed
		//jQuery('#serial_no_box_'+id).css('display','none');
	}


	/*
		Search Serial No, Every Serial No has Product ID
	*/
	function searchSerialNo(value, id) {

		var foo = new Date; // Generic JS date object
		var unixtime_ms = foo.getTime();

		jQuery('#serial_no_list_' + id).css('display', 'none');

		var url = 'index.php?option=com_atelman&task=rmaitems.ajax&r=' + unixtime_ms;
		jQuery.ajax({
			url: url,
			type: 'post',
			data: {
				'section': 'getSerialNo',
				'row_id': id,
				'keyword': value
			},
			dataType: 'json',
			success: function(res) {
				jQuery('#serial_no_list_' + id).html(res.data);
				jQuery('#serial_no_list_' + id).css('display', 'block');
			}
		});
	}

	function chooseSerialNo(id, strText, product_id, product_name) {
		jQuery('#search_serial_no_' + id).css('display', 'none');
		jQuery('#search_serial_no_reset_' + id).css('display', '');
		jQuery('#serial_no_' + id).val(strText);
		jQuery('#serial_no_complete_' + id).html(strText);
		jQuery('#serial_no_list_' + id).css('display', 'none');
		jQuery('#serial_no_list_' + id).html('');


		jQuery('#product_id_' + id).val(product_id);
		jQuery('#product_complete_' + id).html(product_name);
	}

	function resetSerialNo(id) {
		jQuery('#search_serial_no_' + id).css('display', '');
		jQuery('#search_serial_no_reset_' + id).css('display', 'none');
		jQuery('#serial_no_complete_' + id).html('');
		jQuery('#serial_no_' + id).val('');

		resetProductNo(id);

		jQuery('#search_serial_no_input_' + id).val('');
	}

	var row = 1;

	function addRMARequestRow() {

		var foo = new Date; // Generic JS date object
		var unixtime_ms = foo.getTime();

		var url = 'index.php?option=com_atelman&task=rmaitems.ajaxaddrow&r=' + unixtime_ms;

		jQuery.ajax({
			url: url,
			type: 'POST',
			data: {
				'section': 'rma_request',
				'row_id': row
			},
			success: function(data) {
				var div_element = jQuery('<div>', {
					id: 'div-rma-request-' + row,
					html: data
				});

				jQuery('#RMARequestFields').append(div_element);

				row = row + 1;
			}
		});

	}

	function deleteRMARequestRow(row_id) {
		jQuery('#div-rma-request-' + row_id).remove();
	}

	function resetAJAXDropDown(id) {
		$('#' + id).html('');
		$('#' + id).css('display', 'none');
	}

	function myValidate(f) {
		if (document.formvalidator.isValid(f)) {
			return true;
		} else {
			var msg = 'Please insert following fields :\n';

			if (jQuery('#fullname').hasClass('invalid')) {
				msg += '\n- Empty First Name';
			}
			if (jQuery('#address').hasClass('invalid')) {
				msg += '\n- Empty Address';
			}
			if (jQuery('#email').hasClass('invalid')) {
				msg += '\n- Empty / Invalid E-Mail Address';
			}

			var stg = false;
			jQuery('#RMARequestFields .required').each(function(index, el) {
				if (!stg) {
					if (jQuery(el).hasClass('invalid')) {
						msg += '\n- Please fill all required fields (*) on RMA Request Items';
						stg = true;
					}
				}
			});
			alert(msg);
		}
		return false;
	}

	jQuery(function() {
		addRMARequestRow();
	});
</script>
<div class="header">
	<?php echo $this->item->title ?>
</div>
<div id="ATRMARequest" class="ATFormFormat">
	<div class="note1">Note : RMA Number will be given after confirmation by Allied Telesis</div>
	<form action="index.php" method="post" name="adminForm" id="ATelesisCheckRMAStatusForm" class="form-validate" onSubmit="return myValidate(this);">
		<div>
			<div class="left">
				<div class="fields">
					<div class="label">Name&nbsp;<span class="red">*</span></div>
					<div class="inputs"><input type="text" id="fullname" name="fullname" class="required inputbox" value="" /></div>
					<div class="clear"></div>
				</div>
				<div class="fields">
					<div class="label">Address&nbsp;<span class="red">*</span></div>
					<div class="inputs"><textarea name="address" id="address" class="required inputbox"></textarea></div>
					<div class="clear"></div>
				</div>
			</div>

			<div class="right">
				<div class="fields">
					<div class="label">Telephone</div>
					<div class="inputs"><input type="text" name="telephone" class="inputbox" value="" /></div>
					<div class="clear"></div>
				</div>
				<div class="fields">
					<div class="label">Fax</div>
					<div class="inputs"><input type="text" name="fax" class="inputbox" value="" /></div>
					<div class="clear"></div>
				</div>
				<div class="fields">
					<div class="label">Email&nbsp;<span class="red">*</span></div>
					<div class="inputs"><input type="text" id="email" name="email" class="required validate-email inputbox" value="" /></div>
					<div class="clear"></div>
				</div>
			</div>

			<div class="clear"></div>
		</div>
		<?php /*<div style="margin-top:10px;">
    	<span class="red">Before submitting, please make sure <b>Model No.</b> and <b>Serial No.</b> are filled up.</span>
			</div>
		*/ ?>
		<div style="margin-top:10px;">
			<div id="RMARequestFields"><!-- ajax --></div>
			<input type="hidden" name="option" value="com_atelman" />
			<input type="hidden" name="task" value="rmaitems.save" />
			<input type="hidden" name="section" value="rma_request" />
			<input type="hidden" name="Itemid" value="<?php echo Factory::getApplication()->input->getInt('Itemid') ?>" />
			<?php echo HTMLHelper::_('form.token'); ?>
		</div>
		<div class="compulsoryfield">
			* Compulsory Field
		</div>
		<div>
			<div class="" style="float:left;margin-top:10px;"><a style="font-size:16px;" href="javascript:void(0);" onclick="javascript:addRMARequestRow();">+ Add Another Model</a></div>
			<div class="" style="float:right;"><input type="submit" class="button" value="Submit RMA Request" /></div>
			<div class="clear"></div>
		</div>
	</form>
</div>