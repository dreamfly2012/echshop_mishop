<?php
class articleApiControl extends skymvc{
	
	public function __construct(){
		parent::__construct();
		$this->loadModel(array("article","category"));
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
			"where"=>" id in("._implode($ids).") "
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
		$data=$this->article->select($option,$rscount);
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
	
	public function onList($catid=0,$limit=10,$isimg=0,$orderby=" id DESC"){
		$w=" status=2  ";
		if($catid){
			$cids=$this->category->id_family($catid);
			if($cids){
				$w.=" AND catid in(".implode(",",$cids).") ";
			}else{
				$w.=" AND 1=2 ";
			}
		}
		if($isimg){
			$w.=" AND is_img=1";
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
	
	public function onjList(){
		
		echo json_encode($this->onList());
	}
	
	public function onjShow(){
		$id=get_post('id','i');
		$this->loadModel(array("article_data"));
		$data=$this->article->selectRow("id=".$id);
		if($data){
			$t_d=$this->article_data->selectRow(array("where"=>"id=$id"));
			unset($t_d['dateline']);
			if(!empty($t_d)){
				$data=array_merge($data,$t_d);
			}
		}
		echo json_encode($data);
	}
	
	
	public function listByUser($userid,$limit=10,$orderby=" id DESC"){
		$rscount=true;
		$userid=intval($userid);
		$w=" status<99 AND userid=$userid ";
		$list=$this->select(array(
			"where"=>$w,
			"start"=>$start,
			"limit"=>$limit,
			"order"=>$orderby
			
		));
		return $list;
	}
	
	
	
	public function hot($catid=0,$limit=10){
		$where=" status=2  ";
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
			"order"=>" grade DESC,id DESC"
		);
		$data=$this->select($option);
		return $data;
	}
	
	public function recommend($catid=0,$limit=10){
		$where=" status=2  AND is_recommend=1 ";
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
			"order"=>" grade DESC,id DESC"
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
	
	/*身边*/
	/*附近的商家*/
 	public function near($option=array()){
		$meter=0.00001*1.1;//1米以内
		$mi=get('mi','i');
		if(isset($option['mi'])){
			$mi=$option['mi'];
		}
		$meter=$mi?$meter*$mi:$meter*10000;
		
		$miarr=array();
		$latlng=explode("-",$_COOKIE['latlng']);
		$lat=round($latlng[0],6);
		$lng=round($latlng[1],6);
		if($lat>0)
		{
			$ilng=$lng+$meter;
			$mlng=$lng-$meter;
			$ilat=$lat+$meter;
			$mlat=$lat-$meter;
			$pagesize=12;
			$page=max(1,intval($_GET['page']));
			$start=($page-1)*$pagesize;
			$where="  (lng<'$ilng' AND lng>'$mlng') AND (lat>'$mlat' AND lat<'$ilat') ";
			$option=array(
				"where"=>$where,
				"limit"=>2000,
				"fields"=>"id,(ABS(lat-".$lat.") + ABS(lng-".$lng.")) as mi",
			);
			$rscount=true;
			$arr=$this->article->select($option,$rscount,1,600);
			if($arr)
			{
				foreach($arr as $r)
				{
					if($r['mi']<$meter)
					{
						$miarr[]=$r;
						$ids[]=$r['id'];
					}
				}
			}
			
			$start=get('per_page','i');
			if(isset($option['limit'])){
				$limit=$option['limit'];
			}else{
				$limit=24;
			}
			if($ids){
				$max=count($ids);
				$big=$start+$limit;
				$big=$big<$max?$big:$max;
				for($i=$start;$i<$big;$i++){
					$tids[]=$ids[$i];
				}
				if($tids){
					return $data=$this->select(array("where"=>" id in("._implode($tids).")"));
				}
			}
		}
		
	}
	
	

	
}
?>