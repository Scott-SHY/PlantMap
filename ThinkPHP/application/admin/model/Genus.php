<?php


namespace app\admin\model;
use think\Model;

/**
 * Class Genus
 * @package app\admin\model
 */
class Genus extends Model
{
    /**
     * 查询属信息
     * @return genusinfo 数组
     * @throws
     */
    static public function saveGenusData(){
        //查询family的信息并保存到txt中
        $GenusInfo=new self();

        //查询id,头像，用户名，权限，IP,登陆时间
        $genusinfo=$GenusInfo
            ->alias('g')
            ->field('g.genusid,g.familyname,g.name')
            ->select();

        return $genusinfo;
    }

    /**
     * @param $genusid 属id
     * @return name 属名
     * @throws
     */
    static public function getGenusName($genusid){
        $Genus=new self();

        //查找id对应的name
        $name=$Genus
            ->field('name')
            ->where('genusid',$genusid)
            ->select();

        return $name[0];
    }
}