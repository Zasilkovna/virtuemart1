<?php
if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
require_once(CLASSPATH ."shipping/".$this->classname."/".$this->classname.".classes.php");

$countries=array(new country("Česká Republika","cz","CZK"),new country("Slovenská Republika","sk","EUR"));
