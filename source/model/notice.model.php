<?php
class noticeModel extends model{
	public $base;
	function __construct(&$base){
		parent::__construct($base);
		$this->base=$base;
		$this->table="notice";	
	}
	
	public function sendNotice($data){
		$data['id']=$this->insert($data);
		return $data['id'];
	}
	
	public function deleteNotice($userid,$id){
		$data=$this->selectRow(array("where"=>"id=$id"));
		if($data['userid']==$userid){
			$this->delete("id=$id");
		}
	}
}

?>