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
     * @return bool 数组
     * @throws
     */
    static public function saveData()
    {
        //查询bug
        $BugInfo = new self();

        //查询反馈id,标题，提交时间，状态，解决时间，管理员id
        $buginfo = $BugInfo
            ->alias('b')
            ->field('b.bugid,b.title,b.state,b.stime,b.ftime,b.adminid as adminname')
            ->where('b.adminid', 'exp', 'is null')
            ->union('select bugid,title,state,stime,ftime,admin.username as adminame from bug,admin where bug.adminid=admin.adminid')
            ->select();

        //存储json信息,$bugjson是json转换后的变量
        $bugjson=json_encode($buginfo,JSON_UNESCAPED_UNICODE);
        $fp=fopen("../public/static/data/buginfo.txt",'w+') or exit("Unable to open file!");
        fwrite($fp,$bugjson);
        fclose($fp);

        return true;
    }
}