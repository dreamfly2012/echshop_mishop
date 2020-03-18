<?php
$rewriterule=array(
    //m a 3个参数
    array("/<a href=[\"\'][\/]?index\.php\?m=(\w+)&a=(\w+)&(\w+)=(\w+)&(\w+)=(\w+)&(\w+)=(\w+)[\"\']/iUs",'<a href="/\1/\2/\3-\4-\5-\6-\7-\8.html"'),
    //m a 2个参数
    array("/<a href=[\"\'][\/]?index\.php\?m=(\w+)&a=(\w+)&(\w+)=(\w+)&(\w+)=(\w+)[\"\']/iUs",'<a href="/\1/\2/\3-\4-\5-\6.html"'),
    //m a 1个参数
    array("/<a href=[\"\'][\/]?index\.php\?m=(\w+)&a=(\w+)&(\w+)=(\w+)[\"\']/iUs",'<a href="/\1/\2/\3-\4.html"'),   
    //m a规则 
    array("/<a href=[\"\'][\/]?index\.php\?m=(\w+)&a=(\w+)[\"\']/iUs",'<a href="/\1/\2.html"'),
    //m首页规则
    array("/<a href=[\"\'][\/]?index\.php\?m=(\w+)[\"\']/iUs",'<a href="/\1.html"'),
    //首页规则  
    array("/<a href=[\"\'][\/]?index.php[\"\']/iUs",'<a href="/index.html"')  
);

?>