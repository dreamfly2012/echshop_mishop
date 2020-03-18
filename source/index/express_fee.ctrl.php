<?php
class express_feeControl extends skymvc{
	
	public function __construct(){
		
		parent::__construct();	
	}
	
	public function onDefault(){
		
	}
	
	public function ongetMoney(){
		$user_address_id=get('user_address_id');
		$weight=ceil(get('weight'));
		$shopid=get('shopid','i');
		$data=M("express_fee")->getMoney($user_address_id,$weight,$shopid); 
		$this->goAll("success",0,$data); 
	}
	
}

?>