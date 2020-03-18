<?php
	class userApiControl extends skymvc{
		
		public function __construct(){
			parent::__construct();
			$this->loadModel("user");
		}
		
		public function select($option=array()){
			$t_d=$this->user->select($option);
			return $t_d;
		}
		
	}
?>