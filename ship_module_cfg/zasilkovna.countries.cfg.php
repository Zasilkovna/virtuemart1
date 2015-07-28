<?php
if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
$mod_name='zasilkovna';
require_once(CLASSPATH ."shipping/".$mod_name."/".$mod_name.".classes.php");

$countries=array(new country("Česká Republika","cz","CZK"),new country("Slovenská Republika","sk","EUR"));
