<?php
if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
class branch{
	var $name;
	var $currency;
	var $address;
	public function __construct($name,$address,$currency){
		$this->name=$name;
		$this->address=$address;
		$this->currency=$currency;
	}	
}

class country{
	var $name;
	var $code;
	var $CODE;
	var $currency;
	public function __construct($name,$code,$currency){
		$this->name=$name;
		$this->code=$code;
		$this->currency=$currency;
	}
}