<?php 
if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
$mod_name='zasilkovna';

/**
* Zasilkovna
*/

class paymentMethod{
  var $id;
  var $name;
  var $discount;
  var $active;
  function __construct($id,$name,$discount,$active){
    $this->id=$id;
    $this->name=$name;
    $this->discount=$discount;
    $this->active=$active;
  }
}

$lg = &JFactory::getLanguage();      
if (file_exists(CLASSPATH . 'shipping/' . $mod_name . '/' . $lg->getTag() . '.' . $mod_name . '.php')) {            
  require_once(CLASSPATH . 'shipping/' . $mod_name . '/' . $lg->getTag() . '.' . $mod_name . '.php');            
} elseif  (file_exists(CLASSPATH . 'shipping/' . $mod_name . '/' . $lg->getDefault() . '.' . $mod_name . '.php')) {      
  require_once(CLASSPATH . 'shipping/' . $mod_name . '/' . $lg->getDefault() . '.' . $mod_name . '.php');
} elseif  (file_exists(CLASSPATH . 'shipping/' . $mod_name . '/cs-CZ.' . $mod_name . '.php')) {      
  require_once(CLASSPATH . 'shipping/' . $mod_name . '/cs-CZ.' . $mod_name . '.php');
}

mm_showMyFileName( __FILE__ );
require_once( CLASSPATH . "pageNavigation.class.php" );
require_once( CLASSPATH . "htmlTools.class.php" );

require_once(CLASSPATH ."shipping/".$mod_name.".php");    //to know whether payment is cod
$zas_modul=new zasilkovna();
$branchJS=$zas_modul->getJSApi();
if($branchJS==false){
  $errors=$zas_modul->return_errors();
  if($errors){
    echo $errors;
    return false;
  }
  echo $zas_modul->return_warnings();
}

         
if(method_exists('vm_ps_payment_method','paymentMethodIsAllowed')){
  echo $zas_lang['s_p_installed'];
}else{  
  echo $zas_lang['s_p_how_to_install'];
}
$config_url=JURI::base( false )."index.php?page=store.shipping_module_form&shipping_module=".$mod_name.".php&option=com_virtuemart";
?>
<br>
<a href="<?php echo $config_url ;?>"><?php echo $zas_lang['module_config'];?> &raquo;</a>;
<?
        

if (!empty($keyword)) {
  $list  = "SELECT * FROM #__{vm}_payment_method LEFT JOIN #__{vm}_shopper_group ";
  $list .= "ON #__{vm}_payment_method.shopper_group_id=#__{vm}_shopper_group.shopper_group_id WHERE ";
  $count = "SELECT count(*) as num_rows FROM #__{vm}_payment_method LEFT JOIN #__{vm}_shopper_group ";
  $count .= "ON #__{vm}_payment_method.shopper_group_id=#__{vm}_shopper_group.shopper_group_id WHERE ";
  $q  = "(#__{vm}_payment_method.payment_method_name LIKE '%$keyword%' ";
  $q .= "AND #__{vm}_payment_method.vendor_id='$ps_vendor_id' ";
  $q .= ") ";
  $q .= "ORDER BY #__{vm}_payment_method.list_order,#__{vm}_payment_method.payment_method_name ";
  $list .= $q . " LIMIT $limitstart, " . $limit;
  $count .= $q;   
}
else {
  $q = "";
  $list = "SELECT * FROM #__{vm}_payment_method LEFT JOIN #__{vm}_shopper_group ";
  $list .= "ON #__{vm}_payment_method.shopper_group_id=#__{vm}_shopper_group.shopper_group_id WHERE ";
  $count = "SELECT count(*) as num_rows FROM #__{vm}_payment_method LEFT JOIN #__{vm}_shopper_group ";
  $count .= "ON #__{vm}_payment_method.shopper_group_id=#__{vm}_shopper_group.shopper_group_id WHERE ";
  $q .= "#__{vm}_payment_method.vendor_id='$ps_vendor_id' ";
  $list .= $q;
  $list .= "ORDER BY #__{vm}_payment_method.list_order,#__{vm}_payment_method.payment_method_name ";
  $list .= "LIMIT $limitstart, " . $limit;
  $count .= $q;
}
$db->query($count);
$db->next_record();
$num_rows = $db->f("num_rows");
  
// Create the Page Navigation
$pageNav = new vmPageNav( $num_rows, $limitstart, $limit );

// Create the List Object with page navigation
$listObj = new listFactory( $pageNav );



// start the list table

$db->query($list);
$i = 0;
$paymentMethods = array();
while ($db->next_record()) { 
  $paymentMethod=new paymentMethod($db->f("payment_method_id"),$db->f("payment_method_name"),$db->f("payment_method_discount"),$db->f("payment_enabled"));
  if($db->f("payment_enabled")=="Y") $paymentMethods[]=$paymentMethod;
  $i++;
}


require_once( CLASSPATH. "ps_shipping_method.php" );
$ps_shipping_method = new ps_shipping_method;

$carriers=$ps_shipping_method->method_list();

 ?>

<br><br>
<strong><?php echo $zas_lang['s_p_mod_config'];?>:</strong>
<br>
<table width="100%" class="adminlist">
  <tr>
  <th width="20%"><?php echo $zas_lang['module_name'].' | '.$zas_lang['payment_method_name'];?></th>
  <?php 
    foreach($paymentMethods as $paymentMethod){
      echo '<th width="20px">'.$paymentMethod->name.'</th>';
    }
  ?>
  </tr>
  <?php
  $jdb =& JFactory::getDBO();
  global $PSHOP_SHIPPING_MODULES;
  foreach($carriers as $key => $carrier){
    if(! in_array(str_replace('.php', '', $carrier['filename']), $PSHOP_SHIPPING_MODULES ) )continue;

    $carriers[$key]['filename']=$carrier['filename']=str_replace('.php', '', $carrier['filename']);
    if($carrier['filename']=='zasilkovna'){
      require_once(CLASSPATH ."shipping/".$mod_name."/".$mod_name.".countries.cfg.php");    //to know whether payment is cod
      foreach ($countries as $country) {
        $q="SELECT * from #__zasilkovna_ship_payment ";
        $q.= " WHERE `carrier`='".$carrier['filename']."->".$country->code."' ;";
        $jdb->setQuery($q);
        $jdb->query();
        $row = $jdb->loadAssocList('payment_method_id'); 
        $zas_carrier=$carrier['filename'].'->'.$country->code;
        echo '<tr> <th width="20%">'.$carrier['filename'].' '.$country->code.'</th>';  

        foreach($paymentMethods as $paymentMethod){
          $checked="";
          if(!isset($row[$paymentMethod->id])){
            $checked="checked";
          }
          echo '<th width="20px"> <input type="checkbox" class="ship-payment-cb" value="'.$zas_carrier.'/'.$paymentMethod->id.'" '.$checked.'> </th>';      
        }
      }

    }else{
      $q="SELECT * from #__zasilkovna_ship_payment ";
      $q.= " WHERE `carrier`='".$carrier['filename']."' ;";
      $jdb->setQuery($q);
      $jdb->query();
      $row = $jdb->loadAssocList('payment_method_id');  
      echo '<tr> <th width="20%">'.$carrier['filename'].'</th>';
      foreach($paymentMethods as $paymentMethod){
        $checked="";
        if(!isset($row[$paymentMethod->id])){
          $checked="checked";
        }
        echo '<th width="20px"> <input type="checkbox" class="ship-payment-cb" value="'.$carrier['filename'].'/'.$paymentMethod->id.'" '.$checked.'> </th>';      
      }
    }
    echo '</tr>';
  }
  ?>
</table>
<br>
<br>
<strong><?php echo $zas_lang['s_p_standard_mod_config'];?>:</strong>
<br>



<?php 
// --------------- standard shipping module carriers tables
?>
<table width="100%" class="adminlist">
  <tr>
  <th width="20%"><?php echo $zas_lang['carrier_name'].' | '.$zas_lang['payment_method_name'];?></th>
  <?php 
    foreach($paymentMethods as $paymentMethod){
      echo '<th width="20px">'.$paymentMethod->name.'</th>';
    }
  ?>
  </tr>

  <?php
  $q="SELECT shipping_carrier_name from #__{vm}_shipping_carrier;" ;
  $s_db=new ps_DB();
  $s_db->query($q);
  while ($s_db->next_record()){    
    $carrier=$s_db->f('shipping_carrier_name');    

    $q="SELECT * from #__zasilkovna_ship_payment ";
    $q.= " WHERE `carrier`='ssm_".$carrier."' ;";
    $jdb->setQuery($q);
    $jdb->query();
    $row = $jdb->loadAssocList('payment_method_id');  
    echo '<tr> <th width="20%">'.$carrier.'</th>';
    foreach($paymentMethods as $paymentMethod){
      $checked="";
      if(!isset($row[$paymentMethod->id])){
        $checked="checked";
      }
      echo '<th width="20px"> <input type="checkbox" class="ship-payment-cb" value="ssm_'.$carrier.'/'.$paymentMethod->id.'" '.$checked.'> </th>';      
    }
    echo '</tr>';
  }
  ?>
</table>
<?
//<script src="'.JURI::root(true).'/media/com_'.$mod_name.'/media/branch.js"></script>

$export_url=JURI::base( false )."index.php?pshop_mode=admin&page=$modulename.ship_payment_save&option=com_virtuemart";
$self_url=JURI::base( false )."index.php?pshop_mode=admin&page=$modulename.ship_payment&option=com_virtuemart";
echo '
<script src="'.$branchJS.'"></script>
<script>
  function save_s_p(){    
    var $ = window.packetery.jQuery;
    var export_orders="";
    var some_unchecked=false;
    $("input.ship-payment-cb").each(
          function() { 
            if(!$(this).is(\':checked\')){
              if(some_unchecked){
                export_orders+="|";
              }
              console.log($(this).val());                       
              export_orders+=$(this).val();
              some_unchecked=true;              
            }
          });    
    $( "#form-result" ).html( "");     
    var export_url="'.$export_url.'&s_p="+encodeURIComponent(export_orders);          
    $.post("'.$export_url.'", { s_p: encodeURIComponent(export_orders) },   function( data ) {          
        $("<div>"+data+"</div>").dialog({modal: true, title: "'.$zas_lang['saving'].'", buttons: {"OK": function() { $(this).dialog("close"); }}});
    } );
    $.get("'.$self_url.'");
    return true;
  }
</script>
';
echo '<input type="button" onClick="save_s_p();" value="Save"> <div id="form-result" style="color:black;"></div>'
//echo '<a onClick="test();"> ULOŽIT</a>';

?>
