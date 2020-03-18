<?php
class list_productControl extends skymvc{
	public function __construct(){
		parent::__construct();
		$this->loadModel(array("category","product"));
	}
	
	public function onDefault(){
		$catid=get('catid','i');	
		$category=$cat=$this->category->selectRow(array("where"=>"catid=$catid"));
		$cat_top=$cat;
		if($cat['pid']){
			$parent=$this->category->selectRow(array("where"=>"catid=".$cat['pid']));
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
			
		}
		if($catid){
			$children=$this->category->children($catid);
			if(empty($children)){
				if($cat['level']>1){
					$children=$this->category->children($parent['catid']);
				}
			}
		}
		$tpl=$this->category->getTpl($cat['catid'],1);
		$tpl=$tpl?$tpl:"product/list.html";
		$this->smarty->assign(array(
			"cat"=>$cat,
			"parent"=>$parent,
			"cat_top"=>$cat_top,
			"children"=>$children,
			"cat_2nd"=>$cat_2nd,
			"cat_3nd"=>$cat_3nd,
		));
		$rscount=true;
		$cids=$this->category->id_family($catid);
		$where=" status=2 ";
		$url="/index.php?m=list&catid=".$catid;
		if(!empty($cids)){
			$where.=" AND catid in("._implode($cids).") ";
		}else{
			$where.=" AND 1=2 ";
		}
		$attr_1=get_post('attr_1','i');
		$attr_2=get_post('attr_2','i');
		$attr_3=get_post('attr_3','i');
		$attr_4=get_post('attr_4','i');
		if($attr_1){
			$where.=" AND attr_1=".$attr_1;
			$url.="&attr_1=".$attr_1;
		}
		if($attr_2){
			$where.=" AND attr_2=".$attr_2;
			$url.="&attr_2=".$attr_2;
		}
		if($attr_3){
			$where.=" AND attr_3=".$attr_3;
			$url.="&attr_3=".$attr_3;
		}
		if($attr_4){
			$where.=" AND attr_4=".$attr_4;
			$url.="&attr_4=".$attr_4;
		}
		$brand_id=get('brand_id','i');
		if($brand_id){
			$where.=" AND brand_id=".$brand_id;
			$url.="&brand_id=".$brand_id;
		}
 
		switch(get('orderby')){
			case "dateline":
					$order="id DESC";
				break;
			case "price":
					$order="price ASC";
				break;
			case "idasc":
					$order='id ASC';
				break;
			default :
					$order="buy_num DESC,id DESC";
				break;
		}
		$start=get_post('per_page','i');
		$limit=20;
		$option=array(
			"where"=>$where,
			"start"=>$start,
			"limit"=>$limit,
			"order"=>$order
		);
		$data=$this->product->select($option,$rscount);
		if($data){
			$t_ids=array();
			foreach($data as $k=>$v){
				$t_ids[]=$v['catid'];
				$t_uids[]=$v['userid'];
			}
			if($t_ids){
				$t_c=$this->category->cat_list(" catid in("._implode($t_ids).")");
			}
			$this->loadModel("user");
			if($t_uids){
				$t_u=$this->user->getUserByIds($t_uids);
			}
			foreach($data as $k=>$v){
				$v['cname']=$t_c[$v['catid']];
				if(isset($t_u[$v['userid']])){
					$v['nickname']=$t_u[$v['userid']]['nickname'];
				}
				$data[$k]=$v;
			}
		}
		$pagelist=$this->pagelist($rscount,$limit,$url);
		$this->smarty->goassign(array(
			"list"=>$data,
			"rscount"=>$rscount,
			"pagelist"=>$pagelist,
			"url"=>$url
		));
		$this->smarty->display($tpl);
	}
}

?>