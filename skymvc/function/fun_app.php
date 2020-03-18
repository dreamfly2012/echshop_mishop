<?php
if(!defined("SAE")){
	define("SAE",1);
}
switch(SAE){
	case "sina":
			require("fun_app_sina.php");
		break;
	case "baidu":
			require("fun_app_baidu.php");
		break;
	case "qq":
			require("fun_app_qq.php");
		break;
	default:
			require("fun_app_default.php");
		break;
}

?>