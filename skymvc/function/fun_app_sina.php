<?php
if(!defined("STO")){
	define("STO","skymvc");
}

/*设置mem变量*/
function mem_set($k,$v){
	$m=memcache_init();
    $m->set($k,$v);
}
/*获取mem变量*/
function mem_get($k)
{
	$m=memcache_init();
    return $m->get($k);
}
/*mem保存文件*/
function mem_file_put_contents($file,$content){
  mem_set("file_".str_replace(array("/","."),"_",$file),$content);
}

/*mem获取文件内容*/
function mem_file_get_contents($file){
	return mem_get("file_".str_replace(array("/","."),"_",$file));
}

/*smarty保存文件*/
function smarty_file_put_contents($file,$content){
  mem_set("file_".str_replace(array("/","."),"_",$file),array("mtime"=>time(),"content"=>$content));
}



/*smarty获取文件内容*/
function smarty_file_get_contents($file){
	return mem_get("file_".str_replace(array("/","."),"_",$file));
}
/*保存文件*/
function app_file_put_contents($file,$content){
	$s = new SaeStorage();
    $s->write(STO,$file,$content);
    return $s->getUrl(STO,$file);
}
/*获取文件*/
function app_file_get_contents($file){
	return file_get_contents($file);
}

/*删除文件*/
function app_file_delete($file){
	$sto = new SaeStorage();
        preg_match("/http:\/\/[\w]+-([\w]+)\./i",$file,$d);
        $domain=$d[1];
        preg_match("/\.com\/(.*)/i",$file,$f);
        $file=$f[1];
        $sto->delete($domain,$file);
}
 
?>