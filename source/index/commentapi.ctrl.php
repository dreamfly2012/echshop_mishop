<?php
class commentapiControl extends skymvc{
	
	public function __construct(){
		parent::__construct();
		$this->loadModel(array("user","comment","product"));
	}
	
	public function product($object_id,$option=array(),$rscount=false,$cache=0,$expire=60){
		if($option['where']){
			$option['where']= " object_id=$object_id AND type_id=".PRODUCT_TYPE_ID." AND ".$option['where'];
		}else{
			$option['where']= " object_id=$object_id AND type_id=".PRODUCT_TYPE_ID." ";
		}
		if(!isset($option['limit'])){
			$option['limit']=10;	
		}
		if(!isset($option['order'])){
			$option['order']=" id DESC";
		}
		$data=$this->comment->select($option,$rscount,$cache,$expire);
		if($data){
			foreach($data as $k=>$v){
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
	
	/*评论列表*/
	public function commentlist($object_id,$option=array(),&$rscount=false){
		$data=$this->comment->select($option,$rscount);
		if($data){
			foreach($data as $k=>$v){
				$u=$this->user->selectRow(array("where"=>array("userid"=>$v['userid'])));
				$v['nickname']=$u['nickname'];
				$v['user_head']=$u['user_head'];
				$v['author']=$u;
				$data[$k]=$v;
			}
		}
		return $data;
	}
	
	/*获取单个评论*/
	public function getComment($object_id,$id){;
		$data=$this->comment->selectRow(array("where"=>array("id"=>$id)));
		if($data){
			$u=$this->user->selectRow(array("where"=>array("userid"=>$data['userid'])));
			$data['nickname']=$u['nickname'];
			$data['userpic']=$u['userpic'];
			return $data;
		}
	}
	
	public function onGetParent($object_id=0,$id=0){
		$object_id=get_post('object_id','i');
		$id=get_post('id','i');
		$data=$this->comment->selectRow(array("where"=>array("id"=>$id)));
		if($data){
			$u=$this->user->selectRow(array("where"=>array("userid"=>$data['userid'])));
			$data['nickname']=$u['nickname'];
			$data['userpic']=$u['userpic'];
		}
		$this->smarty->assign("data",$data);
		$data=$this->smarty->fetch("comment/getparent.html");
		echo strToJs($data);
	}
	
	
}

?>