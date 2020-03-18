<?php
class yunappControl extends skymvc{
	
	public 	function __construct(){
		parent::__construct();	
	}
	
	public function onInit(){
		
	}
	
	public function onDefault(){
		$access_token=get('access_token');
		
		if(!$access_token){
			header("Location: ".YUNAPPDOMAIN."index.php?m=shopapp&a=GetToken&token=".YUNAPPTOKEN."&pid=".YUNAPPPPID."&domain=".$_SERVER['HTTP_HOST']);
		}else{
			 
			header("Location: ".YUNAPPDOMAIN."index.php?m=shopapp&access_token=".$access_token."&pid=".YUNAPPPPID."&domain=".$_SERVER['HTTP_HOST']);
		}
			
	}
	
	
	
	public function onMenu(){
		
		$this->smarty->display("yunapp/menu.html");
	}
	
}

?>