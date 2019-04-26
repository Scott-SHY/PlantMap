<?php


namespace app\admin\model;
use think\Model;

/**
 * Class Family
 * @package app\admin\model
 */
class Family extends Model
{
    /**
     * 查询科信息
     * @return familyinfo 数组
     * @throws
     */
    static public function saveFamilyData(){
        //查询family的信息并保存到txt中
        $FamilyInfo=new self();

        //查询id,头像，用户名，权限，IP,登陆时间
        $familyinfo=$FamilyInfo
            ->alias('f')
            ->field('f.familyid,f.name')
            ->select();

        return $familyinfo;
    }

    /**
     * @param $familyid 科id
     * @return familyname 科名
     * @throws
     */
    static public function getFamilyName($familyid){
        $Family=new self();

        //查找id对应的name
        $name=$Family
            ->field('name')
            ->where('familyid',$familyid)
            ->select();

        return $name[0];
    }
}