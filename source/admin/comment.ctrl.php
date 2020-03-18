<?php
class commentControl extends skymvc{
	
	public function __construct(){
		parent::__construct();
		$this->loadModel(array("comment","comment_all","user"));
		
	}
	
	public function onDefault(){
		$rscount=true;
		$limit=20;
		$start=get('per_page','i');
		
		$option=array(
			"start"=>$start,
			"limit"=>$limit,
			"order"=>"id DESC",
			"where"=>$where,
		);
		$comment_list=$this->comment->select($option,$rscount);
		if($comment_list){
			foreach($comment_list as $v){
				$uids[]=$v['userid'];
			}
			$us=$this->user->getUserByIds($uids);
			foreach($comment_list as $k=>$v){
				$v['nickname']=$us[$v['userid']]['nickname'];
				$comment_list[$k]=$v;
			}
		}
		$pagelist=$this->pagelist($rscount,$limit,APPADMIN."?m=comment");
		$this->smarty->assign(
			array(
				"comment_list"=>$comment_list,
				"rscount"=>$rscount,
				"pagelist"=>$pagelist,
			)
		);
		$this->smarty->display("comment/index.html");
	}
	
	public function onDelete(){
		$id=get_post('id');
		
		$this->comment->delete($id,array("id"=>$id));
		echo json_encode(array("error"=>0,"message"=>$this->lang['delete_success']));
	}
	
	public function onStatus(){
		$id=get('id','i');
		$status=get('status','i');
		
		 
		$this->comment->update(array("status"=>$status),"id=$id");
		echo json_encode(array("error"=>0,"message"=>$this->lang['edit_success']));
	}
	
	
	
}

?>