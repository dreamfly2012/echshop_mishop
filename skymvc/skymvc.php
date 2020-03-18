<?php
if (!defined("ROOT_PATH")) {
    define("ROOT_PATH", str_replace("\\", "/", dirname(dirname(__FILE__))) . "/");
}

//全局变量
//sql执行语句
$GLOBALS['skysqlrun'] = "";
//sql执行次数
$GLOBALS['skysqlnum'] = 0;
//sql执行时间
$GLOBALS['query_time'] = 0;
//配置信息
$GLOBALS['config_data'] = array();
// End;
define("B_TIME", microtime(true)); //页面开始时间
define("SKYVERSION", 3.0);
date_default_timezone_set('PRC'); //设置默认时区
if (!defined("LANG")) {
    define("LANG", 'chinese');
}

/***
 **载入函数库
 **/
$st = microtime(true);
require_once "function/fun_error.php";
require_once "function/fun_app_default.php";
require_once "function/fun_file.php";
require_once "function/fun_gps.php";
require_once "function/fun_pinyin.php";
require_once "function/fun_time.php";
require_once "function/fun_url.php";
require_once "function/function.php";
/*加载用户自定义*/
if (!empty($user_extends)) {
    foreach ($user_extends as $ex) {
        require_once "extends/$ex";
    }
}

if (defined("REWRITE_TYPE") && REWRITE_TYPE == 'pathinfo') {
    url_get($_SERVER['REQUEST_URI']);
}
if (is_mobile() or get('iswap') or WAP_DOMAIN == $_SERVER['HTTP_HOST']) {
    define("ISWAP", 1);
} else {
    define("ISWAP", 0);
}

/*对非法变量进行转换*/
if (!get_magic_quotes_gpc()) {
    if (!empty($_GET)) {
        $_GET = addslashes_deep($_GET);
    }
    if (!empty($_POST)) {
        $_POST = addslashes_deep($_POST);
    }

    $_COOKIE = addslashes_deep($_COOKIE);
    $_REQUEST = addslashes_deep($_REQUEST);
}
/*配置数据库*/
$_DBS = array();
require_once "class/cls_pdo.php"; //引入数据库文件
require_once "class/cls_model.php"; //引入模型
require_once "class/cls_cache.php";
require_once "class/cls_session.php";
function setDb($table = 'master')
{
    global $dbconfig, $_DBS;
    if (isset($_DBS[$table])) {
        return $_DBS[$table];
    }

    if (isset($dbconfig[$table])) {
        $_DBS[$table] = new mysql();
        $_DBS[$table]->connect($dbconfig[$table]);
        return $_DBS[$table];
    } else {
        if (isset($_DBS['master'])) {
            return $_DBS['master'];
        } else {
            $_DBS['master'] = new mysql();
            $_DBS['master']->connect($dbconfig['master']);
            return $_DBS['master'];
        }
    }
}
/*加载模型*/
function M($model, &$base = null)
{
    $model = strtolower($model);

    if (isset($GLOBALS[$model . 'Model'])) {

        return $GLOBALS[$model . 'Model'];
    } else {

        if (file_exists(MODEL_DIR . "/$model.model.php")) {
            require_once MODEL_DIR . "/$model.model.php";
            //controler  model调用
            $_model = "{$model}Model";

            $m = new $_model($base);

            $m->setDb($model);

            $GLOBALS[$model . 'Model'] = $m;

            return $GLOBALS[$model . 'Model'];

        } else {
            //controler  model调用
            $_model = "{$model}Model";

            $m = new model($base);
            $m->setDb($model);

            $m->table = $model;
            $GLOBALS[$model . 'Model'] = $m;

            return $GLOBALS[$model . 'Model'];
        }
    }
}
/*加载模块的模型*/
function MM($module, $model, &$base = null)
{
    if (isset($GLOBALS[$model . 'Model'])) {

        return $GLOBALS[$model . 'Model'];
    } else {
        if (file_exists(ROOT_PATH . "module/" . $module . "/source/model/$model.model.php")) {
            require_once ROOT_PATH . "module/" . $module . "/source/model/$model.model.php";
            $_model = "{$model}Model";

            $m = new $_model($base);
            $m->setDb($model);

            $GLOBALS[$model . 'Model'] = $m;
            return $GLOBALS[$model . 'Model'];
        } else {
            //controler  model调用
            $_model = "{$model}Model";

            $m = new model($base);
            $m->setDb($model);
            $base->$model = $m;
            $m->table = $model;
            $GLOBALS[$model . 'Model'] = $m;

            return $GLOBALS[$model . 'Model'];
        }
    }
}

/*获取控制器*/
function C($ctrl = '', $dir = false)
{
    $ctrl = strtolower($ctrl);
    if (empty($ctrl)) {
        return $GLOBALS['control'];
    } else {
        if (isset($GLOBALS['control_' . $ctrl])) {
            return $GLOBALS['control_' . $ctrl];
        } else {
            $dir = $dir ? $dir : CONTROL_DIR;
            $file = $dir . "/" . $ctrl . ".ctrl.php";

            if (file_exists($file)) {
                include_once $file;
                $ctrlClass = $ctrl . "Control";
                $GLOBALS['control_' . $ctrl] = new $ctrlClass();
                if (method_exists($ctrlClass, 'onInit')) {
                    $GLOBALS['control_' . $ctrl]->onInit();
                }
                return $GLOBALS['control_' . $ctrl];
            } else {
                exit($ctrl . "不存在");
            }
        }
    }

}
/**/
function cc($module = '', $ctrl = '', $dir = 'index')
{
    if (empty($ctrl)) {
        return $GLOBALS[$module . '_control'];
    } else {
        if (isset($GLOBALS[$module . '_control_' . $ctrl])) {
            return $GLOBALS[$module . '_control_' . $ctrl];
        } else {
            $file = ROOT_PATH . "module/" . $module . "/source/$dir/$ctrl.ctrl.php";
            if (file_exists($file)) {
                include_once $file;
                $ctrlClass = $ctrl . "Control";
                return $GLOBALS[$module . '_control_' . $ctrl] = new $ctrlClass();

            } else {
                exit($ctrl . "不存在");
            }
        }
    }
}

/**
 *缓存
 */
function cache()
{
    if (isset($GLOBALS['cache'])) {
        return $GLOBALS['cache'];
    }

    $GLOBALS['cache'] = new cache();
    return $GLOBALS['cache'];
}
if ($cacheconfig['mysql']) {
    function cache_mysql_set($k, $v, $expire)
    {
        $c = C();
        M('dbcache')->set($k, $v, $expire);
    }

    function cache_mysql_get($k)
    {
        $c = C();
        return M('dbcache')->get($k);
    }
}
/*SESSION*/
function initsession()
{
    if (defined("SESSION_USER") && SESSION_USER == 1) {
        $s = new session();
    } else {
        session_start();
    }
}
if (!function_exists("sess_read")) {
    function sess_read($id)
    {
        $row = setDB('dbsession')->getRow("select * from " . TABLE_PRE . "dbsession WHERE id='" . $id . "'");
        return base64_decode($row['data']);
    }

    function sess_write($id, $data)
    {
        setDB('dbsession')->query("replace into " . TABLE_PRE . "dbsession set data='" . base64_encode($data) . "',dateline=" . time() . " ,id='" . $id . "' ");
    }

    function sess_destroy($id)
    {
        setDB('dbsession')->query("DELETE FROM " . TABLE_PRE . "dbsession WHERE id='" . $id . "' ");
    }
    function sess_gc($t)
    {
        setDB('dbsession')->query("DELETE FROM " . TABLE_PRE . "dbsession WHERE dateline<" . (time() - $t) . " ");
    }
}
/*End Session*/
setDb();
initsession();
//wap模板
$rewrite_on = "";
define("S_WAP_TEMPLATE_DIR", $wap_template_dir ? $wap_template_dir : ROOT_PATH . "themes/wap");
define("S_TEMPLATE_DIR", $template_dir ? $template_dir : ROOT_PATH . "themes/index");
define("S_CACHE_DIR", $cache_dir ? $cache_dir : ROOT_PATH . 'temp/caches');
define("S_HTML_DIR", $html_dir ? $html_dir : ROOT_PATH . "temp/html");
define("S_COMPILE_DIR", isset($compile_dir) ? $compile_dir : ROOT_PATH . "temp/compiled");
define("S_REWRITE_ON", $rewrite_on);
$_GET['m'] = $m = isset($_GET['m']) ? htmlspecialchars($_GET['m']) : "index";
$m = str_replace(array("/", "\\", ".."), "", $m);
if (!file_exists(CONTROL_DIR . "/$m.ctrl.php")) {
    $_GET['m'] = $m = "index";
}

include_once CONTROL_DIR . "/$m.ctrl.php";

$classname = $m . 'Control';

$control = new $classname();
$a = get_post('a');
if (empty($a)) {
    $_GET['a'] = 'default';
}

$method = method_exists($control, 'on' . $_GET['a']) ? 'on' . $_GET['a'] : "onDefault";
if (function_exists("userinit")) {
    if (!defined("SKYINIT")) {
        userinit($control);
    }
}
if (method_exists($control, 'onInit')) {
    $control->onInit();
}
$control->$method();

class skymvc
{
    public $smarty, $cache, $db, $config_data = array(), $lang = array(), $rewriterule = array();
    public $version = '2.0';
    public $skysqlrun = ""; //记录数据库执行的总数
    public $skysqlnum = 0;
    public function __construct($config = array())
    {

        $this->initLang();
        $this->initsmarty();
        $this->initCache();
        $this->db = setDb();
        if (!defined("SKYINIT")) {
            $GLOBALS['control'] = $this;
            define("SKYINIT", 1);
            //过渡方法
            userinit($this);
        }
    }

    //初始化语言包
    public function initLang()
    {
        if (file_exists(ROOT_PATH . "lang/" . LANG)) {
            $dir = ROOT_PATH . "lang/" . LANG;
            $d = opendir($dir);
            while (false !== ($f = readdir($d))) {
                if ($f != "." && $f != "..") {
                    if (strtolower(trim(substr(strrchr($f, '.'), 1))) == "php") {
                        @include $dir . "/" . $f;
                    }
                }
            }
            closedir($d);
            $this->lang = $lang;
        }
        //加载模块语言包

    }

    //初始化版本
    public function version()
    {
        $this->version;
    }

    public function initsmarty()
    {

        include_once "class/cls_smarty.php";
        $this->smarty = new Smarty();
        if (ISWAP) {
            $this->smarty->template_dir = S_WAP_TEMPLATE_DIR;
        } else {
            $this->smarty->template_dir = S_TEMPLATE_DIR;
        }
        $this->smarty->cache_dir = S_CACHE_DIR;
        $this->smarty->html_dir = S_HTML_DIR;
        $this->smarty->compile_dir = S_COMPILE_DIR;
        if (defined("TESTMODEL") && TESTMODEL == 1) {
            $this->smarty->direct_output = true;
        } else {
            $this->smarty->direct_output = false;
        }
        $this->smarty->assign("skins", "/" . (ISWAP ? S_WAP_TEMPLATE_DIR : S_TEMPLATE_DIR) . "/");
        $this->smarty->assign("lang", $this->lang);
    }

    public function initCache()
    {
        $this->cache = cache();
    }

    public function loadModuleModel($module, $model, $base = null)
    {
        $base = $base ? $base : $this;
        if (is_array($model)) {
            foreach ($model as $m) {
                $m = strtolower($m);
                $base->$m = MM($module, $m);
                //end

            }
        } else {
            $model = strtolower($model);
            $base->$model = MM($module, $model);
            //End

        }
    }

    /*相同APP_DIR的model载入*/
    public function loadModel($model, $base = null)
    {
        $base = $base ? $base : $this;
        if (is_array($model)) {

            foreach ($model as $m) {
                $m = strtolower($m);
                $base->$m = M($m, $base);
            }
        } else {
            $model = strtolower($model);
            $base->$model = M($model, $base);
        }
    }

    public function loadControl($ctrl, $dir = false, $base = null)
    {
        $base = $base ? $base : $this;
        $ctrl = strtolower($ctrl);
        $ctrlControl = $ctrl . "Control";
        $base->$ctrlControl = c($ctrl, $dir);
    }

    public function loadModuleControl($module, $ctrl, $dir = "index", $base = null)
    {
        $base = $base ? $base : $this;
        $dir = $dir ? $dir : CONTROL_DIR;
        $ctrl = strtolower($ctrl);
        $ctrlControl = $ctrl . "Control";
        $base->$ctrlControl = cc($module, $ctrl, $dir);
    }

    /*引入类*/
    public function loadClass($cls, $dir = false, $isnew = true)
    {
        $dir = $dir ? $dir : "library";
        if (in_array($cls, array("cache", "image", "upload"))) {
            if (defined("ISSAE") && ISSAE == 1) {
                include_once $dir . "/cls_{$cls}_sae.php"; //引入模板文件
            } else {
                @include_once "{$dir}/cls_{$cls}.php";
            }
        } else {
            @include_once "{$dir}/cls_{$cls}.php";
        }
        if ($isnew == true) {
            $this->$cls = new $cls();
        }
    }
    /*引入func*/
    public function loadfun($fun, $dir = false)
    {
        $dir = $dir ? $dir : "function";
        @include_once "{$dir}/fun_{$fun}.php";
    }
    /*引入配置文件*/
    public function loadconfig($file, $base = null)
    {
        if (file_exists(ROOT_PATH . "config/{$file}.php")) {
            @include ROOT_PATH . "config/{$file}.php";
            $base = $base ? $base : $this;

            if (isset($config)) {
                $GLOBALS['config_data'] += $config;
            }
        }
    }
    /*获取配置信息*/
    public function config_item($k = "", $base = null)
    {
        $base = $base ? $base : $this;
        if (empty($k)) {
            return $GLOBALS['config_data'];
        } else {
            if (is_array($k)) {
                $ct = count($k);
                $d = isset($GLOBALS['config_data'][$k[0]]) ? $GLOBALS['config_data'][$k[0]] : false;
                if ($d) {
                    for ($i = 1; $i < $ct; $i++) {
                        if (isset($d[$k[$i]])) {
                            $d = $d[$k[$i]];
                        } else {
                            return false;
                        }
                    }
                    return $d;
                } else {
                    return false;
                }
            } else {
                return isset($GLOBALS['config_data'][$k]) ? $GLOBALS['config_data'][$k] : false;
            }
        }
    }

    /*hook插件机制*/
    public function hook($param = array())
    {
        $m = get('m', 'h');
        $a = get('a', 'h');
        $m = $m ? $m : "index";
        $a = $a ? $a : "default";
        $this->loadconfig("hook");
        $hook = $this->config_item("hook");
        $data = $hook[$m . "_" . $a];

        if ($data) {
            foreach ($data as $k => $v) {
                if (file_exists(HOOK_DIR . "/" . $v[0] . ".hook.php")) {
                    require_once HOOK_DIR . "/" . $v[0] . ".hook.php";
                    $class = $v[0] . "hook";
                    $h = new $class(array("hook" => 1));
                    $h->$v[1]($param);
                }
            }
        }
    }
    /*全局hook*/
    public function hook_auto()
    {
        $this->loadconfig("hook");
        $hook = $this->config_item("hook");

        $data = $hook['hook_auto'];
        print_r($data);
        if ($data) {
            foreach ($data as $k => $v) {
                if (file_exists(HOOK_DIR . "/" . $v[0] . ".hook.php")) {
                    require_once HOOK_DIR . "/" . $v[0] . ".hook.php";
                    $class = $v[0] . "hook";
                    $h = new $class(array("hook" => 1));
                    $h->$v[1]($param);
                }
            }
        }
    }

    /*分页函数*/
    public function pagelist($rscount, $pagesize, $url, $num = 0)
    {
        if (function_exists("pagelist")) {
            return pagelist($rscount, $pagesize, $url, $num);
        }
        if (!$rscount or !$pagesize) {
            return false;
        }

        if (!defined("ISWAP")) {
            define("ISWAP", false);
        }
        if (!$num) {
            $num = ISWAP ? 6 : 10;
        }

        $url .= strpos($url, '?') !== false ? '&' : '?';
        $per_page = get_post('per_page', 'i');
        $page = ceil($per_page / $pagesize);
        $pagenum = ceil($rscount / $pagesize);
        if ($pagenum < 2) {
            return false;
        }

        $pagestart = 0;
        //中间页面
        $middle = ceil(($num - 2) / 2);

        $prevlot = $lastlot = "";
        if (($per_page - $pagesize * $num / 2) > 0) {
            $prevlot = '<a href="' . R($url . 'per_page=' . min($per_page - $pagesize * $num / 2, $rscount)) . '">....</a>';
        }
        if ($rscount - $per_page - $pagesize * $num / 2 > 0) {
            $lastlot = '<a href="' . R($url . 'per_page=' . min($per_page + $pagesize * $num / 2, $rscount)) . '">....</a>';
        }
        $min = intval(max(0, ($per_page - $pagesize * $middle)) / $pagesize);
        $pagelist = '<div class="pages">';
        if ($per_page == 0) {
            $pagelist .= '<strong>首页</strong>';
        } else {
            $pagelist .= '<a href="' . R($url) . '" class="first">首页</a>';
        }
        if (!ISWAP) {
            $pagelist .= $prevlot;
        }
        $max = min($min + $num, $pagenum);
        if ($max - $min < $num) {
            $min = max(1, $max - $num);
        }
        if (ISWAP) {
            if ($per_page > 0) {
                $pagelist .= '<a href="' . R($url . 'per_page=' . max(0, $per_page - $pagesize)) . '">上一页</a>';
            }
            if ($rscount > ($pagesize + get_post("per_page", "i"))) {
                $pagelist .= '<a href="' . R($url . 'per_page=' . min($per_page + $pagesize, $rscount)) . '">下一页</a>';
            }
        } else {
            for ($i = $min; $i < $max; $i++) {
                if ($i == 0) {
                    continue;
                }

                if ($i >= $pagenum - 1) {
                    continue;
                }

                if ($i == $page) {
                    $pagelist .= '<strong>' . $i . '</strong>';
                } else {
                    $pagelist .= '<a href="' . R($url . 'per_page=' . min($i * $pagesize, $rscount)) . '">' . $i . '</a>';
                }
            }
            $pagelist .= $lastlot;
        }

        if ($per_page >= $rscount - $pagesize) {
            $pagelist .= '<strong>尾页</strong>';
        } else {
            $pagelist .= '<a href="' . R($url . 'per_page=' . ($pagenum - 1) * $pagesize) . '" class="last">尾页</a>';
        }
        $pagelist .= '</div>';
        return $pagelist;
    }

    //信息提示跳转
    public function gomsg($str = '', $url = '', $tpl = '')
    {
        if (!$str) {
            $this->gourl($url);
        } else {

            $this->smarty->assign("message", $str);
            $this->smarty->assign("url", $url ? $url : $_SERVER['HTTP_REFERER']);
            if ($tpl) {
                $this->smarty->template_dir = $tpl;
            }
            $this->smarty->display("gomsg.html");
            exit;
        }

    }

    /*
    地址直接跳转
     */
    public function gourl($url = '')
    {
        if (empty($url)) {
            echo "<script>location.href='" . $_SERVER['HTTP_REFERER'] . "';</script>";
        } else {
            echo "<script>location.href='" . $url . "';</script>";
        }
        exit();
    }

    public function set_cookie($key, $val, $expire, $path = "/", $domain = "")
    {
        if (!$domain) {
            if (defined("COOKIE_DOMAIN") && COOKIE_DOMAIN != "") {
                $domain = COOKIE_DOMAIN;
            } else {
                if (!$domain) {
                    $domain = $_SERVER['HTTP_HOST'];
                }
            }
        }
        setcookie($key, $val, $expire, "/", $domain);
    }

    public function get_cookie($key)
    {
        return $_COOKIE[$key];
    }

    /*MD5加密*/
    public function umd5($string)
    {
        return md5(md5(md5($string)));
    }

    /*验证权限*/
    public function checkpermission($permission, $m = '', $a = '')
    {
        $m = strtolower($m ? $m : get('m', 'h'));
        $a = strtolower($a ? $a : get('a', 'h'));
        $a = $a ? $a : "default";
        if (!isset($permission[$m])) {
            return false;
        }

        $p = $permission[$m];
        $arr = array();
        if ($p) {
            foreach ($p as $v) {
                $arr = array_merge($arr, explode(",", $v));
            }
        }
        if (!in_array($a, $arr)) {
            return false;
        }
        return true;
    }

    public function __destruct()
    {

    }

    public function sexit($data = '')
    {

        exit($data);
    }
    /*Ajax和原始 跳转*/
    public function goall($message, $err = 0, $data = array(), $url = null)
    {
        if (!$url) {
            $url = $_SERVER['HTTP_REFERER'];
        }

        if (get_post('ajax', 1)) {
            if (get_post('jsonp')) {
                exit(get_post('callback', 'h') . "(" . json_encode(array("error" => $err, "message" => $message, "data" => $data, "url" => $url)) . ")");
            } else {
                exit(json_encode(array("error" => $err, "message" => $message, "data" => $data, "url" => $url)));
            }
        } else {
            $this->gomsg($message, $url);
        }
    }
    /*检测字符串*/
    public function checkBadWord()
    {
        if (!empty($_POST)) {
            require ROOT_PATH . "config/badword.php";
            foreach ($_POST as $k => $v) {
                if (is_string($v)) {
                    $b = preg_match("/(" . $badword . ")/iUs", $v, $a);

                    if (!empty($b)) {
                        $this->goall('有敏感字符串<span style="color:red;font-size:20px;">' . $a[1] . '</span>请删除该字符串', 1);
                    }
                }
            }
        }
    }

    public function assignlist($table, $option = array(), $url = '')
    {
        $option['limit'] = isset($option['limit']) ? $option['limit'] : 20;
        $pagesize = $option['limit'];
        $option['rscount'] = isset($option['rscount']) ? $option['rscount'] : true;
        $rscount = $option['rscount'];
        $start = get_post('per_page', 'i');
        $option['start'] = $start;
        $url = $url ? $url : $_SERVER['REQUEST_URI'];
        $url = preg_replace("/&per_page=\d+/i", "", $url);
        $url = str_replace("&&", "&", $url);
        $list = M($table)->select($option, $rscount);
        $pagelist = $this->pagelist($rscount, $pagesize, $url);
        $this->smarty->assign("rscount", $rscount);
        $this->smarty->assign("list", $list);
        $this->smarty->assign("pagelist", $pagelist);
    }

}
