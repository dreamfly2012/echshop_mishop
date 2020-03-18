<?php
class goldorderControl extends skymvc{
	
	public function __construct(){
		parent::__construct();
		$this->loadModel(array("user","login","product","goldorder","goldgoods","goldgoods_attr","attribute","goldorder_address","gold_log","goldorder_log","notice","user_address","district"));
	}
	
	public function ondefault(){
		
	}
		public function onConfirm(){
		$id=get('id','i');
		$user=$this->login->getUser();
		$data=$this->goldgoods->selectRow(array("where"=>"id=$id"));
		if(empty($data)){
			$this->goall("该商品已经兑换完了",1);
		}
		if($data['total_num']<=$data['buy_num']){
			$this->goall("该商品已经兑换完了",1);
		}
		if($data['starttime']>time() or $data['endtime']<time()){
			$this->goall("该商品兑换活动已经结束",1);	
		}
		
		

		//判断是否已经兑换过了
		if($this->goldorder->selectRow(array("where"=>"userid=".$user['userid']."  AND object_id=".$id." ")) && $data['maxbuy'] ){
			 $this->goall("你已经兑换过了，不能再兑换了",1);
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
		$attr=stripslashes(get_post('attr'));
		$t_a=$this->goldgoods_attr->id_list(array("where"=>" id=".$id));
		 
		$attr_content=isset($t_a[$id])?$t_a[$id]['attr_content']:"null";
		$data['gold']=max($data['gold'],$this->attribute->getAttrPrice($attr,$attr_content)); 
		$data['attr']=$this->attribute->strAttr($attr,$attr_content);
		$data['imgurl']=images_site($data['imgurl']);
	 	if($data['gold']>$user['gold']){
			$this->goall("您的金币不足，兑换不成功！",1);
		}
		if(get('ajax')){
			echo sky_json_encode($data);
		}else{
			$this->smarty->assign(array(
				"data"=>$data,
				 
				"address"=>$address,
				"dist_list"=>$dist_list,
				"back_url"=>$_SERVER['HTTP_REFERER'],
			));
			$this->smarty->display("goldorder/confirm.html");
		}
	}
	
	public function onCheck(){
		$id=get('id','i');
		$user=$this->login->getUser();
		$data=$this->goldgoods->selectRow(array("where"=>"id=$id"));
		if(empty($data)){
			exit(json_encode(array("error"=>1,"message"=>"该商品已经兑换完了")));
		}
		if($data['total_num']<=$data['buy_num']){
			exit(json_encode(array("error"=>1,"message"=>"该商品已经兑换完了")));
		}
		
		if($data['starttime']>time() or $data['endtime']<time()){
			exit(json_encode(array("error"=>1,"message"=>"该商品兑换活动已经结束")));	
		}
		if($data['gold']>$user['gold']){
			exit(json_encode(array("error"=>1,"message"=>"您的金币不足，兑换不成功！")));
		}
		$attr=json_encode(get_post('attr'));
		//判断是否已经兑换过了
		if($this->goldorder->selectRow(array("where"=>"userid=".$user['userid']."  AND object_id=".$id." ")) && $data['maxbuy']){
			 exit(json_encode(array("error"=>1,"message"=>"你已经兑换过了，不能再兑换了")));
		}
		exit(json_encode(array("error"=>0,"message"=>"正在跳转中","url"=>"/index.php?m=goldorder&a=confirm&attr=".$attr."&id=".$id)));
	}
	
	public function onOrder(){
		$id=get_post('id','i');
		$back_url=post('back_url','h');
		$user=$this->login->getUser();
		$data=$this->goldgoods->selectRow(array("where"=>"id=$id"));
		if(empty($data)){
			$this->goall("该商品已经兑换完了",1,0,$back_url);
		}
		if($data['total_num']<=$data['buy_num']){
			$this->goall("该商品已经兑换完了",1,0,$back_url);
		}
		if($data['starttime']>time() or $data['endtime']<time()){
			$this->goall("该商品兑换活动已经结束",1,0,$back_url);	
		}
		if($data['gold']>$user['gold']){
			$this->goall("您的金币不足，兑换不成功！",1,0,$back_url);
		}
		//判断是否已经兑换过了
		if($this->goldorder->selectRow(array("where"=>"userid=".$user['userid']."  AND object_id=".$id." ")) && $data['maxbuy']){
			 $this->goall("你已经兑换过了，不能再兑换了",1,0,$back_ur);
		}
		$user_address_id=post('user_address_id','i');
		$addr=$this->user_address->selectRow(array("where"=>"id=".$user_address_id));
		if(empty($addr)){
			$this->goall("请选择收货地址",1,0,$back_url);
		}
		$attr=stripslashes(stripslashes(get_post('attr')));
		$t_a=$this->goldgoods_attr->id_list(array("where"=>" id=".$id));
		 
		$attr_content=isset($t_a[$id])?$t_a[$id]['attr_content']:"null";
		$data['gold']=max($data['gold'],$this->attribute->getAttrPrice($attr,$attr_content)); 
	 	if($data['gold']>$user['gold']){
			$this->goall("您的金币不足，兑换不成功！",1);
		}
		//处理订单
		$order_id=$this->goldorder->insert(array(
			"orderno"=>"e".$user['userid'].time(),
			"dateline"=>time(),
			"ispay"=>2,
			"userid"=>$user['userid'],
			"type_id"=>3,
			"send_id"=>post('send_id','i'),
			"comment"=>post('comment','h'),
			"money"=>$data['gold'],
			"user_address_id"=>$user_address_id,
			"object_id"=>$id,
			"attr"=>$attr
		));
		//处理订单的产品
		 
		
		//处理订单收货地址
		$d_ids=array($addr['province_id'],$addr['city_id'],$addr['town_id']);
		$dist_list=$this->district->dist_list(array("where"=>" id in(".implode(",",$d_ids).") ")); 
		$this->goldorder_address->insert(array(
			"order_id"=>$order_id,
			"userid"=>$user['userid'],
			"truename"=>$addr['truename'],
			"telephone"=>$addr['telephone'],
			"p_c_t"=>$dist_list[$addr['province_id']].$dist_list[$addr['city_id']].$dist_list[$addr['town_id']],
			"address"=>$addr['address'],
			"dateline"=>time()
		));
		
		//添加产品购买数
		$this->goldgoods->changenum("buy_num",1,"id=$id");
		//添加金币消费记录
		$this->loadControl("jfapi");
		$this->jfapiControl->addGold(array(
				"gold"=>"-".$data['gold'],
				"type_id"=>1,
				"ispay"=>1,
				"content"=>"您使用金币兑换了".$data['title'].",消耗了".$data['gold']."个金币，之前有[oldgold]个，目前还剩[newgold]",
			));
		$this->loadModel("goldgoods_user");
		$this->goldgoods_user->changenum("buy_num",1,"userid=".$this->login->userid);			
		$this->goall("恭喜你，兑换成功",0,0,"/index.php?m=goldorder&a=show&order_id=".$order_id);
		
	}
	public function onNeworder(){
		$object_id=get('object_id','i');
		$limit=get('limit','i')?get('limit','i'):10;
		$option=array(
			"where"=>" object_id=".$object_id,
			"order"=>"order_id DESC",
			"limit"=>$limit
		);
		$data=$this->goldorder->select($option);
		if($data){
			foreach($data as $v){
				$uids[]=$v['userid'];			
			}
			$us=$this->user->getUserByIds($uids);
			foreach($data as $k=>$v){
				$v['nickname']=$us[$v['userid']]['nickname'];
				$v['user_head']=$us[$v['userid']]['user_head'];
				$data[$k]=$v;
			}
		}
		$this->smarty->assign(array(
			"data"=>$data
		));
		$this->smarty->display("goldorder/neworder.html");
	}
	
	public function onMy(){
		
		$where=" status<99 AND userid=".$this->login->userid;
		$url=APPINDEX."?m=goldorder";
		$start=get('per_page','i');
		$limit=20;

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
		$this->smarty->display("goldorder/my.html");
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
		$attr_content=$this->goldgoods_attr->selectOne(array("where"=>"id=".$data['object_id'],"fields"=>"attr_content"));
		$order_product['attr']=$this->attribute->strAttr($data['attr'],$attr_content); 
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
	
	public function onHome(){
		$userid=get('userid','i');
		$user=$this->user->selectRow("userid=".$userid);
		if(empty($user)) $this->goall("用户不存在",1,0,"/index.php");
		$where=" status<99 AND userid=".$userid;
		$url=APPINDEX."?m=goldorder&a=home&userid=".$userid;
		$start=get('per_page','i');
		$limit=20;

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
				$ids[]=$v['object_id'];
			}
			$d=$this->goldgoods->id_list(array("where"=>" id in("._implode($ids).")"));
			foreach($data as $k=>$v){
				$v['title']=$d[$v['object_id']]['title'];
				$v['market_price']=$d[$v['object_id']]['market_price'];
				$v['gold']=$d[$v['object_id']]['gold'];
				$v['imgurl']=$d[$v['object_id']]['imgurl'];
				$v['total_num']=$d[$v['object_id']]['total_num'];
				$v['id']=$d[$v['object_id']]['id'];
				$data[$k]=$v;
			}
		}
		$pagelist=$this->pagelist($rscount,$limit,$url);
		 
		$this->smarty->assign(array(
			"data"=>$data,
			"rscount"=>$rscount,
			"pagelist"=>$pagelist,
		 	"user"=>$user
			
		));
		$this->smarty->display("goldorder/home.html");
	}
	
	public function onReceive(){
		$order_id=get('order_id','i');
		$data=$this->goldorder->selectRow(array("where"=>" order_id=".$order_id." AND userid=".$this->login->userid." "));
		if(empty($data) or $data['status']<2){
			$this->goall($this->lang['data_no_exists'],1,0,"/index.php");
		}
		$this->goldorder->update(array("isreceived"=>2)," order_id=".$order_id." ");
		$this->goall($this->lang['save_success'],0,$data);
	}
	
}

?>