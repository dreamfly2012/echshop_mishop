<?php
class order_cartapiControl extends skymvc{
	public $oc_where;
	public function __construct(){
		parent::__construct();
		$this->loadModel(array("login","order_cart","product","shop","product_attr","attribute"));
		$this->oc_where=$this->login->userid?" (userid=".$this->login->userid." or oc_ssid='".OC_SSID."') ":"oc_ssid='".OC_SSID."' ";
	}
	
	public function onDefault(){
		
	}
	
	public function onCart(){
		$data=$this->Cart();
		echo sky_json_encode($data);
	}
	
	public function Cart(){
		$where=$this->oc_where;
		$list=$this->order_cart->select(array("where"=>$where));
		if($list){
			foreach($list as $v){				
				$ids[]=$v['object_id'];
				$ksids[]=$v['ksid'];
			}
			if($ids){
				$products=$this->product->id_list(array("where"=>" id in("._implode($ids).") "));
				$kslist=M('product_ks')->id_list(array("where"=>"  id in("._implode($ksids).") "));
			}
		 
			$total_money=0; 
			foreach($list as $k=>$v){
				$product=$products[$v['object_id']]; 
				
				
				$v['title']=$product['title'];
				$v['imgurl']=images_site($product['imgurl']);
			 	if(isset($kslist[$v['ksid']])){
					$ks=$kslist[$v['ksid']];
					$v['price']=$product['lower_price']>0?$product['lower_price']:$ks['price'];
					$v['ks_title']=$ks['title'];
				}else{
					$v['price']=$product['lower_price']>0?$product['lower_price']:$product['price'];
				}
				 
				$total_money+=$v['price']*$v['amount'];
				$list[$k]=$v;
			}
			
			return array("list"=>$list,"total_money"=>$total_money,"num"=>count($list),"islogin"=>$this->login->userid); 
		}
	 
		return array("list"=>false,"total_money"=>0,"num"=>0,"islogin"=>$this->login->userid); 
	}
	
}
?>