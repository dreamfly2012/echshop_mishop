<?php
class indexControl extends skymvc{
	
	public function __construct(){
		parent::__construct();
	}
	
	public function onInit(){
		
		
		define("DOMAIN",$_SERVER['HTTP_HOST']);
	}
	
	public function onDefault(){
		M("login")->checkLogin();
		$shopappid=get('shopappid','h');	
		header("Location: ".YUNAPPDOMAIN."?m=api&a=get&domain=".DOMAIN."&shopappid=".$shopappid);
		
	}

	public function onLogin(){
		M("login")->checkLogin();
		$token=get('token');
		$user=M("login")->getUser();
		$shopappid=get('shopappid','h');
		$access_token="appyun".$appid.md5($user['userid']);
		cache()->set($access_token,$user['userid'],3600*24*2);
		header("Location:".YUNAPPDOMAIN."?m=api&a=Login&domain=".DOMAIN."&shopappid=".$shopappid."&token=".$token."&access_token=".$access_token);
	}

	/*根据token获取user信息*/
	public function onUser(){
		$_GET['ajax']=1;
	 
		$access_token=get('access_token','h');
		if($userid=cache()->get($access_token)){
			$option=array(
				"where"=>"userid=".$userid,
				"fields"=>"userid,username,nickname,telephone,email"
			);
			$user=M("user")->selectRow($option);
			$this->goAll("success",0,$user);
		}else{
			$this->goAll("error",1,$user);
		}
	}

	public function ongoAdmin(){
		$access_token=get('access_token','h'); 
		header("Location: yun.php?m=yunapp&access_token=".$access_token);
	}
	
}
?>