<?php
class sinaloginControl extends skymvc{

	function __construct(){
		parent::__construct();
		require_once(ROOT_PATH."config/sina_config.php");
		require_once(ROOT_PATH."api/sina/saetv2.ex.class.php");
		$this->loadModel(array("user","invite"));	
	}
	public function onDefault(){
		$keys = array();
		$keys['code'] = $_GET['code'];
		$keys['redirect_uri'] = CALLBACK;   	
		$o = new SaeTOAuthV2(WB_AKEY, WB_SKEY);
		
		$token = $o->getAccessToken('code', $keys);
		$_SESSION['token']=$token;
		
		header("Location: /index.php?m=sinalogin&a=show");
	}

	public function ongeturl(){
		//获取登陆url
		if(ISWAP){
			$w="&display=mobile";	
		}
		header("Location: https://api.weibo.com/oauth2/authorize?client_id=".WB_AKEY."&response_type=code{$w}&redirect_uri=".CALLBACK);
	}
	public function onapilogin(){
	
		//处理登陆数据 新浪端
		//获取加密数据
		$keys = array();
		$keys['code'] = $_GET['code'];
		$keys['redirect_uri'] = CALLBACK;   	
		$o = new SaeTOAuthV2(WB_AKEY, WB_SKEY);
		 
		$token = $o->getAccessToken('code', $keys);
		$_SESSION['token']=$token;
		
		header("Location: /index.php?m=sinalogin&a=show");
	}
	public function onshow(){
		//处理用户数据 本站端
		$c = new SaeTClientV2(WB_AKEY, WB_SKEY, $_SESSION['token']['access_token']);
		$xuser=$c->show_user_by_id($_SESSION['token']['uid']);
		if(empty($xuser['id']))
		{
			$this->goall('新浪微博登录出错',1,0,'/index.php?m=user&a=login');
		}
		//转化字符串编码
		 
		$user=$this->user->getRow("select * from  ".table('user')." where openid='".$xuser['id']."' and xfrom='sina' ");
	//存在记录
		if($user)
		{
			$this->user->query("UPDATE ".table('user')." SET accesstoken='{$_SESSION['token']['access_token']}' WHERE openid='".$xuser['id']."' and xfrom='sina' ");
			M('login')->set("ssuser",$user);
			
		}else
		{
			//如果不存在则插入数据		
				
			//如果没有则 生成一个账号 绑定
				$tempname=$username=$xuser['name'];
				$i=1;
				$j=0;
				while($i)
				{
					
					$i=$this->user->getOne("select userid from ".table('user')." where nickname='$tempname' ");
					if($i>0)
					{
					$tempname=$username.$j;
					$j++;
					}
				}
				$username.=$j?$j:"";		
				
				//关联插件
				$data=array(
					"nickname"=>$xuser['name'],
					"xfrom"=>'sina',
					"openid"=>$xuser['id'],
					"dateline"=>time(),
					"lastfeed"=>time(),
					'accesstoken'=>$_SESSION['token']['access_token'],
					"kday"=>date("Y-m-d")
				);
				if(isset($_COOKIE['invite_uid'])){
					$data['invite_userid']=intval($_COOKIE['invite_uid']);
				}
				$userid=$this->user->insert($data);
				$this->loadControl("inviteapi");
				$this->inviteapiControl->invite_reg($userid);
				$user=$this->user->getRow("SELECT * FROM ".table('user')." WHERE userid=".intval($userid)."  ");
				M('login')->set("ssuser",$user);
				//账号绑定处理
				$this->gourl("/index.php?m=register&a=OpenReg");
	
		}
		 
		$this->goall('登陆成功',1,0,'/index.php');
	}

}
?>