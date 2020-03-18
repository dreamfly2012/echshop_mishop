<?php
/**
	*远程上传接口
	*$arr=>array(
		*$to 目标位置,
		*$from 本地文件位置,
		*OSS_BUCKET=>"",其他
	);
*/

function oss_upload_file($arr=array()){
	switch(UPLOAD_OSS){
			case "aliyun":
				include_once("oss/sdk.class.php");
				$oss=new ALIOSS();
				return $oss->upload_file_by_file($arr['bucket'],$arr['to'],$arr['from']);
				break;
			case "qiniu":
			
				break;
			case "upyun":
			
				break;
			default:
				break;
		}
	}
?>