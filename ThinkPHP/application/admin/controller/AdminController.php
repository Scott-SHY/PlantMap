<?php


namespace app\admin\controller;
use app\admin\model\Admin;

/**
 * Class AdminController
 * @package app\admin\controller
 */
class AdminController extends IndexController
{
    public function index(){
        return $this->fetch();
    }

    public function savedata(){
        $admininfo=Admin::saveData();

        //存储json信息,$test2是json转换后的变量
        $adminjson=json_encode($admininfo,JSON_UNESCAPED_UNICODE);
        $fp=fopen("../public/static/data/admininfo.txt",'w+') or exit("Unable to open file!");
        fwrite($fp,$adminjson);
        fclose($fp);

        return $this->fetch('index');
    }

}