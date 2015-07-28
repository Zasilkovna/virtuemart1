<?php
//fron-end
$zas_lang['shipper']='Dopravce';
$zas_lang['packet_price']="Cena za zásilku";

$zas_lang['errors_warnings']="Chyby a varování";
$zas_lang['no_errors_warnings']="Žádné chyby nebyly zjištěny";
$zas_lang['module_version']="Verze modulu";
$zas_lang['api_key']="API KLÍČ";
$zas_lang['api_key_tip']="Pokud neznáte svůj api klíč, kontaktuje nás.";
$zas_lang['offer_cz']="Nabízet pobočky v ČR";
$zas_lang['offer_sk']="Nabízet pobočky v SR";
$zas_lang['packet_price_by_exchg_rate']="Cena zásilky přepočtena kurzem";
$zas_lang['info_cz']="Info do ČR";
$zas_lang['info_sk']="Info do SR";
$zas_lang['delivery_info']="Informace";
$zas_lang['cash_on_del_fee']="Příplatek za dobírku";
$zas_lang['select_tax_rate']='Vyberte daň z dopravy';
$zas_lang['select_cash_on_del_payments']="Vyberte platby, které jsou na dobírku";
$zas_lang['select_cash_on_del_payments_tip']="Vyberte platby, které jsou na dobírku";
$zas_lang['eshop_domain']="Doména eshopu";
$zas_lang['eshop_domain_tip']="Používáte-li jeden účet Zásilkovny pro více e-shopů, zadejte zde doménu tohoto, aby bylo možné řádně informovat zákazníky o původu zásilky, která je jim dopravována.";
$zas_lang['show_logo']='Zobrazovat logo Zasilkovna.cz';
$zas_lang['priority'] = 'Priorita';
$zas_lang['priority_tip'] = 'Země s vyšší prioritou jsou zobrazovány přednostně.';
$zas_lang['label_cz']='Nazev dopravy pro ČR';
$zas_lang['label_sk']='Nazev dopravy pro SR';
$zas_lang['s_p_settings']='Omezení způsobu platby';
$zas_lang['cod']='Dobírka';
$zas_lang['yes']='ANO';
$zas_lang['no']='NE';
$zas_lang['new_version_aval']='Je dustupná nová verze dopravního modulu Zásilkovna.';
$zas_lang['no_new_version']='Máte nejnovější verzi modulu.';
$zas_lang['select_branch']='Vyberte cílovou pobočku.';
$zas_lang['set_s_p']='Nastavit omezení doprava-platba';
$zas_lang['shipping_price_in_diff_currencies']='Cena dopravy ve více měnách';
$zas_lang['fixed_by_currency']='Pevná podle měny';
$zas_lang['converted_by_rate']='Přepočtena kurzem';
$zas_lang['fixed_price']='Pevná cena zásilky';
$zas_lang['price_not_set']='Cena není nastavena';

//orders.zasilkovna.php
$zas_lang['zas_order_title']='Objednávky Zásilkovna';
$zas_lang['order_exported']='Exportováno';
$zas_lang['order_number']='Číslo objednávky';
$zas_lang['order_date']='Datum objednávky';
$zas_lang['total']='Cena';
$zas_lang['order_remove']='Odstranit';
$zas_lang['save_and_export']='Uložit nastavení dobírek a exportovat vybrané';
$zas_lang['export_usage_info']='Exportovaný soubor můžete nahrát v klientské oblasti webu Zásilkovna, pod Podat zásilku, v části Hromadné podání – CSV.';
$zas_lang['customer_name']='Jméno zákazníka';

//orders.zasilkovna_export.php
$zas_lang['saved_ok']='Uloženo';

//store.ship_payment.php
$zas_lang['module_config']='Konfigurace dopravního modulu Zásilkovna';
$zas_lang['module_name']='Jméno modulu';
$zas_lang['carrier_name']='Jméno dopravce';
$zas_lang['payment_method_name']='Platební metoda';
$zas_lang['s_p_installed']='Rozšíření je nainstalováno.';
$zas_lang['s_p_not_installed']='Rozšíření není nainstalováno.<br> Více informací v nastavení omezení způsobu platby.';
$zas_lang['s_p_how_to_install']='Rozšíření není nainstalováno. <br> 
  1. v souboru <i>/administrator/components/com_virtuemart/classes/ps_payment_method.php</i> najít metodu <i>list_payment_radio</i><br>
  2. v metodě <i>list_payment_radio</i> najít: <br>
  <textarea onfocus="this.select();" onclick="this.select();" onkeyup="this.select();" readonly="" id="taCode"   rows="1" cols="31">
  while ($db->next_record()) {
  </textarea> <br>  
  a na další řádek vložit:<br>  
  <textarea onfocus="this.select();" onclick="this.select();" onkeyup="this.select();" readonly="" id="taCode"   rows="1" cols="110">
  if (!$this->paymentMethodIsAllowed($db->f("payment_method_id"))) continue; //added by ZASILKOVNA
  </textarea> <br>
  3. za metodu <i>list_payment_radio</i> vložit:<br>
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
$zas_lang['s_p_mod_config']='Nastavení omezení plateb pro aktivní dopravní moduly';
$zas_lang['s_p_standard_mod_config']='Nastavení omezení plateb pro dopravce standardního modulu';
$zas_lang['saving']="Ukládání";

//errors and warnings
$zas_lang['shipping_module_errors']='- chyby dopravního modulu';
$zas_lang['shipping_module_warnings']='- varování dopravního modulu';
$zas_lang['api_key_not_set']='Není nastaven API klíč';
$zas_lang['api_key_not_verified']='Nemohu ověřit zadaný API klíč. Pravděpodobně není platný';
$zas_lang['cannot_load_curl']='Nemohu načíst rozšíření curl';
$zas_lang['curl_and_url_fopen_disabled']='Nelze přistupovat k externím souborům. V nastavení webového serveru povolte curl nebo url_fopen';
$zas_lang['cannot_update_js_older_5_days']='Nemohu aktualizovat branch.js. Je starší než 5 dnů nebo neexistuje';
$zas_lang['cannot_update_xml_file_older_5_days']='Nemohu aktualizovat xml soubor poboček. Je starší než 5 dnů nebo neexistuje';
$zas_lang['cannot_load_branch_list']='Nemohu načíst seznam poboček';