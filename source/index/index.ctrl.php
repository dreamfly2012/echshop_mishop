<?php
class indexControl extends skymvc
{
    function __construct()
    {
        parent::__construct();
         
    }
    
	public function onDefault()
    {
		 
		$this->smarty->display("index.html");
		
    }
}

?>