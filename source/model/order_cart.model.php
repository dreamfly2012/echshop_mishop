<?php
class order_cartModel extends model{
	public $base;
	public $oc_where;
	public function __construct(&$base){
		parent::__construct($base);
		$this->base=$base;
		$this->table="order_cart";
		$this->oc_where=M("login")->userid?" (userid=".M("login")->userid." or oc_ssid='".OC_SSID."') ":"oc_ssid='".OC_SSID."' ";
	}
	
	
	
	public function Cart(){
 
		$where=$this->oc_where;
		$list=M("order_cart")->select(array("where"=>$where));
		if($list){
			foreach($list as $v){				
				$ids[]=$v['object_id'];
				$ksids[]=$v['ksid'];
			}
			if($ids){
				$products=M("product")->id_list(array("where"=>" id in("._implode($ids).") "));
				$kslist=M('product_ks')->id_list(array("where"=>"  id in("._implode($ksids).") "));
			}
		 
			$total_money=0;
			$total_num=0;
			$weight=0; 
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
				$total_num+=$v['amount'];
				$weight+=$v['amount']+$v['weight']; 
				$total_money+=$v['price']*$v['amount'];
				$list[$k]=$v;
			}
			
			return array("list"=>$list,"weight"=>$weight,"total_money"=>$total_money,"num"=>count($list),"total_num"=>$total_num,"islogin"=>$this->login->userid); 
		}
	 
		return array("list"=>false,"weight"=>0,"total_money"=>0,"num"=>0,"total_num"=>0,"islogin"=>$this->login->userid); 
	}
}

?>