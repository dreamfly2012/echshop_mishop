<?php
/**
*Author 雷日锦 362606856@qq.com
*model 自动生成
*/				
class optionsModel extends model{
	public $base;
	public function __construct(&$base){
		parent::__construct($base);
		$this->base=$base;
		$this->table="options";
	}
	
 
	public function table_list(){		
		$data=array(
			"afproduct"=>"新品类别",
			"zx_mianji"=>"装修面积",
			"area"=>"区域"
		);
		
		return $data;
	}
	
	public function id_title($option=array()){
		$t_d=$this->select($option);
		if(!empty($t_d)){
			foreach($t_d as $k=>$v){
				$data[$v['id']]=$v['title'];
			}
		}
		return $data;
	}
	
	public function children($pid=0,$tablename=0,$status=0){
		$pid=intval($pid);
	 
		$status=intval($status);
		$where="  1=1 ";
		if($tablename){
			$where.=" AND tablename='".sql($tablename)."'  ";
		}
		if($status){
			$where.=" AND status=$status ";
		}
		$c_1=$this->select(array("where"=>$where." AND pid=".$pid,"order"=>"orderindex asc"));
		if($c_1){
			foreach($c_1 as $k=>$v){
				$c_1[$k]=$v;
				$c_2[$k]=$v;
				$c_2=$this->select(array("where"=>$where."  AND pid=".$v['id'],"order"=>"orderindex asc"));
				
				if($c_2){
					foreach($c_2 as $k_2=>$v_2){
						$c_3=$this->select(array("where"=>$where." AND pid=".$v_2['id'],"order"=>"orderindex asc"));
						$c_2[$k_2]=$v_2;
						$c_2[$k_2]['child']=$c_3;
					}
				}
				
				$c_1[$k]['child']=$c_2;
			}
		}
		return $c_1;
	}
	
	public function showById($id){
		
		if(empty($id)) return false;
		$fwshang=$this->selectRow("id=".$id);
		if($fwshang['pid']){
			$data=$this->selectRow("id=".$fwshang['pid']);
			$data['child']=$fwshang;
		}else{
			$data=$fwshang;
		}
		 
		return $data;
	}
	
}

?>