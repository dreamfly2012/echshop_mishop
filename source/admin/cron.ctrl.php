<?php
class cronControl extends skymvc{
	
	public function __construct(){
		parent::__construct();
		$this->loadModel(array("cron"));
	}
	
	public function onDefault(){
		$data=$this->cron->select();
		$this->smarty->assign(array(
			"data"=>$data
		));
		$this->smarty->display("cron/index.html");	
	}
	
	public function onAdd(){
		$id=get_post("id","i");
		$this->smarty->assign("data",$this->cron->selectRow(array("where"=>"id=$id")));
		$this->smarty->display("cron/add.html");
	}
	
	public function onSave(){
		$id=get_post("id","i");
		$data["title"]=get_post("title","h");
		$data["start_time"]=strtotime(get_post("start_time"));
		$data["end_time"]=strtotime(get_post("end_time"));
		$data["minute"]=get_post("minute","i");
		$data["url"]=get_post("url","h");
		
		
		if($id){
			$this->cron->update($data,array('id'=>$id));
		}else{
			$data["dateline"]=time();
			$this->cron->insert($data);
		}
		$this->goall($this->lang["save_success"]);	
	}
	
}
?>