<?php


namespace app\admin\controller;
use app\admin\model\Bug;
/**
 * Class BugController
 * @package app\admin\controller
 */
class BugController extends IndexController
{
    public function index(){
        //每次调用方法都会更新一遍数据？效率低下，应该把静态方法放在增删改操作之后调用
        //更新数据
        Bug::saveData();

        return $this->fetch();
    }

    public function manage(){
        return $this->fetch();
    }


    //已重构，可以删除
    public function savedata(){
        $buginfo=Bug::saveData();

        //存储json信息,$test2是json转换后的变量
        $bugjson=json_encode($buginfo,JSON_UNESCAPED_UNICODE);
        $fp=fopen("../public/static/data/buginfo.txt",'w+') or exit("Unable to open file!");
        fwrite($fp,$bugjson);
        fclose($fp);

        return $this->fetch('index');
    }

}