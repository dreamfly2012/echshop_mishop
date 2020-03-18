<?php
	/**
	*Author 雷日锦 362606856@qq.com 
	*控制器自动生成
	*/
	if(!defined("ROOT_PATH")) exit("die Access ");
	class goldorder_shaidanControl extends skymvc{
		
		public function __construct(){
			parent::__construct();
			$this->loadmodel(array("goldorder_shaidan"));
		}
		
		public function onDefault(){
			$where="";
			$url=APPADMIN."?m=goldorder_shaidan&a=default";
			$limit=20;
			$start=get("per_page","i");
			$option=array(
				"start"=>intval(get_post('per_page')),
				"limit"=>$limit,
				"order"=>" id DESC",
				"where"=>$where
			);
			$rscount=true;
			$data=$this->goldorder_shaidan->select($option,$rscount);
			$pagelist=$this->pagelist($rscount,$limit,$url);
			$this->smarty->assign(
				array(
					"data"=>$data,
					"pagelist"=>$pagelist,
					"rscount"=>$rscount,
					"url"=>$url
				)
			);
			$this->smarty->display("goldorder_shaidan/index.html");
		}
		
		public function onAdd(){
			$id=get_post("id","i");
			if($id){
				$data=$this->goldorder_shaidan->selectRow(array("where"=>"id={$id}"));
				
			}
			$this->smarty->assign(array(
				"data"=>$data
			));
			$this->smarty->display("goldorder_shaidan/add.html");
		}
		
		public function onSave(){
			
			$id=get_post("id","i");
			
			
			$data["title"]=post("title","h");
			$data["description"]=post("description","h");
			$data["content"]=post("content","h");
			
			$data["imgurl"]=post("imgurl","h");
			$data["comment_num"]=post("comment_num","i");
			$data["view_num"]=post("view_num","i");
			

			if($id){
				$this->goldorder_shaidan->update($data,"id='$id'");
			}else{
				$this->goldorder_shaidan->insert($data);
			}
			$this->goall("保存成功");
		}
		
		public function onStatus(){
			$id=get_post('id',"i");
			$status=get_post("status","i");
			$this->goldorder_shaidan->update(array("status"=>$status),"id=$id");
			$this->sexit(json_encode(array("error"=>0,"message"=>"状态修改成功")));
		}
		
		
		public function onRecommend(){
			$id=get_post('id',"i");
			$isrecommend=get_post("isrecommend","i");
			$this->goldorder_shaidan->update(array("isrecommend"=>1),"id=$id");
			$this->sexit(json_encode(array("error"=>0,"message"=>"状态修改成功")));
		}
		
		
		public function onDelete(){
			$id=get_post('id',"i");
			$this->goldorder_shaidan->delete("id=".$id);
			$this->sexit(json_encode(array("error"=>0,"message"=>"删除成功")));
		}
		
		
	}

?>