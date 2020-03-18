<?php
class adminControl extends skymvc{
	 
	public function __construct(){
		parent::__construct();
		$this->loadModel(array("admin","permission","admin_group"));
		if(!isset($_SESSION['ssadmin'])){
			$this->gourl(APPADMIN."?m=login");
		}
	 
	}
	
	public function onDefault(){
		$grouplist=$this->admin_group->grouplist();
 
		$data=$this->admin->select(array("where"=>$where));
		$this->smarty->assign(
			array(
				"adminlist"=>$data,
				"grouplist"=>$grouplist
			)
		);
		$this->smarty->display("admin/admin.html");
	}
	
	public function onAdd(){
		$id=get_post('id','i');
		$grouplist=$this->admin_group->select(array());
		if($id){
			$data=$this->admin->selectRow(array("where"=>array("id"=>$id)));				
			$this->smarty->assign("data",$data);
		}
		$this->smarty->assign(
			array(
				"grouplist"=>$grouplist,
			)
		);
		$this->smarty->display("admin/admin_add.html");
	}
	
	
	
	public function onSave(){
		$username=post('username','h');
		$password=post('password','h');
		$email=post('email','h');
		$password2=post('password2','h');
		if($password!=$password2){
			$this->goall($this->lang['password_neq'],1);
		}
		if(!is_email($email)){
			$this->goall($this->lang['email_error'],1);
		}
		
		if($this->admin->selectRow(array("where"=>array('username'=>$username)))){
			$this->goall($this->lang['admin_is_exist'],1);
		}
		$data['username']=$username;
		$data['email']=$email;
		$data['salt']=$salt=rand(1000,9999);
		$data['password']=umd5($password.$salt);
		$data['group_id']=post('group_id');
		
		$this->admin->insert($data);
		$this->goall($this->lang['admin_add_success']);
	}
	
	public function onEdit(){
		$id=get_post('id','i');
		$data=$this->admin->selectRow(array("where"=>array("id"=>$id)));
		if(empty($data)) $this->goall($this->lang['admin_no_exist'],1,0,APPADMIN."?m=admin");
		if(get('op')=='db'){
			$password=post('password');
			$password2=post('password2');
			$group_id=post('group_id','i');
			if($password){
				if($password!=$password2){
					$this->goall($this->lang['password_neq'],1);					
				}
				$newdata['password']=umd5($password.$data['salt']);
			}
			$newdata['group_id']=$group_id;
			$this->admin->update($newdata,array("id"=>$id));
			$this->goall($this->lang['edit_success']);
		}
		
		$grouplist=$this->admin_group->select(array());
		$this->smarty->assign(
			array(
				"grouplist"=>$grouplist,
				"data"=>$data,
			)
		);
		
		$this->smarty->display("admin/admin_edi.html");
	}
	
	public function onDel(){
		$id=get('id','i');
		$this->admin->delete(array("id"=>$id));
		echo json_encode(array("error"=>0,"message"=>$this->lang['delete_success']));
	}
	
	public function ongroup(){
		if(file_exists(ROOT_PATH."config/permission.php")){
			include ROOT_PATH."config/permission.php";
		}else{
			file_put_contents(ROOT_PATH."config/permission.php",'<?php \r\n?>');
		}
		$permission=$config;
		switch(get('op','h')){
			case 'add':
					$id=intval(get('id','i'));
					if($id){
						$zu=$this->admin_group->selectRow(array("where"=>array('id'=>$id)));
						$zups=unserialize($zu['content']);
						$this->smarty->assign("zu",$zu);
					}
					foreach($permission as $key=>$val)
					{
						$tmparr=array();
						$chk="";
						$str.="<tr>";
						$str.="<td align='right'>".$key."：</td>";
						$str.="<td>";
						if($zups[$key])
						{
							
							foreach($zups[$key] as $t)
							{
								$tmparr=array_merge($tmparr,array($t)) ;
							}
							
						}
						foreach($val as $k=>$v){
							
							$chk="";
							if($tmparr){	
								if(in_array($v['access'],$tmparr))
								{
									$chk=" checked='checked' ";
								}
							}
							
							$str.= " <input type='checkbox' name='ps[".$key."][]' class='percheck' value='".$v['access']."' ".$chk." > ".$v['title']; 
						}
						$str.="</td>";
						$str.= "</tr>";
					}
					$this->smarty->assign("str",$str);
					$this->smarty->display("admin/admin_group_add.html");
				break;
				
			case 'add_db':
					$id=get_post('id','i');			
					$p=array();
					$title=post('title','h');
					if(empty($title))
					{
						$this->goall($this->lang['admin_group_unempty'],1);
					}
					$ps=post('ps','h');
					if($ps)
					{
						foreach($ps as $key=>$arr)
						{				
							foreach($arr as $k=>$v)
							{
								$p[$key][]=$v;
							}
							
						}
					}
					$data['title']=$title;
					$data['content']=serialize($p);
					
					if($id){
						$this->admin_group->update($data,array("id"=>$id));
					}else{
						 
						$this->admin_group->insert($data);
					}
					$this->goall($this->lang['edit_success']);
				break;
				
			case 'del':
					$id=get('id','i');
					$this->admin_group->delete(array("id"=>$id));
					echo json_encode(array("error"=>0,"message"=>$this->lang['delete_success']));
				break;
			default:
					$zulist=$this->admin_group->select(array("order"=>" id DESC"));
					$this->smarty->assign("zulist",$zulist);
					$this->smarty->display("admin/admin_group.html");
				break;
		}
		 
		
	}
	
	public function onPermission(){
		switch(get('op','h')){
			case 'add':
					$id=get_post('id','i');
					if($id){
						$this->smarty->assign("data",$this->permission->selectRow(array("where"=>array('id'=>$id))));
					}
					$this->smarty->display("admin/permission_add.html");
				break;
			case 'save':
					$id=get_post('id','i');
					$data['m']=post('m','h');
					$data['access']=post('access','h');
					$data['title']=post('title','h');
					if($id){
						$this->permission->update($data,array("id"=>$id));
						$this->goall($this->lang['edit_success']);
					}else{
						$this->permission->insert($data);
						$this->goall($this->lang['add_success']); 
					}
					
				break;
			case 'delete':
					$id=get('id','i');
					$this->permission->delete(array("id"=>$id));
					echo json_encode(array("error"=>0,"message"=>$this->lang['delete_success']));
				break;
			//生成配置文件
			case 'saveconfig':
				 set_time_limit(0);
				 $data=$this->permission->select(array());
				  
				 if($data){
					 foreach($data as $k=>$v){
						 $permission[$v['m']][]=array("title"=>$v['title'],"access"=>$v['access']);
					 }
				 }
				 $str="<?php";
				 if($permission){
					 foreach($permission as $k=>$v){
						 $str.="\r\n\$config['$k']=array(\r\n";
						 
						 foreach($v as $d){
						 	$str.="      array('title'=>'{$d['title']}','access'=>'{$d['access']}'),\r\n";
						 }
						 
						 $str.="\r\n);\r\n";
					 }
				 }
				 $str.="?>";
				 file_put_contents(ROOT_PATH."config/permission.php",$str);
				 $this->goall($this->lang['permission_write_success']);
				break;
			
			case "getpermission":	
					$this->loadModel("permission");
					$dir=ROOT_PATH."source/admin";
					$dh=opendir($dir);
					while($file=readdir($dh)){
						if($file!="." && $file!=".."){
							$temp=$dir."/".$file;
							$data=file_get_contents($temp);
							preg_match_all("/function on(\w+)\(/i",$data,$arr);
							if(isset($arr[1])){
								$m=substr($file,0,strpos($file,"."));
								$access="";
								foreach($arr[1] as $k=>$v){
									if($k==0){
										$access .=$v;
									}else{
										$access .=",".$v;
									}
									
								}
								if(!$this->permission->selectRow(array('where'=>"m='".$m."'"))){
									$this->permission->insert(array(
										'm'=>$m,
										'access'=>strtolower($access), 
										'title'=>$file,
										
									));
								}
							}
						}
					}
					
					$this->goall("权限生成成功");
				break;
			default:
					$data=$this->permission->select(array("order"=>"id desc"));
					$this->smarty->assign(
						array(
							"data"=>$data,
						)
					);
					$this->smarty->display("admin/permission.html");
				break;
		}
	}
	
}

?>