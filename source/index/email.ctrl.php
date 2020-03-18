<?php
class emailControl extends skymvc{
	
	public function __construct(){
		parent::__construct();
	}
	
	public function onDefault(){
		$this->loadModel("email");
		//$this->email->sendEmail("362606856@qq.com","你好测试","欢迎哦哦哦哦哦");
		$res=$this->email->sendsms(15985840591,"欢迎你使用得推网络服务，请继续关注我们");
		if($res){
			echo "发送成功";
		}else{
			echo "发送失败";
		}
	}
 
	 
	
}

?>