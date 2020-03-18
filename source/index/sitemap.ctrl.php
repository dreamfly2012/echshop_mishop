<?php
class sitemapControl extends skymvc{
	function __construct(){
		parent::__construct();
		 
	}
	
	public function ondefault(){
		$this->smarty->display("sitemap/index.html");
	}
	
	public function onWrite(){
			set_time_limit(0);
			session_write_close();
			$data=array();
			$option=array(
				"fields"=>"id,title,dateline",
				"where"=>"status=2",
				"limit"=>1000,
				"order"=>"id DESC"
			);
			$mm=explode("|",get('mm'));
			foreach($mm as $m){
				$m=trim($m);
				if($m){
					$data[]=M($m)->select($option);
				}
			}
			$xml='<?xml version="1.0" encoding="UTF-8"?>'."\n <urlset>\n";
			$str="";
			foreach($data as $t){
			foreach($t as $v){
				 $xml.="<url>\n\r<loc>http://".$_SERVER['HTTP_HOST']."/".R("/index.php?m=show&id=".$v['id'])."</loc></url>\n\r"; 
				 $str.="http://".$_SERVER['HTTP_HOST']."/".R("/index.php?m=show&id=".$v['id'])."\r\n";
			}
			}
			$xml.="</urlset>";
			file_put_contents("sitemap.txt",$str);
			file_put_contents("sitemap.xml",$xml);
			echo "生成成功";
		 
	}

}
?>