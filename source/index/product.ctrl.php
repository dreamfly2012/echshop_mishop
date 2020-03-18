<?php
class productControl extends skymvc{
	public $userid;
	public function __construct(){
		parent::__construct();
		$this->loadModel(array("category","model","product","product_data","login","model_index","user","user_category"));
		if(in_array(get('a'),array("my","save","add"))){
			$this->login->checklogin();
		}
		$this->userid=$this->login->userid;
	}
	
	function onDefault(){
		 
		$rscount=true;
		 
		$where=" status=2 ";
		$url="/index.php?m=product";
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
			$cids=$this->category->id_family($catid);
			$where.=" AND catid in("._implode($cids).") ";
		}
		$keyword=get_post('keyword','h');
		if($keyword){
			$where.=" AND title like '%".$keyword."%'";
			$url.="&keyword=".urlencode($keyword);
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
			case "buy_num":
					$order="buy_num DESC,price DESC";
				break;
			case "price":
					$order="price ASC";
				break;
			default :
					$order="buy_num DESC,id DESC";
				break;
		}
		$filter=get('filter');
		switch($filter){
			case "cuxiao":
					$where.=" AND lower_price>0";
				break;
			case "isnew":
					$where.=" AND isnew=1";
				break;
		}
		if($filter){
			$url.="&filter=".$filter;
		}
		$start=get_post('per_page','i');
		$limit=24;
		$option=array(
			"where"=>$where,
			"start"=>$start,
			"limit"=>$limit,
			"order"=>$order
		);
		$data=$this->product->select($option,$rscount);
		$catlist=M("category")->children(0,MODEL_PRODUCT_ID);
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
		$per_page=$start+$limit;
		$per_page=$per_page>=$rscount?0:$per_page;
		$this->smarty->goassign(array(
			"list"=>$data,
			"rscount"=>$rscount,
			"pagelist"=>$pagelist,
			"catlist"=>$catlist,
			"url"=>$url,
			"per_page"=>$per_page
		));
		$this->smarty->display("product/index.html");
		
	}

	public function onHome(){
		$userid=get('userid','i');
		if(empty($userid)){
			$userid=$this->login->userid;
		}
		$user=$this->user->selectRow("userid=".$userid);
		if(empty($user)) $this->goall($this->lang['user_no_exists'],1,0,"/index.php");
		$limit=20;
		$where=" status=2 AND userid=".$userid."  ";
		$url="/index.php?m=product&a=home&userid".$userid;
		$user_catid=get('user_catid','i');
		if($user_catid){
			$ucids=$this->user_category->id_family($user_catid);
			$where.=" AND user_catid in("._implode($ucids).") ";
			$url.="&user_catid=".$user_catid;
		}
		$start=get('per_page','i');
		$option=array(
			"start"=>$start,
			"limit"=>$limit,
			"order"=>"id DESC",
			"where"=>$where,
		);
		$rscount=true;
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
		$this->smarty->assign(
			array(
				"data"=>$data,
				"rscount"=>$rscount,
				"pagelist"=>$pagelist,
				"user"=>$user,
				"user_cat_list"=>$this->user_category->id_title(array("where"=>"userid=".$userid)),
			)
		);
		$this->smarty->display("product/home.html");
	}
	
	/*我的产品模型*/
	public function onMy(){
		$this->login->checklogin();
		$limit=20;
		$where=" status<99 AND userid=".$this->login->userid;;
		$url="/index.php?m=product&a=my";
		$user_catid=get('user_catid','i');
		if($user_catid){
			$ucids=$this->user_category->id_family($user_catid);
			$where.=" AND user_catid in("._implode($ucids).") ";
			$url.="&user_catid=".$user_catid;
		}
		$start=get('per_page','i');
		$option=array(
			"start"=>$start,
			"limit"=>$limit,
			"order"=>"id DESC",
			"where"=>$where,
		);
		$rscount=true;
		$data=$this->product->select($option,$rscount);
		if($data){
			foreach($data as $k=>$v){
				$data[$k]['cname']=$this->category->selectOne(array("where"=>array("catid"=>$v['catid']),"fields"=>"cname"));
				if($v['catid_2nd']){
					$data[$k]['cname_2nd']=$this->category->selectOne(array("where"=>array("catid"=>$v['catid_2nd']),"fields"=>"cname"));
				}
				
				if($v['catid_3nd']){
					$data[$k]['cname_3nd']=$this->category->selectOne(array("where"=>array("catid"=>$v['catid_3nd']),"fields"=>"cname"));
				}
				
				
			}
		}
		$url="/index.php?m=product&a=my";
		$pagelist=$this->pagelist($rscount,$limit,$url);
		$this->smarty->assign(
			array(
				"data"=>$data,
				"rscount"=>$rscount,
				"pagelist"=>$pagelist,
				"user_cat_list"=>$this->user_category->id_title(array("where"=>"userid=".$this->login->userid)),
				"pid_list"=>$this->user_category->children(array("where"=>" tablename='product' AND pid=0 AND userid=".$this->login->userid))
			)
		);
		$this->smarty->display("product/my.html");
	}
	
	/*发表产品模型*/
	public function onAdd(){
		$cat_list=$this->category->children(0,MODEL_PRODUCT_ID);;
		$id=get('id','i');
		if($id){
			$data=$this->product->selectRow(array("where"=>"id=$id"));
			$t_d=$this->product_data->selectRow(array("where"=>"id=$id"));
			if(!empty($t_d)){
				$data=array_merge($data,$t_d);
			}		
		}else{
			$data=$this->product->selectRow(array("where"=>"is_temp=1 AND userid=".$this->login->userid." AND dateline>".(time()-3600)." "));
				if(empty($data)){
				$id=$this->model_index->insert(array("tablename"=>"product"));
				$this->product->insert(array("id"=>$id,"dateline"=>time(),"is_temp"=>1,"status"=>99,"userid"=>$this->login->userid));
				$this->product_data->insert(array("id"=>$id,"dateline"=>time()));
				$data['id']=$id;
			}
		}
		$this->smarty->assign(
			array(
				"cat_list"=>$cat_list,
				"data"=>$data,
				"user_cat_list"=>$this->user_category->children(array("where"=>" tablename='product' AND pid=0 AND userid=".$this->login->userid))
			)
		);
		$this->smarty->display("product/add.html");
	}
	
	public function onSave(){
		$id=post('id','i');
		if($id){
			$row=$this->product->selectRow("id=".$id);
			if($row['is_temp']==1){
				$data['is_temp']=0;
				$data['status']=1;
			}
			if($row['userid']!=$this->login->userid) $this->goall($this->lang['die_access'],1,0,"/index.php");
		}
		$data['title']=post('title','h');	
		$data['last_time']=time();		
		$data['keywords']=post('keywords','h');
		$data['description']=post('description','h');
		if(empty($data['description'])){			
			$data['description']=cutstr(strip_tags($_POST['content']),240);
		}
		$data['catid']=post('catid','i');
		$data['user_catid']=post('user_catid','i');
		$cat=$this->category->selectRow("catid=".$data['catid']);
		if(empty($cat)) $this->goall($this->lang['cat_empty'],1);
		$data['model_id']=$cat['model_id'];
		$data['imgurl']=post('imgurl','h');
		if($data['imgurl']){
			$data['is_img']=1;
		}else{
			$data['is_img']=0;
		}				
		if($data['imgurl']){			 
			$data['is_img']=1;			 
		}
		
		//gps信息
		if(post('latlng')){
			$latlng=explode(",",post('latlng'));
			$data['lat']=round($latlng[0],6);
			$data['lng']=round($latlng[1],6);
			if ($data['lat'] && $data['lng']){
					$data['isgps']=1;
			}
		}
		
		$sdata=array(
			"id"=>$id,
			"content"=>post('content','x'),
			"dateline"=>time()
		);
		/*扩展信息*/
		$data['price']=post('price','r','1');
		$data['lower_price']=post('lower_price','r',1);
		$data['total_num']=post('total_num','i');
		/*END 扩展信息*/
		if($id){			
			$this->product->update($data,array("id"=>$id));
			if(!$this->product_data->selectRow("id=$id")){
				$this->product_data->insert($sdata);
			}else{
				$this->product_data->update($sdata,array("id"=>$id));
			}
		}else{
			$data['userid']=$this->login->userid;
			$data['dateline']=time();
			
			$data['id']=$id=$this->model_index->insert(array("tablename"=>"product"));
			$sdata['id']=$id;
			if($this->product->insert($data)){
				$this->product_data->insert($sdata);
			}
		}
		//更新相关统计
		if($row['is_temp']==1){
			$this->user->changeNum("topic_num",1,"userid=".$this->userid);
			$user=$this->login->getUser();
			$this->category->update_new_topic($data['catid'],array("last_time"=>time(),"nickname"=>$user['nickname'],"userid"=>$this->userid,"title"=>$data['title'],"id"=>$id));
		}
		$this->goall($this->lang['save_success'],0,$data,APPINDEX."?m=product&a=add&id=".$id);
	}
	
	public function onDelete(){
		$row=$this->product->selectRow("id=".$id);
		if($row['userid']!=$this->login->userid) $this->goall($this->lang['die_access'],1,0,"/index.php");
		$this->product->update(array("status"=>98),"id=".$id);
		$this->goall($this->lang['delete_success']);
	}
	
	public function onAddClick(){
		$id=get_post('id','i');
		$row=$this->product->selectRow("id=".$id);
		if($row){
			$this->product->update(array("view_num"=>$row['view_num']+1),"id=".$id); 
		}
		 
	 }
	
}
?>