<?php
class categoryControl extends skymvc{
	
	function __construct(){
		parent::__construct();
		$this->loadmodel(array("category","model","attribute_cat"));
		
	}
	
	public function onDefault(){
		$pid=get('pid','i');
		$where= " status<99 AND pid=$pid  ";
		$model_id=max(1,get('model_id','i'));
		 
		$url=APPADMIN."?m=category&model_id=".$model_id;
		if($model_id){
			$where.=" AND model_id=".$model_id;
			$url.="&model_id=".$model_id;	
		}
		$start=get('per_page','i');
		$option=array(
			"where"=>$where,
			"order"=>" orderindex ASC",
			"start"=>$start,
			"limit"=>100,
		);
		$rscount=true;
		$catlist=$this->category->getlist($option,$rscount);
		if($pid){
			$parent=$this->category->selectRow(array("where"=>array("catid"=>$pid)));
			$nextpid=$parent['pid'];
		}
		$pagelist=$this->pagelist($rscount,100,$url);
		$this->smarty->assign(
			array(
				"catlist"=>$catlist,
				"modellist"=>$this->model->getlist(array("where"=>"status=1")),
				"model_index"=>$this->model->model_table(),//对应的入口文件
				"nextpid"=>$nextpid,
				"pagelist"=>$pagelist,
				"model_id"=>$model_id
			)
		);
		$this->smarty->display("category/category.html");
	}
	
	public function onAdd(){
		$model_id=max(1,get_post('model_id','i'));
		$modellist=$this->model->getlist(array("where"=>"status=1"));
		$pid=get('pid','i');
		if($pid){
			$parent=$this->category->selectRow(array("where"=>array("catid"=>$pid)));
			$this->smarty->assign(
				"parent",$parent
			);
		}
		$catid=get('catid','i');
		if($catid){
			$data=$this->category->selectRow(array("where"=>array("catid"=>$catid)));
			$this->smarty->assign("data",$data);
			if($data){
				$catlist=$this->category->children(0,$data['model_id']);
			}
		}
		
		$this->smarty->assign(
			array(
				"modellist"=>$modellist,
				"catlist"=>$catlist,
				"attr_cat"=>$this->attribute_cat->attr_cat(),
				"model_id"=>$model_id
			)
		);
		$this->smarty->display("category/category_add.html");
	}
	
	
	public function onSave(){
		$catid=get_post('catid','i');
		$data['model_id']=post('model_id','i');		
		$data['status']=post('status','i');
		$data['pid']=post('pid','i');
		$data['attr_cat_id']=post('attr_cat_id','i');
		$data['type_id']=post('type_id','i');
		$data['cname']=post('cname','h');
		$data['orderindex']=post('orderindex','i');
		$data['cat_tpl']=post('cat_tpl','h');
		$data['list_tpl']=post('list_tpl','h');
		$data['show_tpl']=post('show_tpl','h');
		$data['title']=post('title','h');
		$data['keywords']=post('keywords','h');
		$data['description']=post('description','h');
		$data['logo']=post('logo','h');
		if(empty($data['description'])){			
			$data['description']=cutstr(strip_tags($_POST['content']),240);
		}
		
		if($data['pid']){
			$parent=$this->category->selectRow(array("where"=>array("catid"=>$data['pid'])));
			$data['model_id']=$parent['model_id'];
			$data['level']=$parent['level']+1;
			 
		}else{
			$model=$this->model->selectRow(array("where"=>array("id"=>$data['model_id'])));
			
			if(!$catid){
				$data['level']=1;
			}
			
		}
		
		
		if($catid){
			unset($data['model_id']);
			unset($data['parent_id']);
			
			unset($data['level']);
			$this->category->update($data,array("catid"=>$catid));
		}else{
				 				
			$this->category->insert($data);
		}
		$this->onLevel(1);
		$this->gourl();
	}
	
	/*批量子分类添加*/
	public function onAddmore(){
		$catid=get('catid','i');
		$model_id=max(1,get_post('model_id','i'));
		$data=$this->category->selectRow(array("where"=>"catid=".$catid));
		if(empty($data)) $this->goall("数据出错",1);
		$this->smarty->assign(array(
			"data"=>$data,
			"model_id"=>$model_id
		));
		$this->smarty->display("category/addmore.html");
	}
	
	public function onSaveMore(){
		$catid=get_post('catid','i');
		$data=$this->category->selectRow(array("where"=>"catid=".$catid));
		if(empty($data)) $this->goall("数据出错",1);
		$content=post('content');
		$arr=explode("\r\n",$content);
		if($arr){
			foreach($arr as $v){
				$v=trim($v);
				if(!empty($v)){
					$t_d=array(
						"cname"=>$v,
						"title"=>$v,
						"keywords"=>$v,
						"description"=>$v,
						"pid"=>$data['catid'],
						"type_id"=>$data['type_id'],
						"level"=>$data['level']+1, 
						"model_id"=>$data['model_id'],
						
						"attr_cat_id"=>$data['attr_cat_id']
					);
					$this->category->insert($t_d);
				}
			}
		}
		$this->goall("添加成功");
	}
	
	public function OnChangestatus(){
		$status=get('status','i');
		$catid=get('catid','i');
		$this->category->update(array("status"=>$status),array("catid"=>$catid));
	}
	public function onOrderindex(){
		$catid=get('catid','i');
		$orderindex=get('data','i');
		$this->category->update(array("orderindex"=>$orderindex),array("catid"=>$catid));
	}
	
	
	public function onDelete(){
		$catid=get('catid','i');
		$this->category->update(array("status"=>99),array("catid"=>$catid));
		echo json_encode(array("error"=>0,"message"=>$this->lang['delete_success']));	
	}
	
	public function onAjax_getchild(){
		$pid=get('pid','i');
		
		$data=$this->category->select(array("where"=>array("pid"=>$pid),"order"=>" orderindex asc"));
		
		echo "<option value=0>{$this->lang['please_select']}</option>";
		if($data){
			foreach($data as $k=>$v){
				echo "<option value='{$v['catid']}'>{$v['cname']}</option>";
			}
		}
		exit;
	}
	
	public function onLevel($res=false){
		$this->category->update(array("level"=>99),"1");
		$this->category->update(array("level"=>1),"pid=0");
		$this->level(1);
		$this->level(2);
		$this->level(3);
		$this->level(4);
		$this->level(5);
		$this->level(6);
		$this->level(7);
		$this->level(8);
		$this->level(9);
		if($res) return true;
		$this->goall("分类修复成功");
	}
	
	public function level($level=1){
		$ids=$this->category->selectCols(array(
			"where"=>"level=".$level,
			"fields"=>"catid",
			"limit"=>100000
		));
		$ids && $this->category->update(array("level"=>$level+1),"pid in("._implode($ids).")");
	}
	
}
?>