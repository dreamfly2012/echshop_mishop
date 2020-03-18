<?php
class goControl extends skymvc{
	
	function __construct(){
		parent::__construct();
	}

	
	public function onDefault(){
			$url=get_post('url','h');
			$arr=parse_url($url);
			if(preg_match("/(taobao|tmall)\.com$/i",$arr['host'])){
				header("Location: $url");
			}else{
				header("Location: /index.php");
			}
	}
	
	public function onTaoBao(){
		$url=get_post('url','h');
		$arr=parse_url($url);
		if(preg_match("/(taobao|tmall)\.com$/i",$arr['host'])){
			header("Location: $url");
		}else{
			header("Location: /index.php");
		}
	}
	
}

?>