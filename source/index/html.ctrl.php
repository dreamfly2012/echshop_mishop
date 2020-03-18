<?php
class htmlControl extends skymvc{
	
	public function __construct(){
		parent::__construct();
		$this->smarty->display("html/".str_replace(ROOT_PATH,"",get('a','h')).".html");	
		
	}
	
	public function onDefault(){
			
	}
}
?>