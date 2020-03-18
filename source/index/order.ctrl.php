<?php
class orderControl extends skymvc{
	public $user;
	public $userid;
	public $order_type=1;
	public $oc_where;
	public function __construct(){
		parent::__construct();
		$this->loadModel(array("user","login","product","order","order_address","order_product","gold_log","order_log","notice","user_address","district","order_cart","shop"));
		$this->loadConfig("table");
		
		$this->login->checkLogin();
		$this->userid=$this->login->userid;
		$this->user=$this->login->getUser();
		
		$this->oc_where=$this->login->userid?" (userid=".$this->login->userid." or oc_ssid='".OC_SSID."')  ":"oc_ssid='".OC_SSID."' ";
	}
	public function onDefault(){
		
	}
	
	public function onConfirm(){
		 
		$user=$this->user;
		$orderCart=M("order_cart")->cart();
		 
		if(empty($orderCart['list'])) $this->goall("请先去选购产品",1,0,"/index.php");
		//收货地址
		$address=$this->user_address->select(array("where"=>"userid=".$user['userid'],"order"=>"isdefault desc"));
		if($address){
			foreach($address as $v){
				$d_ids[]=$v['province_id'];
				$d_ids[]=$v['city_id'];
				$d_ids[]=$v['town_id'];
			}
			$dist_list=$this->district->dist_list(array("where"=>" id in(".implode(",",$d_ids).") ","start"=>0,"limit"=>1000000)); 			foreach($address as $k=>$v){
					$v['province']=$dist_list[$v['province_id']];
					$v['city']=$dist_list[$v['city_id']];
					$v['town']=$dist_list[$v['town_id']];
					$address[$k]=$v;
			}
		}
		$moneypay=0;
		$user_address_id=0;
		if($address){
			$user_address_id=$address[0]['id'];
		}
		$express_fee=M("express_fee")->getMoney($user_address_id,$orderCart['weight']);
		$total_money=	$orderCart['total_money']+$express_fee;
		if($user['money']>=$total_money){
			$moneypay=1;
		}
		
		$this->smarty->assign(array(
			"prolist"=>$orderCart['list'],
			"express_fee"=>$express_fee,
			"goods_money"=>$orderCart['total_money'],
			"total_money"=>$total_money,
			"weight"=>$orderCart['weight'],
			"address"=>$address,
			"dist_list"=>$dist_list,
			"back_url"=>$_SERVER['HTTP_REFERER'],
			"orderCart"=>$orderCart,
			"user"=>$user,
			"pay_type_list"=>pay_type_list($moneypay)
		));
		$this->smarty->display("order/confirm.html");
		
	}
	
	public function onBuyProduct(){
		$data['object_id']=get_post('object_id','i');
		if(!$data['object_id']) $this->goall("商品不存在",1);;
		$t_d=$this->product->selectRow(array("where"=>" status>0 AND status<98 AND id=".$data['object_id']));
		if(empty($t_d)) $this->goall("商品已下线",1);;
		//判断商品数量
		$amount=$data['amount']=max(1,get_post('amount','i'));
		if($t_d['total_num']<$data['amount']){
			 
			$this->goall("商品库存不足",1);
		}
		$data['type_id']=max(1,get('type_id'));
		$data['dateline']=time();
		$data['userid']=$this->login->userid;
		$data['ksid']=get_post('ksid','i');
		
		$data['shopid']=$t_d['shopid'];
		$data['oc_ssid']=OC_SSID;
		 
		$row=$this->order_cart->selectRow(array("where"=>$this->oc_where."  AND object_id=".$data['object_id']." AND ksid=".$data['ksid']."  ")); 
		if($row){
			$cart_id=$row['id'];	
			$this->order_cart->update(array("amount"=>$data['amount']),"id=".$row['id']);
			$isupdate=1;
		}else{
			$isupdate=0;
			$cart_id=$this->order_cart->insert($data);
		}
		$_POST['cart_id']=array($cart_id);
		$this->onOrder();
	}
	
	public function onOrder(){
		$user=$this->user;
		$user_address_id=post('user_address_id','i');
		$addr=$this->user_address->selectRow(array("where"=>"id=".$user_address_id));
		if(empty($addr)){
			$this->goall("请选择收货地址",1);
		}
		//处理商品
		$ids=post('cart_id','i');
		$discount_money=0;
		if($ids){
			$cart_list=$this->order_cart->select(array("where"=> $this->oc_where ." AND id in("._implode($ids).") "));
			if(empty($cart_list)) $this->goall("请选择要购买的商品",1);
			$total_money=0;
			$shops=array();
			foreach($cart_list as $v){				
				$ids[]=$v['object_id'];
				$ksids[]=$v['ksid'];
			}
			if($ids){
				$products=$this->product->id_list(array("where"=>" id in("._implode($ids).") "));
				$kslist=M('product_ks')->id_list(array("where"=>"  id in("._implode($ksids).") "));
			}
			
			foreach($cart_list as $k=>$v){
				$product=$products[$v['object_id']];
				if(empty($product) or ($product['status']!=2)){
					$this->goall("产品".$t_d['title']."已下线，请删除该商品",1);
				}
				if(isset($kslist[$v['ksid']])){
					$ks=$kslist[$v['ksid']];
					$total_num=$ks['total_num'];
					$v['price']=$product['lower_price']>0?$product['lower_price']:$ks['price'];
				}else{
					$v['price']=$product['lower_price']>0?$product['lower_price']:$product['price'];
					$total_num=$product['total_num'];
				}
				if($total_num<$v['amount']){
					$this->goall("产品<a href='".R("/index.php?m=show&id=".$t_d['id'])."'>".$t_d['title']."</a>库存不足，当前还剩".($t_d['total_num']-$t_d['buy_num'])."件，请删除该商品",1);
				}
				if($product['lower_price']>0){
					$discount_money+=($product['price']-$product['lower_price'])*$v['amount'];
				}
				$total_money +=$v['price']*$v['amount'];
				$cart_list[$k]=$v;	
			}
			
			
			
		}else{
			$this->goall("请选择要购买的商品",1);
		}
		
		//End 处理商品
		$isfinish=0;
		$ispay=1;
		$this->loadControl("jfapi");
		$goods_money=$total_money;
		$express_money=EXPRESS_FEE;
		$total_money=$total_money+$express_money;
		/* Start优惠券 */
		$coupon_id=post('coupon_id','i');
		if($coupon_id){
			$this->loadModel(array("coupon","coupon_user"));
			$coupon=$this->coupon->selectRow("id=".$coupon_id);
			$coupon_user=$this->coupon_user->selectRow("coupon_id=".$coupon_id." AND status=0 AND userid=".$this->login->userid);			
			if($coupon && $coupon_user){
				$coupon_money=$coupon['money'];
				$this->coupon_user->update(array("status"=>1),"id=".$coupon_user['id']);
				$this->coupon->update(array("use_num"=>$coupon['use_num']+1),"id=".$coupon_id);
			}else{
				$coupon_id=0;
			}						
		}else{
			$coupon_money=0;
		}
		/* End 优惠券*/
		/****处理支付方式****/
		$pay_type=get_post('pay_type','h');
		if($pay_type=='moneypay'){
			if($total_money>$user['money']){
				$this->goALL("余额不足",1);
			}else{
				//添加金钱消费记录		
				M("user")->addMoney(array(
					"userid"=>$this->login->userid,
					"money"=>-$total_money,
					"content"=>"您购买了产品花了[money]元",	
				));
				$ispay=2;
				$isfinish=1;
			}
		}
		 
		
		if($pay_type=='unpay'){
			$isfinish=1;
			$unpay=1;
		}
		
		 /****首单优惠***/
		 
		 if(ORDER_FIRST_DISCOUNT>0){
			 $firstorder=M("order")->selectRow("userid=".$this->login->userid." AND status<4 ");
			 if(empty($firstorder) && $total_money>=ORDER_FIRST_MONEY){
			 	$total_money-=ORDER_FIRST_DISCOUNT;
				$discount_money+=ORDER_FIRST_DISCOUNT;
				$hdtype=1;
			 }
		 }
		 /****END 收单优惠*****/
		 
		 /****End 处理支付方式****/
			//处理订单
			$order_id=$this->order->insert(array(
				"orderno"=>"o_".$user['userid'].time(),
				"dateline"=>time(),
				"ispay"=>$ispay,
				"userid"=>$user['userid'],
				"type_id"=>$this->order_type,
				"send_id"=>post('send_id','i'),
				"comment"=>post('comment','h'),
				"money"=>$total_money,
				"express_money"=>$express_money,
				"goods_money"=>$goods_money,
				"discount_money"=>$discount_money,
				"user_address_id"=>$user_address_id,
				"object_id"=>0,
				"unpay"=>$unpay,
				"paytype"=>$pay_type,
				"shopid"=>$k,
				"coupon_id"=>$coupon_id,
				"coupon_money"=>$coupon_money, 
				"hdtype"=>$hdtype
			));
			//处理订单的产品
			if($order_id){
				foreach($cart_list as $k=>$v){
					$this->order_product->insert(array(
						"order_id"=>$order_id,
						"userid"=>$user['userid'],
						"object_id"=>$v['object_id'],
						"type_id"=>$v['type_id'],
						"price"=>$v['price'],
						"amount"=>$v['amount'],
						"dateline"=>time(),
						"ksid"=>$v['ksid']
					));
					//减掉商品库存
					$this->product->changenum("total_num","-".$v['amount'],"id=".$v['object_id']);
				}
			}else{
				$this->goall("订单处理失败",1,0,$back_url);
			}
			
			//处理订单收货地址
			$d_ids=array($addr['province_id'],$addr['city_id'],$addr['town_id']);
			$dist_list=$this->district->dist_list(array("where"=>" id in(".implode(",",$d_ids).") ")); 
			$this->order_address->insert(array(
				"order_id"=>$order_id,
				"userid"=>$user['userid'],
				"truename"=>$addr['truename'],
				"telephone"=>$addr['telephone'],
				"p_c_t"=>$dist_list[$addr['province_id']].$dist_list[$addr['city_id']].$dist_list[$addr['town_id']],
				"address"=>$addr['address'],
				"dateline"=>time()
			));
		 
		//清除购物车
		
		$this->order_cart->delete($this->oc_where." AND id in("._implode($ids).")");
		if($isfinish){
			$this->goall($this->lang['order_success'],0,0,"/index.php?m=order&a=show&order_id=".$order_id);
		}else{
			$this->goall("下单成功",0,0,"/index.php?m=order&a=pay&order_id=".$order_id);
		}
	}
	
	public function onPay(){
		$order_id=get('order_id','i');
		 $this->gourl("/index.php?m=recharge&a=default&table=order&order_id=".$order_id);
	}
	
	public function onMy(){
		$where="   status<99 AND userid=".$this->userid;
		$url=APPINDEX."?m=order&a=my";
		$start=get('per_page','i');
		$limit=20;
		$option=array(
			"where"=>$where,
			"order"=>"order_id DESC",
			"start"=>$start,
			"limit"=>$limit
		);
		$rscount=true;
		$data=$this->order->select($option,$rscount);
		if($data){
			foreach($data as $k=>$v){
				$v['address']=$this->order_address->selectRow(array("where"=>"order_id=".$v['order_id'],"order"=>"id DESC"));
			 
				$v['product']=$this->orderproduct($v['order_id']);
				 
				$data[$k]=$v;
			}
		}
		$pagelist=$this->pagelist($rscount,$limit,$url);
		$order_status_list=$this->config_item('order_status_list');
		$order_type_list=$this->config_item('order_type_list');
		$order_ispay=$this->config_item('order_ispay');
		$this->smarty->assign(array(
			"data"=>$data,
			"rscount"=>$rscount,
			"pagelist"=>$pagelist,
			"order_status_list"=>$order_status_list,
			"order_type_list"=>$order_type_list,
			"order_ispay"=>$order_ispay,
			
		));
		$this->smarty->display("order/my.html");
	}
	

	
	public function onshow(){
		 
		$order_id=get('order_id','i');
		$data=$this->order->selectRow(array("where"=>"order_id=".$order_id));
		if(empty($data)) $this->goall("参数出错",1);
		if($data['userid']!=$this->login->userid){
			$this->goall("该订单不是您的",1);
		}
		
		$addr=$this->order_address->selectRow(array("where"=>"order_id=".$order_id,"order"=>"id DESC"));
		 
		$order_status_list=$this->config_item('order_status_list');
		
		$order_type_list=$this->config_item('order_type_list');
		$order_ispay=$this->config_item('order_ispay');
		//获取商品
		 
		$order_product=$this->order_product->select(array("where"=>"order_id=".$data['order_id']));
		foreach($order_product as $k=>$v){
			$p=$this->product->selectRow(array("where"=>"id=".$v['object_id']));
			$p['order_price']=$v['price'];
			$p['iscomment']=$v['iscomment'];
			
			$p['rating_grade']=$v['rating_grade'];
			$p['amount']=$v['amount'];
			$ks=M('product_ks')->selectRow("id=".$v['ksid']);
			if($ks){
				$p['ks_title']=$ks['title'];
				$p['price']=$ks['price'];
			}
			$order_product[$k]=$p;
		}
		$this->smarty->assign(array(
			"data"=>$data,
			"addr"=>$addr,
			"order_status_list"=>$order_status_list,
			"order_type_list"=>$order_type_list,
			"order_ispay"=>$order_ispay,
			"order_product"=>$order_product,
		));
		$this->smarty->display("order/show.html");
	}
	
	public function orderproduct($order_id){
		
		$order_product=$this->order_product->select(array("where"=>"order_id=".intval($order_id)));
		foreach($order_product as $k=>$v){
			$p=$this->product->selectRow(array("where"=>"id=".$v['object_id']));
			$p['order_price']=$v['price'];
			$p['amount']=$v['amount'];
			$order_product[$k]=$p;
		}
		return $order_product;	
	}
	
	public function onReceive(){
		$order_id=get('order_id','i');
		$data=$this->order->selectRow(array("where"=>" order_id=".$order_id." AND userid=".$this->userid." "));
		if(empty($data) or $data['status']<2){
			$this->goall($this->lang['data_no_exists'],1,0,"/index.php");
		}
		$this->order->update(array("isreceived"=>2)," order_id=".$order_id." ");
		$this->goall($this->lang['save_success'],0,$data);
	}
	
	public function onproduct_comment(){
		$order_id=get_post('order_id','i');
		$id=get_post('id','id');
		$order=$this->order->selectRow("order_id=".$order_id);
		if($order['userid']!=$this->userid or $order['status']!=3 ){
			exit ("订单信息出错");
		}
		$row=$this->order_product->selectRow(" order_id=".$order_id." AND object_id=".$id." ");
		if(empty($row) or $row['iscomment'] ){
			exit ("产品出错");
		}
		$this->smarty->display("order/product_comment.html");
	}
	
	public function onproduct_comment_save(){
		$order_id=get_post('order_id','i');
		$id=get_post('id','id');
		$order=$this->order->selectRow("order_id=".$order_id);
		if($order['userid']!=$this->userid or $order['status']!=3 ){
			$this->goall("订单信息出错",1);
		}
		$row=$this->order_product->selectRow(" order_id=".$order_id." AND object_id=".$id." ");
		if(empty($row) or $row['iscomment']){
			$this->goall("产品出错",1);
		}
		$this->loadModel(array("rating"));
		$data=array(
			"grade"=>post('grade','i'),
			"userid"=>$this->userid,
			"object_id"=>$id,
			"type_id"=>1,
			"dateline"=>time(),
			"status"=>0,
			"model_id"=>MODEL_PRODUCT_ID,
			"content"=>post('content','h'),
			"order_id"=>$order_id
		);
		$this->rating->insert($data);
		$this->order_product->update(array("iscomment"=>1,"rating_grade"=>post('grade','i'))," order_id=".$order_id." AND object_id=".$id." ");
		$pro=$this->product->selectRow("id=".$id);
		$rating_grade=$this->rating->selectOne(array("where"=>" model_id=".MODEL_PRODUCT_ID." AND type_id=1 AND object_id=".$id." ","fields"=>" AVG(grade) as g"));
		$this->product->update(array("rating_grade"=>$rating_grade,"rating_num"=>$pro['rating_num']+1)," id=".$id);
		$this->goall("评论成功");
	}
	
	
}
?>