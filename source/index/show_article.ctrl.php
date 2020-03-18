<?php
class show_articleControl extends skymvc{
	
	public function __construct(){
		parent::__construct();
		$this->loadModel(array("category","article","article","article_data","article_attr","user"));
		$this->smarty->assign("nav","article");
	}
	
	public function onDefault(){
		$parent=$cat_2nd=$cat_3nd=array();
		$id=get_post('id','i');
		$data=$this->article->selectRow("id=$id");
		if(empty($data)) $this->goall($this->lang['data_no_exists'],1,0,"/index.php");
		$t_d=$this->article_data->selectRow(array("where"=>"id=$id"));
		unset($t_d['dateline']);
		if(!empty($t_d)){
			$data=array_merge($data,$t_d);
		}
		if($data['userid']){
			$data['author']=$this->user->selectRow("userid=".$data['userid']);			
		}
		if(empty($data['author'])){
				$data['author']=$this->user->selectRow("userid=1");
			}
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
			$r=$this->article_attr->selectRow(array("where"=>" id=$id "));
			if($r['attr_cat_id']){
				$attr=$this->attribute->getAttr($r['attr_cat_id']); 
				$data_attr=json_decode(base64_decode($r['attr_content']),true);
				 
				$this->smarty->assign("data_attr",$data_attr);
			}
			$data['attr']=$attr;
			
		/*******获取产品属性结束***************************/
		$this->smarty->assign(array(
			"data"=>$data,
			"cat"=>$category,
			"cat_top"=>$cat_top,
			"cat_2nd"=>$cat_2nd,
			"cat_3nd"=>$cat_3nd,
		));
		/*******获取上下篇*******/
		$cids=$this->category->id_family($data['catid']);
		$cids && $last_rs=$this->article->selectRow(array("where"=>" catid in("._implode($cids).")  AND id<".$data['id']." AND (status<10 AND status >0 )  ", "order"=>"id DESC") );
		$cids && $next_rs=$this->article->selectRow(array("where"=>" catid in("._implode($cids).")  AND id>".$data['id']." AND (status<10 AND status >0 )  ", "order"=>"id ASC") );
		/*******End获取上下篇*******/
		
		$this->smarty->assign(array(
			"last_rs"=>$last_rs,
			"next_rs"=>$next_rs,
		));
		/*********评论开始****************/
		 
		 $this->smarty->assign(
		 	array(
				"comment_tablename"=>"article",
				"comment_object_id"=>$id,
				"comment_f_userid"=>$data['userid'],
				"comment_referer"=>$_SERVER['REQUEST_URI'], 
			)
		 );
		/*********End评论****************/
		
		$tpl=$this->category->getTpl($data['catid'],2);
		$tpl=$tpl?$tpl:"article/show.html";
		$this->smarty->display($tpl);
	}
	
}

?>