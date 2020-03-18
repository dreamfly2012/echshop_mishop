<?php
	if(!defined("ROOT_PATH")) exit("die Access ");
	class weixinControl extends skymvc{
		
		public function __construct(){
			parent::__construct();
			$this->loadmodel(array("weixin","category"));
		}
		
		public function onDefault(){
			$where="";
			$url=APPADMIN."?m=weixin&a=default";
			$limit=20;
			$start=get("per_page","i");
			$option=array(
				"start"=>intval(get_post('per_page')),
				"limit"=>$limit,
				"order"=>" id DESC",
				"where"=>$where
			);
			$rscount=true;
			$data=$this->weixin->select($option,$rscount);
			$pagelist=$this->pagelist($rscount,$limit,$url);
			$cat_list=$this->category->children(0,MODEL_WEIXIN_ID);;	
			$this->smarty->assign(
				array(
					"data"=>$data,
					"pagelist"=>$pagelist,
					"rscount"=>$rscount,
					"cat_list"=>$cat_list,
					"url"=>$url
				)
			);
			$this->smarty->display("weixin/index.html");
		}
		
		public function onList(){
			$where="";
			$url="/index.php?m=weixin&a=default";
			$limit=20;
			$start=get("per_page","i");
			$option=array(
				"start"=>intval(get_post('per_page')),
				"limit"=>$limit,
				"order"=>" id DESC",
				"where"=>$where
			);
			$rscount=true;
			$data=$this->weixin->select($option,$rscount);
			$pagelist=$this->pagelist($rscount,$limit,$url);
			$this->smarty->assign(
				array(
					"data"=>$data,
					"pagelist"=>$pagelist,
					"rscount"=>$rscount,
					"url"=>$url
				)
			);
			$this->smarty->display("weixin/index.html");
		}
		
		public function onShow(){
			$id=get_post("id","i");
			if($id){
				$data=$this->weixin->selectRow(array("where"=>"id={$id}"));
				
			}
			$this->smarty->assign(array(
				"data"=>$data
			));
			$this->smarty->display("weixin/show.html");
		}
		public function onAdd(){
			$id=get_post("id","i");
			if($id){
				$data=$this->weixin->selectRow(array("where"=>"id={$id}"));
				
			}
			$cat_list=$this->category->children(0,MODEL_WEIXIN_ID);
			$this->smarty->assign(array(
				"data"=>$data,
				"cat_list"=>$cat_list,
			));
			$this->smarty->display("weixin/add.html");
		}
		
		public function onSave(){
			
			$id=get_post("id","i");
			$data=M('weixin')->postData();
			$data["token"]=post("token","h");
			$data["title"]=post("title","h");
			$data["dateline"]=time();
			$data['status']=post('status','i');
			$data['catid']=post('catid','i');
			$data['imgurl']=post('imgurl','h');
			$data['logo']=post('logo','h');
			$data['imgurl']=post('imgurl','h');
			$data['imgsdata']=post('imgsdata','x');
			$data['appid']=post('appid','h');
			$data['appkey']=post('appkey','h');
			$data['ysid']=post('ysid','h');
			$data['wx_username']=post('wx_username','h');
			$data['wx_pwd']=post('wx_pwd','h');
			if($id){
				$this->weixin->update($data,"id='$id'");
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
		
		public function onDelete(){
			$id=get_post('id',"i");
			$this->weixin->delete("id={$id}");
			exit(json_encode(array("error"=>0,"message"=>"删除成功")));
		}
		
		public function onLeft(){
			$id=get_post('id','i');
			$weixin=$this->weixin->selectRow("id=".$id);
			$this->smarty->assign("weixin",$weixin);
			$this->smarty->display("weixin/left.html");
		}
		
		
	}

?>