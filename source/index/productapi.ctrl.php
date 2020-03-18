<?php
class productApiControl extends skymvc{
	public $sw="";
	public function __construct(){
		parent::__construct();
		$this->loadModel(array("product","product_data","category"));
		//城市库存
		if($_COOKIE['city']){
			//$this->sw.=" AND attr_3 like '%".sql($_COOKIE['city'])."%'";
		}
	}
	
	public function onTop($catid=0,$limit=10){
		return $this->onList($catid,$limit," grade DESC");
	}
	public function getByIds($ids){
		
		if(empty($ids)) return false;
		if(!is_array($ids)){
			$ids=explode(",",$ids);
		}
		foreach($ids as $k=>$v){
			$ids[$k]=intval($v);
		}
		
		$option=array(
			"where"=>" id in("._implode($ids).") ".$this->sw
		);
		$t_d=$this->select($option);
		
		if($t_d){
			$arr=$ids;
			foreach($t_d as $v){
				$d[$v['id']]=$v;
			}
			foreach($arr as $v){
				if(isset($d[$v])){
					$data[]=$d[$v];
				}
			}
			
			return $data;
		}
	}	
	public function select($option=array(),&$rscount=false){
		$this->loadModel(array("user"));
		$data=$this->product->select($option,$rscount);
		if($data){
			foreach($data as $k=>$v){
				$uids[]=$v['userid'];
				$t_ids[]=$v['catid'];
			}
			$us=$this->user->getUserByIds($uids);
			$t_ids && $t_c=$this->category->cat_list(" catid in("._implode($t_ids).")");
			foreach($data as $k=>$v){
				if(!isset($us[$v['userid']])){
					$u=array(
						"nickname"=>"管理员",
						"user_head"=>"/images/user_head.jpg",
					);
				}else{
					$u=$us[$v['userid']];
				}
				$v['cname']=$t_c[$v['catid']];
				$v['nickname']=$u['nickname'];
				$v['user_head']=$u['user_head'];
				$data[$k]=$v;
			}
		}
		return $data;
	}
	
	public function onList($catid=0,$limit=10,$orderby=" id DESC"){
		$w=" status=2    ".$this->sw;
		if($catid){
			$cids=$this->category->id_family($catid);
			if($cids){
				$w.=" AND catid in(".implode(",",$cids).") ";
			}else{
				$w.=" AND 1=2 ";
			}
		}
		$rscount=false;
		$list=$this->select(array(
			"where"=>$w,
			"start"=>$start,
			"limit"=>$limit,
			"order"=>$orderby
			
		),$rscount);
		return $list;
	}
	
	public function listByUser($userid,$limit=10,$orderby=" id DESC"){
		$rscount=true;
		$userid=intval($userid);
		$w="   status<99   AND userid=$userid ";
		$list=$this->select(array(
			"where"=>$w,
			"start"=>$start,
			"limit"=>$limit,
			"order"=>$orderby
			
		));
		return $list;
	}
	
	
	
	public function hot($catid=0,$limit=10){
		$where=" status=2   ".$this->sw;
		if($catid){
			$ids=$this->category->id_family($catid);
			if($ids){
				$where.=" AND catid in("._implode($ids).")";
			}else{
				$where .=" AND 1=2 ";
			}
		}
		$option=array(
			"where"=>$where,
			"limit"=>$limit,
			"order"=>" grade DESC"
		);
		$data=$this->select($option);
		return $data;
	}
	
	public function recommend($catid=0,$limit=10){
		$where=" status=2   AND is_recommend=1 ".$this->sw;
		if($catid){
			$ids=$this->category->id_family($catid);
			if($ids){
				$where.=" AND catid in("._implode($ids).")";
			}else{
				$where .=" AND 1=2 ";
			}
		}
		$option=array(
			"where"=>$where,
			"limit"=>$limit,
			"order"=>" grade DESC"
		);
		$data=$this->select($option);
		return $data;
	}
	
	public function newlist($catid=0,$limit=10){
		$where=" status=2   AND isnew=1 ".$this->sw;
		if($catid){
			$ids=$this->category->id_family($catid);
			if($ids){
				$where.=" AND catid in("._implode($ids).")";
			}else{
				$where .=" AND 1=2 ";
			}
		}
		$option=array(
			"where"=>$where,
			"limit"=>$limit,
			"order"=>" grade DESC"
		);
		$data=$this->select($option);
		return $data;
	}
	
	public function listByShop($shopid=0,$option=array()){
		$shopid=intval($shopid);
		if(!isset($option['order'])){
			$option['order']=" grade DESC";
		}
		if(!isset($option['limit'])){
			$option['limit']=10;
		}
		if(!isset($option['where'])){
			$option['where']=" shopid=".$shopid." AND status<99 ";
		}else{
			$option['where']=" shopid=".$shopid." AND status<99 ".$option['where'];
		}
		$data=$this->select($option);
		return $data;
	}
	
	public function histroy($limit=10){
		$his_ids=explode(",",$_COOKIE['click_products']);
		if(empty($his_ids)) return false;
		
		$data=$this->getbyids($his_ids);
		if($data){
			foreach($data as $k=>$v){
				if($k>$limit){
					unset($data[$k]);
				}
			}
		}
		
		return $data;
	}
	
	/*组合商品*/
	public function zuhe($catid,$limit){
		$data=$this->recommend($catid,$limit);
		if($data){
			foreach($data as $k=>$v){
				if(empty($v['zuhe_ids'])){
					unset($data[$k]);
					continue;
				}
				$ids=explode(",",$v['zuhe_ids']);
				$d=$this->product_data->selectRow("id=".$v['id']);
				$v['imgsdata']=getImgs($d['imgsdata']);
				$v['imgsdata2']=getImgs($d['imgsdata2']);
				if(!empty($ids)){
				$t_d=$this->getByIds($ids);
				if($t_d){
					foreach($t_d as $kk=>$vv){
						if(!empty($t_d[$kk]['attr_1'])){			
							$t_d[$kk]['attr_1']=explode("\n",$vv['attr_1']);
						}
					}
				}
				$v['item']=$t_d;
				}
				$data[$k]=$v;
			}
		}
		return $data;
	}
	
	public function getCatCount($catid){
		$where=" status=2     ";
		if($catid){
			$ids=$this->category->id_family($catid);
			if($ids){
				$where.=" AND catid in("._implode($ids).")";
			}else{
				$where .=" AND 1=2 ";
			}
		}
		$option=array(
			"where"=>$where,
			"limit"=>$limit,
			"order"=>" grade DESC",
			"fields"=>" count(*) as ct "
		);
		return $this->product->selectOne($option);
	}

	
}
?>