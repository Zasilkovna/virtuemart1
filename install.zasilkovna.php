<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

$mod_name='zasilkovna';


function recurse_copy($src,$dst) { 
    $dir = opendir($src); 
    @mkdir($dst); 
    while(false !== ( $file = readdir($dir)) ) { 
        if (( $file != '.' ) && ( $file != '..' )) { 
            if ( is_dir($src . '/' . $file) ) { 
                recurse_copy($src . '/' . $file,$dst . '/' . $file); 
            } 
            else { 
                copy($src . '/' . $file,$dst . '/' . $file); 
            } 
        } 
    } 
    closedir($dir); 
} 








$com_zasilkovna=JPATH_ADMINISTRATOR."/components/com_".$mod_name;
$com_virtuemart=JPATH_ADMINISTRATOR."/components/com_virtuemart";


//copy the virtuemart shipping module
recurse_copy($com_zasilkovna."/ship_module_cfg",$com_virtuemart."/classes/shipping/".$mod_name);
copy($com_zasilkovna."/".$mod_name.".php",$com_virtuemart."/classes/shipping/".$mod_name.".php");
copy($com_zasilkovna."/".$mod_name.".cfg.php",$com_virtuemart."/classes/shipping/".$mod_name.".cfg.php");
copy($com_zasilkovna."/".$mod_name.".ini",$com_virtuemart."/classes/shipping/".$mod_name.".ini");

//copy to orders
copy($com_zasilkovna."/order.".$mod_name.".php",$com_virtuemart."/html/order.".$mod_name.".php");
copy($com_zasilkovna."/order.".$mod_name."_export.php",$com_virtuemart."/html/order.".$mod_name."_export.php");

copy($com_zasilkovna."/store.ship_payment_save.php",$com_virtuemart."/html/store.ship_payment_save.php");
copy($com_zasilkovna."/store.ship_payment.php",$com_virtuemart."/html/store.ship_payment.php");


$db =& JFactory::getDBO();
$q="CREATE TABLE IF NOT EXISTS #__zasilkovna_branches (
                `id` int(10) NOT NULL,
                `name_street` varchar(200) NOT NULL,
                `currency` text NOT NULL,
                `country` varchar(10) NOT NULL
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
$db->setQuery($q);
$db->query();

/*
$q="CREATE TABLE IF NOT EXISTS #__zasilkovna_exported (
                `order_id` int(11) NOT NULL,
                PRIMARY KEY (`order_id`)
                )ENGINE=MyISAM DEFAULT CHARSET=utf8;";
$db->setQuery($q);
$db->query();*/

//for ship-payment disabled combination
$q="CREATE TABLE IF NOT EXISTS #__zasilkovna_ship_payment (
                  `carrier` varchar(500) NOT NULL,
                  `payment_method_id` int(50) NOT NULL
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
$db->setQuery($q);
$db->query();

$q="CREATE TABLE IF NOT EXISTS #__zasilkovna_orders (
  `order_id` int(50) NOT NULL,
  `is_cod` int(1) NOT NULL,
  `exported` int(1) NOT NULL,
  PRIMARY KEY (`order_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
$db->setQuery($q);
$db->query();

