<?php
class gold_logControl extends skymvc{
	
	public function __construct(){
		parent::__construct();
		$this->loadModel(array("login","user","gold_log"));
		$this->login->checkLogin();
	}
	
	public function onDefault(){
		$where=" isdelete=0 AND userid=".$this->login->userid;
		$start=get('per_page','i');
		$limit=20;
		$url="/index.php?m=gold_log";
		$option=array(
			"where"=>$where,
			"start"=>$start,
			"limit"=>$limit,
			"order"=>" id DESC",
		);
		$rscount=true;
		$data=$this->gold_log->select($option,$rscount);
		$pagelist=$this->pagelist($rscount,$limit,$url);
		$this->smarty->assign(array(
			"list"=>$data,
			"rscount"=>$rscount,
			"pagelist"=>$pagelist
			
		));
		
		$this->smarty->display("gold_log/index.html");
	}
	
}
?>