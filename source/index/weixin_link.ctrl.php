<?php
class weixin_linkControl extends skymvc{
	
	public function __construct(){
		parent::__construct();
		$this->loadModel(array("login","user","weixin","weixin_link","weixin_link_apply"));
		$this->wx=$this->weixin->selectRow("userid=".$this->login->userid);
		if(empty($this->wx)){
			$this->goall("请先申请微信",1,0,"/index.php?m=weixin&a=my");
		}
	}
	
	public function onDefault(){
		
	}
	
	public function onMy(){
		$start=get('per_page','i');
		$limit=20;
		$where=" f_id=".$this->wx['id'];
		$url="/index.php?m=weixin_link&a=my";
		$option=array(
			"where"=>$where,
			"start"=>$start,
			"limit"=>$limit,
			"order"=>" id DESC"
		);
		$rscount=true;
		$data=$this->weixin_link->select($option,$rscount);
		if($data){
			foreach($data as $k=>$v){
				$w=$this->weixin->selectRow("id=".$v['t_id']);
				$v['title']=$w['title'];
				$v['imgurl']=$w['imgurl'];
				$data[$k]=$v;
			}
		}
		$pagelist=$this->pagelist($rscount,$limit,$url);
		$this->smarty->assign(array(
			"data"=>$data,
			"rscount"=>$rscount,
			"pagelist"=>$pagelist 
		));
		$this->smarty->display("weixin_link/my.html");
	}
	
	public function onDelete(){
		$id=get_post('id','i');
		$data=$this->weixin_link->selectRow("id=".$id." AND f_id=".$this->wx['id']."");
		if(empty($data)){
			exit(json_encode(array("error"=>1,"message"=>"您无权限")));
		}
		$this->weixin_link->delete("f_id=".$data['f_id']." AND t_id=".$data['t_id']." ");
		$this->weixin_link->delete("t_id=".$data['f_id']." AND f_id=".$data['t_id']." ");
		
		$this->weixin_link_apply->delete("f_id=".$data['f_id']." AND t_id=".$data['t_id']." ");
		$this->weixin_link_apply->delete("t_id=".$data['f_id']." AND f_id=".$data['t_id']." ");
		//删除
		exit(json_encode(array("error"=>0,"message"=>"操作成功")));
	}
	
}
?>