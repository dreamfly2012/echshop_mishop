<?php
require_once("CloudsearchClient.php");
require_once("CloudsearchIndex.php");
require_once("CloudsearchDoc.php");
require_once("CloudsearchSearch.php");
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


// 实例化一个搜索类 search_obj
$search_obj = new CloudsearchSearch($client);
// 指定一个应用用于搜索
$search_obj->addIndex($app_name);
// 指定搜索关键词
$search_obj->setQueryString("index:'xyo2o安装出现了temp/caches不可写提示'");
// 指定返回的搜索结果的格式为json
$search_obj->setFormat("json");
// 执行搜索，获取搜索结果
$json = $search_obj->search();
// 将json类型字符串解码
$result = json_decode($json,true);
print_r($result);