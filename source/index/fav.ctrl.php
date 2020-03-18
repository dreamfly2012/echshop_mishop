<?php
class favControl extends skymvc
{
	public $userInfo;
	public function __construct(){
		parent::__construct();
		$this->loadModel(array("fav","login","model"));
		$this->login->checklogin();
	}
	
	public function onDefault(){
		
	}
	
	public function  onfav_btn(){
		
		$id=get('id','i');
		$tablename=get('tablename','h');
		$t_userid=get('t_userid','i');
		$this->loadModel(array("fav"));
		if(!$this->login->userid){
			$isfav=0;
		}else{
			$isfav=$this->fav->selectRow(" object_id=".$id." AND tablename='".$tablename."'   ");
		}
		$this->smarty->assign(array(
			"isfav"=>$isfav,
			"addurl"=>"/index.php?m=fav&a=add&object_id=".$id."&t_userid=".$t_userid."&tablename=".$tablename."",
			"removeurl"=>"/index.php?m=fav&a=delete&object_id=".$id."&t_userid=".$t_userid."&tablename=".$tablename."",
		));
		$data=$this->smarty->fetch("fav/jsbtn.html");
		echo strtojs($data);
		exit;
	}
	
	/**
	*添加收藏组件 
	*<a href="{$appindex}?m=fav&a=add&object_id={$data.id}&type_id=1&model_id={const.ARTICLE_MODEL_ID}">喜欢</a>
	*/
	public function onadd(){
		
		$where['object_id']=$data["object_id"]=get_post("object_id","i");
		$where['userid']=$data["userid"]=$this->login->userid;
		$data["t_userid"]=get_post("t_userid","i");
		$data["dateline"]=time();
		$where['tablename']=$data["tablename"]=get_post('tablename');
		if($row=$this->fav->selectRow(array("where"=>$where))){
			exit(json_encode(array("error"=>1,"message"=>$this->lang['fav_is_existed'])));
		}else{
			$this->fav->insert($data);
			M($data["tablename"])->changenum("fav_num",1,"id=".$data['object_id']);
			exit(json_encode(array("error"=>0,"message"=>$this->lang['fav_success'])));
		}
	}
	/**
	*检测是否喜欢组件
	*/
	public function onCheck(){
		$where['object_id']=get('object_id','i');
		$where['tablename']=get_post('tablename');
		$where['userid']=$this->login->userid;
		$data=$this->fav->selectRow(array("where"=>$where));
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
		$where['object_id']=get('object_id','i');
		$where['tablename']=get_post('tablename');
		$where['userid']=$this->login->userid;
		$data=$this->fav->selectRow(array("where"=>$where));
		if($data){			
			$this->fav->delete($where);
			M($where['tablename'])->changenum("fav_num",-1,"id=".$data['object_id']);
			exit(json_encode(array("error"=>0,"message"=>$this->lang['delete_success'])));
		}else{
			exit(json_encode(array("error"=>1,"message"=>$this->lang['delete_fail'])));
		}
		
	}
	
	
	public function onmyfav(){
		$userid=$this->userInfo['userid'];
		$model_id=max(1,get('model_id','i'));
		$option=array(
			"where"=>" userid=".$userid." AND model_id=".$model_id." ",
		);
		$rscount=true;
		$data=$this->fav->select($userid,$option,$rscount);
		$this->loadModel("article");
		if($data){
			foreach($data as $k=>$v){
				$a=$this->article->selectRow(array("id"=>$v['object_id']));
				$v['title']=$a['title'];
				$data[$k]=$v;
			}
		}
		$this->smarty->assign(array(
			"data"=>$data,
		));
		$this->smarty->display("fav/my.html");
		
	}
	
	 
	
}
?>