<?php



/***
**活动类型 请勿更改
**/
function hdtype($type=0){
	$arr=array(
		1=>"新用户满3减2活动"
	);
	if($type){
		return $arr[$type];
	}else{
		return $arr;
	}
}

function pay_type_list($moneypay=1,$un=array()){
	$data=array();
	if(ORDER_UNPAY){
		$data['unpay']="货到付款";
	}
	if(ALIPAY){
		$data['alipay']="支付宝";
	}
	if(MONEYPAY && $moneypay){
		$data['moneypay']='余额支付';
	}
	if(WXPAY){
		$data['wxpay']="微信支付";
	}
	
	if(TENPAY){
		$data['tenpay']="财付通";
	}
	if(!empty($un)){
		$data=array_diff_key($data,$un);
	}
	return $data;
}

function shopopen($start,$end){
	
	$s1=explode(":",$start);
	$s2=explode(":",$end);
	$type=opentype($s1[0],$s1[1],$s2[0],$s2[1]);
	return $type;
	
}
/*获取统计*/
function getStatK(){
	switch($_GET['k']){
				case "ymdian":					
					$k="ymdian";
					break;
				case "koudai":
					$k="koudai";
					break;
				case "shop":
					$k="shop";
					break;
				default:
					$k="";
					break;
	}
	return $k;
}

function loadEditor(){
	if(ISWAP){
		echo '<link href="/plugin/umeditor/themes/default/css/umeditor.css" type="text/css" rel="stylesheet">
		<style>.edui-editor-body img{max-width:95% !important;}</style>
		<script type="text/javascript" src="/plugin/umeditor/umeditor.config.js?v2"></script>';
echo '<script>options={
			initialFrameWidth:"100%",
			toolbar:[],
			elementPathEnabled : false,
			wordCount:false
		}</script>';
echo '<script language="javascript" src="/plugin/umeditor/umeditor.min.js?v2"></script>
<script>var UE=UM;</script>
';
	}else{
		echo '
		<script type="text/javascript" src="/plugin/ueditor/ueditor.config.js?v2"></script>

<script type="text/javascript" src="/plugin/ueditor/ueditor_simple.js"></script>
<script language="javascript" src="/plugin/ueditor/ueditor.all.min.js?v2"></script>';
	}
}

function gps_set($lat,$lng){
	setcookie("ck_latlng",$lat."-".$lng,time()+3600*24*14,"/",DOMAIN);
}

function gps_get(){
	if(isset($_GET['lat'])){
		return array(
			"lat"=>get('lat'),
			"lng"=>get('lng')
		);
	}elseif(isset($_COOKIE['ck_latlng'])){
		$latlng=explode("-",$_COOKIE['ck_latlng']);
		return array(
			"lat"=>$latlng[0],
			"lng"=>$latlng[1]
		);
	}
}

?>