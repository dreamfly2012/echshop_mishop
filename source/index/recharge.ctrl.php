<?php
class rechargeControl extends skymvc{
	public $userid;
	public function __construct(){
		parent::__construct();
		$this->loadModel(array("recharge","user","login","product","order","order_address","order_product"));
		$this->login->checkLogin();
		$this->userid=$this->login->userid;	
	}
	
	public function onDefault(){
		if(get('pay_type')){
			$this->onRecharge();
			exit();
		}
		$order_id=get('order_id','i');
		if($order_id){
			$table=get_post("table",'h');
			if($table){
				$this->smarty->assign(array(
					"ordertable"=>$table
				));
			}
			 
			switch($table){
				case "order":
						$table_order="order";
						$table_order_address="order_address";
					break;
				default:
						$table_order=$table."_order";
						$table_order_address=$table."_order_address";
					break;
			}
			$user=$this->login->getUser();
			$data=M($table_order)->selectRow(array("where"=>"order_id=".$order_id));
			if(empty($data)) $this->goall("参数出错",1);
			$addr=M($table_order_address)->selectRow(array("where"=>"order_id=".$order_id,"order"=>"id DESC"));
			$order_status_list=$this->config_item('order_status_list');
			$order_type_list=$this->config_item('order_type_list');
			$order_ispay=$this->config_item('order_ispay');
			if(empty($data)) $this->goall("参数出错",1);
			if($data['ispay']==2) $this->goall("该订单已支付",1);
			 
			 
			 
			
		}
		
		$this->smarty->assign(array(
				"data"=>$data,
				"addr"=>$addr,
				"order_status_list"=>$order_status_list,
				"order_type_list"=>$order_type_list,
				"order_ispay"=>$order_ispay,
				"table"=>$table,
				"pay_type_list"=>pay_type_list(0,array("unpay"=>1)) 
			));
		
		if($order_id){
			$this->smarty->display("recharge/order.html");
		}else{
			$this->smarty->display("recharge/index.html");
		}
		
	}
	
	public function onMy(){
		$start=get('per_page','i');
		$limit=20;
		$where=" userid=".$this->userid;
		$url=APPINDEX."?m=recharge&a=my";
		$option=array(
			"where"=>$where,
			"start"=>$start,
			"limit"=>$limit,
			"order"=>" id DESC",
		);
		$rscount=true;
		$data=$this->recharge->select($option,$rscount);
		$pagelist=$this->pagelist($rscount,$limit,$url);
		$this->smarty->assign(array(
			"data"=>$data,
			"rscount"=>$rscount,
			"pagelist"=>$pagelist
		));
		$this->smarty->display("recharge/my.html");
	}
	
	
	 
	
	
	public function onRecharge(){
		$order_id=get_post('order_id','i');
		$order_des="网站充值";
		if($order_id){
			$table=get_post("table",'h');
			$order_des="订单支付";
			switch($table){
				case "order":
						$table_order="order";
						$table_order_address="order_address";
					break;
				default:
						$table_order=$table."_order";
						$table_order_address=$table."_order_address";
					break;
			}
			$data=M($table_order)->selectRow(array("where"=>"order_id=".$order_id));
			if(empty($data)) $this->goall("参数出错",1);
			$data['table']=$table;
			$orderdata=base64_encode(json_encode($data));
			if($data['ispay']==2) $this->goall("该订单已支付",1);
			$backurl="/index.php/{$table_order}/order_id-".$order_id;
		}else{
			$backurl="/index.php/recharge/";
		}
		$pay_type=get_post('pay_type','h');
		$orderno="re".date("YmdHis").$this->login->userid;//根据实际情况一个用户1s不可能重复下订单
		$order_product=post('product_name')?post('product_name'):$order_des;
		$order_price=$data?$data['money']:post('order_price',"r",2);
		$order_info=post('order_info','h');
		$bank_type=post('bank_type');
		/*****插入充值表******/
		$this->recharge->insert(array(
			"userid"=>$this->userid,
			"money"=>$order_price,
			"pay_type"=>$pay_type,
			"orderno"=>$orderno,
			"orderinfo"=>$order_product."<br>".$order_info, 
			"type_id"=>1,
			"tablename"=>$table?$table:"",
			"dateline"=>time(),
			"status"=>2,
			"orderdata"=>$orderdata,
		));
		
		/*插入充值表结束*/
		
		$url="http://".$_SERVER['HTTP_HOST']."/api/".$pay_type."/".$pay_type.".php";
		$url.="?orderno=$orderno";
		$url.="&bank_type=".$bank_type;
		$url.="&order_product=".urlencode($order_product);
		$url.="&order_price=".$order_price;
		$url.="&order_info=".urlencode($order_info);
		$url.="&backurl=".urlencode($backurl);
		header("Location: ".$url);
		exit;
	}
	
}

?>