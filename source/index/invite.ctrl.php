<?php
class inviteControl extends skymvc{
	
	public function __construct(){
		parent::__construct();
		$this->loadModel(array("login","user","invite"));
	}
	
	public function onDefault(){
		
		$this->smarty->display("invite/index.html");
	}
	public function onMy(){
		$uids=$this->invite->selectCols(array("where"=>"userid=".$this->login->userid,"fields"=>"in_userid"));
		$data=$this->user->getUserByids($uids);
		$this->smarty->assign(array(
			"data"=>$data
		));
		$this->smarty->display("invite/my.html");
	}
	
	public function onReg(){
		$userid=get('userid','i');
		if($userid){
			setcookie("invite_uid",$userid,time()+3600*24,"/",DOMAIN);
		}
		header("Location: ".R("/index.php?m=register"));
	}
}
?>