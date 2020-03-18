<?php
class sysmsgControl extends skymvc{
	public $userid;
	public function __construct(){
		parent::__construct();
		$this->loadModel(array("sysmsg","sysmsg_user","login","user"));
		$this->login->checkLogin();
		$this->userid=$this->login->userid;
		$this->onGet();
	}
	
	public function onDefault(){
		$where=" status<99 AND  userid=".$this->userid;
		$url=APPINDEX."?m=sysmsg";
		$limit=20;
		$start=get('per_page','i');
		$option=array(
			"start"=>$start,
			"limit"=>$limit,
			"order"=>" id DESC",
			"where"=>$where
		);
		$rscount=true;
		$data=$this->sysmsg_user->select($option,$rscount);
		if($data){
			foreach($data as $k=>$v){
				$d=$this->sysmsg->selectRow(array("where"=>"id=".$v['msgid']));
				$v['title']=$d['title'];
				$data[$k]=$v;
			}
		}
		$pagelist=$this->pagelist($rscount,$limit,$url);
		$this->smarty->assign(array(
			"data"=>$data,
			"rscount"=>$rscount,
			"pagelist"=>$pagelist,
		));
		$this->smarty->display("sysmsg/index.html");
	}
	
	public function onShow(){
		$id=get('id','i');
		if($id){
			$data=$this->sysmsg_user->selectRow(array("where"=>"id=$id"));
			$d=$this->sysmsg->selectRow(array("where"=>"id=".$data['msgid']));
			$data['title']=$d['title'];
			$data['content']=$d['content'];
			if($data['status']==0){
				$this->sysmsg_user->update(array("status"=>1),"id=$id");
				
			}
		}
		$this->smarty->assign(array(
			"data"=>$data
		));
		$this->smarty->display("sysmsg/show.html");
	}
	
	public function onDelete(){
		$id=get('id','i');
		$this->sysmsg_user->update(array("status"=>99),"id=$id AND userid=".$this->userid);
		$this->goall("删除成功");
	}
	
	public function onGet(){
		$msg=$this->sysmsg_user->selectRow(array("where"=>" userid=".$this->userid,"order"=>" id DESC"));
		$w="";
		if($msg){
			$w=" AND id>".$msg['msgid'];
		}
		$data=$this->sysmsg->select(array("where"=>" start_time<".time()." AND end_time >".time()." AND type_id in(1,2) AND status=1 $w "));
		if($data){
			foreach($data as $k=>$v){
				if(!$this->sysmsg_user->selectRow(" userid=".$this->userid." AND msg_id=".$v['id'])){
					$sdata=array(
						"userid"=>$this->userid,
						"msgid"=>$v['id'],
						"status"=>0,
						"dateline"=>time()
					);
					
					$this->sysmsg_user->insert($sdata);
				}
			}
		}
	}
	
}
?>