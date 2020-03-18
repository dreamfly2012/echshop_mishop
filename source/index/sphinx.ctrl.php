<?php
class sphinxControl extends skymvc{
	function __construct(){
		parent::__construct();
	}
	
	public function onDefault(){
		$this->loadClass("sphinxapi");
		$cl = new SphinxClient ();
		$cl->SetServer ( '127.0.0.1', 9312);
		$cl->SetConnectTimeout ( 3 );
		$cl->SetArrayResult ( true );
		$cl->SetMatchMode ( SPH_MATCH_ANY);
		$res = $cl->Query ( '网络搜索', "*" );
		 
		print_r($res);
	}
}
?>