<?php
class wxloginControl extends skymvc{
	
	public function __construct(){
		parent::__construct();
		$this->loadModel(array("user"));
		$wid=get_post('wid','i');
		if($wid){
			$where=" id=".$wid;
		}else{
			$where="";
		}
		$this->loadModel(array("weixin"));
		$this->wx=$this->weixin->selectRow(array("where"=>$where,"order"=>"id DESC"));

		define("WXTOKEN",$this->wx['token']);
		define("APPID",$this->wx['appid']);
		define("APPSECRET",$this->wx['appkey']);
		define("REDIRECT_URI","http://".$_SERVER['HTTP_HOST']."/index.php?m=wxlogin&a=callback&wid=".$this->wx['id']);
	}
	
	public function onDefault(){
		echo "hello";
	}
	
public function onGeturl()
{
	//echo "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".APPID."&redirect_uri=".urlencode(REDIRECT_URI)."&response_type=code&scope=snsapi_base&state=STATE#wechat_redirect";exit;
	header("Location: https://open.weixin.qq.com/connect/oauth2/authorize?appid=".APPID."&redirect_uri=".urlencode(REDIRECT_URI)."&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect");
	
	exit();
}

public function oncallback()
{
	$c=file_get_contents("https://api.weixin.qq.com/sns/oauth2/access_token?appid=".APPID."&secret=".APPSECRET."&code=".$_GET['code']."&grant_type=authorization_code");
	$data=json_decode($c,true);
		if(isset($data['access_token'])){
			$c=file_get_contents("https://api.weixin.qq.com/sns/oauth2/refresh_token?appid=".APPID."&grant_type=refresh_token&refresh_token=".$data['refresh_token']);
			$data=json_decode($c,true);
			if(isset($data['access_token'])){
				if(!empty($data['openid'])){
					$arr=file_get_contents("https://api.weixin.qq.com/sns/userinfo?access_token=".$data['access_token']."&openid=".$data['openid']."&lang=zh_CN");
					$arr=json_decode($arr,true);
					
					$nickname=$arr['nickname'];
					if(empty($nickname))
					{
						$this->goall('微信接口错误',1,0,'/index.php?m=index');
					}
					if($user=$this->user->getRow("SELECT *  FROM ".table('user')." WHERE openid='".$data['openid']."' AND xfrom='weixin' "))
					{
						M('login')->set("ssuser",$user);
						$this->goall('登陆成功',1,0,'/index.php');
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
						
						$dir="attach/user_head/".date("Y/m/d");
						umkdir($dir);
						if($arr['headimgurl']){
							$user_head=$dir."/".base64_encode($u).time().".jpg";
							
							file_put_contents($user_head,curl_get_contents($arr['headimgurl']));
							
							$this->loadClass("image",false,false);
							$img=new image();
							$img->makethumb($user_head.".100x100.jpg",$user_head,"100","100",1);
							$img->makethumb($user_head.".small.jpg",$user_head,"240");
							$img->makethumb($user_head.".middle.jpg",$user_head,"440");
						}
						//关联插件
						$data=array(
							"nickname"=>$u,
							"username"=>$u, 
							"xfrom"=>'weixin',
							"openid"=>$data['openid'],
							"dateline"=>time(),
							"lastfeed"=>time(),
							"kday"=>date("Y-m-d")
						);
						if($user_head){
							$data['user_head']=$user_head;
						}
						if(isset($_COOKIE['invite_uid'])){
							$data['invite_userid']=intval($_COOKIE['invite_uid']);
						}
						$userid=$this->user->insert($data);
						$this->loadControl("inviteapi");
						$this->inviteapiControl->invite_reg($userid);
						$user=$this->user->getRow("SELECT * FROM ".table('user')." WHERE userid='$userid' ");
						 M('login')->set("ssuser",$user);
						$this->goall('注册登陆成功',0,0,'/index.php');
					}
				}
				
			}else{
					exit($c);
			}
		}else{
					exit($c);
		}
	
	
}

}
?>