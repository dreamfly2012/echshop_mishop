<?php
error_reporting(E_ALL);
if (!file_exists("config/install.lock")) {
    header("Location:install");exit;
}
;
header("Content-type:text/html; charset=utf-8");
define("ROOT_PATH", str_replace("\\", "/", dirname(__FILE__)) . "/");
@include_once "config/site_list.php";
@include_once "config/setconfig.php";

require "config/config.php";
require "config/const.php";
require "config/version.php";
define("CONTROL_DIR", "source/admin");
define("MODEL_DIR", "source/model");
$template_dir = "themes/admin";

#define("REWRITE_ON",false);//开启伪静态 默认关闭
define("WAP_DOMAIN", ""); //开启伪静态 默认关闭
define("dir", ""); //开启伪静态 默认关闭
$cache_dir = "";
$template_dir = $wap_template_dir = "themes/admin";
$compiled_dir = "";
$html_dir = "";

require "./skymvc/skymvc.php";
function userinit(&$base)
{
    $base->loadConfig("table");
    $base->smarty->assign("config", $base->config_data);
    $base->smarty->assign("appindex", APPINDEX);
    $base->smarty->assign("appadmin", APPADMIN);
    $base->smarty->assign("appshop", APPSHOP);
    if (isset($_SESSION['ssadmin']['id'])) {
        $base->ssadmin = $_SESSION['ssadmin']; //当前登录用户的信息

        if ($_SESSION['ssadmin']['isfounder']) {
            if (get_post('setsite')) {
                $_SESSION['ssadmin']['siteid'] = get_post('siteid', 'i');
            }
        }
        $base->smarty->assign("ssadmin", $base->ssadmin);

        $base->smarty->assign("site", m("sites", $base)->selectRow(array("where" => array("siteid" => 1))));
    }
    if (!in_array(get('m'), array('login', "checkcode", "backup", "user"))) {
        session_write_close();
    }
    $base->smarty->assign(array(
        "skins" => "/themes/admin/",
        "skinsadmindir" => "themes/admin",
    ));
    $base->smarty->assign("appadmin", APPADMIN);
    $m = get('m', 'h');
    #define("SITEID", max(1, $_SESSION['ssadmin']['siteid']));
    define("SITEID", 1);
    if (!in_array($m, array('login'))) {
        if (!isset($_SESSION['ssadmin']['id'])) {
            $base->gourl(APPADMIN . "?m=login");
        }

        $permission = unserialize(m("admin_group", $base)->selectOne(array("where" => array("id" => $_SESSION['ssadmin']['group_id']), "fields" => "content")));
        if (!$base->checkpermission($permission) && !$_SESSION['ssadmin']['isfounder']) {
            $base->gomsg("您无权限", APPADMIN . "?m=login");

        }
    }
}
