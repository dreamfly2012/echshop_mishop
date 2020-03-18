<?php
	/**
	*Author 雷日锦 362606856@qq.com 
	*控制器自动生成
	*/
	if(!defined("ROOT_PATH")) exit("die Access ");
	class brandControl extends skymvc{
		
		public function __construct(){
			parent::__construct();
			$this->loadmodel(array("brand"));
		}
		
		public function onDefault(){
			$where="";
			$url=APPADMIN."?m=brand&a=default";
			$limit=20;
			$start=get("per_page","i");
			$option=array(
				"start"=>intval(get_post('per_page')),
				"limit"=>$limit,
				"order"=>" id DESC",
				"where"=>$where
			);
			$rscount=true;
			$data=$this->brand->select($option,$rscount);
			$pagelist=$this->pagelist($rscount,$limit,$url);
			$this->smarty->assign(
				array(
					"data"=>$data,
					"pagelist"=>$pagelist,
					"rscount"=>$rscount,
					"url"=>$url
				)
			);
			$this->smarty->display("brand/index.html");
		}
		
		public function onAdd(){
			$id=get_post("id","i");
			if($id){
				$data=$this->brand->selectRow(array("where"=>"id={$id}"));
				
			}
			$this->smarty->assign(array(
				"data"=>$data
			));
			$this->smarty->display("brand/add.html");
		}
		
		public function onSave(){
			
			$id=get_post("id","i");
			$data["title"]=post("title","h");
			$data["logo"]=post("logo","h");
			$data["dateline"]=time();
			$data["status"]=post("status","i");
			$data['isindex']=post('isindex','i');
			$data["shopid"]=post("shopid","i");
			$data["orderindex"]=post("orderindex","i");
			$data['description']=post('description','h');
			$data['content']=post('content','x');
			if($id){
				$this->brand->update($data,"id='$id'");
			}else{
				$this->brand->insert($data);
			}
			$this->goall("保存成功");
		}
		
		public function onStatus(){
			$id=get_post('id',"i");
			$status=get_post("status","i");
			$this->brand->update(array("status"=>$status),"id=$id");
			$this->sexit(json_encode(array("error"=>0,"message"=>"状态修改成功")));
		}
		
		public function onDelete(){
			$id=get_post('id',"i");
			$this->brand->delete("id=".$id);
			$this->sexit(json_encode(array("error"=>0,"message"=>"删除成功")));
		}
		
		
	}

?>