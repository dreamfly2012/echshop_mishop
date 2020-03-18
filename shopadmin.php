<?php
error_reporting(E_ALL ^ E_NOTICE);
if(!file_exists("config/install.lock")){
	header("Location:install");exit;
};
header("Content-type:text/html; charset=utf-8");
define("ROOT_PATH",  str_replace("\\", "/", dirname(__FILE__))."/");
@include_once("config/setconfig.php");
@include_once("config/version.php");
 
require("config/rewriterule.php");
require("config/config.php");
require("config/const.php");

define("CONTROL_DIR","source/shopadmin");
define("MODEL_DIR","source/model");
/*视图模版配置*/
$cache_dir="";//模版缓存文件夹
$wap_template_dir=$template_dir="themes/baicha/shopadminwap";//模版风格文件夹
$wap_template_dir="themes/baicha/shopadminwap/";
$compiled_dir="";//模版编译文件夹
$html_dir="";//生成静态文件夹
$rewrite_on=REWRITE_ON;//是否开启伪静态 0不开 1开启
$smarty_caching=true;//是否开启缓存
$smarty_cache_lifetime=3600;//缓存时间
require("./skymvc/skymvc.php");
//用户自定义初始化函数
function userinit(&$base){
	
	$base->loadConfig("table");		
	$base->smarty->assign("skins","/themes/shopadmin/");
	$base->smarty->assign("appindex",APPINDEX);
	$base->smarty->assign("appadmin",APPADMIN);
	$base->smarty->assign("appshop",APPSHOP);
	if(!in_array(get('m'),array('login','register',''))){
		if(!$_SESSION['ssshopadmin']){
			$base->gomsg("请先登录",APPSHOP."?m=login");
		}
	}
	 
	if(isset($_SESSION['ssuser']['userid'])){
		$base->ssuser=$_SESSION['ssuser'];//当前登录用户的信息
		$base->smarty->assign("ssuser",$base->ssuser);
	}
	$base->smarty->assign("ssshopadmin",$_SESSION['ssshopadmin']);
	if($_SESSION['ssshopadmin']){
	define("SITEID",max(1,$_SESSION['ssshopadmin']['siteid']));
	define("SHOPID",max(1,$_SESSION['ssshopadmin']['shopid']));
	}else{
		define("SHOPID",0);
	}
}

?>