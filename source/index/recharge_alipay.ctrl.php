<?php
class recharge_alipayControl extends skymvc{
	public function __construct(){
		parent::__construct();
		$this->loadModel(array("recharge"));
	}
	
	public function onDefault(){
		exit("暂无权限");
	}
	
	public function onTest(){
		$this->Success();
	}
	 
	public function onNotifyWap(){
		 /*************************页面功能说明*************************
		 * 创建该页面文件时，请留心该页面文件中无任何HTML代码及空格。
		 * 该页面不能在本机电脑测试，请到服务器上做测试。请确保外部可以访问该页面。
		 * 该页面调试工具请使用写文本函数$this->_log，该函数已被默认关闭，见alipay_notify_class.php中的函数verifyNotify
		 * 如果没有收到该页面返回的 success 信息，支付宝会在24小时内按一定的时间策略重发通知
		 */
		unset($_GET['a']);
		unset($_GET['m']);
		require_once(ROOT_PATH."/api/alipay/wap/alipay.config.php");
		require_once(ROOT_PATH."/api/alipay/wap/lib/alipay_notify.class.php");
		
		
		//计算得出通知验证结果
		$alipayNotify = new AlipayNotify($alipay_config);
		$verify_result = $alipayNotify->verifyNotify();
		
		
		if($verify_result) {//验证成功
			$this->_log("wap验证成功");
		 	$out_trade_no = $_POST['out_trade_no'];

			//支付宝交易号
		
			$trade_no = $_POST['trade_no'];
		
			//交易状态
			$trade_status = $_POST['trade_status'];
			$total_fee=$_POST['total_fee'];
			$row=$this->recharge->selectRow(array("where"=>" orderno='".$out_trade_no."' "));
			$this->_log($out_trade_no.json_encode($row).' 验证订单中\r\n');
				 
				if(empty($row) or $row['status']==1){
					$this->_log('您的充值订单失效了');
					exit;
				}elseif(intval($row['money'])!=intval($total_fee)){
			 		$this->_log('订单金额不对\r\n');
					exit;
				}else{
					/***充值成功**/
															
					$this->Success($trade_no,$row);
					 
				}

		}
		else {
			//验证失败
			echo "fail";
			$this->_log("wap验证失败");
			//调试用，写文本函数记录程序运行情况是否正常
			//$this->_log("这里写入想要调试的代码变量值，或其他运行的结果记录");
		}
	}
	
	public function onNotify(){
		unset($_GET['a']);
		unset($_GET['m']);
		/* *
		 * 功能：支付宝服务器异步通知页面
		 * 版本：3.3
		 * 日期：2012-07-23
		 * 说明：
		 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
		 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。
		
		
		 *************************页面功能说明*************************
		 * 创建该页面文件时，请留心该页面文件中无任何HTML代码及空格。
		 * 该页面不能在本机电脑测试，请到服务器上做测试。请确保外部可以访问该页面。
		 * 该页面调试工具请使用写文本函数$this->_log，该函数已被默认关闭，见alipay_notify_class.php中的函数verifyNotify
		 * 如果没有收到该页面返回的 success 信息，支付宝会在24小时内按一定的时间策略重发通知
		 */
		
		require_once(ROOT_PATH."api/alipay/alipay.config.php");
		require_once(ROOT_PATH."api/alipay/lib/alipay_notify.class.php");
		
		//计算得出通知验证结果
		$alipayNotify = new AlipayNotify($alipay_config);
		$verify_result = $alipayNotify->verifyNotify();
		
		if($verify_result) {//验证成功

			//请在这里加上商户的业务逻辑程序代
		
			
			//——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
			
			//获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
			
			//商户订单号
		
			$out_trade_no = $_POST['out_trade_no'];
		
			//支付宝交易号
		
			$trade_no = $_POST['trade_no'];
			$total_fee=$_POST['total_fee'];
			//交易状态
			$trade_status = $_POST['trade_status'];
			
			$row=$this->recharge->selectRow(array("where"=>" orderno='".$out_trade_no."' "));
				$this->_log($out_trade_no.' 验证订单中\r\n'.json_encode($row));
				 
			if(empty($row) or $row['status']==1){
				$this->_log('您的充值订单失效了');
				exit;
			}elseif(intval($row['money'])!=intval($total_fee)){
				$this->_log('订单金额不对\r\n');
				exit;
			}else{
				/***充值成功**/
				$money=$row['money'];						
				$this->Success($trade_no,$row);
				$this->_log($out_trade_no."充值成功");
			}
			
			
		}
		else {
			//验证失败
			$this->_log("fail");
		
			//调试用，写文本函数记录程序运行情况是否正常
			//$this->_log("这里写入想要调试的代码变量值，或其他运行的结果记录");
		}
	}
	
	
	public function Success($trade_no,$order){
		$this->recharge->update(array("status"=>1,"pay_orderno"=>$trade_no)," id='".$order['id']."'" );
			
		$orderdata=json_decode(base64_decode($order['orderdata']),true);
		$this->_log($out_trade_no."处理订单支付".$orderdata['table']);
		 
			switch($orderdata['table']){
				 
				case "shop":
						M("shop_order")->update(array("ispay"=>2),"order_id=".$orderdata['order_id']);
					break;
				case "order":
						M("order")->update(array("ispay"=>2,"paytype"=>$row['pay_type']),"order_id=".$orderdata['order_id']);
					break;
				default:
						//充值
						$option=array(
							"userid"=>$order['userid'],
							"money"=>$order['money'],
							"content"=>"您在".date("YmdHis")."充值[money]元",
						) ;
						M("user")->addMoney($option);
					break; 
					
			}
		 
		
			
		
	}
	
	public function onReturn(){
		unset($_GET['a']);
		unset($_GET['m']);
		require_once(ROOT_PATH."api/alipay/alipay.config.php");
		require_once(ROOT_PATH."api/alipay/lib/alipay_notify.class.php");
		//计算得出通知验证结果
		$alipayNotify = new AlipayNotify($alipay_config);
		$verify_result = $alipayNotify->verifyReturn();
		if($verify_result) {//验证成功
			/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			//请在这里加上商户的业务逻辑程序代码
			
			//——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
			//获取支付宝的通知返回参数，可参考技术文档中页面跳转同步通知参数列表
		
			//商户订单号
		
			$out_trade_no = $_GET['out_trade_no'];
		
			//支付宝交易号
		
			$trade_no = $_GET['trade_no'];
		
			//交易状态
			$trade_status = $_GET['trade_status'];
		
		
			if($_GET['trade_status'] == 'TRADE_FINISHED' || $_GET['trade_status'] == 'TRADE_SUCCESS') {
				//判断该笔订单是否在商户网站中已经做过处理
					//如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
					//如果有做过处理，不执行商户的业务程序
			}
			else {
			  //echo "trade_status=".$_GET['trade_status'];
			}
				
			$row=$this->recharge->selectRow(array("where"=>" orderno='".$out_trade_no."' "));
			$orderdata=json_decode(base64_decode($row['orderdata']),true);
			//判断该笔订单是否在商户网站中已经做过处理
				//如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
				//如果有做过处理，不执行商户的业务程序
			if($orderdata['order_id']){
				$url="/index.php?m=".$orderdata['table']."_order&a=show&order_id=".$orderdata['order_id'];
			}else{
				$url="/index.php";
			}
			$this->goall("验证成功",0,0,$url);
		
			//——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
			
			/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		}
		else {
			//验证失败
			//如要调试，请看alipay_notify.php页面的verifyReturn函数
			$this->goall("验证失败",1,0,"/index.php?m=recharge&a=my");	
		}

	}
	
	public function onReturnWap(){
		
		unset($_GET['a']);
		unset($_GET['m']);
		require_once(ROOT_PATH."/api/alipay/wap/alipay.config.php");
		require_once(ROOT_PATH."/api/alipay/wap/lib/alipay_notify.class.php");
		//计算得出通知验证结果
		 
		$alipayNotify = new AlipayNotify($alipay_config);
		$verify_result = $alipayNotify->verifyReturn();
		
		if($verify_result) {//验证成功
			//请在这里加上商户的业务逻辑程序代码
			
			//——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
			//获取支付宝的通知返回参数，可参考技术文档中页面跳转同步通知参数列表
		
			//商户订单号
			$out_trade_no = $_GET['out_trade_no'];
		
			//支付宝交易号
			$trade_no = $_GET['trade_no'];
		
			//交易状态
			$result = $_GET['result'];
			$row=$this->recharge->selectRow(array("where"=>" orderno='".$out_trade_no."' "));
			$orderdata=json_decode(base64_decode($row['orderdata']),true);
			//判断该笔订单是否在商户网站中已经做过处理
				//如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
				//如果有做过处理，不执行商户的业务程序
			if($orderdata['order_id']){
				$url="/index.php?m=".$orderdata['table']."_order&a=show&order_id=".$orderdata['order_id'];
			}else{
				$url="/index.php";
			}
			$this->goall("验证成功",0,0,$url);
		
			//——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
			
			/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		}
		else {
			//验证失败
			//如要调试，请看alipay_notify.php页面的verifyReturn函数
			 
			$this->goall("验证失败",1,0,"/index.php?m=recharge&a=my");	
		}
	}
	
	
	public function _log($str){
		return false;
		umkdir("log/alipay/");
		$f=fopen(ROOT_PATH."/log/alipay/".date("Ymd").".txt","a+");
		fwrite($f,date("YmdHis").$str."\r\n");
		fclose($f);
	}
}
?>