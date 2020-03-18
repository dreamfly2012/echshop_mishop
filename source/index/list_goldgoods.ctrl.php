<?php
class list_goldgoodsControl extends skymvc{
	public function __construct(){
		parent::__construct();
		$this->loadModel(array("category","goldgoods"));
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
				if($cat['level']>2){
					$children=$this->category->children($parent['catid']);
				}
			}
		}
		$tpl=$this->category->getTpl($cat['catid'],1);
		$tpl=$tpl?$tpl:"goldgoods/list.html";
		$this->smarty->assign(array(
			"cat"=>$cat,
			"children"=>$children,
			"parent"=>$parent,
			"cat_top"=>$cat_top,
			"cat_2nd"=>$cat_2nd,
			"cat_3nd"=>$cat_3nd,
		));
		$rscount=true;
		$cids=$this->category->id_family($catid);
		$where=" status=2  ";
		$url="/index.php?m=list&catid=".$catid;
		if(!empty($cids)){
			$where.=" AND catid in("._implode($cids).") ";
		}else{
			$where.=" AND 1=2 ";
		}
		
		$start=get_post('per_page','i');
		$limit=20;
		$option=array(
			"where"=>$where,
			"start"=>$start,
			"limit"=>$limit,
			"order"=>"last_time DESC"
		);
		$data=$this->goldgoods->select($option,$rscount);
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
		$this->smarty->assign(array(
			"list"=>$data,
			"rscount"=>$rscount,
			"pagelist"=>$pagelist
		));
		$this->smarty->display($tpl);
	}
}

?>