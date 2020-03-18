<?php
	/**
	*Author 雷日锦 362606856@qq.com 
	*控制器自动生成
	*/
	if(!defined("ROOT_PATH")) exit("die Access ");
	class optionsControl extends skymvc{
		
		public function __construct(){
			parent::__construct();
			$this->loadmodel(array("options","model"));
		}
		
		public function onDefault(){
			$where=" 1=1 ";
			$url=APPADMIN."?m=options&a=default";
			$limit=20;
			$start=get("per_page","i");
			$pid=get_post('pid','i');
			$where.=" AND pid=".$pid;
			$url.="&pid=".$pid;
			$tablename=get_post('tablename','h'); 
			if($pid){				
				$parent=$this->options->selectRow("id=".$pid);	
				$tablename=$parent['tablename'];		
			}
			$table_list=$this->options->table_list();
			
			if(!$tablename){
				$this->smarty->assign(array(
					"table_list"=>$table_list,
				));
				$this->smarty->display("options/table_list.html");
			}
			$url.="&tablename=".urlencode($tablename);
			$where.=" AND tablename='".$tablename."'";
			$option=array(
				"start"=>intval(get_post('per_page')),
				"limit"=>$limit,
				"order"=>" id DESC",
				"where"=>$where
			);
			$rscount=true;
			$data=$this->options->select($option,$rscount);
			$pagelist=$this->pagelist($rscount,$limit,$url);
			$opt_list=$this->options->children();
			
 
			$this->smarty->assign(
				array(
					"data"=>$data,
					"pagelist"=>$pagelist,
					"rscount"=>$rscount,
					"url"=>$url,
					"parent"=>$parent, 
					"opt_list"=>$opt_list,
					"table_list"=>$table_list,
					"tablename"=>$tablename
				)
			);
			$this->smarty->display("options/index.html");
		}
		
		public function onAdd(){
			$id=get_post("id","i");
			$tablename=get_post('tablename','h'); 
			if($id){
				$data=$this->options->selectRow(array("where"=>"id={$id}"));
				$tablename=$data['tablename'];
			}
			$opt_list=$this->options->children(0,$tablename);
			$table_list=$this->options->table_list();
			$this->smarty->assign(array(
				"tablename"=>$tablename, 
				"data"=>$data,
				"opt_list"=>$opt_list,
				"table_list"=>$table_list,
			));
			$this->smarty->display("options/add.html");
		}
		
		
		
		public function onSave(){
			
			$id=get_post("id","i");
			$data["title"]=post("title","h");
			$data["pid"]=post("pid","i");
			$data["tablename"]=post("tablename","h");
			$data["status"]=post("status","i");
			$data["dateline"]=time();
			
			$data['orderindex']=post('orderindex','i');
			if($id){
				$this->options->update($data,"id='$id'");
			}else{
				$this->options->insert($data);
			}
			$this->goall("保存成功");
		}
		
		
		public function onAddmore(){
			$pid=get_post('pid','i');
			$tablename=get_post('tablename','h');
			if($pid){
				$data=$this->options->selectRow("id=".$pid);
				$opt_list=$this->options->children(0,$data['tablename']);
				$tablename=$data['tablename'];
			}else{
			
			 $opt_list=$this->options->children(0,$tablename);
			}
			 
			$table_list=$this->options->table_list();
			$this->smarty->assign(array(
 				"data"=>$data,
				"tablename"=>$tablename,
				"table_list"=>$table_list,
				"opt_list"=>$opt_list,
 
			));
			$this->smarty->display("options/addmore.html");
		}
		
		public function onSavemore(){
			$titles=post('titles','h');
			$arr=explode("\r\n",$titles);
			if($arr){
				foreach($arr as $v){
					if(empty($v)) continue;
					$data["title"]=$v;
					$data["pid"]=post("pid","i");
					$data["tablename"]=post("tablename","h");
					$data["status"]=post("status","i");
					$data["dateline"]=time();
					
					$data['orderindex']=post('orderindex','i');
					$this->options->insert($data);
				}
			}
			$this->goall("保存成功");
		}
		
		public function onStatus(){
			$id=get_post('id',"i");
			$status=get_post("status","i");
			$this->options->update(array("status"=>$status),"id=$id");
			exit(json_encode(array("error"=>0,"message"=>"状态修改成功")));
		}
		
		public function onDelete(){
			$id=get_post('id',"i");
			$this->options->delete("id={$id}");
			exit(json_encode(array("error"=>0,"message"=>"删除成功")));
		}
		
		 
		
	}

?>