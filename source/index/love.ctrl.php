<?php
class loveControl extends skymvc
{
	public $userInfo;
	public function __construct(){
		parent::__construct();
		$this->loadModel(array("love","love_all","loved","login","model"));
		$this->login->checklogin();
		$this->userInfo=$this->login->getUser();
	}
	
	public function onDefault(){
		
	}
	
	public function onLove_btn(){
		$id=get('id','i');
		$tablename=get('tablename','h');
		$t_userid=get('t_userid','i');
		$this->loadModel(array("love"));
		if(!$this->login->userid){
			 $islove=0;
		}else{
			
			$islove=$this->love->selectRow(" object_id=".$id." AND tablename='".$tablename."'  ");
			
		}
		$this->smarty->assign(array(
				"islove"=>$islove,
				"addurl"=>"/index.php?m=love&a=add&ajax=1&object_id=".$id."&t_userid=".$t_userid."&tablename=".$tablename."&type_id=1",
				"removeurl"=>"/index.php?m=love&a=delete&ajax=1&object_id=".$id."&t_userid=".$t_userid."&tablename=".$tablename."&type_id=1",
			));
		$data=$this->smarty->fetch("love/jsbtn.html");
		echo strtojs($data);
		exit;
	}
	
	/**
	*添加喜欢组件 
	*<a href="{$appindex}?m=love&a=add&object_id={$data.id}&type_id=1&model_id={const.ARTICLE_MODEL_ID}">喜欢</a>
	*/
	public function onadd(){
		$where['tablename']=$data['tablename']=get_post('tablename','h');
		$where['object_id']=$data["object_id"]=get_post("object_id","i");
		$where['userid']=$data["userid"]=$this->userInfo['userid'];
		$data["t_userid"]=get_post("t_userid","i");
		$data["dateline"]=time();
		if($row=$this->love->selectRow(array("where"=>$where))){
			exit(json_encode(array("error"=>1,"message"=>$this->lang['love_is_existed'])));
		}else{
			$this->love->insert($data);
			 
			M($data['tablename'])->changenum("love_num",1,"id=".$data['object_id']);
				 
			exit(json_encode(array("error"=>0,"message"=>"success")));
		}
	}
	/**
	*检测是否喜欢组件
	*/
	public function onCheck(){
		$where['object_id']=get('object_id','i');
		$where['userid']=$this->userInfo['userid'];
		$where['tablename']=$data['tablename']=get_post('tablename','h');
		$data=$this->love->selectRow(array("where"=>$where));
		if(!empty($data)){
			exit(json_encode(array("status"=>1)));
		}else{
			exit(json_encode(array("status"=>0)));
		}
	}
	
	/**
	*删除喜欢组件 
	*/
	public function onDelete(){
		$where['tablename']=$data['tablename']=get_post('tablename','h');
		$where['object_id']=get('object_id','i');
		$where['userid']=$this->login->userid;
		$data=$this->love->selectRow(array("where"=>$where));
		if($data){			
			$this->love->delete($where);
			M($where['tablename'])->changenum("love_num",-1,"id=".$data['object_id']);
			exit(json_encode(array("error"=>0,"message"=>"sucess")));
		}else{
			exit(json_encode(array("error"=>1,"message"=>$this->lang['delete_fail'])));
		}
		
	}
	
	
	public function onmyLove(){
		$userid=$this->userInfo['userid'];
		$tablename=max(1,get('tablename','i'));
		$option=array(
			"where"=>" userid=".$userid." AND tablename='".$tablename."' ",
		);
		$rscount=true;
		$data=$this->love->select($userid,$option,$rscount);
		 
		if($data){
			foreach($data as $k=>$v){
				$a=m("tablename")->selectRow(array("id"=>$v['object_id']));
				$v['title']=$a['title'];
				$data[$k]=$v;
			}
		}
		$this->smarty->assign(array(
			"data"=>$data,
		));
		$this->smarty->display("love/my.html");
		
	}
	
	 
	
}
?>