<?php
class loginControl extends skymvc{
	
	function __construct(){
		parent::__construct();
		$this->loadModel(array("admin","admin_group"));
		$data=$this->admin->selectRow();
		if(empty($data)){
			$this->admin->insert(array(
				"username"=>"admin",
				"password"=>umd5("admin1234"),
				"salt"=>"1234",
				"isfounder"=>1
			));
		}
	}
	
	public function onDefault(){
		$this->smarty->display("login.html");
	}
	
	public function onLogin_save(){
		$username=post('username','h');
		$password=post('password','h');
		$checkcode=post('checkcode','h');
		if($checkcode!=$_SESSION['checkcode']){
			$this->goall($this->lang['checkcode_error'],1);
		}
		$data=$this->admin->selectRow(array("where"=>array("username"=>$username)));
		if(umd5($password.$data['salt'])==$data['password']){
			unset($data['password']);
			$_SESSION['ssadmin']=$data;			
			
			 
			$this->goall("登录成功",0,0,APPADMIN."?m=iframe");
		}else{
			$this->goall($this->lang['password_error'],1);
		}
	}
	
	public function onLogout(){
		$_SESSION['ssadmin']="";
		unset($_SESSION['ssadmin']);
		$this->goall("退出成功",0,0,APPADMIN."?m=login");
	}
	
	public function onRefresh(){
		$data=$this->admin->selectRow(array("where"=>array("username"=>$_SESSION['ssadmin']['username'])));
		$_SESSION['ssadmin']=$data;	
	}
}

?>