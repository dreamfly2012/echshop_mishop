<?php
class iframeControl extends skymvc
{

    public function __construct()
    {
        parent::__construct();
        $this->loadModel(array("navbar", "admin_group"));
        $this->loadConfig("version");
    }

    public function onDefault()
    {

        $this->smarty->display("iframe/iframe.html");
    }

    public function onTop()
    {
        $data = $this->navbar->select(array(
            "where" => array("group_id" => 1, "status" => 1),
            "order" => " orderindex ASC",
        )
        );
        $this->smarty->assign("data", $data);
        $this->smarty->display("iframe/iframe_top.html");
    }
    public function onLeft()
    {
        $id = get_post('id', 'i');
        $where = array(
            "pid" => 0,
            "group_id" => 2,
            "id" => $id,
            "status" => 1,
        );
        $option = array(
            "where" => $where,
            "order" => " orderindex ASC",
        );
        $permission = unserialize($this->admin_group->selectOne(array("where" => array("id" => $_SESSION['ssadmin']['group_id']), "fields" => "content")));
        $_SESSION['ssadmin']['permission'] = $permission;
        $data = $this->navbar->select($option, $rscount);
        if ($data) {
            foreach ($data as $k => $v) {
                $child = $this->navbar->select(array("where" => array("pid" => $v['id'], "status" => 1), "order" => " orderindex ASC"));
                if ($child) {
                    foreach ($child as $kk => $vv) {
                        if (!$this->checkpermission($_SESSION['ssadmin']['permission'], $vv['m'], $vv['a'])) {
                            if (!$_SESSION['ssadmin']['isfounder']) {
                                unset($child[$kk]);
                            }

                        }
                    }
                }
                $v['child'] = $child;
                $data[$k] = $v;
            }
        }
        $this->smarty->assign(
            array("data" => $data)
        );
        $this->smarty->display("iframe/iframe_left.html");
    }

    public function onMain()
    {
        //检测文件夹权限
        // $dirs = array("api", "attach", "config", "images", "js", "plugin", "skins", "skymvc", "source", "temp", "update");
        // foreach ($dirs as $d) {
        //     if (is_writable(ROOT_PATH . $d)) {
        //         $data[$d] = true;
        //     } else {
        //         $data[$d] = false;
        //     }
        // }
        // $this->smarty->assign(array(
        //     "data" => $data,
        // ));
        //$this->smarty->display("iframe/iframe_main.html");
    }

    public function onCheckNewVersion()
    {
        $key = "admin_iframe_CheckNewVersion";
        if (!$data = cache()->get($key)) {
            $data = curl_get_contents(CHECKVERSION . "&domain=" . $_SERVER['HTTP_HOST']);
            cache()->set($key, $data, 60);
        }

        if ($data > VERSION_NUM) {
            echo "最新版本为" . $data . "，<a href='javascript:;' id='update_submit' class='btn btn-warning'>在线更新</a>。";
        } else {
            echo "目前已经是最新版本了。";
        }
    }

    public function onUpdate()
    {
        set_time_limit(10000);
        $key = "admin_iframe_update";

        if (!$v = cache()->get($key)) {
            $v = curl_get_contents(ONLINEUPDATE . "&domain=" . $_SERVER['HTTP_HOST']);

            cache()->set($key, $v, 1);
        }

        $v = json_decode($v, true);
        if (isset($v['error'])) {
            exit(json_encode(array("error" => 1, "message" => $v['message'])));
        }
        $now = VERSION_NUM;

        if ($v) {
            foreach ($v as $d) {
                if ($d['v'] > $now) {

                    $this->updateNow($d['f']);
                    $now = $d['v'];
                }
            }
        }
        exit(json_encode(array("error" => 0, "message" => "success")));

    }
    public function onshouquan()
    {
        $key = "admin_iframe_shouquan";

        if (!$data = cache()->get($key)) {
            $data = curl_get_contents(CHECKSHOUQUAN . "&domain=" . $_SERVER['HTTP_HOST']);
            cache()->set($key, $data, 60);
        }
        echo $data;
    }

    public function updateNow($file)
    {
        umkdir("update");
        file_put_contents(ROOT_PATH . "update/update.zip", file_get_contents(ONLINEUPDATE_DIR . $file));

        $this->loadClass("pclzip", false, false);
        $zip = new pclzip(ROOT_PATH . "update/update.zip");
        $zip->extract(ROOT_PATH . "update");
        curl_get_contents("http://" . $_SERVER['HTTP_HOST'] . "/update/index.php?a=update");
        delfile(ROOT_PATH . "update", 1);
        return true;
    }

}
