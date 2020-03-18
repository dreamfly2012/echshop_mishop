<?php
$config['admin']=array(
      array('title'=>'权限管理','access'=>'permission'),
      array('title'=>'管理员','access'=>'default,admin,save,edit,del'),
      array('title'=>'管理组','access'=>'group'),

);

$config['article']=array(
      array('title'=>'文章管理','access'=>'default'),
      array('title'=>'文章编辑','access'=>'add'),

);

$config['user']=array(
      array('title'=>'用户管理','access'=>'default,add,edit,edit_save,edit_password,delete'),

);

$config['model']=array(
      array('title'=>'模型管理','access'=>'default,add,save,delete'),

);

$config['backup']=array(
      array('title'=>'数据备份还原','access'=>'default,backtable,backdata,restoretable,restoredb'),

);

$config['category']=array(
      array('title'=>'分类管理','access'=>'default,add,save,delete,changestatus,orderindex,Ajax_getchild'),

);

$config['comment']=array(
      array('title'=>'评论管理','access'=>'default,add,save,delete,status'),

);

$config['guest']=array(
      array('title'=>'留言管理','access'=>'default,add,save,delete,status,edit'),

);

$config['admin_activity']=array(
      array('title'=>'admin_activity.ctrl.php','access'=>'default,show,add,save,status,delete'),

);

$config['admin_activity_topic']=array(
      array('title'=>'admin_activity_topic.ctrl.php','access'=>'default,show,add,save,status,delete'),

);

$config['admin_ad']=array(
      array('title'=>'admin_ad.ctrl.php','access'=>'default,add,save,status,delete,tag_id_2nd'),

);

$config['admin_ad_tags']=array(
      array('title'=>'admin_ad_tags.ctrl.php','access'=>'default,show,add,save,status,delete,child'),

);

$config['admin_attr']=array(
      array('title'=>'admin_attr.ctrl.php','access'=>'default,add,save,order'),

);

$config['admin_attr_cat']=array(
      array('title'=>'admin_attr_cat.ctrl.php','access'=>'default,show,add,save,status,delete'),

);

$config['admin_collect']=array(
      array('title'=>'admin_collect.ctrl.php','access'=>'default,list,savebyid,save,pidelete,status'),

);

$config['admin_collect_config']=array(
      array('title'=>'admin_collect_config.ctrl.php','access'=>'default,add,save'),

);

$config['admin_collect_rule']=array(
      array('title'=>'admin_collect_rule.ctrl.php','access'=>'default,copy,add,save,import,share,testlist,testcontent,status,delete'),

);

$config['admin_collect_type']=array(
      array('title'=>'admin_collect_type.ctrl.php','access'=>'default,list,add,save,status,delete'),

);

$config['admin_config']=array(
      array('title'=>'admin_config.ctrl.php','access'=>'default,save,testphone,testemail'),

);

$config['admin_cron']=array(
      array('title'=>'admin_cron.ctrl.php','access'=>'default,add,save'),

);

$config['admin_dataapi']=array(
      array('title'=>'admin_dataapi.ctrl.php','access'=>'default,show,add,save,status,delete'),

);

$config['admin_gold_log']=array(
      array('title'=>'admin_gold_log.ctrl.php','access'=>'default,list,show,delete'),

);

$config['admin_grade_log']=array(
      array('title'=>'admin_grade_log.ctrl.php','access'=>'default,list,delete'),

);

$config['admin_guest']=array(
      array('title'=>'admin_guest.ctrl.php','access'=>'default,show,add,save,status,delete'),

);

$config['admin_options']=array(
      array('title'=>'admin_options.ctrl.php','access'=>'default,add,save,status,delete,gettype'),

);

$config['admin_order']=array(
      array('title'=>'admin_order.ctrl.php','access'=>'default,show,confirm,send,finish,cancel,delete'),

);

$config['admin_pay_log']=array(
      array('title'=>'admin_pay_log.ctrl.php','access'=>'default,show,delete'),

);

$config['admin_pm']=array(
      array('title'=>'admin_pm.ctrl.php','access'=>'default,show,add,save,status,delete'),

);

$config['admin_recharge']=array(
      array('title'=>'admin_recharge.ctrl.php','access'=>'default,man,saveman'),

);

$config['admin_specialtopic']=array(
      array('title'=>'admin_specialtopic.ctrl.php','access'=>'default,list,show,add,save,status,delete'),

);

$config['admin_sysmsg']=array(
      array('title'=>'admin_sysmsg.ctrl.php','access'=>'default,add,save,delete'),

);

$config['admin_template']=array(
      array('title'=>'admin_template.ctrl.php','access'=>'default,online,using,save'),

);

$config['admin_user']=array(
      array('title'=>'admin_user.ctrl.php','access'=>'default,add,save'),

);

$config['admin_user_group']=array(
      array('title'=>'admin_user_group.ctrl.php','access'=>'default,add,save'),

);

$config['admin_weibo']=array(
      array('title'=>'admin_weibo.ctrl.php','access'=>'default,config,saveconfig,adduser,saveuser,del,visible'),

);

$config['admin_weixin']=array(
      array('title'=>'admin_weixin.ctrl.php','access'=>'default,list,show,add,save,status,delete,left'),

);

$config['admin_weixin_command']=array(
      array('title'=>'admin_weixin_command.ctrl.php','access'=>'default,add,save,status,delete'),

);

$config['admin_weixin_menu']=array(
      array('title'=>'admin_weixin_menu.ctrl.php','access'=>'default,add,save,order,createmenu,deletemenu,delete'),

);

$config['admin_weixin_reply']=array(
      array('title'=>'admin_weixin_reply.ctrl.php','access'=>'default,delete'),

);

$config['admin_weixin_sucai']=array(
      array('title'=>'admin_weixin_sucai.ctrl.php','access'=>'default,add,addiframe,save,delete'),

);

$config['admin_weixin_user']=array(
      array('title'=>'admin_weixin_user.ctrl.php','access'=>'default,delete'),

);

$config['brand']=array(
      array('title'=>'brand.ctrl.php','access'=>'default,add,save,status,delete'),

);

$config['cache']=array(
      array('title'=>'cache.ctrl.php','access'=>'default,clear'),

);

$config['forum']=array(
      array('title'=>'forum.ctrl.php','access'=>'default,add,save,delete,status,recommend'),

);

$config['iframe']=array(
      array('title'=>'iframe.ctrl.php','access'=>'default,top,left,main,checknewversion,update'),

);

$config['index']=array(
      array('title'=>'index.ctrl.php','access'=>'default'),

);

$config['jfapi']=array(
      array('title'=>'jfapi.ctrl.php','access'=>''),

);

$config['link']=array(
      array('title'=>'link.ctrl.php','access'=>'default,add,save,delete'),

);

$config['login']=array(
      array('title'=>'login.ctrl.php','access'=>'default,login_save,logout,refresh'),

);

$config['module']=array(
      array('title'=>'module.ctrl.php','access'=>'default,add,create,default,install,uninstall,delete'),

);

$config['navbar']=array(
      array('title'=>'navbar.ctrl.php','access'=>'default,add,save,delete,order,status'),

);

$config['picture']=array(
      array('title'=>'picture.ctrl.php','access'=>'default,add,save,delete,status,recommend'),

);

$config['product']=array(
      array('title'=>'product.ctrl.php','access'=>'default,add,save,delete,status,recommend,attrbycat,attr'),

);

$config['seo']=array(
      array('title'=>'seo.ctrl.php','access'=>'default,add,save,del'),

);

$config['sites']=array(
      array('title'=>'sites.ctrl.php','access'=>'default,add,save,writeconfig'),

);

$config['weixin_fun']=array(
      array('title'=>'weixin_fun.ctrl.php','access'=>'default,add,save,delete,status,recommend'),

);
?>