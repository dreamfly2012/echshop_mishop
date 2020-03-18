<?php
class weixinControl extends skymvc{
	public function __construct(){
		parent::__construct();
		$this->loadModel(array("weixin","login","category"));
	}
	
	public function onDefault(){
		$this->smarty->display("weixin/index.html");
	}
	public function onShow(){
		$id=get('id','i');
		$data=$this->weixin->selectRow("id=".$id);
		if($data){
			$data['imgsdata']=getImgs($data['imgsdata']);
		}
		$this->smarty->assign(array(
			"data"=>$data
		));
		$this->smarty->display("weixin/show.html");
	}
	
	public function onmy(){
			$data=$this->weixin->selectRow(array("where"=>" userid=".$this->login->userid));
			
			$cat_list=$this->category->children(0,MODEL_WEIXIN_ID);	
			$this->smarty->assign(array(
				"data"=>$data,
				"cat_list"=>$cat_list
			));
			$this->smarty->display("weixin/my.html");
		}
		
	public function onSave(){
		$this->loadModel("domain");
		$id=get_post("id","i");
		if($id){
			$row=$this->weixin->selectRow("id='$id'  " );
			if($row['userid']!=$this->login->userid){
				$this->goall("你无权修改",1);
			}
			$dm=$this->domain->selectRow("domain='".post('domain','h')."'");
			if($dm && $dm['userid']!=$this->login->userid){
				$this->goall("域名已经存在",1);
			}
		}
		$data["token"]=post("token","h");
		$data["title"]=post("title","h");
		$data["dateline"]=time();
		$data['status']=post('status','i');
		$data['userid']=$this->login->userid;
		$data['catid']=post('catid','i');
		$data['imgurl']=post('imgurl','h');
		if(!isset($row['domain']) or empty($row['domain'])){
			$data['domain']=post('domain','h');
			if(strlen($data['domain'])<4 or strlen($data['domain'])>20 or preg_match("/\W/is",$data['domain'])){
				$this->goall("微信URL不符合要求",1);
			}
			$this->domain->insert(array(
				"domain"=>$data['domain'],
				"userid"=>$this->login->userid,
				
			));
		}
		$data['logo']=post('logo','h');
		$data['imgsdata']=post('imgsdata','x');
		$data['appid']=post('appid','h');
		$data['appkey']=post('appkey','h');
		if($id){
			$this->weixin->update($data,"id='$id' AND userid=".$this->login->userid);
		}else{
			$this->weixin->insert($data);
		}
		$this->goall("保存成功");
	}
	
	public function onStatus(){
		$id=get_post('id',"i");
		$status=get_post("status","i");
		$this->weixin->update(array("status"=>$status),"id=$id");
		exit(json_encode(array("error"=>0,"message"=>"状态修改成功")));
	}
	
}
?>