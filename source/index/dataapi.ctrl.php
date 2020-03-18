<?php
class dataapiControl extends skymvc{
	
	public function __construct(){
		parent::__construct();
		$this->loadModel(array("dataapi"));
	}
	
	public function getWord($word){
		$word=addslashes($word);
		return $this->dataapi->selectRow(array("where"=>" word='".$word."' AND status=2 " ));
	}
}
?>