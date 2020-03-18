<?php
require("model.php");
require("userconfig.php");
define("APPINDEX","/index.php");
define("APPADMIN","/admin.php");
define("APPMODULE","/module.php");
define("APPSHOP","/shopadmin.php");
//默认地区一级ID
define("DISTRICTID",0);//福鼎
if(!defined("DEFAULT_USERID")){
	define("DEFAULT_USERID",1);
}
if(!defined("DOMAIN")){
	define("DOMAIN",$_SERVER['HTTP_HOST']);
}
//type-id
define("GROUP_TYPE_ID",1);
define("GROUP_TITLE_TYPE_ID",2);
define("ACTIVITY_TYPE_ID",3);
define("ACTIVITY_TOPIC_TYPE_ID",4);
define("MAGAZINE_TYPE_ID",5);

define("SHOP_TYPE_ID",1);
define("PRODUCT_TYPE_ID",1);
 

define("ALLOWPOST",1);
if(!isset($SITELIST[$_SERVER['HTTP_HOST']]) && !isset($_GET['skins'])){
	//模板
	define("SKINS",'baicha/index');
	//模板
	define("WAPSKINS",'baicha/wap');
}elseif($_GET['skins']){
	//模板
	define("SKINS",$_GET['skins']);
	//模板
	define("WAPSKINS",$_GET['skins']);	
}else{
	//模板
	define("SKINS",$SITELIST[$_SERVER['HTTP_HOST']]['skins']);
	//模板
	define("WAPSKINS",$SITELIST[$_SERVER['HTTP_HOST']]['wapskins']);	
}

