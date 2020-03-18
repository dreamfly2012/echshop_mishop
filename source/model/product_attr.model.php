<?php
class product_attrModel extends model{
	public $base;
	public function __construct(&$base){
		parent::__construct($base);
		$this->base=$base;
		$this->table="product_attr";
	}
	
	public function id_list($option){
		$data=$this->select($option);
		if($data){
			foreach($data as $k=>$v){
				$t[$v['id']]=$v;
			}
			return $t;
		}
		return false;
	}
}

?>