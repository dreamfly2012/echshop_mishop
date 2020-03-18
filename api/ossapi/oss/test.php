<?php
if(!$_FILES['upimg']){?>
<form method="post" enctype="multipart/form-data">
	<input type="file" name="upimg">
    <button type="submit">上传图片</button>
</form>	
<?php
}else{
	print_r($_FILES['upimg']);
 ?>
<?php
require_once 'sdk.class.php';

$oss_sdk_service = new ALIOSS();

//设置是否打开curl调试模式
$oss_sdk_service->set_debug_mode(FALSE);
function upload_by_file($obj){
	$bucket = 'skycms';
	$object = 'test/ads/asds/a.jpg';	
	$file_path = $_FILES['upimg']['tmp_name'];
	
	$response = $obj->upload_file_by_file($bucket,$object,$file_path);
	_format($response);
}

upload_by_file($oss_sdk_service);

//upload_by_content($oss_sdk_service);

function list_object($obj){
	$bucket = 'tiaoshike';
	$options = array(
		'delimiter' => '/',
		'prefix' => '',
		'max-keys' => 10,
		//'marker' => 'myobject-1330850469.pdf',
	);
	
	$response = $obj->list_object($bucket,$options);	
	_format($response);
}

function upload_by_content($obj){
	$bucket = 'skycms';
	$folder = 'bbb/';
	
	for($index = 100;$index < 201;$index++){	
		
		$object = $folder.'&#26;&#26;_'.$index.'.txt';
		
		$content  = 'uploadfile';
		/**
	    for($i = 1;$i<100;$i++){
			$content .= $i;
		}
		*/
	    
		$upload_file_options = array(
			'content' => $content,
			'length' => strlen($content),
			ALIOSS::OSS_HEADERS => array(
				'Expires' => '2012-10-01 08:00:00',
			),
		);
		
		$response = $obj->upload_file_by_content($bucket,$object,$upload_file_options);	
		echo 'upload file {'.$object.'}'.($response->isOk()?'ok':'fail')."\n";
	}
	//_format($response);
}

function _format($response) {
	echo '|-----------------------Start---------------------------------------------------------------------------------------------------'."\n";
	echo '|-Status:' . $response->status . "\n";
	echo '|-Body:' ."\n"; 
	echo $response->body . "\n";
	echo "|-Header:\n";
	print_r ( $response->header );
	echo '-----------------------End-----------------------------------------------------------------------------------------------------'."\n\n";
}


?>

<?php }?>