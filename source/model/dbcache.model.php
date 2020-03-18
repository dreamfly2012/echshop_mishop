<?php
class dbcacheModel extends model
{
	public $base;
	function __construct(&$base)
	{
		parent::__construct($base);
		$this->base=$base;
		$this->table="dbcache";
	}
	
	public function clear(){
		self::delete("expire<".time());
	}
	public function set($k,$v,$expire=60){
		
		$row=self::selectRow("k='".$k."'");
		$data=array(
			"k"=>$k,
			"v"=>urlencode(json_encode($v)), 
			"expire"=>time()+$expire
		);
		if(empty($row)){
			self::insert($data);
		}else{
			self::update($data,"id=".$row['id']);
		}
	}
	
	public function get($k){
		$row=self::selectRow("k='".$k."'");
		if($row && $row['expire']>time()){
			return json_decode(urldecode($row['v']),true); 
		}
		return false;
	}
	
}

?>