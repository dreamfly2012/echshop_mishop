<?php
class goldorder_shaidanControl extends skymvc{
	
	public function __construct(){
		parent::__construct();
		$this->loadModel(array(
			"login","goldgoods","goldorder_shaidan","goldorder","user"
		));
	}
	
	public function onDefault(){
		$start=get('per_page','i');
		$limit=20;
		$option=array(
			"where"=>"",
			"start"=>$start,
			"limit"=>$limit,
			"order"=>"id DESC"
		);
		$rscount=true;
		$data=$this->goldorder_shaidan->select($option,$rscount);
		if($data){
			foreach($data as $v){
				$uids[]=$v['userid'];
				$gids[]=$v['object_id'];			
			}
			$us=$this->user->getUserByIds($uids);
			$gs=$this->goldgoods->id_list(array("where"=>" id in("._implode($gids).")"));
			foreach($data as $k=>$v){
				$v['nickname']=$us[$v['userid']]['nickname'];
				$v['user_head']=$us[$v['userid']]['user_head'];
				$v['g_title']=$gs[$v['object_id']]['title'];
				$v['imgurl']=$gs[$v['object_id']]['imgurl'];
				$v['gold']=$gs[$v['object_id']]['gold'];
				$v['market_price']=$gs[$v['object_id']]['market_price'];
				$data[$k]=$v;
			}
		}
		$pagelist=$this->pagelist($rscount,$limit,$url);
		$this->smarty->assign(array(
			"data"=>$data,
			"rscount"=>$rscount,
			"pagelist"=>$pagelist
		));
		$this->smarty->display("goldorder_shaidan/index.html");
	}
	
	public function onList(){
		
	}
	
	public function onShow(){
		$id=get('id','i');
		$data=$this->goldorder_shaidan->selectRow("id=".$id);
			if(empty($data)){
				$this->goall("数据出错",1,0,"/index.php");
			}
		$user=$this->user->selectRow("userid=".$data['userid']);
		$goldgoods=$this->goldgoods->selectRow("id=".$data['object_id']);
		/*******************评论处理**************************/
		$this->loadModel("goldorder_shaidan_comment");
		$limit=20;
		$start=get('per_page','i');
		if(COMMENT_STATUS_CHECK){
			$status_where=" AND status=1";
		}else{
			$status_where=" AND status<10";
		}
		$option=array(
			"where"=>" object_id='$id'  $status_where ",
			"order"=>" id ASC",
			"start"=>$start,
			"limit"=>$limit
		);
		$rscount=true;
		$comment=$this->goldorder_shaidan_comment->select($option,$rscount);
		$temp_uids=array();
		if($comment){
			foreach($comment as $k=>$v){
				$v['userid']  && $temp_uids[]=$v['userid'];
				$v['object_user_id'] && $temp_uids[]=$v['object_user_id'];
			}
				$us=$this->user->getUserByIds($temp_uids);
				foreach($comment as $k=>$v){
					$v['nickname']=$us[$v['userid']]['nickname'];
					$v['user_head']=$us[$v['userid']]['user_head'];					
					$comment_list[$k]=$v;				
				}
			 
			
			 
			 
		}
		
		$pagelist=$this->pagelist($rscount,$limit,APPINDEX."?m=goldorder_shaidan&a=show&id=$id");
		//评论发布处理
		$this->smarty->assign(array(
			"comment_object_id"=>$id,
			"comment_referer"=>APPINDEX."?m=goldorder_shaidan&a=show&id=$id",
			"comment_list"=>$comment_list,
			"rscount"=>$rscount,			
			"comment_f_userid"=>$data['userid'],
		
		));
		/***********END 评论处理*********************/	
		$this->smarty->assign(array(
			"data"=>$data,
			"user"=>$user,
			"goldgoods"=>$goldgoods
		));	
		$this->smarty->display("goldorder_shaidan/show.html");
	}
	
	public function onAdd(){
		$order_id=get('order_id','i');
		$id=get('id','i');
		if($id){
			$data=$this->goldorder_shaidan->selectRow("id=".$id);
			if(empty($data)){
				$this->goall("数据出错",1);
			}
			if($data['userid']!=$this->login->userid){
				$this->goall("您无权限",1);
			}
			$order_id=$data['order_id'];
			$order=$this->goldorder->selectRow("order_id=".$order_id);	
		}else{
			$order=$this->goldorder->selectRow("order_id=".$order_id);			
			if(empty($order)){
				$this->goall("数据出错",1);
			}
			if($order['userid']!=$this->login->userid){
				$this->goall("您无权限",1);
			}
			
			$data=$this->goldorder_shaidan->selectRow("order_id=".$order_id);
		}
		$goldgoods=$this->goldgoods->selectRow("id=".$order['object_id']);
		 
		$this->smarty->assign(array(
			"data"=>$data,
			"order"=>$order,
			"goldgoods"=>$goldgoods,
		 
		));
		$this->smarty->display("goldorder_shaidan/add.html");
	}
	
	public function onSave(){
		$id=get_post("id","i");
		$order_id=get_post('order_id','i');		
		$data["title"]=get_post("title","h");
		$data["description"]=get_post("description","h");
		$data["content"]=get_post("content","x");		
		$data["imgurl"]=get_post("imgurl","h");
		if($id){
			$row=$this->goldorder_shaidan->selectRow("id=".$id);
			if(empty($row)){
				$this->goall("数据出错",1);
			}
			if($row['userid']!=$this->login->userid){
				$this->goall("您无权限",1);
			}
			$this->goldorder->update(array("isshaidan"=>1),"order_id=".$row['order_id']);
			$this->goldorder_shaidan->update($data,array('id'=>$id));
		}else{
			$order=$this->goldorder->selectRow("order_id=".$order_id);			
			if(empty($order)){
				$this->goall("数据出错",1);
			}
			if($order['userid']!=$this->login->userid){
				$this->goall("您无权限",1);
			}
			$data["dateline"]=time();
			$data["order_id"]=$order_id;
			$data["object_id"]=$order['object_id'];
			$data["userid"]=$this->login->userid;
			$this->goldorder_shaidan->insert($data);
			$this->goldorder->update(array("isshaidan"=>1),"order_id=".$order_id);
			$this->loadModel("goldgoods_user");
			$this->goldgoods_user->changenum("shaidan_num",1,"userid=".$this->login->userid);
		}
		$this->goall($this->lang["save_success"]);
	}
	
	public function onMy(){
		$url="/index.php?m=goldorder_shaidan&a=my";
		$start=get('per_page','i');
		$limit=20;
		$option=array(
			"where"=>" userid=".$this->login->userid,
			"order"=>"id DESC",
			"start"=>$start,
			"limit"=>$limit
		);
		$rscount=true;
		$data=$this->goldorder_shaidan->select($option,$rscount);
		if($data){
			foreach($data as $v){
				$qgids[]=$v['object_id'];
				 
			}
			$qgs=$this->goldgoods->id_list(array("where"=>" id in("._implode($qgids).")"));
			 
			foreach($data as $k=>$v){
				$v['title']=$qgs[$v['object_id']]['title'];
				$v['price']=$qgs[$v['object_id']]['price'];
				 
				$data[$k]=$v;
			}
		}
		$pagelist=$this->pagelist($rscount,$limit,$url);
		$this->smarty->assign(array(
			"data"=>$data,
			"rscount"=>$rscount,
			"pagelist"=>$pagelist
		));
		$this->smarty->display("goldorder_shaidan/my.html");
	}
	
	
	public function onHome(){
		
		$userid=get('userid','i');
		$user=$this->user->selectRow("userid=".$userid);
		if(empty($user)) $this->goall($this->lang['user_no_exists'],1,0,"/index.php");
		$url="/index.php?m=goldorder_shaidan&a=home&userid=".$userid;
		$start=get('per_page','i');
		$limit=20;
		$option=array(
			"where"=>" userid=".$userid,
			"order"=>"id DESC",
			"start"=>$start,
			"limit"=>$limit
		);
		$rscount=true;
		$data=$this->goldorder_shaidan->select($option,$rscount);
		if($data){
			foreach($data as $v){
				$qgids[]=$v['object_id'];
				 
			}
			$qgs=$this->goldgoods->id_list(array("where"=>" id in("._implode($qgids).")"));
			 
			foreach($data as $k=>$v){
				$v['title']=$qgs[$v['object_id']]['title'];
				$v['price']=$qgs[$v['object_id']]['price'];
				$v['market_price']=$qgs[$v['object_id']]['market_price']; 
				$v['gold']=$qgs[$v['object_id']]['gold'];
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
		$this->smarty->display("goldorder_shaidan/home.html");
	}
	
}
?>