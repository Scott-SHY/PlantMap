<?php


namespace app\admin\controller;
use app\admin\model\Admin;
use app\admin\model\Family;
use app\admin\model\Genus;
/**
 * Class AdminController
 * @package app\admin\controller
 */
class AdminController extends IndexController
{
    public function index(){
        //每次调用方法都会更新一遍数据？效率低下，应该把静态方法放在增删改操作之后调用
        //更新数据
        Admin::saveData();

        return $this->fetch();
    }

    public function setting(){
        $this->savefamilydata();
        $this->savegenusdata();

        //查询Family的所有值传递给视图
        $family=Family::all();
        $this->assign('family',$family);

        return $this->fetch();
    }

    public function account(){
        $map=session('adminid');
        $Admin=Admin::get($map);
        if(!is_null($Admin)){
            $this->assign('admin',$Admin);
        }
        return $this->fetch();
    }

    //setting页查询科名信息
    public function savefamilydata(){
        $familyinfo=Family::saveFamilyData();

        //存储json信息,$test2是json转换后的变量
        $familyjson=json_encode($familyinfo,JSON_UNESCAPED_UNICODE);
        $fp=fopen("../public/static/data/familyinfo.txt",'w+') or exit("Unable to open file!");
        fwrite($fp,$familyjson);
        fclose($fp);

//        return $this->fetch('setting');
    }

    //setting页查询属名信息
    public function savegenusdata(){
        $genusinfo=Genus::saveGenusData();

        //存储json信息,$test2是json转换后的变量
        $genusjson=json_encode($genusinfo,JSON_UNESCAPED_UNICODE);
        $fp=fopen("../public/static/data/genusinfo.txt",'w+') or exit("Unable to open file!");
        fwrite($fp,$genusjson);
        fclose($fp);

//        return $this->fetch('setting');
    }

    //已被重构，可以删除
    //index页查询管理员信息
    public function savedata(){
        $admininfo=Admin::saveData();

        //存储json信息,$test2是json转换后的变量
        $adminjson=json_encode($admininfo,JSON_UNESCAPED_UNICODE);
        $fp=fopen("../public/static/data/admininfo.txt",'w+') or exit("Unable to open file!");
        fwrite($fp,$adminjson);
        fclose($fp);

        return $this->fetch('index');
    }

    public function test2(){
        echo time();
        echo date('Y-m-d H:i:s');
    }

}