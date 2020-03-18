<?php
class configControl extends skymvc{
	
	function __construct(){
		parent::__construct();
		if(empty($_SESSION['ssadmin'])){
				$this->gourl("".APPADMIN."?m=admin&a=login");	
		}
	}
	
	public function onDefault(){
		$this->loadmodel("config");
		$data=$this->config->selectRow();
		$this->smarty->assign(array(
			"data"=>$data,
		));
		$this->smarty->display("config/index.html");
	}
	
	public function onsave(){
		$data=M("config")->postData();
		
		$this->loadmodel("config");
		if($this->config->selectRow()){
			$this->config->update($data,"1=1");
		}else{
			$this->config->insert($data);
		}
		$this->configfile();
		$this->qq($data);
		$this->weibo($data);
		$this->taobao($data);
		$this->goall("保存成功");
	}
	
	public function qq($data){
		$content='<?php
/*
先到http://connect.qq.com
*/
define("APPID","'.$data['qqid'].'");
define("APPKEY","'.$data['qqkey'].'");
define("CALLBACK","http://{$_SERVER[\'HTTP_HOST\']}/index.php?m=qqlogin&a=callback");
define("SCOPE","get_user_info,add_share,list_album,add_album,upload_pic,add_topic,add_one_blog,add_weibo");

?>';
		file_put_contents("config/qq_config.php",$content);
	}
	public function weibo($data){
		$content='<?php
//新浪微博接口
define("WB_AKEY" , "'.$data['wbid'].'" );
define("WB_SKEY" , "'.$data['wbkey'].'" );
define("CALLBACK","http://{$_SERVER[\'HTTP_HOST\']}/index.php?m=sinalogin&a=apilogin");

?>';
		file_put_contents("config/sina_config.php",$content);
	}
	public function taobao($data){
		$content='<?php
		define("TBPID","'.$data['tbpid'].'");
$tao_appconfig=array(
					array("'.$data['tbid'].'","'.$data['tbkey'].'"),
					 
			);
?>';
		file_put_contents("config/taobao_config.php",$content);
	}
	
	public function configfile()
	{
		$rs=$this->config->selectRow();
		unset($rs['siteid']);
		unset($rs['id']);
		$str='<?php'."\r\n";
		foreach($rs as $key=>$val)
		{
			$str.='define("'.strtoupper($key).'",'."\"{$val}\");\r\n";
			
		}
		$str.='?>';
		file_put_contents(ROOT_PATH."/config/setconfig.php",$str);
	}
	
	public function onTestPhone(){
		$this->loadModel("email");
		if($res=$this->email->setSms(array("uid"=>get_post('phone_user'),"sign"=>get_post('phone_pwd')))->sendSms(get_post('phone_num'),"【得推网络科技】验证码：159867")){
			echo "发送成功,请接收短信！";
		}else{
			echo "发送失败".$res;
		}
		
	}
	
	public function onTestEmail(){
		$this->loadModel("email");
		
		if($this->email->setEmail($_GET)->sendEmail(get('smtpemail'),"这是测试内容哦","测试一下哦")){
			echo "邮件发送成功";
		}else{
			echo "邮件发送失败";
		}
	}
	
	
}

?>