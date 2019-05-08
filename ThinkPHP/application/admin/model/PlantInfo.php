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
        $plantname=$this->getData('plantname');
        $PlantClass=PlantClass::get($plantname);
        return $PlantClass;
    }

    /**
     * 获取信息并保存
     * @return bool 查询结果数组
     * @throws
     */
    static public function saveData(){
        //查询plant_info和plant_class的信息并保存到txt中
        $PlantInfo=new self();

        //查询名称，别名，学名，区域，科，属，介绍
        //join()函数用来关联两个表
        //where这里不用也可以
        $plantinfo=$PlantInfo
            ->alias('i')
            ->join('plant_class c','c.plantid=i.plantid')
            ->join('plant_admin a','a.plantid=i.plantid')
            ->join('admin ad','ad.adminid=a.adminid')
            ->field('i.plantname,i.alias,i.sciname,i.area,c.familyname,c.genusname,ad.username,a.updatetime')
//            ->where('i.name=c.plantname')
            ->select();

        //存储json信息,$plantjson是json转换后的变量
        $plantjson=json_encode($plantinfo,JSON_UNESCAPED_UNICODE);
        $fp=fopen("../public/static/data/plantinfo.txt",'w+') or exit("Unable to open file!");
        fwrite($fp,$plantjson);
        fclose($fp);

        return true;
    }
}