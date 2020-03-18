<?php
class categoryModel extends model{
	public $base;
	
	function __construct(&$base){
		parent::__construct($base);
		$this->table="category";
	}
	/**
	*获取模板
	*catid 分类id
	* type 1列表2分类
	*/
	public function getTpl($catid,$type=1){
		$catid=intval($catid);
		$data=$this->selectRow("catid=$catid");
		switch($type){
			case 1:
				if($data['list_tpl']){
					return $data['list_tpl'];
				}else{
					if($data['pid']){
						return self::getTpl($data['pid'],$type);
					}
				}
				break;
			case 2:
				if($data['show_tpl']){
					return $data['show_tpl'];
				}else{
					if($data['pid']){
						return self::getTpl($data['pid'],$type);
					}
				}
				break;
				
		
		}
	}
	/*增加主题数*/
	public function add_topic_num($catid,$num){
		$catid=intval($catid);
		$data=$this->selectRow("catid=$catid");
		$this->update(array("topic_num"=>$data['topic_num']+1),"catid=".$catid);
		if($data['pid']){
			return self::add_topic_num($data['pid'],$num);
		}
	}
	
	/*更新最后发表的主题*/
	public function update_new_topic($catid,$last_post){
		$catid=intval($catid);
		$data=$this->selectRow("catid=$catid");
		$this->update(array("topic_num"=>$data['topic_num']+1,"last_post"=>addslashes(json_encode($last_post))),"catid=".$catid);
		if($data['pid']){
			return self::update_new_topic($data['pid'],$last_post);
		}
	}
	
	/*更新最后发表的帖子*/
	public function update_comment_num($catid,$num){
		$catid=intval($catid);
		$num=intval($num);
		$data=$this->selectRow("catid=$catid");
		$this->update(array("comment_num"=>$data['comment_num']+$num),"catid=".$catid);
		if($data['pid']){
			return self::update_comment_num($data['pid'],$num);
		}
	}
	
	public function cat_list($where=""){

		$c=$this->select(array("where"=>$where,"fields"=>"cname,catid","limit"=>100));	
		if($c){
			foreach($c as $v){
				$data[$v['catid']]=$v['cname'];
			}
		}
		return $data;
	}
	
	public function children($pid=0,$model_id=0,$status=0){
		$pid=intval($pid);
		$model_id=intval($model_id);
		$status=intval($status);
		$cache_key="category_children_".$model_id."_".$status."_".$pid;
		if($d=cache()->get($cache_key)) return $d;
		$where="   status<99 ";
		if($model_id){
			$where.=" AND model_id=".intval($model_id)."  ";
		}
		if($status){
			$where.=" AND status=$status ";
		}
		$c_1=$this->select(array("where"=>$where." AND pid=".$pid,"order"=>"orderindex asc"));
		if($c_1){
			foreach($c_1 as $k=>$v){
				$v['last_post']=json_decode($v['last_post'],true);
				$v['logo']=IMAGES_SITE($v['logo']);
				$c_1[$k]=$v;
				$c_2[$k]=$v;
				$c_2=$this->select(array("where"=>$where."  AND pid=".$v['catid'],"order"=>"orderindex asc"));
				
				if($c_2){
					foreach($c_2 as $k_2=>$v_2){
						$c_3=$this->select(array("where"=>$where." AND pid=".$v_2['catid'],"order"=>"orderindex asc"));
						if($c_3){
							foreach($c_3 as $k_3=>$v_3){
								$v_3['last_post']=json_decode($v_3['last_post'],true);
								$v_3['logo']=IMAGES_SITE($v_3['logo']);
								$c_3[$k_3]=$v_3;
							}
							
						}
						$v_2['logo']=IMAGES_SITE($v_2['logo']);
						$v_2['last_post']=json_decode($v_2['last_post'],true);
						$c_2[$k_2]=$v_2;
						$c_2[$k_2]['child']=$c_3;
					}
				}
				
				$c_1[$k]['child']=$c_2;
			}
		}
		cache()->set($cache_key,$c_1,30);
		return $c_1;	
		 
	}
	
	public function getList($option,$child=true){
		$cat=$this->select($option);
		if(!$child) return $cat;
		if($cat){
			foreach($cat as $k=>$c){
				$cat[$k]['child']=$this->select(array("where"=>array("pid"=>$c['catid'],"status<"=>99),"order"=>" orderindex asc"));
				if($cat[$k]['child']){
					foreach($cat[$k]['child'] as $kk=>$cc){
						$cat[$k]['child'][$kk]['child']=$this->select(array("where"=>array("pid"=>$cc['catid'],"status<"=>99),"order"=>" orderindex asc"));
					}
				}
			}
		}
		return $cat;
	}
	
	public function cat_child($catid){
		return $this->select(array("where"=>array("pid"=>intval($catid)),"order"=>" orderindex asc"));
	}
	public function get($catid){
		return $this->selectRow(array("where"=>array("catid"=>$catid)));
	}
	
	public function cat_navlist($catid=0,$model_id=0){
		$where="  status=1 ";
		if($catid){
			$where.=" AND catid=".$catid."";
		}else{
			$where .=" AND pid=0 ";
		}
		$data=$this->selectRow($where);
		if(empty($data)) return false;
		if($data['pid']){
			$parent=$this->selectRow(array("where"=>array("catid"=>$data['pid'])));
		}
		$child=$this->select(array("where"=>array("pid"=>$catid,"status"=>1,"model_id"=>$model_id),"order"=>"orderindex asc"));
		//如果没有子类 选同级分类
		if(empty($child)){
			$child=$this->select(array("where"=>array("pid"=>$data['pid'],"status"=>1,"model_id"=>$model_id),"order"=>"orderindex asc"));
		}
		return $child;
		
	}
	
	public function tag_nav($catid){
		$cache_key="category_tag_nav_$catid";
		if($d=cache()->get($cache_key)) return $d;
		$catid=intval($catid);
		$data=$this->selectRow(array("where"=>"catid=$catid"));
		if(empty($data)) return false;
		if($data['pid']) $data=$this->selectRow(array("where"=>"catid=".$data['pid']));
		$child=$this->select(array("where"=>array("pid"=>$data['catid'],"status"=>1),"order"=>"orderindex asc"));
		if($child){
			foreach($child as $kk=>$vv){
				$vv['child']=$this->select(array("where"=>array("pid"=>$vv['catid'],"status"=>1),"order"=>"orderindex asc"));
				$vv['tags']=explode("\n",str_replace("\r\n","\n",str_replace(" ","",trim($vv['tags']))));
				$child[$kk]=$vv;	
			}
		}
		$data['child']=$child;
		cache()->set($cache_key,$data,60);
		return $data;
		
	}
	
	public function id_byids($ids){
		if(empty($ids)) return false;
		$data=$this->select(array("where"=>" id in("._implode($ids).") " ));
		if($data){
			foreach($data as $k=>$v){
				$t_c[$v['id']]=$v;
			}
			return $t_c;
		}
	}
	
	public function id_family($id=0){
		$id=intval($id);
		$ids[]=$id;
		$ids1=$this->selectCols(array("where"=>" pid=".$id."  ","fields"=>"catid"));
		if($ids1){
			$ids=array_merge($ids,$ids1);
			$ids2=$this->selectCols(array("where"=>" pid in("._implode($ids1).") ","fields"=>"catid"));
			if($ids2){
				$ids=array_merge($ids,$ids2);
				$ids3=$this->selectCols(array("where"=>" pid in("._implode($ids2).") ","fields"=>"catid"));
				if($ids3){
					$ids=array_merge($ids,$ids3);
				}
			}
		}
		return $ids;
		
	}
	
	public function get_attr_cat_id($catid){
		$catid=intval($catid);
		$r1=$this->selectRow("catid=".$catid);
		if(!$r1)return 0;
		if($r1['attr_cat_id']) return $r1['attr_cat_id'];
		if($r1['pid']){
			$parent=$this->selectRow("catid=".$r1['pid']);
			if(!$parent) return 0;
			if($parent['attr_cat_id']) return $parent['attr_cat_id'];
		}
		if($parent['pid']){
			$parent=$this->selectRow("catid=".$parent['pid']);		
			if(!$parent) return 0;
			if($parent['attr_cat_id']) return $parent['attr_cat_id'];
		}
		
		if($parent['pid']){
			$parent=$this->selectRow("catid=".$parent['pid']);		
			if(!$parent) return 0;
			if($parent['attr_cat_id']) return $parent['attr_cat_id'];
		}
		return 0;
	}
	
	
}
?>