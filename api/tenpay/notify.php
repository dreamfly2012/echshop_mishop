<?php
//file_put_contents("notify.txt","测试通知".serialize($_GET));
$f=fopen("notify_".date("Ym").".txt","w+");
$c=file_get_contents("http://".$_SERVER['HTTP_HOST']."/index.php?m=recharge_tenpay&a=NotifyUrl&".http_build_query($_GET));
fwrite($f,$c);
fclose($f);
?>