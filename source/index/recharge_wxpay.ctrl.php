<?php
class recharge_wxpayControl extends skymvc{
	
	public function __construct(){
		parent::__construct();
		$this->loadModel(array("user","login"));
	}
	
	public function onDefault(){
		$url=$_SERVER['REQUEST_URI'];
		print_r($_GET);
		echo $url;
	}
	
	public function onGo(){
		$this->login->checkLogin();
		$this->loadModel("recharge");
		$orderno=get('orderno','h');
		if(!$orderno){
			$orderno=$_SESSION['orderno'];
		}else{
			$_SESSION['orderno']=$orderno;
		}
		$order=$this->recharge->selectRow(array("where"=>" orderno='".$orderno."' "));
		
		//处理微信
		include_once(ROOT_PATH."api/wxpay/WxPayPubHelper/WxPayPubHelper.php");
	 
		//使用jsapi接口
		$jsApi = new JsApi_pub();
	
		//=========步骤1：网页授权获取用户openid============
		//通过code获得openid
		if (!isset($_GET['code']))
		{
			//触发微信返回code码 
			$url = $jsApi->createOauthUrlForCode(WxPayConf_pub::JS_API_CALL_URL);
			 
			Header("Location: $url"); 
			exit;
		}else
		{
			
			//获取code码，以获取openid
			$code = $_GET['code'];
			$jsApi->setCode($code);
			$openid = $jsApi->getOpenId();
			/*更新*/
			$this->recharge->update(array("openid"=>$openid)," orderno='".$orderno."' ");
		}
		  
		//=========步骤2：使用统一支付接口，获取prepay_id============
		//使用统一支付接口
		$unifiedOrder = new UnifiedOrder_pub();
		
		//设置统一支付接口参数
		//设置必填参数
		//appid已填,商户无需重复填写
		//mch_id已填,商户无需重复填写
		//noncestr已填,商户无需重复填写
		//spbill_create_ip已填,商户无需重复填写
		//sign已填,商户无需重复填写
		$unifiedOrder->setParameter("openid",$openid);//商品描述
		$unifiedOrder->setParameter("body",$_SESSION['ssuser']['nickname']."支付订单");//商品描述
		//自定义订单号，此处仅作举例
		$timeStamp = time();
		$out_trade_no = WxPayConf_pub::APPID."$timeStamp";
		
		$unifiedOrder->setParameter("out_trade_no",$order['orderno']);//商户订单号 
		$unifiedOrder->setParameter("total_fee",$order['money']*100);//总金额
		$unifiedOrder->setParameter("notify_url",WxPayConf_pub::NOTIFY_URL);//通知地址 
		$unifiedOrder->setParameter("trade_type","JSAPI");//交易类型
		
		//非必填参数，商户可根据实际情况选填
		//$unifiedOrder->setParameter("sub_mch_id","XXXX");//子商户号  
		//$unifiedOrder->setParameter("device_info","XXXX");//设备号 
		//$unifiedOrder->setParameter("attach","XXXX");//附加数据 
		//$unifiedOrder->setParameter("time_start","XXXX");//交易起始时间
		//$unifiedOrder->setParameter("time_expire","XXXX");//交易结束时间 
		//$unifiedOrder->setParameter("goods_tag","XXXX");//商品标记 
		//$unifiedOrder->setParameter("openid","XXXX");//用户标识
		//$unifiedOrder->setParameter("product_id","XXXX");//商品ID
	
		$prepay_id = $unifiedOrder->getPrepayId();
		//=========步骤3：使用jsapi调起支付============
		$jsApi->setPrepayId($prepay_id);
	
		$jsApiParameters = $jsApi->getParameters();
		$orderdata=json_decode(base64_decode($order['orderdata']),true);
		if(!empty($orderdata)){
			switch($orderdata['table']){
				 
				case "shop":
						$url="/index.php?m=shop_order&a=show&order_id=".$orderdata['order_id'];
					break;
				case "order":
						$url="/index.php?m=order&a=show&order_id=".$orderdata['order_id'];
					break;
					
			}
		}else{
			$url="/index.php?m=user";
		}
		$this->smarty->assign(array(
			"jsApiParameters"=>$jsApiParameters,
			"order"=>$order,
			"url"=>$url, 
		));
		 
		$this->smarty->display("recharge_wxpay/go.html");
	}
	
	
	
	public function onNotify(){
		/**
		 * 通用通知接口demo
		 * ====================================================
		 * 支付完成后，微信会把相关支付和用户信息发送到商户设定的通知URL，
		 * 商户接收回调信息后，根据需要设定相应的处理流程。
		 * 
		 * 这里举例使用log文件形式记录回调信息。
		*/
			include_once(ROOT_PATH."/api/wxpay/demo/log_.php");
			include_once(ROOT_PATH."/api/wxpay/WxPayPubHelper/WxPayPubHelper.php");
		 
			//使用通用通知接口
			$notify = new Notify_pub();
		
			//存储微信的回调
			$xml = $GLOBALS['HTTP_RAW_POST_DATA'];
			
			$notify->saveData($xml);
			file_put_contents(ROOT_PATH."paylog.txt",serialize($notify->data));	
			//验证签名，并回应微信。
			//对后台通知交互时，如果微信收到商户的应答不是成功或超时，微信认为通知失败，
			//微信会通过一定的策略（如30分钟共8次）定期重新发起通知，
			//尽可能提高通知的成功率，但微信不保证通知最终能成功。
			if($notify->checkSign() == FALSE){
				$notify->setReturnParameter("return_code","FAIL");//返回状态码
				$notify->setReturnParameter("return_msg","签名失败");//返回信息
			}else{
				$notify->setReturnParameter("return_code","SUCCESS");//设置返回码
			}
			$returnXml = $notify->returnXml();
			
			
			//==商户根据实际情况设置相应的处理流程，此处仅作举例=======
			
			//以log文件形式记录回调信息
			 
			 
			
		
			if($notify->checkSign() == TRUE)
			{
				if ($notify->data["return_code"] == "FAIL") {
					//此处应该更新一下订单状态，商户自行增删操作
					$$this->_log("【通信出错】:\n".$xml."\n");
				}
				elseif($notify->data["result_code"] == "FAIL"){
					//此处应该更新一下订单状态，商户自行增删操作
					$this->_log("【业务出错】:\n".$xml."\n");
				}
				else{
					//此处应该更新一下订单状态，商户自行增删操作
					$this->_log("【支付成功】:\n".$xml."\n");
					$arr=$notify->data;
					$out_trade_no=$s_orderno=$arr['out_trade_no'];
					$this->loadModel(array("recharge"));
					$row=$this->recharge->selectRow(array("where"=>" orderno='".$out_trade_no."' "));
					if(!$row  or $row['status']==1 ){
						$this->_log($olog,"订单错误:\n".$xml."\n");
					}else{
						/***充值成功**/
						$money=$row['money'];						
						$this->recharge->update(array("status"=>1,"pay_orderno"=>$trade_no)," id='".$row['id']."'" );
						$orderdata=json_decode(base64_decode($row['orderdata']),true);
						$this->_log("订单记录".base64_decode($row['orderdata']));
						if(!empty($orderdata)){
							//订单支付
							switch($orderdata['table']){
								case "order":
										M("order")->update(array("ispay"=>2,"paytype"=>$row['pay_type']),"order_id=".$orderdata['order_id']);
									break;
								 
								case "shop":
										M("shop_order")->update(array("ispay"=>2,"paytype"=>$row['pay_type']),"order_id=".$orderdata['order_id']);
									break;
								 
									
							}
						}else{
							//在线充值
								$this->loadControl("jfapi");
								$money=round($total_fee/100,1);
								$this->jfapiControl->userid=$row['userid'];
								$this->jfapiControl->addMoney(array(
									"money"=>$money,
									"content"=>"你使用财付通充值了￥".$money."，之前余额￥[oldmoney]，现在余额￥[newmoney]。", 
									"type_id"=>21,
									"ispay"=>2,
								));
						}
						
						 
					
						$this->_log($out_trade_no."充值成功");
						 
	 
					}
					
				}
				
				//商户自行增加处理流程,
				//例如：更新订单状态
				//例如：数据库操作
				//例如：推送支付完成信息
			}
	}
	
	public function _log($str){
		umkdir("log/wxpay/");
		$f=fopen(ROOT_PATH."/log/wxpay/".date("Ymd").".txt","w+");
		fwrite($f,$str."\r\n");
		fclose($f);
	}
	

		
}
?>