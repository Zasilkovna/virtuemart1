<?php 
/**
* Zasilkovna
*/
if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
require_once( CLASSPATH.'connectionTools.class.php');    //vmConnector::
$s_p_arr=explode('|',urldecode($_REQUEST['s_p']));

$db =& JFactory::getDBO();
$q="TRUNCATE TABLE #__zasilkovna_ship_payment;";
$db->setQuery($q);
$db->query();

foreach($s_p_arr as $s_p){  
  $s_p=explode('/',$s_p);
  $q="INSERT IGNORE INTO #__zasilkovna_ship_payment ";
  $q.="(
      `carrier` ,
      `payment_method_id`
      ) ";
  $q.=" VALUES (
      '$s_p[0]',  '$s_p[1]'
      );";  
  $db->setQuery($q);
  $db->query();
}
vmConnector::sendHeaderAndContent( 200, "Saved");
exit();

?>
