<?php
class weixin_userControl extends skymvc{
	
	public function __construct(){
		parent::__construct();
		$this->loadModel(array("login","weixin","weixin_user"));
	}
	
	public function onDefault(){
		$op=array();
		$data=$this->weixin_user->select($op);
		$this->smarty->assign(array(
			"data"=>$data
		));
		$this->smarty->display("weixin_user/index.html");
	}
	
	public function onAdd(){
		
	}
	
	public function onSave(){
		
	}
	
	public function onOrder(){
		
	}
	
	public function onDelete(){
		
	}
	
}
?>