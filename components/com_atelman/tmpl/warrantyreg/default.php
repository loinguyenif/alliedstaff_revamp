<?php defined('_JEXEC') or die('Restricted access'); ?>

<?php  
	JHTML::_('behavior.tooltip');  
	JHTML::_('behavior.formvalidation');
	JHTML::_('behavior.calendar');
	JHTML::script('joomla.javascript.js','includes/js/');
?>

<script type="text/javascript">
	var icon_err = '<img src="<?php echo JURI::root().'images/icon-error.png' ?>" />';
	var icon_good = '<img src="<?php echo JURI::root().'images/icon-correct.png' ?>" />';
	
	var row = 1;
	var myValidator;
	
	function addWarrantyRegRow() {
		var foo = new Date; // Generic JS date object
		var unixtime_ms = foo.getTime();
		
		var url = 'index.php?option=com_atelman&task=ajaxaddrow&r=' + unixtime_ms;	
		
		var req = new Ajax(url, {
			data		: 	
			{
				'section' 	: 	'warranty_reg',
				'row_id'	:	row
			},
			method		: "get",
			onSuccess	: function(data) {
				
				// add hidden for saving purpose into systems
				var div_element = new Element('div', 
				{
					'id' : 'div-warranty-reg-'+row
				}).setHTML(data);
				
				div_element.inject($('WarrantyRegistrationFields'));
				
				searchCompanyBasedOnCountry('', row);
				
				row = row + 1;
			
				document.formvalidator = null;
				document.formvalidator = new JFormValidator();
				
			},
			evalScripts : true
		}).request();						
		
	}
	function deleteWarrantyRegRow(row_id) {
		$('div-warranty-reg-'+ row_id).remove();
		
		document.formvalidator = null;
		document.formvalidator = new JFormValidator();
	}
	
	function searchProductListing(value, id) {
		var foo = new Date; // Generic JS date object
		var unixtime_ms = foo.getTime();
		
		$('product_list_'+id).setStyle('display','none');
		
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
					$('product_list_'+id).setHTML(data);
					$('product_list_'+id).setStyle('display','block');
					} else {
				}
				
			},
			evalScripts : true
		}).request();		
	}
	
	function searchCompanyBasedOnCountry(country_id, row_id) {
		
		var foo = new Date; // Generic JS date object
		var unixtime_ms = foo.getTime();
		
		var url = 'index.php?option=com_atelman&task=ajaxDistributor&r=' + unixtime_ms;	
		
		var req = new Ajax(url, {
			data		: 	
			{
				'country_id'	:	country_id,
				'row_id'	:	row_id
			},
			method		: "post",
			onSuccess	: function(data) {
				$('ajaxCompanyBasedOnCountry'+row_id).setHTML(data);
				
				document.formvalidator = null;
				document.formvalidator = new JFormValidator();
			},
			evalScripts : true
		}).request();		
		
		
	}
	
	/*
		Search Serial No, Every Serial No has Product ID
	*/
	function searchSerialNo(value, id) {
		
		var foo = new Date; // Generic JS date object
		var unixtime_ms = foo.getTime();
		
		$('serial_no_list_'+id).setStyle('display','none');
		
		var url = 'index.php?option=com_atelman&task=ajax&r=' + unixtime_ms;	
		
		var req = new Ajax(url, {
			data		: 	
			{
				'section' 		: 	'getSerialNo',
				'row_id'		:	id,
				'keyword'		:	value
			},
			method		: "post",
			onSuccess	: function(data) {
				
				var obj1 		= 	Json.evaluate(data);
				var status		=	obj1.status;
				var data		=	obj1.data;
				
				if(status) { // true, load the data in
					$('serial_no_list_'+id).setHTML(data);
					$('serial_no_list_'+id).setStyle('display','block');
					
					/* i need to load this model id */
					
				} else {
				
				}
				
			},
			evalScripts : true
		}).request();		
	}
	
	function chooseSerialNo(id, strText, product_id, product_name) {
		$('search_serial_no_'+id).setStyle('display','none');
		$('search_serial_no_reset_' + id).setStyle('display','');
		$('serial_no_'+id).setProperty('value',strText);
		$('serial_no_complete_'+id).setHTML(strText);
		$('serial_no_list_'+id).setStyle('display','none');
		$('serial_no_list_'+id).setHTML('');
		
		
		$('product_id_'+id).setProperty('value',product_id);
		$('product_complete_'+id).setHTML(product_name);
		
	}
	
	function resetSerialNo(id) {
		$('search_serial_no_'+id).setStyle('display','');
		$('search_serial_no_reset_'+id).setStyle('display','none');
		$('serial_no_complete_'+id).setHTML('');
		$('serial_no_'+id).setProperty('value','');
		
		resetProductNo(id);
		
		$('search_serial_no_input_'+id).setProperty('value','');
	}
	
	function resetProductNo(id) {
		//$('search_product_no_'+id).setStyle('display','');
		//$('search_product_no_reset_'+id).setStyle('display','none');
		$('product_complete_'+id).setHTML('N/A');
		$('product_id_'+id).setProperty('value','');
		//$('search_product_no_input_'+id).setProperty('value','');
		
		//resetSerialNo(id);
		
		// serial_no search not displayed
		//$('serial_no_box_'+id).setStyle('display','none');
	}
	var row = 1;
	
	window.addEvent('domready', function(){									 
		addWarrantyRegRow();
	});
	
	function myValidate(f) {
		if (document.formvalidator.isValid(f)) {
			return true; 
		}
   	else {
			var msg = 'Please insert following fields :\n';
			
	  	if($('first_name').hasClass('invalid')){msg += '\n- Empty First Name';}
	  	if($('last_name').hasClass('invalid')){msg += '\n- Empty Last Name';}
	  	if($('address').hasClass('invalid')){msg += '\n- Empty Address';}
	  	if($('city').hasClass('invalid')){msg += '\n- Empty City';}
	  	if($('postal_code').hasClass('invalid')){msg += '\n- Empty Postal Code';}
	  	if($('purchase_country').hasClass('invalid')){msg += '\n- Empty Country';}
			if($('email').hasClass('invalid')){msg += '\n- Empty / Invalid E-Mail Address';}
			
			var stg = false;
 	  	$$('#WarrantyRegistrationFields .required').each(function(klass){
				if(!stg) {	
					
					if($(klass.id).hasClass('invalid')) {msg += '\n- Please fill all required fields (*) on Warranty Items';stg = true;}
				}
			});
			alert(msg);
		}
   	return false;
	}
	
</script>
<div class="header">
	<?php echo $this->item->name ?>
</div>
<div id="ATelesisWarrantyRegistrationFormBox" class="ATFormFormat">
	<div></div>
	<form action="index.php" method="post" name="adminForm" id="ATelesisWarrantyRegistrationForm" class="form-validate" onSubmit="return myValidate(this);">
    <div>
			<div class="left">
				<div class="fields">
					<div class="label">First Name&nbsp;<span class="red">*</span></div>
					<div class="inputs"><input type="text" name="first_name" id="first_name" class="required inputbox" value="" /></div>
					<div class="clear"></div>
				</div>
				<div class="fields">
					<div class="label">Address&nbsp;<span class="red">*</span></div>
					<div class="inputs"><textarea name="address" id="address" class="required inputbox"></textarea></div>
					<div class="clear"></div>
				</div>
				<div class="fields">
					<div class="label">Country</div>
					<div class="inputs"><?php echo $this->country ?></div>
					<div class="clear"></div>
				</div>
				<div class="fields">
					<div class="label">Fax</div>
					<div class="inputs"><input type="text" name="fax" class="inputbox" value="" /></div>
					<div class="clear"></div>
				</div>
				<div class="fields">
					<div class="label">Company Name</div>
					<div class="inputs"><input type="text" name="company_name" class="inputbox" value="" /></div>
					<div class="clear"></div>
				</div>
			</div>
			<div class="right">
				<div class="fields">
					<div class="label">Last Name&nbsp;<span class="red">*</span></div>
					<div class="inputs"><input type="text"  name="last_name" id="last_name" class="required inputbox" value="" /></div>
					<div class="clear"></div>
				</div>
				<div class="fields">
					<div class="label">City&nbsp;<span class="red">*</span></div>
					<div class="inputs"><input type="text" name="city" id="city" class="required inputbox" value="" /></div>
					<div class="clear"></div>
				</div>
				<div class="fields">
					<div class="label">Postal Code&nbsp;<span class="red">*</span></div>
					<div class="inputs"><input type="text" name="postal_code" id="postal_code" class="required inputbox" value="" /></div>
					<div class="clear"></div>
				</div>
				<div class="fields">
					<div class="label">Telephone</div>
					<div class="inputs"><input type="text" name="telephone" class="inputbox" value="" /></div>
					<div class="clear"></div>
				</div>
				<div class="fields">
					<div class="label">Email&nbsp;<span class="red">*</span></div>
					<div class="inputs"><input type="text" name="email" id="email" class="required validate-email inputbox" value="" /></div>
					<div class="clear"></div>
				</div>
				<div class="fields">
					<div class="label">Job Title</div>
					<div class="inputs"><input type="text" name="job_title" class="inputbox" value="" /></div>
					<div class="clear"></div>
				</div>
			</div>
			<div class="clear"></div>
		</div>
		<div style="margin-top:10px;">
		
    	<div id="WarrantyRegistrationFields"><!-- ajax --></div>
			
			<input type="hidden" name="option" value="com_atelman" />
			<input type="hidden" name="task" value="save" />
			<input type="hidden" name="section" value="warranty_reg" />
			<input type="hidden" name="Itemid" value="<?php echo JRequest::getVar('Itemid') ?>" />
			<?php echo JHTML::_( 'form.token' ); ?>
		</div>
		<div class="compulsoryfield">
			* Compulsory Field
		</div>
		<div>
			<div class="" style="float:left;margin-top:10px;"><a style="font-size:16px;" href="javascript:void(0);" onclick="javascript:addWarrantyRegRow();">+ Add Another Model</a></div>
			<div class="" style="float:right;"><input type="submit" class="button" value="Submit Warranty Registration" /></div>
			<div class="clear"></div>
		</div>
	</form>
</div>