<?php
class weixin_link_applyControl extends skymvc{
	public $wx;
	public function __construct(){
		parent::__construct();
		$this->loadModel(array("login","user","weixin","weixin_link","weixin_link_apply"));
		$this->login->checkLogin();
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
		$url="/index.php?m=weixin_link_apply&a=my";
		$option=array(
			"where"=>$where,
			"start"=>$start,
			"limit"=>$limit,
			"order"=>" id DESC"
		);
		$rscount=true;
		$data=$this->weixin_link_apply->select($option,$rscount);
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
		$this->smarty->display("weixin_link_apply/my.html");
	}
	
	public function onTome(){
		$start=get('per_page','i');
		$limit=20;
		$where=" t_id=".$this->wx['id'];
		$url="/index.php?m=weixin_link_apply&a=my";
		$option=array(
			"where"=>$where,
			"start"=>$start,
			"limit"=>$limit,
			"order"=>" id DESC"
		);
		$rscount=true;
		$data=$this->weixin_link_apply->select($option,$rscount);
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
		$this->smarty->display("weixin_link_apply/tome.html");
	}
	
	public function onapply(){
		$id=get_post('id','i');
		$w=$this->weixin->selectRow("id=$id");
		if(empty($w)){
			exit(json_encode(array("error"=>1,"message"=>"微信不存在")));
		}
		if($id==$this->wx['id']){
			exit(json_encode(array("error"=>1,"message"=>"不能申请自己")));
		}
		if($this->weixin_link_apply->selectRow("f_id=".$this->wx['id']." AND t_id=".$id."")){
			exit(json_encode(array("error"=>1,"message"=>"已经申请过了")));
		}
		$this->weixin_link_apply->insert(array(
			"f_id"=>$this->wx['id'],
			"t_id"=>$w['id'],
			"dateline"=>time(),
			"status"=>0,
		));
		exit(json_encode(array("error"=>0,"message"=>"微信友情链接申请成，请等待对方确认！"))); 
	}
	
	public function onPass(){
		$id=get('id','i');
		$data=$this->weixin_link_apply->selectRow(" id=".$id." AND t_id=".$this->wx['id']." ");
		if(empty($data)){
			exit(json_encode(array("error"=>1,"message"=>"您无权限")));
		}
		//检测是否存在友情链接
		if($this->weixin_link->selectRow(" f_id=".$data['f_id']." AND t_id=".$this->wx['id']." ")){
			exit(json_encode(array("error"=>1,"message"=>"友情链接已经存在")));
		}
		$this->weixin_link->insert(
			array(
				"t_id"=>$data['t_id'],
				"f_id"=>$data['f_id'],
				"dateline"=>time()
			)
		);
		
		$this->weixin_link->insert(
			array(
				"t_id"=>$data['f_id'],
				"f_id"=>$data['t_id'],
				"dateline"=>time()
			)
		);
		$this->weixin_link_apply->update(array("status"=>1),"id=".$data['id']);
		exit(json_encode(array("error"=>0,"message"=>"操作成功")));
	}
	
	public function onForbid(){
		$id=get('id','i');
		$data=$this->weixin_link_apply->selectRow(" id=".$id." AND t_id=".$this->wx['id']." ");
		if(empty($data)){
			exit(json_encode(array("error"=>1,"message"=>"您无权限")));
		}
		$this->weixin_link_apply->update(array("status"=>2),"id=".$data['id']);
		exit(json_encode(array("error"=>0,"message"=>"操作成功")));
	}
	
	public function onDelete(){
		$id=get('id','i');
		$data=$this->weixin_link_apply->selectRow(" id=".$id." AND f_id=".$this->wx['id']." ");
		if(empty($data)){
			exit(json_encode(array("error"=>1,"message"=>"您无权限")));
		}
		$this->weixin_link_apply->delete("id=".$data['id']);
		exit(json_encode(array("error"=>0,"message"=>"操作成功")));
	}
	
}
?>