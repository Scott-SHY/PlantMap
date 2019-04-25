<?php


namespace app\admin\model;
use think\Model;

/**
 * Class Bug
 * @package app\admin\model
 */
class Bug extends Model
{
    /**
     * 查询Bug信息
     * @return buginfo 数组
     * @throws
     */
    static public function saveData(){
        //查询bug
        $BugInfo=new self();

        //查询反馈id,标题，提交时间，状态，解决时间，管理员id
        $buginfo=$BugInfo
            ->alias('b')
            ->field('a.adminid,a.headpic,a.username,a.authority,a.IP')
            ->select();

        return $buginfo;
    }
}