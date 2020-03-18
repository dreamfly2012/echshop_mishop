<?php
class inviteapiControl extends skymvc{
	
	public function __construct(){
		parent::__construct();
		$this->loadModel(array("user","invite"));
	}
	/*邀请注册处理函数*/
	public function invite_reg($in_userid){
		if(!isset($_COOKIE['invite_uid'])){
			return false;
		}
		$this->invite->insert(array(
			"userid"=>intval($_COOKIE['invite_uid']),
			"in_userid"=>$in_userid,
			"dateline"=>time()
		));
		
	}
	
	
	
}
?>