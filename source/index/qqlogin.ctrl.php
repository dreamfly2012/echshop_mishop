<?php
class qqloginControl extends skymvc{
	
	public function __construct(){
		parent::__construct();
		require_once(ROOT_PATH."config/qq_config.php");
		$this->loadModel(array("user"));
	}
	
public function onGeturl()
{
	$_SESSION['state'] = md5(uniqid(rand(), TRUE)); //CSRF protection
    $login_url = "https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id=" 
        . APPID . "&redirect_uri=" . urlencode(CALLBACK)
        . "&state=" . $_SESSION['state']
        . "&scope=".SCOPE;
	header("Location:$login_url");
	exit();
}

public function oncallback()
{
	require("api/qq/qqapi.php");		
	//QQ登录成功后的回调地址,主要保存access token
	qq_callback();	
	//获取用户标示id
	get_openid();	
	//echo $_SESSION['openid'];
	$arr=get_user_info();
	
	$nickname=$arr['nickname'];
	if(empty($nickname))
	{
		$this->goall('qq接口错误',1,0,'/index.php?m=index');
	}
	if($user=$this->user->getRow("SELECT *  FROM ".table('user')." WHERE openid='".$_SESSION['openid']."' AND xfrom='qq' "))
	{
		M('login')->set("ssuser",$user);
		//$_SESSION['ssuser']=$user;
		$this->goall('登陆成功',0,0,'/index.php');
	}else
	{
		//生成账户
		$i=0;
		$u=$nickname;
		while($this->user->getOne("SELECT userid FROM ".table('user')." WHERE  nickname='$u' "))
		{
			$i++;
			$u=$nickname.$i;
		}
		
		//关联插件
		$data=array(
			"nickname"=>$u,
			"xfrom"=>'qq',
			"openid"=>$_SESSION['openid'],
			"dateline"=>time(),
			"lastfeed"=>time(),
			"kday"=>date("Y-m-d")
		);
		if(isset($_COOKIE['invite_uid'])){
			$data['invite_userid']=intval($_COOKIE['invite_uid']);
		}
		$userid=$this->user->insert($data);
		$this->loadControl("inviteapi");
		$this->inviteapiControl->invite_reg($userid);
		$user=$this->user->getRow("SELECT * FROM ".table('user')." WHERE userid='$userid' ");
		M('login')->set("ssuser",$user);
		//账号绑定处理
		//$this->gourl("/index.php?m=register&a=OpenReg");
		$this->goall('注册登陆成功',0,0,'/index.php');
	}
}

}
?>