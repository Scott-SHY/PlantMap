<?php


namespace app\admin\model;
use think\Model;

/**
 * Class PlantInfoController
 * @package app\admin\model
 */
class PlantInfo extends Model
{
    /**
     * 获取植物科属信息
     * @return PlantClass 植物分类
     * @throws
     */
    public function getPlantClass(){
        $plantname=$this->getData('name');
        $PlantClass=PlantClass::get($plantname);
        return $PlantClass;
    }
}