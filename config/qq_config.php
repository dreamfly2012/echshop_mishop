<?php
/*
先到http://connect.qq.com
*/
define("APPID","");
define("APPKEY","");
define("CALLBACK","http://{$_SERVER['HTTP_HOST']}/index.php?m=qqlogin&a=callback");
define("SCOPE","get_user_info,add_share,list_album,add_album,upload_pic,add_topic,add_one_blog,add_weibo");

?>