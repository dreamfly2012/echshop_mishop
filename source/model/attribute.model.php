<?php
class attributeModel extends model{
	public $base;
	public function __construct(&$base){
		parent::__construct($base);
		$this->base=$base;
		$this->table="attribute";
	}
	
	
	
	public function attr_type(){
		return array(
			1=>"唯一属性",
			2=>'单选属性'
		);
	}
	
	public function input_type(){
		return array(
			1=>"手动输入",
			2=>"列表选择",
		);
	}
	
	public function getAttr($cat_id=0){
		$cat_id=intval($cat_id);
		$option=array(
			"where"=>" cat_id=".$cat_id." ",
			"order"=>" orderindex ASC"
		);
		$data=$this->select($option);
		if($data){
			foreach($data as $k=>$v){
				$c=$v['content'];
				if($v['input_type']==2){
					$d=explode("\n",str_replace("\r\n","\n",$c));
					$select=array();
					foreach($d as $vv){
						$s=explode("=",$vv);
						$select[$s[0]]=$s[1];
					}
					$v['select']=$select;
				}
				$data[$k]=$v;
			}
		}
		return $data;
	}
	
	public function getNameByVal($id,$key=0){
		$data=$this->selectRow("id=$id");
		if($data['attr_type']==1) return false;
		if($data['input_type']==1) return $key;
		$d=explode("\n",str_replace("\r\n","\n",$data['content']));
		$select=array();
		foreach($d as $vv){
			$s=explode("=",$vv);
			$select[$s[0]]=$s[1];
		}
		return $data['title'].":".$select[$key];
	}
	
	public function getAttrPrice($attr,$p_attr){
		
		$arr=json_decode($attr,true);
		$p_arr=json_decode($p_attr,true);
		$price=0;
		if(empty($p_arr)){
			return 0;
		}
		$ps=array();
		if($p_arr){
			foreach($p_arr as $k=>$p){
				if(!is_array($p) or !isset($p['value'])  ) continue;
				if(!empty($p['value'])){
					foreach($p['value'] as $kk=>$v){				
						$ps[$k][$v]=$p['price'][$kk];
					}
				}
			}
		}
		if($arr){
			foreach($arr as $k=>$v){
				 $price=max($price,$ps[$k][$v]);
			}
		
		}
		return $price;
	}
	public function strAttr($attr,$p_attr){
		 
		if($attr!="null"){
			$arr=json_decode($attr,true);
			 
			$p_arr=json_decode($p_attr,true);
			$str='';
			$i=0;
			if($arr){
				
				foreach($arr as $k=>$v){
					if($i>0){
						$str.=",";
					}
					$str.=$this->getNameByVal($k,$v);
					$i++;
				}
			}
			return $str;
		}else{
			return "默认";
		}
		
	}
	
	function col_name_attr($cat_id){
		$cat_id=intval($cat_id);
		$t_d=$this->select(array("where"=>"cat_id=".$cat_id));
		if($t_d){
			foreach($t_d as $v){
				if($v['col_name']!=""){
					$data[$v['id']]=$v['col_name'];
				}
			}
		}
		return $data;
	}
	
	function col_name_attr_list($cat_id){
		$cat_id=intval($cat_id);
		$t_d=$this->select(array("where"=>"cat_id=".$cat_id));
		if($t_d){
			foreach($t_d as $v){
				$val=array();
				if($v['col_name']!=""){
					$arr=explode("\r\n",$v['content']);
					if($arr){
						foreach($arr as $kk=>$vv){
							
							$a2=explode("=",$vv);
							if(!empty($a2[1])){
								$val[$a2[0]]=$a2[1];
							}
						}
					}
					$v['list']=$val;
					$data[$v['id']]=$v;
				}
			}
		}
		return $data;
	}
	
}

?>