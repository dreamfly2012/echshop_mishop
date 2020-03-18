<?php
class loginControl extends skymvc{
	function __construct(){
		parent::__construct();
		$this->loadModel(array("user","login"));
		
	}
	
	function onDefault(){
		$this->smarty->display("login/login.html");
	}
	
	public function onget_oc_ssid(){
		$this->goall(OC_SSID);
	}
	
	public function onCheck(){
		if(M('login')->userid){
			echo sky_json_encode(array("error"=>0,"nologin"=>0));
		}else{
			echo sky_json_encode(array("error"=>1,"nologin"=>1)); 
		}
	}
	
	public function onLogin(){
		if(get('ajax')){
			$this->smarty->display("login/ajax_login.html");
		}else{
			$this->smarty->display("login/login.html");
		}
	}
	
	public function onLoginSave(){
		$username=post('username','h');
		$email=post('email','h');
		$password=post('password','h');
		$telephone=post('telephone','h');
		
		if(isset($_POST['checkcode'])){
			$checkcode=post('checkcode','h');
			if($checkcode!=$_SESSION['checkcode']){
				$this->g($this->lang['checkcode_error'],1);
			}
		}
		if(is_email($username) && !$email){
			$email=$username;
		}
		if(is_tel($username) && !$telephone){
			$telephone=$username;
		}
		if($email){
			$user=$this->user->selectRow(array("where"=>"email='".$email."' "));
		}
		
		if(!$user && $telephone){
			$user=$this->user->selectRow(array("where"=>"telephone='".$telephone."' "));
		}

		if(!$user && $username){
			$user=$this->user->selectRow(array("where"=>"username='".$username."' "));
		}
		
		if(empty($user)){
			$this->g('账号不存在',1,"",$_SERVER['HTTP_REFERER']);
		}
		if($user['password']!=umd5($password.$user['salt'])){
			$this->g("密码出错了",1,"",$_SERVER['HTTP_REFERER']);
		}
		$_SESSION['ssuser']=$user;
		$backurl=get_post('backurl','h');
		if(!$backurl){
			$backurl=$_SERVER['HTTP_REFERER'];
		}
		if(preg_match("/login/i",$_SERVER['HTTP_REFERER'])){
			$backurl="/index.php";
		} 
		$authcode=jiami($user['userid']."|".umd5($user['password']));
		if(ISWAP or post('autologin')){ 
			setcookie("authcode",$authcode,time()+3600000,"/",DOMAIN);
		}
		$this->goall("登陆成功",0,array("authcode"=>$authcode,"str"=>$user['userid']."|".umd5($user['password'])),$backurl);
	}
	
	private function g($message,$err=0,$data=array(),$url="/index.php"){
		if(get_post('ajax',1)){
			$this->sexit(json_encode(array("error"=>$err,"message"=>$message,"data"=>$data))); 
		}else{
			$this->goall($message,0,0,$url);
		}
	}
	
 
	
	public function onLogout(){
		$_SESSION['ssuser']="";
		 setcookie("authcode","",time()-3999,"/",DOMAIN);	
		$this->goall("注销成功",0,0,"/index.php");
	}
	
	public function onforgetPassword(){
		$this->smarty->display("login/forgetpassword.html");
	}
	
	public function onforgetPasswordSave(){
		$email=post('email','h');
		$this->loadModel("email");
		$code=md5(rand(10000,99999));
		cache()->set("fc_".md5($email),array("email"=>$email,"code"=>$code),3600);
		$html="点击链接找回密码<br> <a href='http://".$_SERVER['HTTP_HOST']."/index.php?m=login&a=findpwd&code=".$code."'>http://".$_SERVER['HTTP_HOST']."/index.php?m=login&a=findpwd&code=".$code."</a>";
		if($this->email->sendEmail($email,"找回密码",$html)){
			
			$this->goall("邮件发送成功,请到邮箱确认",0,0,"/index.php");
		}else{
			$this->goall("邮件发送失败",1);
		}
	}
	
	public function onfindpwd(){
		$this->smarty->display("login/findpwd.html");
	}
	
	public function onFindPwdSave(){
		$email=post('email','h');
		$password=post('password','h');
		$password2=post('password2','h');
		$code=post('code','h');
		if($password!=$password2){
			$this->goall("两次输入的密码不一致",1);		
		}
		$findpwdcode=cache()->get("fc_".md5($email));
		if($findpwdcode['email']!=$email or $findpwdcode['code']!=$code){
			$this->goall("找回密码验证码出错",1);
		}
		$data['salt']=rand(1000,9999);
		$data['password']=umd5($password.$data['salt']);
		$this->user->update($data,"email='$email'");
		$this->goall("密码修改成功，马上登录",0,0,"/index.php?m=login");
	}
}
?>