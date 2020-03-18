<?php
class show_goldgoodsControl extends skymvc{
	
	public function __construct(){
		parent::__construct();
		$this->loadModel(array("category","goldgoods","goldgoods","goldgoods_data","goldgoods_attr","user"));
	}
	
	public function onDefault(){
		
		$id=get_post('id','i');
		$data=$this->goldgoods->selectRow("id=$id");
		if(empty($data)) $this->goall($this->lang['data_no_exists'],1,0,"/index.php");
		$t_d=$this->goldgoods_data->selectRow(array("where"=>"id=$id"));
		unset($t_d['dateline']);
		if(!empty($t_d)){
			$data=array_merge($data,$t_d);
		}
		if($data['userid']){
			$data['author']=$this->user->selectRow("userid=".$data['userid']);
		}
		$imgsdata=M("imgs")->get("goldgoods",$id);
		$category=$cat=$this->category->selectRow("catid=".$data['catid']);
		if($cat['level']==1){
			$cat_top=$cat;
		}
		if($cat['pid']){
			$parent=$this->category->selectRow(array("where"=>array("catid"=>$cat['pid'])));
			if($parent['pid']){
				$cat_2nd=$parent;
				$cat_top=$this->category->selectRow(array("where"=>array("catid"=>$parent['pid'])));
				$cat_3nd=$cat;
			}else{
				$cat_top=$parent;
				$cat_2nd=$cat;
			}			 
		}
		$seo=array(
				"title"=>htmlspecialchars($data['title']),
				"keywords"=>htmlspecialchars($data['keywords']?$data['keywords']:$data['title']),
				"description"=>htmlspecialchars($data['description']?$data['description']:$data['title']),
		);
		$this->smarty->assign("seo",$seo);
		/*******获取产品属性*****************/
			$this->loadModel("attribute");
			$data_attr=$attr=array();
			$r=$this->goldgoods_attr->selectRow(array("where"=>" id=$id "));
			if($r['attr_cat_id']){
				$attr=$this->attribute->getAttr($r['attr_cat_id']); 
				$data_attr=json_decode(base64_decode($r['attr_content']),true);
				$this->smarty->assign("data_attr",$data_attr);
			}
			$data['attr']=$attr;
			
		/*******获取产品属性结束***************************/
		$this->smarty->assign(array(
			"data"=>$data,
			"imgsdata"=>$imgsdata,
			"cat"=>$category,
			"cat_top"=>$cat_top,
			"cat_2nd"=>$cat_2nd,
			"cat_3nd"=>$cat_3nd,
		));
		/*******获取上下篇*******/
		$cids=$this->category->id_family($data['catid']);
		$cids && $last_rs=$this->goldgoods->selectRow(array("where"=>" catid in("._implode($cids).")  AND id<".$data['id']." AND (status<10 AND status >0 )  ", "order"=>"id DESC") );
		$cids && $next_rs=$this->goldgoods->selectRow(array("where"=>" catid in("._implode($cids).")  AND id>".$data['id']." AND (status<10 AND status >0 )  ", "order"=>"id ASC") );
		/*******End获取上下篇*******/
		/***************获取相关产品推荐***********************/
		
		$mem_key="show_recommend_$id";
		if(!$recommendlist=cache()->get($mem_key)){
			
		$cids &&	$recommend=$this->goldgoods->select(array("where"=>" catid in("._implode($cids).")  AND status=1 AND grade>='{$data['grade']}' ", "order"=>"grade ASC","start"=>0,"limit"=>20) );
			if(!$recommend or count($recommend)==1){
				$cids &&	$recommend=$this->goldgoods->select(array("where"=>" catid in("._implode($cids).")  AND (status<10 AND status >0 ) AND grade<='{$data['grade']}' ","order"=>"grade desc","start"=>0,"limit"=>20) );
			}
			
			$reclist=array();
			if($recommend){
				foreach($recommend as $k=>$v){
					if($v['id']!=$data['id']){
						$reclist[]=$v;
					}
				}
				
				if(count($reclist)>10){
					shuffle($reclist);
					$recommendlist=array_slice($reclist,0,10);
				}else{
					$recommendlist=$reclist;
				}
				cache()->set($mem_key,$recommendlist,120);
			}
		}
		/***************获取相关产品推荐**********************/
		$this->smarty->assign(array(
			"recommendlist"=>$recommendlist,
			"last_rs"=>$last_rs,
			"next_rs"=>$next_rs,
		));
		/*********评论开始****************/
		 $this->smarty->assign(
		 	array(
				"comment_tablename"=>"goldgoods",
				"comment_object_id"=>$id,
				"comment_f_userid"=>$data['userid'],
				"comment_referer"=>$_SERVER['REQUEST_URI'], 
			)
		 );
		/*********End评论****************/
		
		$tpl=$this->category->getTpl($data['catid'],2);
		$tpl=$tpl?$tpl:"goldgoods/show.html";
		$this->smarty->display($tpl);
	}
	
}

?>