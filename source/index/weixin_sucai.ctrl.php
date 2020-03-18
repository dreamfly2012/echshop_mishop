<?php
class weixin_sucaiControl extends skymvc{
	
	public function __construct(){
		parent::__construct();
		$this->loadModel(array("login","weixin","weixin_sucai"));
	}
	
	public function onDefault(){
		
	}
	
	public function onShow(){
		$id=get('id','i');
		$data=$this->weixin_sucai->selectRow("id=".$id);
		$weixin=$this->weixin->selectRow("id=".intval($data['wid']));
		$this->smarty->assign(array(
			"data"=>$data,
			"weixin"=>$weixin
		));
		$this->smarty->display("weixin_sucai/show.html");
	}
}
?>