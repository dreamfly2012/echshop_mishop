<?php
class qqopenControl extends skymvc{
	public function __construct(){
		parent::__construct();
		require(ROOT_PATH."api/qqopen/config.php");	
		require_once (ROOT_PATH."api/qqopen/OpenApiV3.php");
		$this->loadModel(array("user","login"));
	}
	
	public function onDefault(){
		
	}
	
	public function onLogin(){
		
		$openid = get('openid');
		$openkey = get('openkey');
		$arr=$this->getUser($openid,$openkey,get('pf'));
		$nickname=$arr['nickname'];
		if(empty($nickname))
		{
			$this->g("获取用户失败",1);
		}
		if($user=$this->user->getRow("SELECT *  FROM ".table('user')." WHERE openid='".$openid."' AND xfrom='qq' "))
		{
			$_SESSION['ssuser']=$user;
			$this->g("登陆成功",0,$user); 
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
				"openid"=>$openid,
				"dateline"=>time(),
				"lastfeed"=>time(),
			);
			if(isset($_COOKIE['invite_uid'])){
				$data['invite_userid']=intval($_COOKIE['invite_uid']);
			}
			$userid=$this->user->insert($data);
			$this->loadControl("inviteapi");
			$this->inviteapiControl->invite_reg($userid);
			$_SESSION['ssuser']=$user=$this->user->getRow("SELECT * FROM ".table('user')." WHERE userid='$userid' ");
			//账号绑定处理
			$this->g("登陆成功",0,$user);  
			//$this->goall('注册登陆成功',0,0,'/index.php');
		}
	}
	
	private function g($message,$err=0,$data=array()){
		if(get('ajax',1)){
			$this->sexit(json_encode(array("error"=>$err,"message"=>$message,"data"=>$data))); 
		}else{
			$this->goall($message,$err,$data,"/index.php");
		}
	}
	
	public function onGetUser(){
		$openid = get('openid');
		$openkey = get('openkey');
		$ret=$this->getUser($openid,$openkey,get('pf'));
		echo json_encode($ret);
	}
	
	public function getUser($openid,$openkey,$pf='qzone'){
		$sdk = new OpenApiV3(QQOPEN_APPID, QQOPEN_APPKEY);
		$sdk->setServerName(QQOPEN_SERVER);
		return  $this->get_user_info($sdk, $openid, $openkey, $pf);
	}
	
	/**
	 * 获取好友资料
	 *
	 * @param object $sdk OpenApiV3 Object
	 * @param string $openid openid
	 * @param string $openkey openkey
	 * @param string $pf 平台
	 * @return array 好友资料数组
	 */
	function get_user_info($sdk, $openid, $openkey, $pf)
	{
		$params = array(
			'openid' => $openid,
			'openkey' => $openkey,
			'pf' => $pf,
		);
		
		$script_name = '/v3/user/get_info';
		return $sdk->api($script_name, $params,'post');
		
		
	}
}
?>