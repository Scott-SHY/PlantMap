<?php


namespace app\admin\model;
use think\Model;

/**
 * Class Website
 * @package app\admin\model
 */
class Website extends Model
{
    /**
     * 网站基本信息
     * @return array 数组第一个元素
     * @throws
     */
    static public function Webinit(){
        $Website=new self();
        $website=$Website->select();
        $webdata=$website[0];
        return $webdata;
    }

    /**
     * 更新网站信息
     * @return boolean
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function updateWeb(){
        $Website=Website::get('Plant');
        $plantnum=PlantInfo::field('count(*) as num')->select();
        $bugnum=Bug::field('count(*) as num')->select();
        $adminnum=Admin::field('count(*) as num')->select();
        $Website->plantnum=$plantnum[0]->getData('num');
        $Website->bugnum=$bugnum[0]->getData('num');
        $Website->adminnum=$adminnum[0]->getData('num');
        $Website->save();
        return true;
    }
}