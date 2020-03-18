<?php
class weixin_sucaiControl extends skymvc{
	public $wx;
	
	public function __construct(){
		parent::__construct();
		$this->loadModel(array("weixin","weixin_sucai"));
		$wid=get_post('wid','i');
		$this->wx=$weixin=$this->weixin->selectRow("id=".$wid);
			
		if(empty($weixin) && !in_array(get('a'),array("default"))){
			$this->goall("请选择微信",1);
		}
		$this->smarty->assign("weixin",$weixin);
	}
	
	public function onDefault(){
		$rscount=true;
		$limit=20;
		$where=" pid=0 AND wid=".$this->wx['id'];
		$option=array(
			"where"=>$where,
			"start"=>get('per_page','i'),
			"limit"=>$limit,
			"order"=>" id DESC"
		);
		$data=$this->weixin_sucai->select($option,$rscount);
		$pagelist=$this->pagelist($rscount,$limit,$url);
		$this->smarty->assign(array(
			"data"=>$data,
			"pagelist"=>$pagelist
		));
		$this->smarty->display("weixin_sucai/index.html");
	}
	
	public function onAdd(){
		$id=get_post("id","i");
		$data=$this->weixin_sucai->selectRow("id=".$id);
		if($data){
			$data['child']=$this->weixin_sucai->select(array("where"=>" wid=".$this->wx['id']." AND pid=".$id));
		}
		$this->smarty->assign(array(
			"data"=>$data,
		));
		$this->smarty->display("weixin_sucai/add.html");
	}
	
	public function onAddiframe(){
		$id=get_post("id","i");
		$data=$this->weixin_sucai->selectRow("id=".$id);
		$this->smarty->assign(array(
			"data"=>$data,
			"pid"=>get_post('pid','i')
		));
		$this->smarty->display("weixin_sucai/addiframe.html");
	}
	
	public function onSave(){
		$id=get_post("id","i");
		$data["title"]=get_post("title","h");
		$data["dateline"]=time();
		
		$data["content"]=get_post("content","x");
		$data["status"]=get_post("status","i");
		$data['description']=get_post('description','h');
		$data["imgurl"]=get_post("imgurl","h");
		$data['linkurl']=get_post('linkurl','x');
		
		$pid=get_post("pid","i");
		if($id){
			$row=$this->weixin_sucai->selectRow("id=".$id);
			$pid=$row['pid'];
			$this->weixin_sucai->update($data,"   id=".$id);
		}else{
			$data["pid"]=get_post("pid","i");
						
			$data['wid']=post('wid','i');
			$data['shopid']=$this->wx['shopid'];
			$id=$this->weixin_sucai->insert($data);
		}
		if($pid){
			echo "<script>window.parent.location='admin.php?m=weixin_sucai&a=add&wid=".$this->wx['id']."&id=".$pid."';</script>";
		}else{
			echo "<script>window.parent.location='admin.php?m=weixin_sucai&a=add&wid=".$this->wx['id']."&id=".$id."';</script>";
		}
	}
	
	public function onDelete(){
		$id=get_post("id","i");
		$this->weixin_sucai->delete("id=".$id);
		echo json_encode(array(
			"error"=>0
		));
	}
		
}

?>