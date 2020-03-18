<?php
class commentControl extends skymvc{
	public $userid;
	function __construct(){
		parent::__construct();
		$this->loadModel(array("login","user","category","comment","notice","notice_all","model"));
		$this->userid=$this->login->userid;
	}
	
	public function onDefault(){
		$tablename=get('tablename','h');
		$object_id=get('object_id','i');
	   $rscount=M('comment')->getCount(array("tablename"=>$tablename,object_id=>$object_id));
		$this->smarty->assign(array(
			"comment_tablename"=>$tablename,
			"comment_object_id"=>$object_id,
			"comment_num"=>$rscount
		));
		$this->smarty->display("comment/index.html"); 
	}
	
	public function onList(){
		
		$rscount=true;
		$limit=20;
		$page=get('page','i');
		$start=get('per_page','i');
		$tablename=get_post('tablename','h');
		$where=" status < 99 AND tablename ='".$tablename."'";
		$url="/index.php?m=comment&a=list&tablename=".urlencode($tablename);
		$object_id=get('object_id','i');
		if($object_id){
			$where.=" AND object_id=".$object_id;
		}
		$option=array(
			"where"=>$where,
			"start"=>$start,
			"limit"=>$limit,
			"order"=>"id ASC"
		);
		$data=$this->comment->select($option,$rscount);
		if($data){
			foreach($data as $k=>$v){
				$u=$this->user->selectRow(array("where"=>array("userid"=>$v['userid'])));
				$v['nickname']=$u['nickname'];
				$v['user_head']=$u['user_head'];
				$v['author']=$u;
				$data[$k]=$v;
			}
		}
		$pagelist=$this->pagelist($rscount,$limit,$url);
		$this->smarty->assign(array(
			"data"=>$data,
			"rscount"=>$rscount,
			"pagelist"=>$pagelist
		));
		$this->smarty->display("comment/list.html");
	}
	
	public function onjList(){
		
		$rscount=true;
		$limit=20;
		$page=get('page','i');
		$start=max(0,($page-1)*$limit);
		$tablename=get_post('tablename','h');
		$where=" status < 99 AND tablename ='".$tablename."'";
		if($object_id){
			$where.=" AND object_id=".$object_id;
		}
		$option=array(
			"where"=>$where,
			"start"=>$start,
			"limit"=>$limit,
			"order"=>"id ASC"
		);
		$data=$this->comment->select($option,$rscount);
		if($data){
			foreach($data as $k=>$v){
				$u=$this->user->selectRow(array("where"=>array("userid"=>$v['userid'])));
				$v['nickname']=$u['nickname'];
				$v['user_head']=$u['user_head'];
				$v['author']=$u;
				$data[$k]=$v;
			}
		}
		echo json_encode($data);
	}
	
	/*我的评论*/
	public function onMy(){
		$this->login->checklogin(1);
		$pagesize=20;
		$start=get('per_page','i');
		$rscount=true;
		$userid=$this->userid;
		$where['userid']=$userid;
		$option=array(
			"where"=>$where,
			"start"=>$start,
			"limit"=>$pagesize,
			"order"=>"id DESC"
		);
		$data=$this->comment->select($option,$rscount);
		$pagelist=$this->pagelist($rscount,$pagesize,"/index.php?m=comment&a=my");
		$this->smarty->assign(
			array(
				"comment_list"=>$data,
				"rscount"=>$rscount,
				"pagelist"=>$pagelist,
			)
		);
		
		$this->smarty->display("comment/my.html");
		 
	}
	
	/*我的评论*/
	public function onToMe(){
		$this->login->checklogin(1);
		$pagesize=20;
		$start=get('per_page','i');
		$rscount=true;
		$userid=$this->userid;
		$where['f_userid']=$userid;
		$option=array(
			"where"=>$where,
			"start"=>$start,
			"limit"=>$pagesize,
			"order"=>"id DESC"
		);
		$data=$this->comment->select($option,$rscount);
		$pagelist=$this->pagelist($rscount,$pagesize,"/index.php?m=comment&a=tome");
		$this->smarty->assign(
			array(
				"comment_list"=>$data,
				"rscount"=>$rscount,
				"pagelist"=>$pagelist,
			)
		);
		
		$this->smarty->display("comment/tome.html");
		 
	}
	
	 
	/**
	*评论添加组件
	*/
	public function oninsert(){
		 
		$this->login->checkLogin(1);
		$this->checkBadWord();
		$data['object_id']=get_post('object_id','i');
		$data['tablename']=post('tablename','h');
		if(empty($data['tablename'])) exit(json_encode(array("error"=>1,"message"=>"参数错误")));
		/*即将删除*/
		$data['type_id']=max(1,get_post('type_id','i'));
		$data['model_id']=get_post('model_id','i');
		$data['table_id']=get_post('table_id')?get_post('table_id'):$data['object_id'];
		/*即将删除*/
		$data['userid']=$this->userid;
		if(post("shopid")){
			$data['shopid']=post("shopid","i");
		}
		$data['f_userid']=get_post('f_userid','i');
		$data['f_id']=get_post('f_id','i');
		
		$data['dateline']=time();
		
		
		$data['title']=post('title','h');
		$data['content']=get_post('content','x');
		$data['status']=1;
		//评论跳转的地址
		$data['pid']=post('pid','i');
		$data['ip']=$_SERVER['REMOTE_ADDR'];
		$ipcity=ipcity($_SERVER['REMOTE_ADDR']);
		if($ipcity){
			$data['ip_city']=$ipcity['region'].$ipcity['city'].$ipcity['county'];
		}else{
			$data['ip_city']="外星球";
		}
		$referer=post('referer','h');
		$data['referer']=$referer;
		if(strlen(get_post('content','h'))<2){
			exit(json_encode(array("error"=>1,"message"=>$this->lang['comment_error_1'])));
		}
		$id=$this->comment->insert($data);
		if(!$id){
			exit(json_encode(array("error"=>2,"message"=>$this->lang['comment_error_2'])));  
		}
		$data['id']=$id;	
		 		
		//发送通知
		$data['nickname']=$this->userid?$_SESSION['ssuser']['nickname']:$this->lang['visitor'];
		if($data['userid']!=$data['f_userid'] && $data['f_userid']){
			$ndata['userid']=$data['f_userid'];
			$ndata['status']=1;
			$ndata['type_id']=2;
			$ndata['dateline']=time();
			$ndata['id']=$this->notice->insert($ndata);
		}
		//更新主题评论数
		
		$mod=$this->model->selectRow("tablename='".$data['tablename']."'");
		$this->loadModel($data['tablename']);
		$fs=$this->$data['tablename']->getFields();
		$fsid=$fs[0]['Field'];
		if($mod){
			$row=$this->$mod['tablename']->selectRow("$fsid=".$data['object_id']);
			$this->$mod['tablename']->update(
				array(
					"comment_num"=>$row['comment_num']+1,
					"last_time"=>time()
				),"$fsid=".$data['object_id']
			);
			//更新分类评论数
			$this->category->update_comment_num($row['catid'],1);
		}else{
			
			$row=$this->$data['tablename']->selectRow("$fsid=".$data['object_id']);
			
			$this->$data['tablename']->update(
				array(
					"comment_num"=>$row['comment_num']+1
				),"$fsid=".$data['object_id']
			);
		}
		
		//更新用户评论数
		$this->user->changenum("comment_num",1,"userid=".$this->userid);
		$data['content']=nRemoveXSS(stripslashes($_POST['content']));
		
		exit(json_encode(array("error"=>0,"message"=>$data)));
		
	}
	/**
	*评论删除组件
	*/
	public function ondelete(){
		$this->login->checklogin(1);
		$id=get_post('id','i');
		$data=$this->comment->selectRow(array("where"=>array("id"=>$id)));
		 
		if($data['userid']!=$this->login->userid){
			exit(json_encode(array("error"=>1,"message"=>$this->lang['die_access'])));
		}
		$this->comment->delete(array("id"=>$id));
		exit(json_encode(array("error"=>0,"message"=>$this->lang['comment_delete_success'])));
	}
	 
	
}

?>