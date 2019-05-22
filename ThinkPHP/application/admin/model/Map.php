<?php


namespace app\admin\model;

use app\admin\model\PlantMap;
use think\Model;

/**
 * Class Map
 * @package app\admin\model
 */
class Map extends Model
{
    /**
     * 根据plantname匹配区域，判断是否需要勾选复选框
     * @param PlantInfo $PlantInfo
     * @return bool
     * @throws
     */
    public function getIsChecked(PlantInfo &$PlantInfo){
        //获取Map和PlantInfo的name
        $mapname=$this->mapname;
        $plantname=$PlantInfo->plantname;

        //查找PlantMap中有无对应记录
        $map=array();
        $map['mapname']=$mapname;
        $map['plantname']=$plantname;
        $plantmap=PlantMap::get($map);

        //判断有无记录
        if(is_null($plantmap)){
            return false;
        }else {
            return true;
        }
    }

    /**
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function updateMap(){
        $number=PlantMap::field('mapname,count(*) as number')->group('mapname')->select();
        for($i=0; $i<8; $i++){
            Map::where('mapname',$number[$i]->getData('mapname'))
                ->update(['number'=>$number[$i]->getData('number')]);
        }
        return true;
    }
}