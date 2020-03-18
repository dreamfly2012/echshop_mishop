<?php
class adapiControl extends skymvc{
	public $sw;
	public function __construct(){
		parent::__construct();
		$this->loadModel(array("ad","ad_tags"));
		//城市库存
		if($_COOKIE['city']){
			$this->sw.=" AND checkbox_attr like '%".sql($_COOKIE['city'])."%'";
		}
	}
	
	public function adlist($tag_id,$limit=4){
		$data=$this->ad->select(array(
			"where"=>" starttime<".time()."  AND endtime>".time()." AND status=2 AND (tag_id=".intval($tag_id)." or tag_id_2nd=".intval($tag_id).") ",
			"order"=>" orderindex asc",
			"start"=>0,
			"limit"=>$limit
		));
		if($data){
			foreach($data as $k=>$v){
				$v['info']=$v['info']?$v['info']:$v['title'];
				$v['link2']=$v['link2']?$v['link2']:$v['link1'];
				$data[$k]=$v;
			}
		}
		
		return $data;
	}
	
	public function onListbyno(){
		$no=get_post('no','h');
		$limit=get_post('limit','i');
		$limit=$limit?$limit:4;
		$data=$this->listbyno($no,$limit);
		
		exit(json_encode($data));
	}
	
	public function listbyno($no,$limit=4,$sw=false){
		$tag_id=$this->ad_tags->selectOne(array("where"=>"  tagno='".$no."'  ","fields"=>"tag_id"));
		if(!$tag_id) return false;
		 $where=" starttime<".time()."  AND endtime>".time()." AND status=2 AND (tag_id=".intval($tag_id)." or tag_id_2nd=".intval($tag_id).") ";
		if($sw){
			$where.=$this->sw;
		}
		
		$data=$this->ad->select(array(
			"where"=>$where,
			"order"=>" orderindex asc",
			"start"=>0,
			"limit"=>$limit
		));
		if($data){
			foreach($data as $k=>$v){
				$v['info']=$v['info']?$v['info']:$v['title'];
				$v['link2']=$v['link2']?$v['link2']:$v['link1'];
				$data[$k]=$v;
			}
		}
		 
		return $data;
	}
	
	/*产品广告*/
	public function productByNo($no,$limit=4,$sw=false){
		$tag_id=$this->ad_tags->selectOne(array("where"=>"  tagno='".$no."'  ","fields"=>"tag_id"));
		if(!$tag_id) return false;
		$where=" starttime<".time()." AND endtime>".time()." AND status=2  AND tag_id_2nd=".intval($tag_id)." ";
		if($sw){
			$where.=$this->sw;
		}
		$t_c=$this->ad->select(array(
			"where"=>$where,
			"order"=>" orderindex asc",
			"start"=>0,
			"limit"=>$limit,
		));
		if($t_c){
			foreach($t_c as $k=>$v){
				$ids[]=$v['object_id'];
				unset($v['price']);
				$tc[$v['object_id']]=$v;
			}
		}
		if($ids){
			$this->loadControl("productapi");
			$data=$this->productapiControl->select(array("where"=>" id in("._implode($ids).")"));
			if($data){
				foreach($data as $k=>$v){
					unset($tc[$v['id']]['id']);
					$v=array_merge($v,$tc[$v['id']]);
					$data[$k]=$v;
				}
			}
			 
			return $data;
		}
		
	}
	
	/*产品广告*/
	public function goldgoodsByNo($no,$limit=4,$sw=false){
		$tag_id=$this->ad_tags->selectOne(array("where"=>"  tagno='".$no."'  ","fields"=>"tag_id"));
		if(!$tag_id) return false;
		$where=" starttime<".time()." AND endtime>".time()." AND status=2  AND tag_id_2nd=".intval($tag_id)." ";
		if($sw){
			$where.=$this->sw;
		}
		$t_c=$this->ad->select(array(
			"where"=>$where,
			"order"=>" orderindex asc",
			"start"=>0,
			"limit"=>$limit,
		));
		if($t_c){
			foreach($t_c as $k=>$v){
				$ids[]=$v['object_id'];
				unset($v['price']);
				$tc[$v['object_id']]=$v;
			}
		}
		if($ids){
			$this->loadControl("goldgoodsapi");
			$data=$this->goldgoodsapiControl->select(array("where"=>" id in("._implode($ids).")"));
			if($data){
				foreach($data as $k=>$v){
					unset($tc[$v['id']]['id']);
					$v=array_merge($v,$tc[$v['id']]);
					$data[$k]=$v;
				}
			}
			 
			return $data;
		}
		
	}
}

?>