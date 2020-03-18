<?php
class registerControl extends skymvc{
	
	public function __construct(){
		parent::__construct();
		$this->loadModel(array("login","user","invite"));
	}
	
	public function onDefault(){
		 
		if(PHONE_REG){
			$this->Ontelephone();
		}else{
			$this->onReg();
		}
	}
	
	public function onReg(){
		if(get('ajax')){
			$this->smarty->display("register/ajax_reg.html");
		}else{
			$this->smarty->display("register/reg.html");
		}
	}
	
	public function Ontelephone(){
		 
		$this->smarty->display("register/telephone.html");
	}
	
	public function onSendSms(){
		$telephone=get_post('telephone','h');
		if(!is_tel($telephone)){
			$this->goall("请输入正确手机号码",1);
		}
		if($this->user->select(array("where"=>"telephone='".$telephone."' "))){
			$this->goall("手机已经存在了",1);
		}
		$t=cache()->get("reg_".$telephone);
		if($t){
			$this->goall("请过".(60-(time()-$t))."秒再发送",1);
		}
		$yzm=rand(111111,999999);
		$site=M("sites")->selectRow(array("order"=>"siteid ASC","limit"=>1));
		$res=M("email")->sendSms($telephone,"【".$site['sitename']."】验证码：".$yzm."，请您5分钟内完成注册");
		$key="reg_sms".$telephone.$yzm;
		
		if($res){
			cache()->set($key,1,300);
			cache()->set("reg_".$telephone,time(),60);
			$this->goAll("短信已发送，请在5分钟内验证注册");
		}else{
			$this->goAll("短信发送失败",1);
		}
	}
	
	public function onYzSms(){
		$yzm=get_post('yzm','h');
		$telephone=get_post('telephone','h');
		$key="reg_sms".$telephone.$yzm;
		if(cache()->get($key)){
			$key="regyz_".$telephone.$yzm;
			cache()->set($key,1,300);
			$this->goAll("success");
		}
		$this->goAll("error",1);
	}
	
	public function onRegPhone(){
		$yzm=get_post('yzm','h');
		$telephone=get_post('telephone','h');
		$key="regyz_".$telephone.$yzm;
		if(cache()->get($key)){
			$this->onRegSave(false);
		}
		$this->goAll("error",1);
	}
	public function onRegSave($ischeckcode=false){
		
		$checkcode=post('checkcode','j');
			if($ischeckcode && $checkcode!=$_SESSION['checkcode']){
				$this->goall($this->lang['checkcode_error'],2);
			}
		$data['email']=$email=post('email','h');
		$telephone=post('telephone','h');
 
			$password=post('password','h');
			$password2=post('password2','h');
			
			if($password!=$password2 or empty($password)){
				$this->goall("两次输入的密码不一致",1);		
			}
			if($email){
				if( !is_email($email)){
					$this->goall("请正确输入邮箱",1);
				}
				if($this->user->select(array("where"=>"email='".$email."' "))){
					$this->goall("邮箱已经存在了",1);
				}
			}
			
			$data['username']=$data['nickname']=post('username','h')?post('username','h'):post('nickname','h');
			
			if(empty($data['nickname'])){
				if(post('telephone')){
					$data['username']=$data['nickname']=post('telephone','h');
				}else{
					$this->goall("请输入昵称",1);
				}
			}
			if(empty($data['username']) && empty($telephone)){
				$this->goAll("用户名不能为空");
			}
			if(post('telephone')){
				if($this->user->select(array("where"=>"telephone='".$telephone."' "))){
					$this->goall("手机已经存在了",1);
				}
			}
				
			if($this->user->select(array("where"=>"nickname='".$data['nickname']."' "))){
				$this->goall("昵称已经存在",1);
			}
			$data['gender']=min(1,get('gender'));
			$data['salt']=rand(1000,9999);
			$data['password']=umd5($password.$data['salt']);
			$data['qq']=post('qq','i');
			$data['telephone']=post('telephone','i');
			$data['truename']=post('truename','h');
			$data['dateline']=time();
			$data['kday']=date("Y-m-d"); 
			if(isset($_COOKIE['invite_uid'])){
				$data['invite_userid']=intval($_COOKIE['invite_uid']);
			}
			$data['userid']=$userid=$this->user->insert($data);
			$this->loadControl("inviteapi");
			$this->inviteapiControl->invite_reg($userid);
			
			$_SESSION['ssuser']=$user=$this->user->selectRow("userid=".$userid);
			setcookie("authcode",$user['userid']."|".md5($user['password'].$user['email']),time()+3600000,"/",DOMAIN);
			$this->goall("注册成功",0,0,R("/index.php"));
		 
	}
	
	public function onOpenReg(){
		$this->login->checkLogin();	
		$user=$this->login->getuser();
		if($user['email']){
			 $this->goall("你已经绑定过了",1,0,"/index.php");
		}
		$this->smarty->display("register/openreg.html");
	}
	
	public function onOpenRegSave(){
		$this->login->checkLogin();
		$user=$this->login->getuser();
		if($user['email']){
			 $this->goall("你已经绑定过了",1,0,"/index.php");
		}
		$data['email']=$email=post('email','h');
		$password=post('password','h');
		$password2=post('password2','h');
		if($password!=$password2){
			$this->goall("两次输入的密码不一致",1);		
		}
		if($this->user->select(array("where"=>"email='".$email."' "))){
			$this->goall("邮箱已经存在了",1);
		}
		$data['gender']=min(1,get('gender'));
		$data['salt']=rand(1000,9999);
		$data['password']=umd5($password.$data['salt']);
		$this->user->update($data,"userid=".$user['userid']);
		$this->goall("账号绑定成功",0,0,"/index.php");
	}
	
	public function onCheck(){
		if(get_post('nickname')){
			$nickname=get('nickname');
			if($this->user->select(array("where"=>"nickname='".get_post('nickname')."' "))){
				exit(json_encode(array("error"=>1,"status"=>"failed","message"=>"昵称已经存在")));
			}
		}
		
		if(get_post('email')){
			if($this->user->select(array("where"=>"email='".get_post('email')."' "))){
				exit(json_encode(array("error"=>1,"status"=>"failed","message"=>"邮箱已经存在")));
			}
		}
		
		exit(json_encode(array("error"=>0,"status"=>"success","msg"=>"OK")));
	}
	
	public function oncheckNickName(){
	 		$nickname=get_post("param",'h');
			if($this->user->select(array("where"=>"nickname='".$nickname."' "))){
				exit(json_encode(array("status"=>"n", "info"=>"昵称已经存在")));
			}else{
				exit(json_encode(array("status"=>"y", "info"=>"昵称可以使用")));
			}
		 
	}
	
	public function oncheckEmail(){
	 		$email=get_post("param",'h');
			if($this->user->select(array("where"=>"email='".$email."' "))){
				exit(json_encode(array("status"=>"n", "info"=>"邮箱已经存在")));
			}else{
				exit(json_encode(array("status"=>"y", "info"=>"邮箱可以使用")));
			}
		 
	}
	
}

?>