<?php
class cronControl extends skymvc{
	
	public function __construct(){
		parent::__construct();
		session_write_close();
		$this->loadModel(array("cron"));
	}
	
	public function onDefault(){
		$data=$this->cron->selectRow(array("order"=>"next_time asc","where"=>" next_time<".time()));
		if(!empty($data)){
			$data['last_time']=time();
			$data['next_time']=time()+$data['minute']*60;
			$this->cron->update($data,"id=".$data['id']);
			if(strpos($data['url'],"http")===false){
				$fun="on".trim($data['url']);
				self::$fun();
			}else
				file_get_contents($data['url']);
			} 
	}
		 
	
	/*删除产品临时数据*/
	public function onData_temp(){
		$this->loadModel("model");
		//删除超过1小时的临时数据
		$mods=$this->model->select(array("module=''"));
		$this->loadModel("model_index");
		foreach($mods as $mod){
			$mod_data=$mod."_data";
			$this->loadModel(array($mod,$mod_data));
			$ids=$this->$mod->selectCols(array("where"=>" is_temp=1 AND dateline<".(time()-3600),"fields"=>"id","limit"=>5000));
			if($ids){
				$this->$mod->delete(" id in("._implode($ids).")");
				$this->$mod_data->delete(" id in("._implode($ids).")");
				$this->model_index->delete(" id in("._implode($ids).")");
			}
		}
		
	}

	/*分享*/
	public function onShare(){
		file_get_contents("http://".$_SERVER['HTTP_HOST']."/index.php?m=weibo&a=share");
	}
	/*采集*/
	public function onRandStole(){
		file_get_contents("http://".$_SERVER['HTTP_HOST']."/index.php?m=tao&a=RandStole");
	}
	
	 

	
	
	
}

?>