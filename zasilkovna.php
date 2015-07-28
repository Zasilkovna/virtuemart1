<?php
if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
 * Zasilkovna
 */
class zasilkovna {
  const VERSION = '1.1';
  var $classname = "zasilkovna";  
  var $_carrier = "Zásilkovna";
  var $_zas_url="http://www.zasilkovna.cz/";

  var $_module_public_url= "";
  var $_module_media_url="";
  var $errors;
  var $warnings;
  
  var $main_currency;

  var $checked_configuration=false;
  var $config_ok=false;
  
  public function __construct(){
    global $ps_vendor_id;    
    $this->_module_public_url=JURI::root( true )."/components/com_$this->classname/";
    $this->_module_media_url=JURI::root( true )."/media/com_$this->classname/media/";
    require_once(CLASSPATH ."shipping/".$this->classname."/".$this->classname.".classes.php");    
    $vm_db=new ps_DB;
    $q="SELECT vendor_currency from #__{vm}_vendor WHERE vendor_id='$ps_vendor_id'";    
    $vm_db->query($q);
    $this->main_currency=$vm_db->f("vendor_currency");    
  }

  

  function list_rates( &$d ) 
  {  
    // Display the content
    echo $this->display_html($this->parcels);            
    return true;
  }

  private function csv_escape($s){
    return str_replace('"', '\"', $s);
  }

  /**
  * export zasilkovna orders from db in CSV format
  */
  public function exportCSV()  {
    $lg = &JFactory::getLanguage();      
    if (file_exists(CLASSPATH . 'shipping/' . $this->classname . '/' . $lg->getTag() . '.' . $this->classname . '.php')) {            
      require_once(CLASSPATH . 'shipping/' . $this->classname . '/' . $lg->getTag() . '.' . $this->classname . '.php');            
    } elseif  (file_exists(CLASSPATH . 'shipping/' . $this->classname . '/' . $lg->getDefault() . '.' . $this->classname . '.php')) {      
      require_once(CLASSPATH . 'shipping/' . $this->classname . '/' . $lg->getDefault() . '.' . $this->classname . '.php');
    } elseif  (file_exists(CLASSPATH . 'shipping/' . $this->classname . '/cs-CZ.' . $this->classname . '.php')) {      
      require_once(CLASSPATH . 'shipping/' . $this->classname . '/cs-CZ.' . $this->classname . '.php');
    }

    require_once( CLASSPATH.'connectionTools.class.php');    //vmConnector::
    if($this->updateBranchesInfo()==false||$this->errors){ //check if branches info is up to date
      echo $this->return_errors();      
      return false;
    }

    if($this->warnings){
      echo $this->return_warnings();
    }        
    if(isset($_REQUEST['cod_orders'])){

      $vm_db = new ps_DB;      
      $cod_arr=explode('|',$_REQUEST['cod_orders']);       
      foreach($cod_arr as $cod_order){
        if($cod_order>0){
          $is_cod='1';
        }else{
          $is_cod='0';
        }
        $cod_order=abs($cod_order);
        $q="INSERT INTO #__zasilkovna_orders (order_id, is_cod,exported) values ('$cod_order', '$is_cod','0')
         ON DUPLICATE KEY UPDATE is_cod = '$is_cod'";        
        $vm_db->query($q);
      }
    } 
    if(isset($_REQUEST['orders_id'])){//if have some orders to export
        $vm_db = new ps_DB;      
        $orders_arr=explode('|',$_REQUEST['orders_id']);    

        //musi se delat array_map floor?    
  
        $q = "SELECT o.order_id, o.cdate, o.order_currency, o.order_total, o.ship_method_id, oui.first_name, oui.last_name, oui.phone_1, oui.phone_2, oui.user_email, pm.payment_method_id from  #__{vm}_orders o 
        join #__{vm}_order_user_info oui on(oui.order_id=o.order_id) 
        join #__{vm}_order_payment pm on(pm.order_id=o.order_id)    
        ";    
        $q .= " WHERE o.order_id in (".implode(',', $orders_arr).") ORDER BY o.cdate DESC;";          
        $vm_db->query($q);
  
        //head for exported query;
        $mark_exp_q="UPDATE #__zasilkovna_orders SET exported='1' WHERE ";
        $first=true;
  
        while ($vm_db->next_record()) {   
          //prepare exported query for each order
          if ($first) {        
            $first=false;
          }else{
            $mark_exp_q.=' OR ';
          }
          $mark_exp_q .= " order_id='".$vm_db->f('order_id')."' ";    
            foreach(array('phone_1', 'phone_2') as $field) {    
              $phone_n=$this->normalize_phone($vm_db->f($field));              
              if(preg_match('/^\+42[01][0-9]{9}$|^$/', $phone_n)) {        
                $phone = $phone_n;
              }
            } 
      
          //get destination branch id from ship_method.. Adresa - [id=ID]
          $ship_method=$vm_db->f('ship_method_id');
          $ship_info=explode('|',$ship_method);
          $ship_info=$ship_info[2];    
          $branch_id=substr($ship_info,strpos($ship_info,'[id=')+strlen('[id='),-1);//-1 removes closing ]
  
          $b_db=new ps_DB;
          $b_db->query('SELECT currency,country FROM #__zasilkovna_branches WHERE id='.$branch_id);
          $branch_currency=$b_db->f('currency');
        
          $total=$vm_db->f('order_total');
  
          //currency conversion
          if($vm_db->f('order_currency')!=$branch_currency){            
            $total = $GLOBALS['CURRENCY']->convert( $total, $vm_db->f('order_currency'),$branch_currency);      
          }
  
          //rounding
          if($branch_currency=='CZK'){
            $total=round($total);
          }else{
            $total=round($total,2);
          }
  
          //is cod?
          $exp_o = new ps_DB; 
          $q="SELECT is_cod from #__zasilkovna_orders WHERE order_id='".$vm_db->f('order_id')."';";          
          $exp_o->query($q);          
          if($exp_o->f('is_cod')==1){
            $cod_cash=$total;            
          }else{
            $cod_cash='0';
          }

          $csv_out.=';"'.$this->csv_escape($vm_db->f('order_id')).'";"'.$this->csv_escape($vm_db->f('first_name')).'";"'.$this->csv_escape($vm_db->f('last_name')).'";;"'.$this->csv_escape($vm_db->f('user_email')).'";"'.$this->csv_escape($phone).'";"'.$this->csv_escape($cod_cash).'";"'.$this->csv_escape($total).'";"'.$this->csv_escape($branch_id).'";"'.$this->csv_escape($this->getConfig('eshop_domain')).'"'."\r\n";
        //$csv_out.=';"'.$this->csv_escape($vm_db->f('order_id')).'";"'.$this->csv_escape($vm_db->f('first_name')).'";"'.$this->csv_escape($vm_db->f('last_name')).'";;"'.$this->csv_escape($vm_db->f('user_email')).'";"'.$this->csv_escape($phone).'";"'.$this->csv_escape(($this->getConfig('cod'.$vm_db->f('payment_method_id')) ? $total : "0")).'";"'.$this->csv_escape($total).'";"'.$this->csv_escape($branch_id).'";"'.$this->csv_escape($this->getConfig('eshop_domain')).'"'."\r\n";
      }
      
      //close and exec exported query
      $mark_exp_q.=';';      
      $vm_db->query($mark_exp_q);      

      header("Content-Type: text/csv");
      header("Content-Disposition: attachment; filename=\"export-" . date("Ymd-His") . ".csv\"");          
    }else{//when no orders for export and just want to update cod info
      $csv_out.=$zas_lang['saved_ok'];
    }
    
    vmConnector::sendHeaderAndContent( 200, $csv_out);
    //echo $csv_out;
    exit();    


  }

  public function normalize_phone($value)
      {
      $value = str_replace(' ', '', trim($value));
      
      // remove garbage around phone number - but only accept proper count of digits, else we want an error thrown
      if(preg_match('/(?:\+|00)?(42[01][0-9]{9})([^0-9]|$)/', $value, $m)) { $value = "+$m[1]"; }
      elseif(preg_match('/(^|[^0-9])0?([0-9]{9})([^0-9]|$)/', $value, $m)) { $value = $m[2]; }
    
      // clear default value (backwards compatibility), autodetect prefix
      if($value == "+420" || $value == "+421") {
          $value = "";
      }
      elseif($value[0] == '6' || $value[0] == '7') {
          $value = "+420$value";
      }
      elseif($value[0] == '9') {
          $value = "+421$value";
      }
      
      return ($value ? $value : null);      
    }

  /**
   * Display shipping options on front-end
   */

  function display_html(&$myfile, &$order=null, $options=null)
  {
    require(CLASSPATH ."shipping/".$this->classname."/".$this->classname.".countries.cfg.php");
    global $CURRENCY_DISPLAY;    
    // load config    
    $lg = &JFactory::getLanguage();      
    if (file_exists(CLASSPATH . 'shipping/' . $this->classname . '/' . $lg->getTag() . '.' . $this->classname . '.php')) {            
      require_once(CLASSPATH . 'shipping/' . $this->classname . '/' . $lg->getTag() . '.' . $this->classname . '.php');            
    } elseif  (file_exists(CLASSPATH . 'shipping/' . $this->classname . '/' . $lg->getDefault() . '.' . $this->classname . '.php')) {      
      require_once(CLASSPATH . 'shipping/' . $this->classname . '/' . $lg->getDefault() . '.' . $this->classname . '.php');
    } elseif  (file_exists(CLASSPATH . 'shipping/' . $this->classname . '/cs-CZ.' . $this->classname . '.php')) {      
      require_once(CLASSPATH . 'shipping/' . $this->classname . '/cs-CZ.' . $this->classname . '.php');
    }
    if($this->getConfig("fix_prices")=='fix_prices_true'){
      $fix_prices=true;
    }else{
      $fix_prices=false;
    }    

    $html = null;    
    $branchJS=$this->getJSApi();    
    if($this->errors){
      $this->return_errors();
      return "";
    }
    if($this->warnings){
      $this->return_warnings();
    }        
    if($this->updateBranchesInfo()==false||$this->errors){
      $this->return_errors();
      return "";
    }
    if($this->warnings){
      $this->return_warnings();
    }
    $html .='<script src="'.$branchJS.'"></script>';
    $html .='
    <script language="javascript\" type="text/javascript">      
      (function($) {
      window.addHooks=function(){        
        function updateError() {
        $("div.packetery-branch-list").each(function() { 
            this.packetery.option("required", false);            
          }).prev("p").hide();
          var checkedRadio=$(\'input[name="shipping_rate_id"]:radio:checked\');
          var radioVal=decodeURIComponent(checkedRadio.val());
          var splitted=radioVal.split(\'|\');  
          if(splitted[0]!="'.$this->classname.'")return;
          var selectDiv=checkedRadio.closest("tr").find("div.packetery-branch-list");
          if(selectDiv[0].packetery.option("selected-id"))return;          
          checkedRadio.closest("tr").find(\'p[name="select-branch-message"]\').show(); 
          selectDiv[0].packetery.option("required", true);         
        }
        updateError();
        function selectMethod(){                                                      
          var branches = this.packetery.option("branches");
          var selected_id = this.packetery.option("selected-id");              
          var x = $(this).closest(\'tr\').find("input:radio");                           
          var oldVal=decodeURIComponent(x.val());              
          var splitted=oldVal.split(\'|\');              
          var newVal="";
          if(selected_id){
            newVal=splitted[0]+"|"+splitted[1]+"|" + branches[selected_id].name_street + " - [id=" + branches[selected_id].id + "]|" +splitted[3];              
            if(splitted[4])newVal+="|" + splitted[4];                              
          }else{
            newVal=splitted[0]+"|"+splitted[1]+"|not-set|"+splitted[3];
          }
          newVal=encodeURIComponent(newVal);              
          x.val(newVal);                                      
          x.attr(\'checked\',true);              
          setTimeout(updateError, 1);
        }
        $("div.packetery-branch-list").each(
          function() {             
            this.packetery.option("selected-id",0);
            this.packetery.on("branch-change", selectMethod);
          }
        );
      $(\'input[name="shipping_rate_id"]:radio\').on("change", function() {setTimeout(updateError, 1); });
      
      $("div.defaultOption").each(
        function() {             
          selectMethod.call(this);
        }
      );
        
        
      } 
      })(window.packetery.jQuery);
    </script>';        
    $html .='<table width="100%">
    <tr class="sectiontableheader"><th>&nbsp;</th><th>'.$zas_lang['shipper'].'</th><th>'.$zas_lang['delivery_info'].'</th><th width="10%">'.$zas_lang['packet_price'].'</th></tr>
    ';
    foreach($countries as $key => $country){
      $country->priority = $this->getConfig("_priority",$country->code);
    }
    $sortByPriority = function ($a, $b){
      return $a->priority <= $b->priority;
    };
    usort($countries,$sortByPriority);
    $defaultMethod = false;
    foreach($countries as $key => $country){
      if($this->getConfig("_branches",$country->code)=="_branches_false"){
        continue;
      }
      $shippingID = uniqid('zasilkovna_shipping');
      if($fix_prices){    
        $shippingPrice=$this->getConfig('_fix_price_'.$GLOBALS['product_currency'],$country->code);
        $radioValShipPrice=$shippingPrice*$this->exchgRateCheck($GLOBALS['product_currency']);
        if($shippingPrice=="") {
          continue;
        }        
      }else{     
        $shippingPrice = number_format($GLOBALS['CURRENCY']->convert( $this->getConfig('_branch_price',$country->code), $this->getConfig("currency"), $GLOBALS['product_currency'] ),2);              
        $radioValShipPrice=$shippingPrice*$this->exchgRateCheck($GLOBALS['product_currency']);
        if($shippingPrice=="") {
          continue;
        }    
      }      
      $vm_db = new ps_DB;      
      $q = "SELECT name_street,id ";
      $q .= "FROM #__zasilkovna_branches ";
      $vm_db->query($q);

      //register shipping_method_id to session for every possible dest. branch
      while ($vm_db->next_record()) {          
        $shipping_rate_id = rawurlencode( $this->classname.'|'.$this->_carrier.'|'.$vm_db->f("name_street")." - [id=".$vm_db->f("id").']|'.$radioValShipPrice);                        
        $_SESSION[$shipping_rate_id] = 1;            
      }    

      $html.='  
      <tr class="sectiontableentry2"><td width="10">
      <input type="radio" id="'.$shippingID.'" name="shipping_rate_id" value="'.$this->classname.'|'.$this->_carrier.'|not-set|'.$radioValShipPrice.'" /></td>
      <td>';
      if($this->getConfig("_show_logo",$country->code)=="_show_logo_true"){
        $html .= '<img src="'.$this->_module_media_url.'logo-'.$country->code.'.jpg"><br>';            
      }
      $html.= $this->getConfig('_shipper_name',$country->code).'<br>';
      $html.='<p name="select-branch-message" style="float: none; color: red; font-weight: bold; display: none; ">'.$this->lang('select_branch').'</p><div id="zasilkovna_select" class="packetery-branch-list list-type=3 country='.$country->code.' '.($defaultMethod==false?'defaultOption':'').'" style="border: 1px dotted black;">Načítání: seznam poboček osobního odběru</div>';  
      $defaultMethod = true;
      $html.='</select>';
                
      $html.='</td><td><label for="shipping_rate_id_ss_8">'.$this->getConfig('_info',$country->code).'</label></td><td>'.$CURRENCY_DISPLAY->getFullValue($shippingPrice).'</td></tr>';          
    }
    $html.='</table>';

    return $html;
  }

  /**
   * Get rate info
   */
  function get_rate( &$d ) 
  {
    $shipping_rate_id = $d["shipping_rate_id"];
    $is_arr = explode("|", urldecode(urldecode($shipping_rate_id)) );
    $order_shipping = $is_arr[3];
    
    return $order_shipping;
  }

  /**
   * Get tax info
   */
  function get_tax_rate() 
  {
    /** Read current Configuration ***/    
    
    require_once(CLASSPATH ."shipping/".$this->classname.".cfg.php");    
    if( intval($this->getConfig("_tax_rate"))){
      require_once( CLASSPATH. "ps_tax.php" );
      $tax_rate = ps_tax::get_taxrate_by_id( intval($this->getConfig("_tax_rate")) );
      return $tax_rate;
    }
    return 0;
  }

  /** 
  * check if session has been set for this shipping_rate_id
   */
  function validate( &$d ) 
  {        
    $d["shipping_rate_id"]=str_replace('+','%20',$d["shipping_rate_id"]);    
    return ( array_key_exists( $d["shipping_rate_id"], $_SESSION )) ? true : false;
  }
  
  /**
  * show configuration form
  */
  function show_configuration() 
  {   
    global $VM_LANG;
      
    require(CLASSPATH ."shipping/".$this->classname."/".$this->classname.".countries.cfg.php");

    $lg = &JFactory::getLanguage();      
    if (file_exists(CLASSPATH . 'shipping/' . $this->classname . '/' . $lg->getTag() . '.' . $this->classname . '.php')) {            
      require_once(CLASSPATH . 'shipping/' . $this->classname . '/' . $lg->getTag() . '.' . $this->classname . '.php');            
    } elseif  (file_exists(CLASSPATH . 'shipping/' . $this->classname . '/' . $lg->getDefault() . '.' . $this->classname . '.php')) {      
      require_once(CLASSPATH . 'shipping/' . $this->classname . '/' . $lg->getDefault() . '.' . $this->classname . '.php');
    } elseif  (file_exists(CLASSPATH . 'shipping/' . $this->classname . '/cs-CZ.' . $this->classname . '.php')) {      
      require_once(CLASSPATH . 'shipping/' . $this->classname . '/cs-CZ.' . $this->classname . '.php');
    }    
    $accepted_currency=$this->getAcceptedCurrenciesArr();        
    $this->checkConfiguration();
    $branchJS=$this->getJSApi(); 
    $this->updateBranchesInfo();
    echo '<script src="'.$branchJS.'"></script>';
    ?>
    <script language="javascript\" type="text/javascript">      
    (function($) {
      window.fixedPricesOn = function(){
        $("tr.relative_price_form").hide();
        $("tr.fix_price_form").show(); 
      }
      window.fixedPricesOff = function(){
        $("tr.relative_price_form").show();
        $("tr.fix_price_form").hide(); 
      }
    })(window.packetery.jQuery);
    </script>
  <table>
      <tr>
        <td>
          <strong><?php echo $zas_lang['errors_warnings'].":"; ?></strong>
        </td>
        <td>
          <?php 
            if($this->errors||$this->warnings){          
              echo $this->return_errors().$this->return_warnings();
            }else{
              echo $zas_lang['no_errors_warnings'];
            }
          ?>
        </td>      
      </tr>       
    <tr>
      <td>
        <strong><?php echo $zas_lang['module_version'].":"; ?></strong>
      </td>
      <td>
        <?php echo self::VERSION.' - '.$this->checkVersion();?>
      </td>      
    </tr>
    <tr>
      <td>
        <strong><?php echo $zas_lang['api_key'].":"; ?></strong>
      </td>
      <td>
        <input type="text" name="api_key" class="inputbox" size="18" value="<?php echo $this->getConfig("api_key"); ?>" />
      </td>      
      <td>
        <?php echo mm_ToolTip($zas_lang['api_key_tip']); ?>        
      </td>
    </tr>    
    <tr>
      <td>  
        <strong><?php echo $zas_lang['eshop_domain'].":"; ?></strong>
      </td>
      <td>
        <input type="text" name="eshop_domain" class="inputbox" size="18" value="<?php echo $this->getConfig("eshop_domain"); ?>" />
      </td>      
      <td>
        <?php echo mm_ToolTip($zas_lang['eshop_domain_tip']); ?>        
      </td>
    </tr>    
    <tr>

    <tr>      
      <td>         
        <strong><?php echo $this->lang('shipping_price_in_diff_currencies'); ?>:</strong>
      </td>
      <td>      
      <?php 
      if($this->getConfig("fix_prices")=='fix_prices_true'){
        $fix_prices=true;
      }else{
        $fix_prices=false;
      }
      ?>  
        <input type="radio" onchange="fixedPricesOn();" name="fix_prices" value="fix_prices_true" <?php echo ($this->getConfig("fix_prices")=='fix_prices_true' ? 'checked':''); ?> ><?php echo $this->lang('fixed_by_currency'); ?>
        <input type="radio" onchange="fixedPricesOff();" name="fix_prices" value="fix_prices_false" <?php echo ($this->getConfig("fix_prices")!='fix_prices_true' ? 'checked':''); ?> > <?php echo $this->lang('converted_by_rate'); ?>
      </td>      
    </tr>

    <tr>      
      <td colspan="3"><hr /></td>
    </tr> 
    <?php foreach($countries as $key => $country){ ?>
    <tr>      
      <td>
         
        <strong><?php echo $zas_lang['offer_'.$country->code].":"; ?></strong>
      </td>
      <td>        
        <input type="radio" name="<?php echo $country->code;?>_branches" value="_branches_true" <?php echo ($this->getConfig("_branches",$country->code)=='_branches_true' ? 'checked':''); ?> >Ano
        <input type="radio" name="<?php echo $country->code;?>_branches" value="_branches_false" <?php echo ($this->getConfig("_branches",$country->code)!='_branches_true' ? 'checked':''); ?> > Ne
      </td>      
    </tr> 
    <tr>      
      <td>         
        <strong><?php echo $zas_lang['show_logo'].":"; ?></strong>
      </td>
      <td>        
        <input type="radio" name="<?php echo $country->code;?>_show_logo" value="_show_logo_true" <?php echo ($this->getConfig("_show_logo",$country->code)=='_show_logo_true' ? 'checked':''); ?> >Ano
        <input type="radio" name="<?php echo $country->code;?>_show_logo" value="_show_logo_false" <?php echo ($this->getConfig("_show_logo",$country->code)!='_show_logo_true' ? 'checked':''); ?> > Ne
      </td>      
    </tr>

    <?php
      echo $this->fixPricesForm($country,$fix_prices);
    ?>

    <tr class="relative_price_form" style="<?php echo ($fix_prices ? "display:none;": "");?>" >
      <td>  
        <strong><?php echo $zas_lang['packet_price_by_exchg_rate'].":"; ?></strong>
      </td>
      <td>   
      <?php
        $branch_price = $GLOBALS['CURRENCY']->convert( $this->getConfig("_branch_price",$country->code), $this->getConfig("currency"), $GLOBALS['product_currency'] );      
        $branch_price = number_format($branch_price,2);
      ?>     
        <input type="text" name="<?php echo $country->code;?>_branch_price" class="inputbox" size="5" value="<?php echo $branch_price; ?>" /> <?php echo $GLOBALS['product_currency']; ?>
      </td>  
      <?php
        if($branch_price==""){
          echo '<td style="color:red;">'.$this->lang('price_not_set').'!</td>';
        }
      ?>    
    </tr>
    <tr>

    <tr>
      <td>  
        <strong><?php echo $zas_lang['label_'.$country->code].":"; ?></strong>
      </td>
      <td>   
   
        <input type="text" name="<?php echo $country->code;?>_shipper_name" class="inputbox" size="15" value="<?php echo $this->getConfig('_shipper_name',$country->code); ?>" />
      </td>      
    </tr>    
    <tr>
      <td>
        <strong><?php echo $zas_lang['info_'.$country->code].":"; ?></strong>
      </td>
      <td>        
        <textarea type="text" name="<?php echo $country->code;?>_info" class="inputbox" rows="3" ><?php echo $this->getConfig("_info",$country->code); ?></textarea>
      </td>      
    </tr>
    <tr>
      <td>
        <strong><?php echo $zas_lang['priority'].":"; ?></strong>
      </td>
      <td>        
        <input type="text" name="<?php echo $country->code;?>_priority" class="inputbox" size="5" value="<?php echo $this->getConfig("_priority",$country->code); ?>" />
      </td>
      <td>
        <?php echo mm_ToolTip($zas_lang['priority_tip']); ?> 
      </td>      
    </tr>         
    <tr>
      <td colspan="3"><hr /></td>
    </tr>  
    <?php } ?> 
    <td><strong><?php echo $zas_lang['select_tax_rate'] ?></strong></td>
    <td>
      <?php
      require_once(CLASSPATH.'ps_tax.php');
      ps_tax::list_tax_value("_tax_rate", $this->getConfig("_tax_rate")); ?>
    </td>
    <td><?php echo mm_ToolTip($VM_LANG->_('PHPSHOP_UPS_TAX_CLASS_TOOLTIP')) ?><td>
    </tr>   
  <tr>
      <td>
        <strong><?php echo $zas_lang['select_cash_on_del_payments'].":"; ?></strong>
      </td>      
      <td>
        <?php echo mm_ToolTip($zas_lang['select_cash_on_del_payments_tip']); ?>        
      </td>
    </tr> 
    <?php
    
      $vm_db = new ps_DB;      
      $q = "SELECT payment_method_id,payment_method_name ";
      $q .= "FROM #__{vm}_payment_method WHERE payment_enabled = 'Y'";
      $vm_db->query($q);
      while ($vm_db->next_record()) {         
        echo '<tr>
      <td>
        <strong>'.$vm_db->f("payment_method_name").'</strong>
      </td>
      <td>        
        <input type="checkbox" name="cod'.$vm_db->f("payment_method_id").'"';
        if($this->getConfig('cod'.$vm_db->f("payment_method_id"))=='true'){
          echo " checked ";
        } 
        echo '>
      </td>      
    </tr> '  ;      
      }
      ?>
  <tr>
      <td colspan="3"><hr /></td>
  </tr>
    <tr>
      <td>
        <strong><?php echo $zas_lang['s_p_settings'].":"; ?> </strong>
      </td>      
      <td>
        <?php 
        if(method_exists('vm_ps_payment_method','paymentMethodIsAllowed')){
          echo $zas_lang['s_p_installed'];
        }else{  
          echo $zas_lang['s_p_not_installed'];
        }        
        ?>
        <br>
        <a href="<?php echo JURI::base(true);?>/index.php?page=store.ship_payment&amp;option=com_virtuemart"><strong><?php echo $this->lang('set_s_p');?> &raquo;</strong></a>
      </td>
    </tr>     
  </table>
  <input type="hidden" name="currency" value="<?php echo $GLOBALS['product_currency']; ?>" />
    <?php   
    return true;
  }
  /**
  * true when cfg is writable
  */
  function configfile_writeable() {
    return is_writeable( CLASSPATH . "shipping/".$this->classname. ".cfg.php" );
  }

  /**
  * write config to cfg file
  */
  function write_configuration( &$d ) 
  {
    require_once(CLASSPATH ."shipping/".$this->classname."/".$this->classname.".countries.cfg.php");
    global $vmLogger;    
    $my_config_array = array(      
      '_branches',
      '_branch_price',
      '_cash_on_del_fee',
      '_info',
      '_show_logo',
      '_shipper_name',
      '_fix_price_',
      '_priority'
    );
    
    $config = "<?php\n";
    $config .= "if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); \n\n";
    $config .= '$config[\'api_key\']=\''.$d['api_key']."';\n";
    $config .= '$config[\'eshop_domain\']=\''.$d['eshop_domain']."';\n";
    $config .= '$config[\'currency\']=\''.$d['currency']."';\n";    
    $config .= '$config[\'fix_prices\']=\''.$d['fix_prices']."';\n";    
    $config .= '$config[\'_tax_rate\']=\''.$d['_tax_rate']."';\n";    
    foreach($countries as $key => $country){
      foreach( $my_config_array as $key => $value ) {       
        if($value=='_fix_price_'){
          foreach ($this->getAcceptedCurrenciesArr() as $currency) {
            $config .= '$config[\''.$country->code.'\'][\''.$value.$currency.'\']=\''.$d[$country->code.$value.$currency]."';\n";  
          }
        }else{
          $config .= '$config[\''.$country->code.'\'][\''.$value.'\']=\''.$d[$country->code.$value]."';\n";
        }
      }
    }
    $vm_db = new ps_DB;      
    $q = "SELECT payment_method_id,payment_method_name ";
    $q .= "FROM #__{vm}_payment_method WHERE payment_enabled = 'Y'";
    $vm_db->query($q);
    while ($vm_db->next_record()) {  
      if($d['cod'.$vm_db->f("payment_method_id")]=='on'){  
        $config .='$config[\'cod'.$vm_db->f("payment_method_id").'\']=';
        $config .="'true';\n";
      }
    }

    if ($fp = fopen(CLASSPATH ."shipping/".$this->classname.".cfg.php", "w")) 
    {
      fputs($fp, $config, strlen($config));
      fclose ($fp);
      return true;
    }
    else 
    {
      $vmLogger->err( "Error writing to configuration file" );
      return false;
    }
  }



  public function updateBranchesInfo(){
    $localFilePath=JPATH_SITE.'/media/com_'.$this->classname.'/media/branch.xml';            
    if(!$this->is_writable($localFilePath))return false;
    if(!$this->isFileUpToDate($localFilePath)){     
      // file is older than one days
      if(!$this->updateFile($localFilePath,"xml")){
        //failed updating
        if(!$this->isFileUsable($localFilePath)){
          //file is older than 5 days and thus not usable
          $this->errors[]=$this->lang('cannot_update_xml_file_older_5_days');          
          return false;
        }      
      }else{      
        //updating succeeded, update mysql db
        if(!$this->saveBranchesXmlToDb($localFilePath)){        
          $this->errors[]=$this->lang('cannot_load_branch_list');
          return false;
        }
      }
    }
    return true;
  }

  private function saveBranchesXmlToDb($path){
    
    $xml = simplexml_load_file($path);
    if($xml){      
      $vm_db = new ps_DB; 
      $q =  "TRUNCATE TABLE #__zasilkovna_branches";
      $vm_db->query($q);
      $q = "INSERT INTO #__zasilkovna_branches (
              `id` ,
              `name_street` ,
              `currency` ,
              `country`
              ) VALUES ";
      $first=true;              
      foreach($xml->branches->branch as $key => $branch){          
        if($first){
          $q.=" (";
          $first=false;
        }else{
          $q.=", (";          
        }  
        $q .= "'$branch->id', '$branch->name_street','$branch->currency','$branch->country')";                    
        
      }
    $vm_db->query($q);        
    }else{      
      return false;
    }
    return true;
  }
  public function getJSApi(){    
    $localFilePath=JPATH_SITE.'/media/com_'.$this->classname.'/media/branch.js';            
    if(!$this->is_writable($localFilePath))return false;
    if(!$this->isFileUpToDate($localFilePath)){       
      if(!$this->updateFile($localFilePath,'js')){
        //updating file failed                

        if(!$this->isFileUsable($localFilePath)){
          // if file is older than 5 days
          $this->errors[]=$this->lang('cannot_update_js_older_5_days');
          return false;
        }
      }      
    }
    return $this->_module_media_url."branch.js";
  }

  private function isFileUpToDate($path){    
    if(!file_exists($path)){return false;    }
    if(filemtime($path) < time() - (60*60*24)) {
      // file too old
      return false;
    }
    if(filesize($path)<=1024){
      //weird, file is too small
      return false;
    }
    //file is updated
    return true;    
  }

  private function isFileUsable($path){    
    if(!file_exists($path)){return false;    }
    if(filemtime($path) < time() - (60*60*24*5)) {
      // file too old
      return false;
    }
    if(filesize($path)<=1024){
      //weird, file is too small
      return false;
    }
    //file is updated
    return true;      
  }


  private function updateFile($path,$type){
    $remote = $this->_zas_url."api/".$this->getConfig("api_key")."/branch.".$type;
    if($type=='js'){
      $lib_path=substr($this->_module_media_url,0,-1);
      $remote.="?callback=addHooks";
      $remote.="&lib_path=$lib_path&sync_load=1";
    }        
    $data=$this->fetch($remote);        
    file_put_contents($path, $data);
    clearstatcache();
    if(filesize($path)<1024){          
      return false;
    }
    return true;
  }

  private function checkConfiguration(){        
    if($this->checked_configuration)return $this->config_ok;
    $this->checked_configuration=true;    
    $key = $this->getConfig("api_key");    
    $testUrl = $this->_zas_url."api/$key/test";    
    if(!$key) {
        $this->errors[] = $this->lang('api_key_not_set');
        $this->config_ok=false;
        return false;
    }
    if($this->curlAllowed()){
      //curl ok
    }else if($this->urlOpenAllowed()){
      //fopen ok
    }else{
      $this->errors[] = $this->lang('curl_and_url_fopen_disabled');
      $this->config_ok=false;
      return false;      
    }
    if($this->fetch($testUrl) != 1) {
      $this->errors[] = $this->lang('api_key_not_verified');      
    }   
    $this->config_ok=true;
    return true;
  }

  private function curlAllowed(){
    return extension_loaded('curl') && function_exists('curl_exec');
  }

  private function urlOpenAllowed(){
    return ini_get('allow_url_fopen') && (function_exists('stream_context_create')||function_exists('file_get_contents'));
  }

  public function configExists($configName,$country="NotSet"){
    require(CLASSPATH ."shipping/".$this->classname.".cfg.php");    
    if($country=="NotSet"){
      if(array_key_exists($configName,$config)){
        return true;
      }      
    }else{
      if(isset($config[$country][$configName])){
        return true;
      }
    }
    return false;
  }
  public function getConfig($configName,$country="NotSet"){
    require(CLASSPATH ."shipping/".$this->classname.".cfg.php");    
    if($country=="NotSet"){
      if(array_key_exists($configName,$config)){
        return $config[$configName];
      }
      return "";
    }else{
      return $config[$country][$configName];
    }
  } 
  private function fetch($url)
  {
    if($this->curlAllowed()) {
          $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL, $url);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
          curl_setopt($ch, CURLOPT_AUTOREFERER, false);
          curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
          curl_setopt($ch, CURLOPT_TIMEOUT, 3);
          $body = curl_exec($ch);
          if(curl_errno($ch) > 0) {
              return false;
          }
          return $body;
      }
    elseif($this->urlOpenAllowed()) {
          if(function_exists('stream_context_create')) {
              $ctx = stream_context_create(array('http' => array('timeout' => 3)));
              return file_get_contents($url, 0, $ctx);              
          }
          else {            
              return file_get_contents($url);
          }
      }
      else return false;
  }

  private function is_writable($filepath){
    if(!file_exists($filepath)){      
      @touch($filepath);
    }
    if(is_writable($filepath)){
      return true;
    }
    $this->errors[]=$filepath." must be writable.";
    return false;
  }

  public function return_errors(){
    if(!$this->errors)return;
    $ret="<br><b>".$this->classname." ".$this->lang('shipping_module_errors').": </b><br>"  ;
    foreach($this->errors as $error){
      $ret.=$error."<br>";
    }
    unset($this->errors);
    return $ret;
  }


  public function return_warnings(){
    if(!$this->warnings)return;
    $ret="<br><b>".$this->classname." ".$this->lang('shipping_module_warnings').": </b><br>"  ;
    foreach($this->warnings as $warning){
      $ret.=$warning."<br>";
    }
    unset($this->warnings);
    return $ret;
  }

  public function checkVersion(){
    if(!$this->checkConfiguration()){
      return "";
    }
    $key = $this->getConfig("api_key");    
    $url=$this->_zas_url."api/$key/version-check-virtuemart?my=" . self::VERSION;    
    $data = json_decode($this->fetch($url));        
    if($data->version > self::VERSION) {
        global $cookie;          
        $lg=&JFactory::getLanguage();
        $lang=substr($lg->getTag(), 0, 2);
        $ret = $this->lang('new_version_aval'). ' ' . $data->message->$lang;
    }else{        
        $ret = $this->lang('no_new_version');
    }   
    return $ret;
  }

  public function lang($key){
    $lg = &JFactory::getLanguage();      
    if (file_exists(CLASSPATH . 'shipping/' . $this->classname . '/' . $lg->getTag() . '.' . $this->classname . '.php')) {            
      require(CLASSPATH . 'shipping/' . $this->classname . '/' . $lg->getTag() . '.' . $this->classname . '.php');                  
    } elseif  (file_exists(CLASSPATH . 'shipping/' . $this->classname . '/' . $lg->getDefault() . '.' . $this->classname . '.php')) {      
      require(CLASSPATH . 'shipping/' . $this->classname . '/' . $lg->getDefault() . '.' . $this->classname . '.php');
    } elseif  (file_exists(CLASSPATH . 'shipping/' . $this->classname . '/cs-CZ.' . $this->classname . '.php')) {      
      require(CLASSPATH . 'shipping/' . $this->classname . '/cs-CZ.' . $this->classname . '.php');
    }
    return $zas_lang[$key];
  }

  public function getAcceptedCurrenciesArr(){
    include_class( "vendor" );
    global $ps_vendor, $ps_vendor_id;            
    $vm_db = new ps_DB;      
    $q = "SELECT vendor_accepted_currencies FROM #__{vm}_vendor WHERE vendor_id='$ps_vendor_id'"; 
    $vm_db->query($q);  
    return explode(',',$vm_db->f('vendor_accepted_currencies'));
  }

  public function fixPricesForm($country,$fix_prices){
    $currencies=$this->getAcceptedCurrenciesArr();
    $ret="";
    foreach($currencies as $currency){
      $ret.= '<tr class="fix_price_form" style="'.($fix_prices ? "": "display:none;").'">
      <td>  
        <strong>'.$this->lang('fixed_price').'</strong>
      </td>
      <td>     
        <input type="text" name="'.$country->code.'_fix_price_'.$currency.'" class="inputbox" size="5" value="'.$this->getConfig('_fix_price_'.$currency,$country->code).'" /> '.$currency.'
      </td>';      
    if($this->getConfig('_fix_price_'.$currency,$country->code)==""){
      $ret.= '<td style="color:red;">'.$this->lang('price_not_set').'!</td>';
    }
    $ret.= '
    </tr>    
    <tr>';
    }
    return $ret;
  }
  public function exchgRateCheck($currency){
    $conversion_check=$GLOBALS['CURRENCY']->convert( 100, $currency,$this->main_currency);
    if($conversion_check!=100){
      $rate_fix=$conversion_check/100;
    }else{
      $rate_fix=1;
    }      
    return $rate_fix;
  }
}

?>