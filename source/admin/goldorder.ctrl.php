<?php
class goldorderControl extends skymvc{
	private $admin;
	public function __construct(){
		parent::__construct();
		$this->loadModel(array("user","login","product","goldorder","goldgoods","goldorder_address","gold_log","goldorder_log","notice"));
		$this->loadConfig("table");
		$this->admin=$this->login->getAdmin();
 
	}
	
	public function onDefault(){
		$where=" status<99 ";
		$url=APPADMIN."?m=goldorder";
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
		$data=$this->goldorder->select($option,$rscount);
		if($data){
			foreach($data as $k=>$v){
				$v['address']=$this->goldorder_address->selectRow(array("where"=>"order_id=".$v['order_id'],"order"=>"id DESC"));
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
		$this->smarty->display("goldorder/index.html");
	}
	/*
	*订单详情
	*/
	public function onShow(){
		$order_id=get('order_id','i');
		$data=$this->goldorder->selectRow(array("where"=>"order_id=".$order_id));
		if(empty($data)) $this->goall("参数出错",1);
		$addr=$this->goldorder_address->selectRow(array("where"=>"order_id=".$order_id,"order"=>"id DESC"));
		$order_status_list=$this->config_item('order_status_list');
		$order_type_list=$this->config_item('order_type_list');
		$order_ispay=$this->config_item('order_ispay');
		//获取商品
		$order_product=$this->goldgoods->selectRow(array("where"=>" id=".$data['object_id']));
		 
		$this->smarty->assign(array(
			"data"=>$data,
			"addr"=>$addr,
			"order_status_list"=>$order_status_list,
			"order_type_list"=>$order_type_list,
			"order_ispay"=>$order_ispay,
			"goods"=>$order_product,
			"admin"=>$this->admin
		));
		$this->smarty->display("goldorder/show.html");
		
	}
	/**
	*订单确认
	*/
	public function onConfirm(){
		$order_id=get_post('order_id','i');
		$data=$this->goldorder->selectRow(array("where"=>"order_id=".$order_id));
		if(empty($data)) $this->goall("参数出错",1);
		$this->goldorder->update(array("status"=>1),"order_id=".$order_id);
		$content=post('content');
		$this->goldorder_log->insert(array(
			"dateline"=>time(),
			"admin_id"=>$this->admin['id'],
			"order_id"=>$order_id,
			"content"=>$content,
		));
		$this->goall("确认成功");
	}
	/**
	*订单发送
	*/
	public function onSend(){
		$order_id=get_post('order_id','i');
		$data=$this->goldorder->selectRow(array("where"=>"order_id=".$order_id));
		if(empty($data)) $this->goall("参数出错",1);
		if($data['status']<2){
			$this->goldorder->update(array("status"=>2),"order_id=".$order_id);
		}
		$content=post('content');
		$shipping_info=post('shipping_info','h');
		$this->goldorder_log->insert(array(
			"dateline"=>time(),
			"admin_id"=>$this->admin['id'],
			"order_id"=>$order_id,
			"content"=>$content."快递信息：".$shipping_info,
		));
		$this->goldorder->update(array("shipping_info"=>$shipping_info),"order_id=".$order_id);
		//发送通知
		$msg=array(
			"dateline"=>time(),
			"type_id"=>1,
			"status"=>0,
			"userid"=>$data['userid'],
			"content"=>"您的兑换已发货，".$content."快递信息：".$shipping_info
		);
		$msg['id']=$this->notice->insert($msg);
		$this->goall("发货成功");
	}

	/**
	*订单完成
	*/
	public function onFinish(){
		$order_id=get_post('order_id','i');
		$data=$this->goldorder->selectRow(array("where"=>"order_id=".$order_id));
		if(empty($data)) $this->goall("参数出错",1);
		$this->goldorder->update(array("status"=>3),"order_id=".$order_id);
		$content=post('content');
		$this->goldorder_log->insert(array(
			"dateline"=>time(),
			"admin_id"=>$this->admin['id'],
			"order_id"=>$order_id,
			"content"=>$content,
		));
		 
		//添加积分
		$this->loadControl("jfapi","source/index/");
		$this->jfapiControl->setUserId($data['userid'])->addGrade(array(
					"grade"=>$data['money'],
					"type_id"=>2,
					"content"=>"您的订单完成获得了".$data['money']."积分，之前有[oldgrade]分，目前有[newgrade]分",
				));
		$this->goall("订单完成");
	}
	
	/**
	*取消订单
	*/
	 	
	public function onCancel(){
		$order_id=get_post('order_id','i');
		$data=$this->goldorder->selectRow(array("where"=>"order_id=".$order_id));
		if(empty($data)) $this->goall("参数出错",1);
		if($data['status']>=3) $this->goall("操作失败",1);
		$this->goldorder->update(array("status"=>10),"order_id=".$order_id);
		$content=post('content');
		$message=post('message','h');
		$this->goldorder_log->insert(array(
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
		$this->jfapiControl->setUserId($data['userid'])->addGold(array(
					"gold"=>$data['money'],
					"type_id"=>1,
					"ispay"=>2,
					"content"=>"您的订单被取消了，退还给您".$data['lower_price']."个金币，之前有[oldgold]个，目前还剩[newgold]",
		));
		//商品数减1
		$this->goldgoods->changenum("buy_num",-1,"id=".$data['object_id']);
		$this->goall("取消成功");
	}

	public function onDelete(){
		$order_id=get_post('order_id','i');
		$data=$this->goldorder->selectRow(array("where"=>"order_id=".$order_id));
		if(empty($data)) $this->goall("参数出错",1);
		if($data['status']!=0 && $data['status']!=10 ) $this->goall("操作失败",1);
		$this->goldorder->update(array("status"=>99),"order_id=".$order_id);
		$content=post('content');
		$message=post('message','h');
		$this->goldorder_log->insert(array(
			"dateline"=>time(),
			"admin_id"=>$this->admin['id'],
			"order_id"=>$order_id,
			"content"=>$content."，原因：".$message,
		));
		$this->goall("订单删除");
	}	
	
}
?>