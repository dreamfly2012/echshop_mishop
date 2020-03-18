<?php
error_reporting(0);
header("Content-type:text/html; charset=utf-8");
if(ini_get("register_globals"))
{
	die("请关闭全局变量");
}
if(!file_exists("config/install.lock"))
{
	header("Location: install/");
	exit;
}

 
require("config/config.php");
require("config/version.php");
@include_once("config/setconfig.php");
require("config/const.php");
define("ROOT_PATH",  str_replace("\\", "/", dirname(__FILE__))."/");
@include_once("config/site_list.php");
define("CONTROL_DIR","source/index");
define("MODEL_DIR","source/model");
define("HOOK_DIR","source/hook");
/*视图模版配置*/
$cache_dir="";//模版缓存文件夹
$template_dir="themes/skyshop/index";
$wap_template_dir="themes/skyshop/wap";
$compiled_dir="";//模版编译文件夹
$html_dir="";//生成静态文件夹
$rewrite_on=REWRITE_ON;//是否开启伪静态 0不开 1开启
$smarty_caching=true;//是否开启缓存
$smarty_cache_lifetime=3600;//缓存时间
if(isset($_GET['jsonp'])){
	if(!defined("ALLOW_ORIGIN")){
		define("ALLOW_ORIGIN","*");
	}
	header("Access-Control-Allow-Origin: ".ALLOW_ORIGIN);
}
define("SITEID",1);
define("dir","");
require("./skymvc/skymvc.php");
//用户自定义初始化函数
function userinit(&$base){
 
	 if(isset($_GET['oc_ssid'])){
		 define("OC_SSID",get('oc_ssid','h'));
	 }else{	 
		//设置唯一cookie
		if(isset($_COOKIE['oc_ssid'])){
			$oc_ssid=$_COOKIE['oc_ssid'];
			if($_COOKIE['oc_ssid_expire']<time()+3600*24*5){
				setcookie("oc_ssid",$oc_ssid,time()+3600*24*14,"/",DOMAIN);
				setcookie("oc_ssid_expire",time()+3600*24*14,time()+3600*24*14,"/",DOMAIN);
			}
			define("OC_SSID",$oc_ssid);
		}else{
			$oc_ssid=session_id().time();
			setcookie("oc_ssid",$oc_ssid,time()+3600*24*14,"/",DOMAIN);
			setcookie("oc_ssid_expire",time()+3600*24*14,time()+3600*24*14,"/",DOMAIN);
			define("OC_SSID",$oc_ssid);
		}
	 }
	

	if(isset($_SESSION['ssuser']['userid'])){
		$base->ssuser=$_SESSION['ssuser'];//当前登录用户的信息
		$base->smarty->assign("ssuser",$base->ssuser);
	}else{
		//存在登录码
		if((isset($_COOKIE['authcode']) or get_post('authcode') ) && get('m')!="login"){
			 
			M('login')->CodeLogin();
		}
		//存在QQ空间
		if(isset($_GET['openkey']) && isset($_GET['pf']) && isset($_GET['pfkey']) && get('m')!="qqopen" ){
			header("Location: /index.php?m=qqopen&a=login&openid=".get('openid')."&openkey=".get('openkey')."&pf=".get('pf')."&pfkey=".get('pfkey')."");
			$base->sexit();
		}
	}
 
	//seo信息
	$base->loadModel("seo");
	$base->seo=$base->seo->get(get('m'),get('a'));
	
	$base->smarty->assign("seo",$base->seo);
	//seo信息结束
	$base->smarty->assign("appindex",APPINDEX);
	//风格
	global $wap_template_dir,$template_dir;
	$base->smarty->assign("skins","/".(ISWAP?$wap_template_dir:$template_dir)."/");
	$base->loadConfig("table");
	$base->smarty->assign("config",$base->config_item());
	if(!in_array(get('m'),array('login',"","index","user","gps","sinalogin","qqlogin","qqopen","taobaologin","checkcode","setgps","near","register","kdyuan"))){
		session_write_close();
	}	
	
}


?>