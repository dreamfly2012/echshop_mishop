<?php
/**
 * PHP SDK for QQ登录 OpenAPI
 *
 * @version 1.2
 * @author connect@qq.com
 * @copyright © 2011, Tencent Corporation. All rights reserved.
 */

/**
 * @brief 本文件包含了OAuth认证过程中会用到的公用方法 
 */



function do_post($url, $data)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); 
    curl_setopt($ch, CURLOPT_POST, TRUE); 
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data); 
    curl_setopt($ch, CURLOPT_URL, $url);
    $ret = curl_exec($ch);

    curl_close($ch);
    return $ret;
}

function get_url_contents($url)
{
    if (ini_get("allow_url_fopen") == "1")
        return file_get_contents($url);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_URL, $url);
    $result =  curl_exec($ch);
    curl_close($ch);

    return $result;
}


function qq_callback()
{
	if($_REQUEST['state'] == $_SESSION['state']) //csrf
	{
		$token_url = "https://graph.qq.com/oauth2.0/token?grant_type=authorization_code&"
			. "client_id=" . APPID. "&redirect_uri=" . urlencode(CALLBACK)
			. "&client_secret=" . APPKEY. "&code=" . $_REQUEST["code"];

		$response = get_url_contents($token_url);
		
		if (strpos($response, "callback") !== false)
		{
			$lpos = strpos($response, "(");
			$rpos = strrpos($response, ")");
			$response  = substr($response, $lpos + 1, $rpos - $lpos -1);
			$msg = json_decode($response);
			if (isset($msg->error))
			{
				echo "<h3>error:</h3>" . $msg->error;
				echo "<h3>msg  :</h3>" . $msg->error_description;
				exit;
			}
		}

		$params = array();
		parse_str($response, $params);
		$_SESSION["access_token"] = $params["access_token"];

	}
	else 
	{
		echo("The state does not match. You may be a victim of CSRF.");
	}
}

function get_openid()
{
	$graph_url = "https://graph.qq.com/oauth2.0/me?access_token=" 
		. $_SESSION['access_token'];

	$str  = get_url_contents($graph_url);
	if (strpos($str, "callback") !== false)
	{
		$lpos = strpos($str, "(");
		$rpos = strrpos($str, ")");
		$str  = substr($str, $lpos + 1, $rpos - $lpos -1);
	}

	$user = json_decode($str);
	if (isset($user->error))
	{
		echo "<h3>error:</h3>" . $user->error;
		echo "<h3>msg  :</h3>" . $user->error_description;
		exit;
	}

	//set openid to session
	$_SESSION["openid"] = $user->openid;
}
//获取用户信息
function get_user_info()
{
    $get_user_info = "https://graph.qq.com/user/get_user_info?"
        . "access_token=" . $_SESSION['access_token']
        . "&oauth_consumer_key=" . APPID
        . "&openid=" . $_SESSION["openid"]
		. "&client_id=".APPID."" 
        . "&format=json";

    $info = get_url_contents($get_user_info);

    $arr = json_decode($info, true);

    return $arr;
}

?>
