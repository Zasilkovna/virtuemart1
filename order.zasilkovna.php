<?php
if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 

$mod_name='zasilkovna';

/**
* Zasilkovna
*/
mm_showMyFileName( __FILE__ );
global $page, $ps_order_status;

$show = vmGet( $_REQUEST, "show", "" );
$form_code = "";
require_once( CLASSPATH . "pageNavigation.class.php" );
require_once( CLASSPATH . "htmlTools.class.php" );


require(CLASSPATH ."shipping/".$mod_name.".cfg.php");    //to know if payment is cod

require_once(CLASSPATH ."shipping/".$mod_name.".php"); 
$zas_modul=new zasilkovna();
if($zas_modul->updateBranchesInfo()==false){
	$errors=$zas_modul->return_errors();
	if($errors){
		echo $errors;
		return false;
	}
	echo $zas_modul->return_warnings();	
}
$branchJS=$zas_modul->getJSApi();
if($branchJS==false){
	$errors=$zas_modul->return_errors();
	if($errors){
		echo $errors;
		return false;
	}
	echo $zas_modul->return_warnings();
}

echo $zas_modul->checkVersion();

$list  = "SELECT #__{vm}_orders.order_id,order_status, #__{vm}_orders.cdate,#__{vm}_orders.mdate,order_total,order_currency,#__{vm}_orders.user_id,";
$list .= "first_name, last_name,ship_method_id FROM #__{vm}_orders, #__{vm}_order_user_info WHERE ";
$count = "SELECT count(*) as num_rows FROM #__{vm}_orders, #__{vm}_order_user_info WHERE ";
$q = "address_type = 'BT' AND ";
if (!empty($keyword)) {
        $q  .= "(#__{vm}_orders.order_id LIKE '%$keyword%' ";
        $q .= "OR #__{vm}_orders.order_status LIKE '%$keyword%' ";
        $q .= "OR first_name LIKE '%$keyword%' ";
        $q .= "OR last_name LIKE '%$keyword%' ";
		$q .= "OR CONCAT(`first_name`, ' ', `last_name`) LIKE '%$keyword%' ";
        $q .= ") AND ";
}
if (!empty($show)) {
	$q .= "order_status = '$show' AND ";
}
$q .= "(#__{vm}_orders.order_id=#__{vm}_order_user_info.order_id) ";
$q .= "AND #__{vm}_orders.vendor_id='".$_SESSION['ps_vendor_id']."' AND ship_method_id LIKE  '%Zasilkovna%' ";
$q .= "ORDER BY #__{vm}_orders.cdate DESC ";
$list .= $q . " LIMIT $limitstart, " . $limit;
$count .= $q;   

$db->query($count);
$db->next_record();
$num_rows = $db->f("num_rows");
  
// Create the Page Navigation
$pageNav = new vmPageNav( $num_rows, $limitstart, $limit );

// Create the List Object with page navigation
$listObj = new listFactory( $pageNav );

// print out the search field and a list heading
$listObj->writeSearchHeader($zas_modul->lang('zas_order_title'), VM_THEMEURL.'images/administration/dashboard/orders.png', $modulename, "zasilkovna");

?>
<div align="center">
<?php
$navi_db = new ps_DB;
$q = "SELECT order_status_code, order_status_name ";
$q .= "FROM #__{vm}_order_status WHERE vendor_id = '$ps_vendor_id'";
$navi_db->query($q);
while ($navi_db->next_record()) {  ?> 
  <a href="<?php $sess->purl($_SERVER['PHP_SELF']."?page=$modulename.zasilkovna&show=".$navi_db->f("order_status_code")) ?>">
  <b><?php echo $navi_db->f("order_status_name")?></b></a>
      | 
<?php 
} 
?>
    <a href="<?php $sess->purl($_SERVER['PHP_SELF']."?page=$modulename.zasilkovna&show=")?>"><b>
    <?php echo $VM_LANG->_('PHPSHOP_ALL') ?></b></a>
</div>
<br />
<?php 

$listObj->startTable();

// these are the columns in the table
$checklimit = ($num_rows < $limit) ? $num_rows : $limit;
$columns = Array(  "#" => "width=\"20\"", 
					"<input type=\"checkbox\" name=\"toggle\" value=\"\" onclick=\"checkAll(".$checklimit.")\" />" => "width=\"20\"",					
					$zas_modul->lang('order_exported') => "width=\"20\"",
					$zas_modul->lang('order_number') => '',
					$zas_modul->lang('customer_name') => '',
					$zas_modul->lang('cod') => '',					
					'Zasilkovna'=>'',					
					$zas_modul->lang('order_date')=> '',					
					$zas_modul->lang('total') => '',
					$zas_modul->lang('order_remove') => "width=\"5%\""
				);
$listObj->writeTableHeader( $columns );
// so we can determine if shipping labels can be printed
$dbl = new ps_DB;
$db->query($list);



//get list of exported orders
$vm_db = new ps_DB;      
$q = "SELECT order_id ";
$q .= "FROM #__zasilkovna_orders where exported='1' ";
$vm_db->query($q);
$exported_orders_arr=$vm_db->loadAssocList('order_id');





$export_url=JURI::base( false )."index.php?pshop_mode=admin&page=$modulename.".$mod_name."_export&option=com_virtuemart";
$self_url=JURI::base( false )."index.php?pshop_mode=admin&page=$modulename.".$mod_name."&option=com_virtuemart";
$listObj->newRow();
$i=0;
while ($db->next_record()) { 
    
	//$listObj->newRow("\" style=\"background-color: red;");
	$listObj->newRow();
	
	// The row number
	$listObj->addCell( $pageNav->rowNumber( $i ) );
		
	// The Checkbox	
	$listObj->addCell('<input type="checkbox" class="zasilkovna-cb" id="cb'.$i.'" name="orders_id[]" value="'.$db->f("order_id").'" >');		

	// Is exported?
	if($exported_orders_arr[$db->f("order_id")]['order_id']==$db->f("order_id")){
		$is_exported=$zas_modul->lang('yes');
	}else{
		$is_exported=$zas_modul->lang('no');
	}
    $listObj->addCell( $is_exported,'style="text-align: center;"' );	


	$url = $_SERVER['PHP_SELF']."?page=$modulename.order_print&limitstart=$limitstart&keyword=".urlencode($keyword)."&order_id=". $db->f("order_id");
	$tmp_cell = "<a href=\"" . $sess->url($url) . "\">".sprintf("%08d", $db->f("order_id"))."</a><br />";
	$listObj->addCell( $tmp_cell );

		
	$tmp_cell = $db->f('first_name').' '.$db->f('last_name');
	if( $perm->check('admin') && defined('_VM_IS_BACKEND')) {
		$url = $_SERVER['PHP_SELF']."?page=admin.user_form&amp;user_id=". $db->f("user_id");
		$tmp_cell = '<a href="'.$sess->url( $url ).'">'.$tmp_cell.'</a>';
	}
	
	$listObj->addCell( $tmp_cell );	
	


	$pm_db = new ps_DB;      
	$q = "SELECT order_id,is_cod ";
	$q .= "FROM #__zasilkovna_orders WHERE order_id= ".$db->f('order_id').";";
	$pm_db->query($q);	

	if($pm_db->num_rows()==0){//if it hasnt been specified manually yet, look to the payment method config
		$q = "SELECT payment_method_id ";
		$q .= "FROM #__{vm}_order_payment WHERE order_id= ".$db->f('order_id').";";
		$pm_db->query($q);	
		$payment_method_id=$pm_db->f('payment_method_id');	
		if($zas_modul->getConfig('cod'.$payment_method_id)){
			$is_cod=true;
		}else{
			$is_cod=false;
		}
	}else{//if cod type has been specified already, use it
		if($pm_db->f('is_cod')==1){
			$is_cod=true;
		}else{
			$is_cod=false;
		}
	}
	if($is_cod){				
		$listObj->addCell('<select class="zasilkovna-cod-export">  <option value="'.$db->f("order_id").'" selected>'.$zas_modul->lang('yes').'</option>  <option value="-'.$db->f("order_id").'" >'.$zas_modul->lang('no').'</option></select>');
	}else{
		$listObj->addCell('<select class="zasilkovna-cod-export">  <option value="'.$db->f("order_id").'" >'.$zas_modul->lang('yes').'</option>  <option value="-'.$db->f("order_id").'" selected>'.$zas_modul->lang('no').'</option></select>');
	}
	

	//ship info
	$ship_arr=explode("|",$db->f('ship_method_id'));
	$listObj->addCell($ship_arr[2]);
	
	// Creation Date
	$listObj->addCell( vmFormatDate($db->f("cdate"), "%d-%b-%y %H:%M"));

	// Order total	
	$listObj->addCell( $GLOBALS['CURRENCY_DISPLAY']->getFullValue($db->f("order_total"), '', $db->f('order_currency')));
	
    // Delete Order Button
	$listObj->addCell( $ps_html->deleteButton( "order_id", $db->f("order_id"), "orderDelete", $keyword, $limitstart ) );

	$i++; 
}
$listObj->writeTable();

$listObj->endTable();
$listObj->writeFooter( $keyword, "&show=$show" );

echo '
<script src="'.$branchJS.'"></script>
<script>
	function request_export(){
		var $ = window.packetery.jQuery;
		var export_orders=new Array();
		var cod_orders=new Array();
		var cod_orders_cnt=0;
		var some_checked=false;
		$( "#form-result" ).html( "");   
		$("input.zasilkovna-cb").each(
          function() { 
          	if($(this).is(\':checked\')){
          		export_orders[export_orders.length]=$(this).val();          		          		   		    
          	}
          });
		$("select.zasilkovna-cod-export").each(
          function() {           	
          	if($(this).val()!=0){
          		cod_orders[cod_orders.length]=$(this).val();
          	}
        });		
		var export_url="'.$export_url.'";
		if(export_orders.length){
			export_url+="&orders_id="+encodeURIComponent(export_orders.join("|"));				
		}
		if(cod_orders.length){			
			export_url+="&cod_orders="+encodeURIComponent(cod_orders.join("|"));
		}
		if(export_orders.length){
			window.location = export_url;
		}else if(cod_orders.length){			
		    $.post(export_url, function( data ) {          
        		$("<div>"+data+"</div>").dialog({modal: true, title: "'.$zas_modul->lang('saving').'", buttons: {"OK": function() { $(this).dialog("close"); }}});
      		} );
		}
		$.get("'.$self_url.'");
	}
</script>
';
echo '<input type="submit" value="'.$zas_modul->lang('save_and_export').'" onClick="request_export();"></form><br><div id="form-result" style="color:black;"></div><br><fieldset><legend>Info</legend>'.$zas_modul->lang('export_usage_info').'</fieldset>';
