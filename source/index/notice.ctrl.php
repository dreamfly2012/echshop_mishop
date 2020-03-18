<?php
class noticeControl extends skymvc{
	public $userid;
	public function __construct(){
		parent::__construct();
		$this->loadModel(array("login","notice"));
		$this->userid=$this->login->userid;
		$this->login->checkLogin();
		 
	}
	
	public function onDefault(){
		$url="/index.php?m=notice";
		$where=" userid=".$this->userid."  ";
		$start=get('per_page','i');
		$limit=10;
		$option=array(
			"where"=>$where,
			"order"=>"status asc,id DESC",
			"start"=>$start,
			"limit"=>$limit
		);
		$rscount=true;
		$data=$this->notice->select($option,$rscount);
		$pagelist=$this->pagelist($rscount,$limit,$url);
		$this->smarty->assign(array(
			"data"=>$data,
			"rscount"=>$rscount,
			"pagelist"=>$pagelist
		));
		
		$this->smarty->display("notice/index.html");
	}
	
	public function onDelete(){
		$id=get('id','i');
		$this->notice->delete("id=".$id);
		exit(json_encode(array("error"=>0)));
	}
	
	public function onstatus(){
		$id=get('id','i');
		$this->notice->update(array("status"=>1),"id=".$id);
	}
	
}

?>