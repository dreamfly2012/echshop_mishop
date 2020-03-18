<?php
class goldgoodsControl extends skymvc{
	
	public function __construct(){
		parent::__construct();
		$this->loadmodel(array("goldgoods","category","goldgoods_data","goldgoods_attr","attribute","attribute_cat","model_index"));	
		
	}
	
	public function onDefault(){
		$limit=20;
		$start=get('per_page','i');
		$rscount=true;
		$where=" status<99 ";
		$url=APPADMIN."?m=goldgoods";
		$id=intval(get('id','i'));
		if($id){
			$where.=" AND id=$id";
		}
		$s_is_img=get('s_is_img','i');
		if($s_is_img){
			$where.=" AND is_img=".($s_is_img==1?1:0);
			$url.="&is_img=".$s_is_img;
		}
		$title=get_post('title','h');
		if($title){
			$where.=" AND title like '%{$title}%' ";
			$url.="&title=".urlencode($title);
		}
		$catid=get_post('catid','i');
		if($catid){
			$cids=$this->category->id_family($catid);
			$url.="&catid=$catid";
			if($cids){
				$where.=" AND catid in("._implode($cids).")";
			}else{
				$where.=" AND 1=2 ";
			}
			
		}
		$s_recommend=get_post('s_recommend','i');
		if($s_recommend){
			$url.="&s_recommend=".$s_recommend;
			$where.=" AND is_recommend=".($s_recommend==1?1:0);
		}
		$status=get_post('status','i');
		if($status){
			$url.="&status=".$status;
			$where.=" AND status=$status";
		}
		$op=array(
			"where"=>$where,
			"limit"=>20,
			"start"=>$start,
			"order"=>" id DESC",
		);
		
		
		$data=$this->goldgoods->select($op,$rscount);
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
		$cat_list=$this->category->children(0,MODEL_GOLDGOODS_ID);
		$this->smarty->assign(
			array(
				"data"=>$data,
				"pagelist"=>$this->pagelist($rscount,$limit,$url),
				"cat_list"=>$cat_list
			)
		);
		$this->smarty->display("goldgoods/index.html");	
	}
	
	public function onAdd(){
		$cat_list=$this->category->children(0,MODEL_GOLDGOODS_ID);
		$id=get('id','i');
		if($id){
			$data=$this->goldgoods->selectRow(array("where"=>"id=$id"));
			$t_d=$this->goldgoods_data->selectRow(array("where"=>"id=$id"));
			if(!empty($t_d)){
				$data=array_merge($data,$t_d);
			}
				
		}else{
			$data=$this->goldgoods->selectRow(array("where"=>"is_temp=1 AND userid=".DEFAULT_USERID." "));
			if(empty($data)){
				$id=$this->model_index->insert(array("tablename"=>"goldgoods"));
				$this->goldgoods->insert(array("id"=>$id,"dateline"=>time(),"userid"=>DEFAULT_USERID,"is_temp"=>1,"status"=>99));
				$this->goldgoods_data->insert(array("id"=>$id,"dateline"=>time()));
				$data['id']=$id;
				$this->goldgoods_attr->insert(array("attr_content"=>" ","dateline"=>time(),"id"=>$id));
			}
		}
		$attr=$this->goldgoods_attr->selectRow(array("where"=>"id=$id "));
		$this->smarty->assign(
			array(
				"cat_list"=>$cat_list,
				"data"=>$data,
				"attr"=>$attr,
				"attr_cat"=>$this->attribute_cat->attr_cat(),
			)
		);
		$this->smarty->display("goldgoods/add.html");
	}
	
	public function onSave(){
		$id=post('id','i');
		$data['title']=post('title','h');
		$data['last_time']=time();
		$data['keywords']=post('keywords','h');
		$data['description']=post('description','h');
		if(empty($data['description'])){			
			$data['description']=cutstr(strip_tags($_POST['content']),240);
		}
		$data['catid']=post('catid','i');
		$cat=$this->category->selectRow("catid=".$data['catid']);
		if(empty($cat)) $this->goall($this->lang['category_empty'],1);
		$data['model_id']=$cat['model_id'];
		
		$data['imgurl']=post('imgurl','h');
		if($data['imgurl']){
			$data['is_img']=1;
		}else{
			$data['is_img']=0;
		}
		if($id){
			$row=$this->goldgoods->selectRow("id=$id");
			if($row['is_temp']==1){
				$data['is_temp']=0;
				$data['status']=2;
			}
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
			"dateline"=>time(),
			"imgsdata"=>post('imgsdata','x'),
		);
		/*扩展信息*/
		$post_att=post('attr');
		$col_attr=$this->attribute->col_name_attr(post('attr_cat_id','i'));
		if($col_attr){
			foreach($col_attr as $k=>$v){
				$data[$v]=intval($post_att[$k]);
			}
		}
		$data['market_price']=post('market_price','r',1);
		$data['price']=post('price','r',1);
		$data['gold']=post('gold','i');
		$data['total_num']=post('total_num','i');
		$data['starttime']=post('starttime')?strtotime(post('starttime')):0;
		$data['endtime']=post('endtime')?strtotime(post('endtime')):0;
		$data['attr_cat_id']=post('attr_cat_id','i');
		$data['shop_name']=post('shop_name','h');
		$data['shop_url']=post('shop_url','h');
		/*END 扩展信息*/
		$attr_cat_id=post('attr_cat_id','i');
		$attr_content=base64_encode(json_encode(stripslashes_deep(post('attr','x'))));
		if($id){
			$this->goldgoods->update($data,array("id"=>$id));
			if(!$this->goldgoods_data->selectRow("id=$id")){
				$this->goldgoods_data->insert($sdata);
			}else{
				$this->goldgoods_data->update($sdata,array("id"=>$id));
			}
			
			if($this->goldgoods_attr->selectRow(array("where"=>" id=$id "))){
					$this->goldgoods_attr->update(array(
						"attr_content"=>$attr_content,
						"attr_cat_id"=>$attr_cat_id
					),"id=$id");
			}else{
					$this->goldgoods_attr->insert(array(
						"id"=>$id,
						"attr_content"=>$attr_content,
						"attr_cat_id"=>$attr_cat_id,
						"dateline"=>time()
					));
			}
		}else{
			$data['userid']=1;
			$data['dateline']=time();
			
			$data['id']=$id=$this->model_index->insert(array("tablename"=>"goldgoods"));
			$sdata['id']=$id;
			if($this->goldgoods->insert($data)){
				$this->goldgoods_data->insert($sdata);
			}
			$this->goldgoods_attr->insert(array(
						"id"=>$id,
						"attr_content"=>$attr_content,
						"attr_cat_id"=>$attr_cat_id,
						"dateline"=>time()
			));
			 
		}
		//更新相关统计
		if($row['is_temp']==1){  
			$this->category->update_new_topic($data['catid'],array("last_time"=>time(),"nickname"=>"管理员","userid"=>1,"title"=>$data['title'],"id"=>$id));
		}
		$this->goall($this->lang['add_success'],0,$data,APPADMIN."?m=goldgoods&a=add&id=".$id);
	}
	
	
	
	public function onDelete(){
		$id=get('id','i');
		 $this->goldgoods->update(array("status"=>99),array("id"=>$id));
		echo json_encode(array("error"=>0,"message"=>$this->lang['delete_success']));
	}
	
	public function onStatus(){
		$id=get('id','i');
		$status=get('status','i');
		$this->goldgoods->update(array("status"=>$status),array("id"=>$id));
		echo json_encode(array("error"=>0,"message"=>$this->lang['save_success']));	 
	}
	
	public function onRecommend(){
		$id=get('id','i');
		$is_recommend=get('is_recommend','i');
		$this->goldgoods->update(array("is_recommend"=>$is_recommend),array("id"=>$id));	
		echo json_encode(array("error"=>0,"message"=>$this->lang['save_success']));	 
	}
	
		public function onattrByCat(){
		 $id=get('id','i');
		 $cat=$this->category->selectRow(array("where"=>" catid=$id "));
		 if($cat['attr_cat_id']){
			$this->loadModel(array("attribute"));
		 	$cat_id=$cat['attr_cat_id'];
			$data=$this->attribute->getAttr($cat_id);
			$this->smarty->assign(array(
				"data"=>$data,
				"attr"=>$attr,
				"attr_cat_id"=>$cat_id
			));
			$this->smarty->display("attr/attr.html"); 
		 }
	 }
	 
	 
	 public function onAttr(){
		 $this->loadModel(array("attribute"));
		 $cat_id=get('cat_id','i');
		 $id=get('id','i');
		 $attr=array();
		 if($id){
			$row=$this->goldgoods_attr->selectRow(array("where"=>" id=$id AND attr_cat_id=".$cat_id." "));
			
			if($row){ 
				$attr=json_decode(base64_decode($row['attr_content']),true);
				 
			}
		 }
		$data=$this->attribute->getAttr($cat_id);
		$this->smarty->assign(array(
			"data"=>$data,
			"attr"=>$attr,
			"attr_cat_id"=>$cat_id
		));
		$this->smarty->display("attr/attr.html");
		
	 }
	 
	 public function onCategory(){
		$ids=post('ids','i');
		$catid=post('catid','i');
		if(!$catid) $this->goall("请选择分类",1);
		if($ids){
			foreach($ids as $id){
				$this->goldgoods->update(array("catid"=>$catid),"id=".$id);
			}
		}
		$this->goall("修改成功");
	}
	 
	
}
?>