<?php
class indexControl extends skymvc
{
	function __construct()
	{
		parent::__construct();
	}
	
	function indexControl()
	{
		parent::__construct();//父类厨师话
		$this->loadModel("index");
	}
	function onDefault()
	{		
		if(!$this->ssadmin){
			$this->gourl(APPADMIN."?m=login");
		} else{
			$this->gourl(APPADMIN."?m=iframe");
		}
	}
}

?>