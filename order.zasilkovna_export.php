<?php
if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
$mod_name='zasilkovna';
require_once(CLASSPATH . 'shipping/'.$mod_name.'.php');
$zas=new zasilkovna;
$zas->exportCSV();
