<?php
class brandModel extends model{
	public $base;
	function __construct(&$base){
		parent::__construct($base);
		$this->base=$base;
		$this->table="brand";	
	}
	
	public function id_title($option=array()){
		$data=$this->select($option);
		if($data){
			foreach($data as $k=>$v){
				$t_d[$v['id']]=$v;
			}
		}
		return $t_d;
	}
}
?>