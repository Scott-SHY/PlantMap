<?php


namespace app\admin\controller;
use app\admin\model\Bug;
use app\admin\model\Map;
use app\admin\model\PlantClass;

/**
 * Class ChartController
 * @package app\admin\controller
 */
class ChartController extends IndexController
{
    public function index()
    {
        return $this->fetch();
    }

    public function mapnum(){
        $Map=new Map();
        $Map=$Map->select();
        $mapjson=json_encode($Map,JSON_UNESCAPED_UNICODE);
        echo $mapjson;
    }

    public function familynum(){
        $Family=new PlantClass();
        $Family=$Family->field('familyname,COUNT(*) as number')
            ->group('familyname')
            ->select();
        $familyjson=json_encode($Family,JSON_UNESCAPED_UNICODE);
        echo $familyjson;
    }

    public function bugnum(){
        $Bug=new Bug();
        $Bug=$Bug->field('state,COUNT(*) as number')
            ->group('state')
            ->select();
        $bugjson=json_encode($Bug,JSON_UNESCAPED_UNICODE);
        echo $bugjson;
    }
}