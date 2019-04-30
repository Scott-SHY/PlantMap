<?php


namespace app\admin\controller;
use app\admin\model\Bug;
use think\Request;

/**
 * Class BugController
 * @package app\admin\controller
 */
class BugController extends IndexController
{
    public function index(){
        //每次调用方法都会更新一遍数据？效率低下，应该把静态方法放在增删改操作之后调用
        //更新数据
        Bug::saveData();

        return $this->fetch();
    }

    /**
     * Bug信息查看并修改
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function manage(){
        $map=Request::instance()->param('bugid');
        $Bug=Bug::get($map);

        if(!is_null($Bug)){
            $this->assign('bug',$Bug);
        }
        return $this->fetch();
    }

    /**
     * 修改Bug状态并保存
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function saveBug(){
        $map=Request::instance()->param();
        $Bug=Bug::get(['bugid'=>$map['bugid']]);

        //判断Bug状态，已经解决的无法修改
        if($Bug->state=="已解决" && $map['state']=="未解决"){
            return $this->error('此问题已解决，无法修改');
        }else{
            $Bug->state=$map['state'];
            $Bug->ftime=date('Y-m-d H:i:s');
            $Bug->adminid=session('adminid');
            $Bug->isUpdate(true)->save();
            $this->assign('bug',$Bug);
            return $this->fetch('manage');
        }
    }

    //已重构，可以删除
    public function savedata(){
        $buginfo=Bug::saveData();

        //存储json信息,$test2是json转换后的变量
        $bugjson=json_encode($buginfo,JSON_UNESCAPED_UNICODE);
        $fp=fopen("../public/static/data/buginfo.txt",'w+') or exit("Unable to open file!");
        fwrite($fp,$bugjson);
        fclose($fp);

        return $this->fetch('index');
    }

}