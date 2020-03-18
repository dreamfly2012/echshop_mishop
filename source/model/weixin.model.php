<?php
class weixinModel extends model{
	public $base;
	public function __construct(&$base){
		parent::__construct($base);
		$this->base=$base;
		$this->table="weixin";
	}
	
	public function id_list($option=array(),&$rscount=false){
		$data=$this->select($option,$rscount);
		if($data){
			foreach($data as $k=>$v){
				$t_d[$v['id']]=$v;
			}
			return $t_d;
		}
	}
	
}

?>