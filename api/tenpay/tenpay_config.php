<?php
$spname="自助商户测试帐户";
$partner = "1900000113";                                  	//财付通商户号
$key = "e82573dc7e6136ba414f2e2affbe39fa";											//财付通密钥
/*
$return_url = "http://tao.xingfunv.com/api/tenpay/payReturnUrl.php";			//显示支付结果页面,*替换成payReturnUrl.php所在路径
$notify_url = "http://tao.xingfunv.com/api/tenpay/payNotifyUrl.php";			//支付完成后的回调处理页面,*替换成payNotifyUrl.php所在路径
*/
$return_url = "http://".$_SERVER['HTTP_HOST']."/index.php?m=recharge_tenpay&a=return";			//显示支付结果页面,*替换成payReturnUrl.php所在路径
//$notify_url = "http://".$_SERVER['HTTP_HOST']."/index.php?m=recharge_tenpay&a=NotifyUrl";			//支付完成后的回调处理页面,*替换成payNotifyUrl.php所在路径
$notify_url = "http://".$_SERVER['HTTP_HOST']."/index.php/recharge_tenpay/notify";
?>