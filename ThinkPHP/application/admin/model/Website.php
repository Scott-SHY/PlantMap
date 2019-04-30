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
}