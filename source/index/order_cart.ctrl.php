<?php
class order_cartControl extends skymvc{
	public $oc_where;
	public $userid;
	public function __construct(){
		parent::__construct();
		$this->loadModel(array("login","order_cart","product"));
		$this->oc_where=$this->login->userid?" (userid=".$this->login->userid." or oc_ssid='".OC_SSID."')  ":"oc_ssid='".OC_SSID."' ";
		$this->userid=$this->login->userid;
	}
	
	public function onDefault(){
		 
		$data=M("order_cart")->cart();
		 
		$this->smarty->goassign(array(
			"list"=>$data['list'],
			"total_money"=>$data['total_money'],
			"num"=>$data['num'],
			"total_num"=>$data['total_num']
		));
		$this->smarty->display("order_cart/index.html");
	}
	
	public function onGetCart(){
		 
		$this->smarty->display("order_cart/cart.html");
	}
	
	public function onAdd(){
		$data['object_id']=get_post('object_id','i');
		if(!$data['object_id']) exit(json_encode(array("error"=>1,"status"=>"failed","message"=>"商品不存在")));
		$t_d=$this->product->selectRow(array("where"=>" status=2  AND id=".$data['object_id']));
		if(empty($t_d)) exit(json_encode(array("error"=>1,"status"=>"failed","message"=>"商品已下线")));
		//判断商品数量
		$data['ksid']=get_post('ksid','i');
		if($data['ksid']){
			$ks=M('product_ks')->selectRow("id=".$data['ksid']);
			if($ks){
				$total_num=$ks['total_num'];
			}
		}else{
			$total_num=$t_d['total_num'];
		}
		$amount=$data['amount']=max(1,get_post('amount','i'));
		if($total_num<$data['amount']){
			exit(json_encode(array("error"=>1,"status"=>"failed","message"=>"商品库存不足")));
		}
		$data['type_id']=max(1,get('type_id'));
		$data['dateline']=time();
		$data['userid']=$this->login->userid;
		
		
		$data['shopid']=$t_d['shopid'];
		$data['oc_ssid']=OC_SSID;
		
		 
		$row=$this->order_cart->selectRow(array("where"=>$this->oc_where."  AND object_id=".$data['object_id']." and ksid='".$data['ksid']."' ")); 
		if($row){
			if($total_num <($amount+$row['amount'])){
				exit(json_encode(array("error"=>1,"status"=>"failed","message"=>"商品库存不足")));
			}
			$amount=$amount+$row['amount'];
			$cart_id=$row['id'];	
			$this->order_cart->changenum("amount",$amount,"id=".$row['id']);
			$isupdate=1;
		}else{
			$isupdate=0;
			$cart_id=$this->order_cart->insert($data);
		}
		
		 
		$data=M("order_cart")->cart();
		
		exit(sky_json_encode(array("error"=>0,"message"=>"加入成功","status"=>"success","data"=>$data)));
	}
	/**
	*直接购买
	*/
	public function onBuy(){
		$this->loadModel(array("user_address","district"));
		$this->loadControl("order_cartapi");
		$this->login->checklogin();
		$user=$this->login->getuser();
		$object_id=get('object_id','i');
		$data=$this->product->selectRow(array("where"=>" id=".$object_id));
		$ksid=get_post('ksid','i');
		if(empty($data)) $this->goall("请先去选购产品",1,0,"/index.php");
		if($ksid){
			$ks=M('product_ks')->selectRow("id=".$ksid);
			$data['ks_title']=$ks['title'];
			$data['price']=$data['lower_price']?$data['lower_price']:$ks['price']; 
		 }
		 
		//收货地址
		$address=$this->user_address->select(array("where"=>"userid=".$user['userid'],"order"=>"isdefault desc"));
		if($address){
			foreach($address as $v){
				$d_ids[]=$v['province_id'];
				$d_ids[]=$v['city_id'];
				$d_ids[]=$v['town_id'];
			}
			$dist_list=$this->district->dist_list(array("where"=>" id in(".implode(",",$d_ids).") ","start"=>0,"limit"=>1000000)); 
		}
		$moneypay=0;	
		if($user['money']>=$orderCart['total_money']){
			$moneypay=1;
		}
		$this->smarty->goassign(array(
			"address"=>$address,
			"dist_list"=>$dist_list,
			"back_url"=>$_SERVER['HTTP_REFERER'],
			"data"=>$data,
			"ksid"=>$ksid,
			"user"=>$user,
			"pay_type_list"=>pay_type_list($moneypay)
		));
		$this->smarty->display("order_cart/buy.html");
	}
	
	public function onDelete(){
		$id=get_post('id','i');
		$data=$this->order_cart->selectRow(array("where"=>$this->oc_where." AND id=".$id." "));
		if(empty($data)){
			exit(json_encode(array("error"=>1,"message"=>"购物车不存在该商品")));
		}
		$this->order_cart->delete("id=$id  ");
		 
		$cart=$this->order_cart->cart();
		exit(json_encode(array("error"=>0,"message"=>"删除成功","data"=>$cart)));
	}
	
	public function onClear(){
		$this->order_cart->delete($this->oc_where);
		exit(json_encode(array("error"=>0,"message"=>"删除成功")));
	}
	
	public function onNum_plus(){
		$id=get_post('id','i');
		$num=max(1,get_post('num'));
		$data=$this->order_cart->selectRow(array("where"=>$this->oc_where." AND id=".$id." "));
		if(empty($data)){
			exit(json_encode(array("error"=>1,"message"=>"购物车不存在该商品")));
		}
		$t_d=$this->product->selectRow(array("where"=>" status=2 AND id=".$data['object_id']));
		if(empty($t_d)) exit(json_encode(array("error"=>1,"message"=>"商品已下线")));
		//判断商品数量
		if($t_d['total_num']< ($data['amount']+$num) ){
			exit(json_encode(array("error"=>1,"message"=>"商品库存不足")));
		}
		
		$res=$this->order_cart->changenum("amount",$num,"   id=".$id." " );
		$data=M("order_cart")->cart();
		if($res){
			exit(json_encode(array("error"=>0,"message"=>"增加数量成功","data"=>$data))); 
		}else{
			exit(json_encode(array("error"=>1,"message"=>"增加数量失败")));
		}
	}
	
	public function onnum_minus(){
		$id=get_post('id','i');
		$num=max(1,get_post('num'));
		$data=$this->order_cart->selectRow(array("where"=>$this->oc_where." AND id=".$id." "));
		if(empty($data)){
			exit(json_encode(array("error"=>1,"message"=>"购物车不存在该商品")));
		}
		if($data['amount']-$num<=0){
			exit(json_encode(array("error"=>1,"message"=>"商品数量至少1件")));
		}
		$res=$this->order_cart->changenum("amount","-".$num, "   id=".$id." " );
		$data=M("order_cart")->cart();
		if($res){
			exit(json_encode(array("error"=>0,"message"=>"削减数量成功","data"=>$data)));
		}else{
			exit(json_encode(array("error"=>1,"message"=>"削减数量失败")));
		}
	}
	
}
?>