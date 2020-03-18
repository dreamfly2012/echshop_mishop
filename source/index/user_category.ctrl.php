<?php
	/**
	*Author 雷日锦 362606856@qq.com 
	*控制器自动生成
	*/
	if(!defined("ROOT_PATH")) exit("die Access ");
	class user_categoryControl extends skymvc{
		
		public function __construct(){
			parent::__construct();
			$this->loadmodel(array("login","user_category"));
			$this->login->checkLogin();
		}
		
		public function onDefault(){
			$where=" pid=0 AND userid=".$this->login->userid;
			$url="/index.php?m=user_category&a=default";
			$limit=20;
			$start=get("per_page","i");
			$option=array(
				"start"=>intval(get_post('per_page')),
				"limit"=>$limit,
				"order"=>" id DESC",
				"where"=>$where
			);
			$rscount=true;
			$data=$this->user_category->children($option,$rscount);
			$pagelist=$this->pagelist($rscount,$limit,$url);
			$this->smarty->assign(
				array(
					"data"=>$data,
					"pagelist"=>$pagelist,
					"rscount"=>$rscount,
					"url"=>$url,
					"pid_list"=>$this->user_category->id_title(array("where"=>"userid=".$this->login->userid." AND pid=0 " ))
				)
			);
			$this->smarty->display("user_category/index.html");
		}
		
		public function onAdd(){
			$id=get_post("id","i");
			if($id){
				$data=$this->user_category->selectRow(array("where"=>"id=".$id." AND userid=".$this->login->userid));
				
			}
			$this->smarty->assign(array(
				"data"=>$data,
				"pid_list"=>$this->user_category->id_title(array("where"=>"userid=".$this->login->userid." AND pid=0 " )),
				
				
			));
			$this->smarty->display("user_category/add.html");
		}
		
		public function onSave(){
			
			$id=get_post("id","i");
			if($id){
				$data=$this->user_category->selectRow(array("where"=>"id=".$id." AND userid=".$this->login->userid));
				if(empty($data)){
					$this->goall("您无权限",1); 
				}
			}
			$data["userid"]=$this->login->userid;
			$data["orderindex"]=post("orderindex","i");
			$data["title"]=post("title","h");
			$data["dateline"]=time();
			$data["status"]=post("status","i");
			$data['pid']=post('pid','i');
			
			$data['tablename']=post('tablename','h');
			if($id){
				if($id==$data['pid']) $this->goall("不能添加自己为上级分类",1);
				$this->user_category->update($data,"id='$id'");
			}else{
				$this->user_category->insert($data);
			}
			$this->goall("保存成功");
		}
		
		public function onStatus(){
			$id=get_post('id',"i");
			$status=get_post("status","i");
			$data=$this->user_category->selectRow(array("where"=>"id=".$id." AND userid=".$this->login->userid));
			if(empty($data)){
				exit(json_encode(array("error"=>1,"message"=>"您无权限")));
			}
			$this->user_category->update(array("status"=>$status),"id=$id");
			exit(json_encode(array("error"=>0,"message"=>"状态修改成功")));
		}
		
		public function onDelete(){
			$id=get_post('id',"i");
			$data=$this->user_category->selectRow(array("where"=>"id=".$id." AND userid=".$this->login->userid));
			if(empty($data)){
				exit(json_encode(array("error"=>1,"message"=>"您无权限")));
			}
			$this->user_category->delete("id={$id}");
			exit(json_encode(array("error"=>0,"message"=>"删除成功")));
		}
		
		
	}

?>