<?php


namespace app\index\controller;
use app\index\model\Map;
use app\index\model\PlantClass;
use app\index\model\PlantInfo;
use app\index\model\PlantMap;
use think\Controller;
use think\Request;

/**
 * Class PlantController
 * @package app\index\controller
 */
class PlantController extends Controller
{
    public function index(){
        $PlantInfo=new PlantInfo();
        $Plant=$PlantInfo
            ->alias('i')
            ->join('plant_class c','c.plantid=i.plantid')
            ->field('i.plantid,i.plantname,c.familyname,c.genusname,i.alias,i.area')
            ->paginate(9);
//            ->select();
        $this->assign('plant',$Plant);
//        $PlantInfo=new PlantInfo();
//        $PlantInfo=$PlantInfo->select();
//        $this->assign('plantinfo',$PlantInfo);
//
//        $PlantClass=new PlantClass();
//        $PlantClass=$PlantClass->select();
//        $this->assign('plantclass',$PlantClass);

        $Map=new Map();
        $Map=$Map->field('mapname,number')->select();
        $this->assign('map',$Map);
        return $this->fetch();
    }

    public function single(){
        $plant=Request::instance()->param();
        $Plant=new PlantInfo();
        $Plant=$Plant
            ->alias('p')
            ->select();

        var_dump($Plant);
//        return $this->fetch();
    }

    public function test(){
        $Map=new Map();
        $Map=$Map->field('mapname,number')->select();
        $this->assign('map',$Map);
        return $this->fetch();
    }

    public function test2(){
        $map=Request::instance()->param();

    }

    public function test3(){
        var_dump(Request::instance()->param());
    }
}