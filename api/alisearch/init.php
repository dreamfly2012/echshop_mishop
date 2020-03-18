<?php
if(!defined("ROOT_PATH")){
	define("ROOT_PATH",  str_replace("\\", "/", dirname(dirname(dirname(__FILE__))))."/");
}
require_once(ROOT_PATH."api/alisearch/CloudsearchClient.php");
require_once(ROOT_PATH."api/alisearch/CloudsearchIndex.php");
require_once(ROOT_PATH."api/alisearch/CloudsearchDoc.php");
require_once(ROOT_PATH."api/alisearch/CloudsearchSearch.php");
$access_key = "IrNioAVlNooZgKOM";
$secret = "eLNVY1aR3DVh9c5tTZOCd8dwWrITlW";

//杭州公网API地址：http://opensearch-cn-hangzhou.aliyuncs.com
//北京公网API地址：http://opensearch-cn-beijing.aliyuncs.com (2015年4月初开放)
$host = "http://opensearch-cn-hangzhou.aliyuncs.com";
$key_type = "aliyun";  //固定值，不必修改
$opts = array('host'=>$host);
// 实例化一个client 使用自己的accesskey和Secret替换相关变量
$client = new CloudsearchClient($access_key,$secret,$opts,$key_type);

$app_name = "ksearch"; 