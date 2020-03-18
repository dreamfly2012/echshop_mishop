<?php
class goldorder_shaidanapiControl extends skymvc{
	
	public function __construct(){
		parent::__construct();
		$this->loadModel(array("login","user","goldgoods","goldorder","goldorder_shaidan"));
	}
	
	public function onDefault(){
		
	}
	
	public function select($option=array(),&$rscount=false){
		$this->loadModel(array("user"));
		$data=$this->goldorder_shaidan->select($option,$rscount);
		if($data){
			foreach($data as $k=>$v){
				$uids[]=$v['userid'];
				 
			}
			$us=$this->user->getUserByIds($uids);
 
			foreach($data as $k=>$v){
				if(!isset($us[$v['userid']])){
					$u=array(
						"nickname"=>"管理员",
						"user_head"=>"/images/user_head.jpg",
					);
				}else{
					$u=$us[$v['userid']];
				}
			 
				$v['nickname']=$u['nickname'];
				$v['user_head']=$u['user_head'];
				$data[$k]=$v;
			}
		}
		return $data;
	}
	
	public function onList($w="",$limit=10,$orderby=" id DESC"){
		$w=!empty($w)?$w:" status=1  ";
		
		$rscount=false;
		$list=$this->select(array(
			"where"=>$w,
			"start"=>$start,
			"limit"=>$limit,
			"order"=>$orderby
			
		),$rscount);
		return $list;
	}
	
	public function Recommend($sw="",$limit=10,$orderby=" id DESC"){
		$w=" isrecommend=1 " .$sw;
		
		
		$rscount=false;
		$list=$this->select(array(
			"where"=>$w,
			"start"=>$start,
			"limit"=>$limit,
			"order"=>$orderby
			
		),$rscount);
		return $list;
	}
	
}
?>