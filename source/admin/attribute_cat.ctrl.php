<?php
	if(!defined("ROOT_PATH")) exit("die Access ");
	class attribute_catControl extends skymvc{
		
		public function __construct(){
			parent::__construct();
			$this->loadmodel(array("attribute_cat","attribute"));
		}
		
		public function onDefault(){
			$where="";
			$url=APPADMIN."?m=attribute_cat&a=default";
			$limit=20;
			$start=get("per_page","i");
			$option=array(
				"start"=>intval(get_post('per_page')),
				"limit"=>$limit,
				"order"=>" cat_id DESC",
				"where"=>$where
			);
			$rscount=true;
			$data=$this->attribute_cat->select($option,$rscount);
			$pagelist=$this->pagelist($rscount,$limit,$url);
			$this->smarty->assign(
				array(
					"data"=>$data,
					"pagelist"=>$pagelist,
					"rscount"=>$rscount,
					"url"=>$url
				)
			);
			$this->smarty->display("attr_cat/index.html");
		}
		
		public function onShow(){
			$cat_id=get_post("cat_id","i");
			if($cat_id){
				$data=$this->attribute_cat->selectRow(array("where"=>"cat_id={$cat_id}"));
				
			}
			$this->smarty->assign(array(
				"data"=>$data
			));
			$this->smarty->display("attr_cat/show.html");
		}
		public function onAdd(){
			$cat_id=get_post("cat_id","i");
			if($cat_id){
				$data=$this->attribute_cat->selectRow(array("where"=>"cat_id={$cat_id}"));
				
			}
			$this->smarty->assign(array(
				"data"=>$data
			));
			$this->smarty->display("attr_cat/add.html");
		}
		
		public function onSave(){
			
			$cat_id=get_post("cat_id","i");
			$data["title"]=post("title","h");
			$data["status"]=post("status","i");
			$data["attr_group"]=post("attr_group","h");

			if($cat_id){
				$this->attribute_cat->update($data,"cat_id='$cat_id'");
			}else{
				$cat_id=$this->attribute_cat->insert($data);
				//生成默认四种属性
				for($i=1;$i<5;$i++){
					$this->attribute->insert(array(
						"title"=>"attr_".$i,
						"cat_id"=>$cat_id,
						"attr_type"=>1,
						"col_name"=>"attr_".$i,
						"orderindex"=>$i
					));
				}
				
			}
			$this->goall("保存成功");
		}
		
		public function onStatus(){
			$cat_id=get_post('cat_id',"i");
			$status=get_post("status","i");
			$this->attribute_cat->update(array("status"=>$status),"cat_id=$cat_id");
			exit(json_encode(array("error"=>0,"message"=>"状态修改成功")));
		}
		
		public function onDelete(){
			$cat_id=get_post('cat_id',"i");
			$this->attribute_cat->delete("cat_id={$cat_id}");
			exit(json_encode(array("error"=>0,"message"=>"删除成功")));
		}
		
		
	}

?>