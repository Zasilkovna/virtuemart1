<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
class com_ZasilkovnaInstallerScript
{
    /**
     * method to install the component
     *
     * @return void
     */
    function install($parent) 
    {
        // $parent is the class calling this method
        echo "asdasd";
           echo '<p>' . JText::_('COM_HELLOWORLD_INSTALL_TEXT') . '</p>';
    }
 
    /**
     * method to uninstall the component
     *
     * @return void
     */
    function uninstall($parent) 
    {
        // $parent is the class calling this method
        echo "asdasd";
        echo '<p>' . JText::_('COM_HELLOWORLD_UNINSTALL_TEXT') . '</p>';
    }
 
    /**
     * method to update the component
     *
     * @return void
     */
    function update($parent) 
    {
        // $parent is the class calling this method
        echo '<p>' . JText::sprintf('COM_HELLOWORLD_UPDATE_TEXT', $parent->get('manifest')->version) . '</p>';
    }
 
    /**
     * method to run before an install/update/uninstall method
     *
     * @return void
     */
    function preflight($type, $parent) 
    {
        // $parent is the class calling this method
        // $type is the type of change (install, update or discover_install)
        echo '<p>' . JText::_('COM_HELLOWORLD_PREFLIGHT_' . $type . '_TEXT') . '</p>';
    }
 
    /**
     * method to run after an install/update/uninstall method
     *
     * @return void
     */
    function postflight($type, $parent) 
    {
        // $parent is the class calling this method
        // $type is the type of change (install, update or discover_install)
        echo '<p>' . JText::_('COM_HELLOWORLD_POSTFLIGHT_' . $type . '_TEXT') . '</p>';
    }

    private function recurse_copy($src,$dst) { 
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

}
/*
$mod_name='zasilkovna';
echo $mod_name;

$db =& JFactory::getDBO();
$q="CREATE TABLE IF NOT EXISTS #__zasilkovna_branches_test2 (
                `id` int(10) NOT NULL,
                `name_street` varchar(200) NOT NULL,
                `currency` text NOT NULL,
                `country` varchar(10) NOT NULL
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
$db->setQuery($q);
$db->query();

$q="CREATE TABLE IF NOT EXISTS #__zasilkovna_exported_test2 (
                `order_id` int(11) NOT NULL,
                PRIMARY KEY (`order_id`)
                )ENGINE=MyISAM DEFAULT CHARSET=utf8;";
$db->setQuery($q);
$db->query();





$com_zasilkovna=JPATH_ADMINISTRATOR."/components/com_".$mod_name;
$com_virtuemart=JPATH_ADMINISTRATOR."/components/com_virtuemart";
echo "com_zasilkovna:".$com_zasilkovna."<br>";

echo "com_virtuemart:".$com_virtuemart."<br>";

//copy the virtuemart shipping module
recurse_copy($com_zasilkovna."/ship_module_cfg",$com_virtuemart."/classes/shipping/".$mod_name);
copy($com_zasilkovna."/".$mod_name.".php",$com_virtuemart."/classes/shipping/".$mod_name.".php");
copy($com_zasilkovna."/".$mod_name.".cfg.php",$com_virtuemart."/classes/shipping/".$mod_name.".cfg.php");
copy($com_zasilkovna."/".$mod_name.".ini",$com_virtuemart."/classes/shipping/".$mod_name.".ini");

//copy to orders
copy($com_zasilkovna."/orders.".$mod_name.".php",$com_virtuemart."/html/orders.".$mod_name.".php");
copy($com_zasilkovna."/orders.".$mod_name."_export.php",$com_virtuemart."/html/orders.".$mod_name."_export.php");

/*$com_virtuemart=JPATH_ADMINISTRATOR."/components/".$mod_name."/";
$com_virtuemart=JPATH_ADMINISTRATOR."/components/com_virtuemart/";

$vm_ship_dir=$com_virtuemart."classes/shipping/";

echo $vm_ship_dir."<br>";
mkdir($vm_ship_dir.$mod_name,0700);
$vm_zas_dir=$vm_ship_dir.$mod_name.'/';



copy($com_zasilkovna.$mod_name.'testfile1.php',$vm_ship_dir.'testfile1.php');
copy($com_zasilkovna.$mod_name.'.php',$vm_ship_dir.$mod_name.'.php');
copy($com_zasilkovna.$mod_name.'.cfg.php',$vm_ship_dir.$mod_name.'.cfg.php');
copy($com_zasilkovna.$mod_name.'.classes.php',$vm_ship_dir.$mod_name.'.classes.php');

copy($com_zasilkovna.$mod_name.'.ini',$vm_ship_dir.$mod_name.'.ini');

copy($com_zasilkovna.'cs-CZ.'.$mod_name.'.php',$vm_zas_dir.'cs-CZ.'.$mod_name.'.php');
copy($com_zasilkovna.'en-GB.'.$mod_name.'.php',$vm_zas_dir.'en-GB.'.$mod_name.'.php');
copy($com_zasilkovna.'order.'.$mod_name.'.php',$vm_zas_dir.'cs-CZ.'.$mod_name.'.php');
*/

echo getcwd()."<br>";
