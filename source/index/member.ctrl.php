<?php
class memberControl extends skymvc{
	
	public function __construct(){
		parent::__construct();
		$this->loadModel(array("user","login"));
	}
	
	public function onDefault(){
		$userid=get('userid','i');
		if(empty($userid)){
			$userid=$this->login->userid;
		}
		$user=$this->user->selectRow("userid=".$userid);
		if(empty($user)) $this->goall($this->lang['user_no_exists'],1,0,"/index.php");
		$this->smarty->assign(array(
			"user"=>$user
		));
		$this->smarty->display("member/index.html");
	}
	
	public function onList(){
		$this->smarty->display("member/list.html");
	}
	
	public function onUserInfo(){
		$userid=get('userid','i');
		if(empty($userid)){
			$userid=$this->login->userid;
		}
		$user=$this->user->selectRow("userid=".$userid);
		$this->smarty->assign(array(
			"user"=>$user
		));
		$this->smarty->display("member/userinfo.html");
	}
	
}

?>