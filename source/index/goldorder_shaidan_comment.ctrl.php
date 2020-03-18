<?php
class goldorder_shaidan_commentControl extends skymvc{
	
	public function __construct(){
		parent::__construct();
		$this->loadModel(array("goldgoods","goldorder","goldorder_shaidan","goldorder_shaidan_comment"));
		$this->loadModel(array("login","user"));
		$this->userid=$this->login->userid;
		if(!in_array(get('a'),array('default','list','show'))){
			$this->login->checklogin();
		}
	 
	}
	
	public function onDefault(){
		
		
	}
	
	public function onList(){
		
	}
	
	public function onShow(){
		
	}
	
	public function onMy(){
		$start=get('start','i');
		$limit=10;
		$url="/index.php?m=goldorder_shaidan_comment&a=my";
		$where=" status<99 AND userid=".$this->userid." ";
		$option=array(
			"start"=>$start,
			"limit"=>$limit,
			"where"=>$where,
			"order"=>" id DESC"
		);
		$rscount=true;
		$data=$this->goldorder_shaidan_comment->select($option,$rscount);
		$pagelist=$this->pagelist($rscount,$limit,$url);
		$this->smarty->assign(array(
			"data"=>$data,
			"pagelist"=>$pagelist,
			"rscount"=>$rscount,
		));
		$this->smarty->display("goldorder_shaidan_comment/my.html");
	}
	
	public function onInsert(){
		$this->login->checkLogin(1);
		$data['userid']=$this->userid;
		$data['f_userid']=get_post('f_userid','i');
		$data['f_id']=get_post('f_id','i');
		
		$data['dateline']=time();
		$object_id=$data['object_id']=post('object_id','i');
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
		$row=$this->goldorder_shaidan->selectRow("id=".$object_id);
		if(empty($row)){
			exit(json_encode(array("error"=>1,"message"=>"评论主题不存在")));
		}
		$id=$this->goldorder_shaidan_comment->insert($data);
		if(!$id){
			exit(json_encode(array("error"=>2,"message"=>$this->lang['comment_error_2'])));  
		}
		$data['id']=$id;	
		 		
		//发送通知
		$this->loadModel(array("notice"));
		$data['nickname']=$this->userid?$_SESSION['ssuser']['nickname']:$this->lang['visitor'];
		if($data['userid']!=$data['f_userid'] && $data['f_userid']){
			$ndata['userid']=$data['f_userid'];
			$ndata['status']=1;
			$ndata['type_id']=2;
			$ndata['dateline']=time();
			$ndata['id']=$this->notice->insert($ndata);
		}
		//更新主题评论数
		 
		
		$this->goldorder_shaidan->update(
			array(
				"comment_num"=>$row['comment_num']+1 
				 
			),"id=".$object_id
		);
		
		$data['content']=nRemoveXSS(stripslashes($_POST['content']));
		exit(json_encode(array("error"=>0,"message"=>$data)));
	}
	
	public function onSave(){
		
	}
	
	public function onDelete(){
		$id=get_post('id','i');
		$row=$this->goldorder_shaidan_comment->selectRow("id=".$id);
		if(empty($row)) exit(json_encode(array("error"=>0)));
			if($row['userid']!=$this->login->userid  ){
			 
					exit(json_encode(array("error"=>1,"message"=>"你没有权限")));
				 
			}
		$this->goldorder_shaidan_comment->update(array("status"=>99),"id=".$id);
		exit(json_encode(array("error"=>0)));
	}
	
	 
}

?>