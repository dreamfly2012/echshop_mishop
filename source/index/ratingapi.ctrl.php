<?php
class ratingapiControl extends skymvc{
	
	public function __construct(){
		parent::__construct();
		$this->loadModel(array("rating","user"));
	}
	
	public function onDefault(){
		
	}
	
	public function select($option=array(),&$rscount=false){
		$data=$this->rating->select($option,$rscount);
		if($data){
			foreach($data as $v){
				$uids[]=$v['userid'];
			}
			if($uids){
				$us=$this->user->getUserByIds($uids);
				foreach($data as $k=>$v){
					$v['nickname']=$us[$v['userid']]['nickname'];	
					$v['user_head']=$us[$v['userid']]['user_head'];
					$data[$k]=$v;	
				}
			}
		}
		return $data;
	}
}
?>