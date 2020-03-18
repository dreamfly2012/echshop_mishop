<?php
class orderControl extends skymvc{
	private $admin;
	public function __construct(){
		parent::__construct();
		$this->loadModel(array("user","login","product","order","order_address","order_product","gold_log","order_log","notice","product_attr","attribute"));
		$this->loadConfig("table");
		$this->admin=$this->login->getAdmin();
 
	}
	
	public function onDefault(){
		$where=" status<99 ";
		$url=APPADMIN."?m=order";
		$start=get('per_page','i');
		$limit=20;
		
		$orderno=get('orderno','h');
		if($orderno){
			$where.=" AND orderno='".$orderno."' ";
			$url.="&orderno=$orderno";
		}
		
		$nickname=get('ninckname','h');
		if($nickname){
			$user=$this->user->selectRow(array("where"=>"nickname='".$nickname."' "));
			if($user){
				$where.=" userid=".$user['userid'];
			}else{
				$where.=" 1=2 ";			
			}
			$url.="&nickname=".urlencode($nickname);
		}
		 
		$status=get('s_status','i');
		if($status>=0){
			$where.=" AND status=$status";
			$url.="&s_status=$status";
		}
		if(!isset($_GET['s_ispay'])){
			$ispay=-1;
		}else{
			$ispay=get('s_ispay','i');
		}
		if($ispay>=0){
			$where.=" AND ispay=$ispay";
			$url.="&s_ispay=$ispay"; 
		}
		$start_time=get('start_time','h');
		$end_time=get('end_time','h');
		if($start_time){
			$where.=" AND dateline>".strtotime($start_time)." ";
			$url.="&start_time=".$start_time;
		}
		
		if($end_time){
			$where.=" AND dateline<".strtotime($end_time)." ";
			$url.="&end_time=".$end_time;
		}
		$type_id=get_post('type_id','i');
		if($type_id){
			$where.=" AND type_id=".$type_id." ";
			$url.="&type_id=".$type_id;
		}
		
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
		$this->smarty->display("order/index.html");
	}
	/*
	*订单详情
	*/
	public function onShow(){
		$order_id=get('order_id','i');
		$data=$this->order->selectRow(array("where"=>"order_id=".$order_id));
		if(empty($data)) $this->goall("参数出错",1);
		$addr=$this->order_address->selectRow(array("where"=>"order_id=".$order_id,"order"=>"id DESC"));
		$order_status_list=$this->config_item('order_status_list');
		$order_type_list=$this->config_item('order_type_list');
		$order_ispay=$this->config_item('order_ispay');
		//获取商品
		$order_product=$this->order_product->select(array("where"=>"order_id=".$data['order_id']));
		foreach($order_product as $k=>$v){
			$p=$this->product->selectRow(array("where"=>"id=".$v['object_id']));
			$ks=M('product_ks')->selectRow(array("where"=>"id=".$v['ksid']));
			$p['order_price']=$v['price'];
			$p['ks_title']=$ks['title']; 
			$p['amount']=$v['amount'];
			$order_product[$k]=$p;
		}
		$this->smarty->assign(array(
			"data"=>$data,
			"addr"=>$addr,
			"order_status_list"=>$order_status_list,
			"order_type_list"=>$order_type_list,
			"order_ispay"=>$order_ispay,
			"order_product"=>$order_product,
			"admin"=>$this->admin
		));
		$this->smarty->display("order/show.html");
		
	}
	/**
	*订单确认
	*/
	public function onConfirm(){
		$order_id=get_post('order_id','i');
		$data=$this->order->selectRow(array("where"=>"order_id=".$order_id));
		if(empty($data)) $this->goall("参数出错",1);
		$this->order->update(array("status"=>1),"order_id=".$order_id);
		$content=post('content');
		$this->order_log->insert(array(
			"dateline"=>time(),
			"admin_id"=>$this->admin['id'],
			"order_id"=>$order_id,
			"content"=>$content,
		));
		$msg=array(
			"dateline"=>time(),
			"type_id"=>1,
			"status"=>0,
			"userid"=>$data['userid'],
			"content"=>"订单<a href='/index.php?m=order&a=show&order_id=".$data['order_id']."' target='_blank'>".$data['orderno']."</a>已确认",
		);
		$msg['id']=$this->notice->insert($msg);
		$this->goall("确认成功");
	}
	/**
	*订单发送
	*/
	public function onSend(){
		$order_id=get_post('order_id','i');
		$data=$this->order->selectRow(array("where"=>"order_id=".$order_id));
		if(empty($data)) $this->goall("参数出错",1);
		if($data['status']<2){
			$this->order->update(array("status"=>2),"order_id=".$order_id);
		}
		$content=post('content');
		$shipping_info=post('shipping_info','h');
		$this->order_log->insert(array(
			"dateline"=>time(),
			"admin_id"=>$this->admin['id'],
			"order_id"=>$order_id,
			"content"=>$content."快递信息：".$shipping_info,
		));
		$this->order->update(array("shipping_info"=>$shipping_info),"order_id=".$order_id);
		$msg=array(
			"dateline"=>time(),
			"type_id"=>1,
			"status"=>0,
			"userid"=>$data['userid'],
			"content"=>"订单<a href='/index.php?m=order&a=show&order_id=".$data['order_id']."' target='_blank'>".$data['orderno']."</a>已发送",
		);
		$msg['id']=$this->notice->insert($msg);
		$this->goall("发货成功");
	}

	/**
	*订单完成
	*/
	public function onFinish(){
		$order_id=get_post('order_id','i');
		$data=$this->order->selectRow(array("where"=>"order_id=".$order_id));
		if(empty($data)) $this->goall("参数出错",1);
		$this->order->update(array("status"=>3),"order_id=".$order_id);
		$content=post('content');
		$this->order_log->insert(array(
			"dateline"=>time(),
			"admin_id"=>$this->admin['id'],
			"order_id"=>$order_id,
			"content"=>$content,
		));
		//添加产品销售数量
		$order_product=$this->order_product->select(array("where"=>"order_id=".$data['order_id']));
		foreach($order_product as $k=>$v){
			$this->product->changenum("buy_num",$v['amount'],"id=".$v['object_id']);
			if($v['ksid']){
				M('product_ks')->changenum("buy_num",$v['amount'],"id=".$v['ksid']);
			}
			//增加统计数量
			$option=array(
				"k"=>"product",
				"productid"=>$v['object_id'],
				"ordernum"=>$v['amount']
			);
			M("product")->statDayAdd($option);
			M("product")->statMonthAdd($option);
			M("product")->statWeekAdd($option);
			/**统计订单**/
			$option=array(
				"money"=>$data['money']
			);
			M("order")->statDayAdd($option);
			M("order")->statMonthAdd($option);
			M("order")->statWeekAdd($option);
		}
		//添加积分
		$this->loadControl("jfapi","source/index/");
		$this->jfapiControl->setUserId($data['userid'])->addGrade(array(
					"grade"=>$data['money'],
					"type_id"=>2,
					"content"=>"您的订单完成获得了".$data['money']."积分，之前有[oldgrade]分，目前有[newgrade]分",
		));
		$msg=array(
			"dateline"=>time(),
			"type_id"=>1,
			"status"=>0,
			"userid"=>$data['userid'],
			"content"=>"订单<a href='/index.php?m=order&a=show&order_id=".$data['order_id']."' target='_blank'>".$data['orderno']."</a>已完成",
		);
		$msg['id']=$this->notice->insert($msg);
		$this->goall("订单完成");
	}
	
	/**
	*取消订单
	*/
	 	
	public function onCancel(){
		$order_id=get_post('order_id','i');
		$data=$this->order->selectRow(array("where"=>"order_id=".$order_id));
		if(empty($data)) $this->goall("参数出错",1);
		if($data['status']>=3) $this->goall("操作失败",1);
		$this->order->update(array("status"=>10),"order_id=".$order_id);
		$content=post('content');
		$message=post('message','h');
		$this->order_log->insert(array(
			"dateline"=>time(),
			"admin_id"=>$this->admin['id'],
			"order_id"=>$order_id,
			"content"=>$content."，原因：".$message,
		));
		//发送通知
		$msg=array(
			"dateline"=>time(),
			"type_id"=>1,
			"status"=>0,
			"userid"=>$data['userid'],
			"content"=>$message
		);
		$msg['id']=$this->notice->insert($msg);
	 
		//退钱给用户
		$this->loadControl("jfapi","source/index/");
		$user=$this->user->selectRow(array("where"=>"userid=".$data['userid']));
		$this->jfapiControl->setUserId($data['userid'])->addMoney(array(
					"money"=>$data['money'],
					"type_id"=>1,
					"ispay"=>2,
					"content"=>"您的订单被取消了，退还给您￥".$data['lower_price']."，之前有￥[oldmoney]，目前还剩￥[newmoney]",
		));
		$this->goall("取消成功");
	}

	public function onDelete(){
		$order_id=get_post('order_id','i');
		$data=$this->order->selectRow(array("where"=>"order_id=".$order_id));
		if(empty($data)) $this->goall("参数出错",1);
		if($data['status']!=0 && $data['status']!=10 ) $this->goall("操作失败",1);
		$this->order->update(array("status"=>99),"order_id=".$order_id);
		$content=post('content');
		$message=post('message','h');
		$this->order_log->insert(array(
			"dateline"=>time(),
			"admin_id"=>$this->admin['id'],
			"order_id"=>$order_id,
			"content"=>$content."，原因：".$message,
		));
		$this->goall("订单删除");
	}	
	
}
?>