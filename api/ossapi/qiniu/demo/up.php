<?php
require_once(dirname(__FILE__).'./../qiniu/http.php');
require_once(dirname(__FILE__).'./../qiniu/io.php');
require_once(dirname(__FILE__).'./../qiniu/rs.php');

$bucket = 'skycms';
$key = 'up.php';
$file = dirname(__FILE__).'./pfop.php';


$client = new Qiniu_MacHttpClient(null);
$putPolicy = new Qiniu_RS_PutPolicy("$bucket:$key");
$putPolicy->CallbackUrl = 'https://10fd05306325.a.passageway.io';
$putPolicy->CallbackBody = 'key=$(key)&hash=$(etag)';
$upToken = $putPolicy->Token(null);

$putExtra = new Qiniu_PutExtra();
$s = time();
list($ret, $err) = Qiniu_PutFile($upToken, $key, $file, $putExtra);
echo "time elapse:". (time() - $s) . "\n";
echo "====> Qiniu_PutFile result: \n";
if ($err !== null) {
    var_dump($err);
} else {
    var_dump($ret);
}

