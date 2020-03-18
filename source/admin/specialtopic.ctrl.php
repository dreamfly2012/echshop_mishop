<?php
	if(!defined("ROOT_PATH")) exit("die Access ");
	class specialtopicControl extends skymvc{
		
		public function __construct(){
			parent::__construct();
			$this->loadmodel(array("specialtopic"));
		}
		
		public function onDefault(){
			$where="";
			$url=APPADMIN."?m=specialtopic&a=default";
			$limit=20;
			$start=get("per_page","i");
			$option=array(
				"start"=>intval(get_post('per_page')),
				"limit"=>$limit,
				"order"=>" id DESC",
				"where"=>$where
			);
			$rscount=true;
			$data=$this->specialtopic->select($option,$rscount);
			$pagelist=$this->pagelist($rscount,$limit,$url);
			$this->smarty->assign(
				array(
					"data"=>$data,
					"pagelist"=>$pagelist,
					"rscount"=>$rscount,
					"url"=>$url
				)
			);
			$this->smarty->display("specialtopic/index.html");
		}
		
		public function onList(){
			$where="";
			$url=APPADMIN."?m=specialtopic&a=default";
			$limit=20;
			$start=get("per_page","i");
			$option=array(
				"start"=>intval(get_post('per_page')),
				"limit"=>$limit,
				"order"=>" id DESC",
				"where"=>$where
			);
			$rscount=true;
			$data=$this->specialtopic->select($option,$rscount);
			$pagelist=$this->pagelist($rscount,$limit,$url);
			$this->smarty->assign(
				array(
					"data"=>$data,
					"pagelist"=>$pagelist,
					"rscount"=>$rscount,
					"url"=>$url
				)
			);
			$this->smarty->display("specialtopic/index.html");
		}
		
		public function onShow(){
			$id=get_post("id","i");
			if($id){
				$data=$this->specialtopic->selectRow(array("where"=>"id={$id}"));
				
			}
			$this->smarty->assign(array(
				"data"=>$data
			));
			$this->smarty->display("specialtopic/show.html");
		}
		public function onAdd(){
			$id=get_post("id","i");
			if($id){
				$data=$this->specialtopic->selectRow(array("where"=>"id={$id}"));
				
			}
			$this->smarty->assign(array(
				"data"=>$data
			));
			$this->smarty->display("specialtopic/add.html");
		}
		
		public function onSave(){
			
			$id=get_post("id","i");
			$data["title"]=post("title","h");
			$data["keywords"]=post("keywords","h");
			$data["info"]=post("info","h");
			$data["status"]=post("status","i");
			$data["dateline"]=time();
			$data["starttime"]=strtotime(post("starttime"));
			$data["endtime"]=strtotime(post("endtime"));
			$data['content']=post('content','x');
			$data['imgurl']=post('imgurl','h');
			$data['data']=post('data','h');
			$data['author']=post('author','h');
			$data['tpl']=post('tpl','h');
			if($id){
				$this->specialtopic->update($data,"id='$id'");
			}else{
				$this->specialtopic->insert($data);
			}
			$this->goall("保存成功");
		}
		
		public function onStatus(){
			$id=get_post('id',"i");
			$status=get_post("status","i");
			$this->specialtopic->update(array("status"=>$status),"id=$id");
			exit(json_encode(array("error"=>0,"message"=>"状态修改成功")));
		}
		
		public function onDelete(){
			$id=get_post('id',"i");
			$this->specialtopic->delete("id={$id}");
			exit(json_encode(array("error"=>0,"message"=>"删除成功")));
		}
		
		
	}

?>