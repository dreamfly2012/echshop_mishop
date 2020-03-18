<?php
class newVerSionControl extends skymvc{
	
	public function __construct(){
		parent::__construct();
	}
	public function onDefault(){
		$this->loadConfig("version");
		echo VERSION_NUM;
	}
}
?>