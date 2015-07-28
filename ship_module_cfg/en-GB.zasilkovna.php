<?php
//fron-end
$zas_lang['shipper']='Shipper';
$zas_lang['packet_price']="Packet price";

$zas_lang['errors_warnings']="Errors and warnings";
$zas_lang['no_errors_warnings']="No errors occured";
$zas_lang['module_version']="Module version";
$zas_lang['api_key']="API KEY";
$zas_lang['api_key_tip']="For api key contant us.";
$zas_lang['offer_cz']="Offer branches in CZE";
$zas_lang['offer_sk']="Offer branches in SK";
$zas_lang['packet_price_by_exchg_rate']="Packet price counted by exchange rate";
$zas_lang['info_cz']="Info for CZE";
$zas_lang['info_sk']="Info for SK";
$zas_lang['delivery_info']="Information";
$zas_lang['cash_on_del_fee']="Cash on delivery fee";
$zas_lang['select_tax_rate']='Select tax rate';
$zas_lang['select_cash_on_del_payments']="Select cash on delivery payments";
$zas_lang['select_cash_on_del_payments_tip']="Select cash on delivery payments. ";
$zas_lang['eshop_domain']="Eshop domain";
$zas_lang['eshop_domain_tip']="In case you use more eshop domains on Zasilkovna, specify this one.";
$zas_lang['show_logo']='Show Zasilkovna logo';
$zas_lang['priority'] = 'Priority';
$zas_lang['priority_tip'] = 'Countries with higher priority are shown first.';
$zas_lang['label_cz']='Shipping name for CZE';
$zas_lang['label_sk']='Shipping name for SK';
$zas_lang['s_p_settings']='Setting ship-payment disabled combinations';
$zas_lang['cod']='Cash on delivery';
$zas_lang['yes']='YES';
$zas_lang['no']='NO';
$zas_lang['new_version_aval']='New version of VirtueMart Zasilkovna shipping module is available.';
$zas_lang['no_new_version']='Your version is the newest.';
$zas_lang['select_branch']='Please select pick-up point.';
$zas_lang['set_s_p']='Set ship-payment combinations';
$zas_lang['shipping_price_in_diff_currencies']='Shipping price in different currencies';
$zas_lang['fixed_by_currency']='Fixed by currency';
$zas_lang['converted_by_rate']='Converted by exchange rate';
$zas_lang['fixed_price']='Fixed packet price';
$zas_lang['price_not_set']='Price is not set';

//orders.zasilkovna.php
$zas_lang['zas_order_title']='Zásilkovna orders';
$zas_lang['order_exported']='Exported';
$zas_lang['order_number']='Order number';
$zas_lang['order_date']='Order date';
$zas_lang['total']='Total';
$zas_lang['order_remove']='Remove';
$zas_lang['save_and_export']='Save orders settings and export selected.';
$zas_lang['export_usage_info']='You can upload exported csv file on zasilkovna.cz in client area.';//Exportovaný soubor můžete nahrát v klientské oblasti webu Zásilkovna, pod Podat zásilku, v části Hromadné podání – CSV.
$zas_lang['customer_name']='Customer Name';

//orders.zasilkovna_export.php
$zas_lang['saved_ok']='Saved';

//store.ship_payment.php
$zas_lang['module_config']='Zasilkovna shippinng module configuration';
$zas_lang['module_name']='Module Name';
$zas_lang['carrier_name']='Carrier Name';
$zas_lang['payment_method_name']='Payment method';
$zas_lang['s_p_installed']='Extension is installed.';
$zas_lang['s_p_not_installed']='Extension is not installed.';
$zas_lang['s_p_how_to_install']='Extension is not installed. <br> 
  1. in file <i>/administrator/components/com_virtuemart/classes/ps_payment_method.php</i> find method <i>list_payment_radio</i><br>
  2. in method <i>list_payment_radio</i> find: <br>
  <textarea onfocus="this.select();" onclick="this.select();" onkeyup="this.select();" readonly="" id="taCode"   rows="1" cols="31">
  while ($db->next_record()) {
  </textarea> <br>  
  on next line paste:<br>  
  <textarea onfocus="this.select();" onclick="this.select();" onkeyup="this.select();" readonly="" id="taCode"   rows="1" cols="110">
  if (!$this->paymentMethodIsAllowed($db->f("payment_method_id"))) continue; //added by ZASILKOVNA
  </textarea> <br>
  3. behind method <i>list_payment_radio</i> paste:<br>
  <textarea onfocus="this.select();" onclick="this.select();" onkeyup="this.select();" readonly="" id="taCode"   rows="17" cols="110">
  //added by ZASILKOVNA
  function paymentMethodIsAllowed($paymentMethodId){
    GLOBAL $_REQUEST;
    $shipping_rate_id=urldecode($_REQUEST[\'shipping_rate_id\']);
    $ship_arr=explode(\'|\',$shipping_rate_id)  ;
    $carrier_module=$ship_arr[0];
    $carrier_name=$ship_arr[1];
    $ship_info=$ship_arr[2];
    $db =& JFactory::getDBO();
    if($carrier_module==\'standard_shipping\'){
      //check standard shipping module disabled combinations
      $q_where_cond=" (carrier=\'$carrier_module\' or carrier=\'ssm_$carrier_name\') ";     
    }elseif($carrier_module==\'zasilkovna\'){       
      $branch_id=substr($ship_info,strpos($ship_info,\'[id=\')+strlen(\'[id=\'),-1);//-1 removes closing ]
      $q="SELECT country from #__zasilkovna_branches ";
      $q.=" WHERE id=\'$branch_id\'";
      $db->setQuery($q);
      $row=$db->loadAssoc();
      $carrier_country=$carrier_module."->".$row[\'country\'];
      $q_where_cond=" carrier=\'$carrier_country\' ";   
    }else{
      $q_where_cond=" carrier=\'$carrier_module\' ";    
    }    
    $q="SELECT carrier from #__zasilkovna_ship_payment ";
    $q .= " WHERE $q_where_cond AND payment_method_id=\'$paymentMethodId\'";
    $db->setQuery($q);
    $db->query();    
    if($db->getNumRows()){//if there is record in db that this combination is not allowed
      return false;
    }
    return true;
  }
  </textarea> <br>';
  $zas_lang['s_p_mod_config']='Payment combination settings for active shipping modules';
$zas_lang['s_p_standard_mod_config']='Payment combination settings for standard module shippers';
$zas_lang['saving']="Saving";

//errors and warnings
$zas_lang['shipping_module_errors']='- shipping module errors';
$zas_lang['shipping_module_warnings']='- shipping module warnings';
$zas_lang['api_key_not_set']='API key is not set';
$zas_lang['api_key_not_verified']='Cannot access Packetery API with specified key. Possibly the API key is wrong.';
$zas_lang['cannot_load_curl']='Cannot load curl extension';
$zas_lang['curl_and_url_fopen_disabled']='Cannot access external resources. Allow curl or url_fopen';
$zas_lang['cannot_update_js_older_5_days']='Cannot update branch.js. It\'s older than 5 days or does not exist';
$zas_lang['cannot_update_xml_file_older_5_days']='Cannot update xml branches list. It\'s older than 5 days or does not exist';
$zas_lang['cannot_load_branch_list']='Cannot load branches list';