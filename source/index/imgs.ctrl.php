<?php
	/**
	*Author 雷日锦 362606856@qq.com 
	*控制器自动生成
	*/
	if(!defined("ROOT_PATH")) exit("die Access ");
	class imgsControl extends skymvc{
		
		public function __construct(){
			parent::__construct();
			$this->loadmodel(array("imgs"));
		}
		
		public function onDefault(){
			$where="";
			$url="/index.php?m=imgs&a=default";
			$limit=20;
			$start=get("per_page","i");
			$option=array(
				"start"=>intval(get_post('per_page')),
				"limit"=>$limit,
				"order"=>" id DESC",
				"where"=>$where
			);
			$rscount=true;
			$data=$this->imgs->select($option,$rscount);
			$pagelist=$this->pagelist($rscount,$limit,$url);
			$this->smarty->assign(
				array(
					"data"=>$data,
					"pagelist"=>$pagelist,
					"rscount"=>$rscount,
					"url"=>$url
				)
			);
			$this->smarty->display("imgs/index.html");
		}
		
		public function onAdd(){
			$id=get_post("id","i");
			if($id){
				$data=$this->imgs->selectRow(array("where"=>"id={$id}"));
				
			}
			$this->smarty->assign(array(
				"data"=>$data
			));
			$this->smarty->display("imgs/add.html");
		}
		
		public function onSave(){
			
			$id=get_post("id","i");
			
			if(isset($_POST['imgurl'])){
			$data["imgurl"]=post("imgurl","h");
			}
			$data["title"]=post("title","h");
			
			if($id){
				$this->imgs->update($data,"id='$id'");
			}else{
				$data['object_id']=get_post('object_id','i');
				$data['tablename']=get_post('tablename','h');
				$id=$this->imgs->insert($data);
			}
			$data=$this->imgs->selectRow("id=".$id);
			exit(json_encode(array("error"=>0,"data"=>$data,"message"=>"保存成功")));
		}
		
		public function onList(){
			$tablename=get('tablename','h');
			$where=" tablename='".$tablename."' ";
			$object_id=get('object_id','i');
			if($object_id){
				$where.=" AND object_id=".$object_id;
			}
			$option=array(
				"where"=>$where,
				"order"=>"orderindex asc,id DESC"
			);
			$data=$this->imgs->select($option);
			if($data){
				foreach($data as $k=>$v){
					$v['baseimg']=$v['imgurl'];
					$v['imgurl']=images_site($v['imgurl']);
					
					$data[$k]=$v;
				}
			}
			exit(sky_json_encode($data));
			
		}
		
		public function onOrderindex(){
			$ids=get_post('ids','i');
			if($ids){
				foreach($ids as $k=>$id){
					$this->imgs->update(array("orderindex"=>$k+1),"id=".intval($id));
				}
			}
		}
		
		public function onDelete(){
			$id=get_post('id',"i");
			$row=$this->imgs->selectRow("id=".$id);
			$this->imgs->delete("id=".$id);
			$this->sexit(json_encode(array("error"=>0,"message"=>"删除成功")));
		}
		
		
	}

?>