<?php
	/**
	*Author 雷日锦 362606856@qq.com 
	*控制器自动生成
	*/
	if(!defined("ROOT_PATH")) exit("die Access ");
	class districtControl extends skymvc{
		
		public function __construct(){
			parent::__construct();
			$this->loadmodel(array("district"));
		}
		
		public function onDefault(){
			$upid=get_post('upid','i');
			$where=" upid=".$upid;
			$url=APPADMIN."?m=district&upid=".$upid;
			if($upid){
				$row=$this->district->selectRow(array("where"=>"id=".$upid));
				$parent=$this->district->selectRow("id=".$row['upid']);
			}
			$limit=40;
			$start=get("per_page","i");
			$option=array(
				"start"=>intval(get_post('per_page')),
				"limit"=>$limit,
				"order"=>" id ASC",
				"where"=>$where
			);
			$rscount=true;
			$data=$this->district->select($option,$rscount);
			$pagelist=$this->pagelist($rscount,$limit,$url);
			$this->smarty->assign(
				array(
					"data"=>$data,
					"pagelist"=>$pagelist,
					"rscount"=>$rscount,
					"url"=>$url,
					"upname"=>$row['name'],
					"parent"=>$parent
				)
			);
			$this->smarty->display("district/index.html");
		}
		
		public function onAdd(){
			$id=get_post("id","i");
			if($id){
				$data=$this->district->selectRow(array("where"=>"id={$id}"));
				
			}
			$this->smarty->assign(array(
				"data"=>$data
			));
			$this->smarty->display("district/add.html");
		}
		
		public function onSave(){
			$id=get_post("id","i");
			$data=$this->district->postData();
			if($id){
				$this->district->update($data,"id='$id'");
			}else{
				$this->district->insert($data);
			}
			$this->goall("保存成功");
		}
		
		public function onStatus(){
			$id=get_post('id',"i");
			$status=get_post("status","i");
			$this->district->update(array("status"=>$status),"id=$id");
			$this->sexit(json_encode(array("error"=>0,"message"=>"状态修改成功")));
		}
		
		
		public function onAddChild(){
			$upid=get_post('upid','i');
			$row=$this->district->selectRow("id=".$upid);
			if(!$row){
				$this->goAll("出错了",1);
			}
			$this->smarty->assign(array(
				"parent"=>$row
			));
			$this->smarty->display("district/addchild.html");
		}
		
		public function onSaveChild(){
			$upid=get_post('upid','i');
			$row=$this->district->selectRow("id=".$upid);
			if(!$row){
				$this->goAll("出错了",1);
			}
			$content=post('content','h');
			$arr=explode("\r\n",$content);
			if($arr){
				foreach($arr as $v){
					$v=trim($v);
					if($v){
						$this->district->insert(array(
							"name"=>$v,
							"level"=>$row['level']+1,
							"upid"=>$row['id'],
						));
					}
				}
			}
			$this->goall("保存成功");
		}
		
		public function onDelete(){
			$id=get_post('id',"i");
			$this->district->delete("id=".$id);
			$row=$this->district->selectRow("upid=".$id);
			if($row){
				$this->goAll("删除失败,下级地区要先删除",1);
			}
			$this->sexit(json_encode(array("error"=>0,"message"=>"删除成功")));
		}
		
		
	}

?>