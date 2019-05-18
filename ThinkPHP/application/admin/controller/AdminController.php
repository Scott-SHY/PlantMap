<?php


namespace app\admin\controller;
use app\admin\model\Admin;
use app\admin\model\Family;
use app\admin\model\Genus;
use think\Request;

/**
 * Class AdminController
 * @package app\admin\controller
 */
class AdminController extends IndexController
{
    public function index(){
//        if(session('authority')==1){
//            Admin::saveData();
//        }else{
//            return $this->error('您没有权限访问此页面');
//        }
        //每次调用方法都会更新一遍数据？效率低下，应该把静态方法放在增删改操作之后调用
        //更新数据
        Admin::saveData();

        return $this->fetch();
    }

    /**
     * 超级管理员对此有操作权限
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function manage(){
        if(session('authority')==2||session('authority')==3){
            return $this->error('您没有权限访问此页面');
        }
        $map=Request::instance()->param('adminid');
        $Admin=Admin::get($map);
        //被编辑的管理员id
        session('manageadminid',$Admin->adminid);

        if(!is_null($Admin)){
            $this->assign('admin',$Admin);
        }
        return $this->fetch();
    }

    /**
     * 更改管理员权限
     * @return mixed
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function changeAuthority(){
        $cauthority=Request::instance()->post();
        $Admin=Admin::get(session('manageadminid'));
        $Admin->authority=$cauthority['cauthority'];
        $Admin->isUpdate(true)->save();

        //超级管理员个数
        $admincount=Admin::where('authority',1)->count();

        //没有超级管理员
        if ($admincount<=0){
            $Admin->authority=session('authority');
            $Admin->isUpdate(true)->save();
            $this->assign('admin',$Admin);
            return $this->error('至少有一个超级管理员!');
        }else{
            $this->assign('admin',$Admin);
            return $this->fetch('manage');
        }

    }

    //删除账号后有些级联情况无法解决
    public function deleteAdmin(){
        echo 'delete';
    }

    /**
     * setting视图初始化，显示科属信息
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function setting(){
        $this->savefamilydata();
        $this->savegenusdata();

        //查询Family的所有值传递给视图
        $family=Family::all();
        $this->assign('family',$family);

        return $this->fetch();
    }

    /**
     * 匹配Admin信息，并传递给视图
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function account(){
        $map=Request::instance()->param('adminid');
        if(is_null($map)){
            //从header跳转过来，调用session中的值
            $map=session('adminid');
            $Admin=Admin::get($map);
        }else{
            //从index跳转过来，调用传递值
            $Admin=Admin::get($map);
        }
        if(!is_null($Admin)){
            $this->assign('admin',$Admin);
        }
        return $this->fetch();
    }

    /**
     * 修改密码,接收seesion中的信息，只能由用户自己操作
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function changepwd(){
        $pwd=Request::instance()->param();
        $admin=session('adminid');
        $Admin=Admin::get($admin);
//        var_dump($pwd['oldpwd']);
//        var_dump($Admin->password);
//        var_dump(Admin::encryptPassword($pwd['oldpwd']));
        if($Admin->password!=Admin::encryptPassword($pwd['oldpwd'])){
            return $this->error('原密码错误!');
        }else{
            if($pwd['newpwd']==$pwd['oldpwd']){
                return $this->error('新旧密码不能相同！');
            }else{
                if($pwd['newpwd']!=$pwd['againpwd']){
                    return $this->error('二次密码错误!');
                }
            }
        }
        $Admin->password=Admin::encryptPassword($pwd['newpwd']);
        $Admin->isUpdate(true)->save();
        $this->assign('admin',$Admin);

        return $this->success('密码修改成功',url('account'));
    }

    /**
     * 查询科信息并保存到文件中
     */
    public function savefamilydata(){
        $familyinfo=Family::saveFamilyData();

        //存储json信息,$test2是json转换后的变量
        $familyjson=json_encode($familyinfo,JSON_UNESCAPED_UNICODE);
        $fp=fopen("../public/static/data/familyinfo.txt",'w+') or exit("Unable to open file!");
        fwrite($fp,$familyjson);
        fclose($fp);

//        return $this->fetch('setting');
    }

    /**
     * 查询属信息并保存到文件中
     */
    public function savegenusdata(){
        $genusinfo=Genus::saveGenusData();

        //存储json信息,$test2是json转换后的变量
        $genusjson=json_encode($genusinfo,JSON_UNESCAPED_UNICODE);
        $fp=fopen("../public/static/data/genusinfo.txt",'w+') or exit("Unable to open file!");
        fwrite($fp,$genusjson);
        fclose($fp);

//        return $this->fetch('setting');
    }

    /**
     * 新增一个科名
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function addFamily(){
        $family=Request::instance()->param();
        if(!is_null(Family::get(['name'=>$family['familyname']])) ||
            !is_null(Family::get(['name'=>$family['familyname']."科"]))){
            return $this->error('已存在此科名');
        }else{
            $Family=new Family();
            $Family->name=$family['familyname'];
            $Family->save();
        }
        $this->savefamilydata();
        $this->savegenusdata();
        $family=Family::all();
        $this->assign('family',$family);
        return $this->fetch('setting');
    }

    /**
     * 修改科的名字
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function updateFamily(){
        $family=Request::instance()->param();
        if(!is_null(Family::get(['name'=>$family['updatefamilyname']])) ||
            !is_null(Family::get(['name'=>$family['updatefamilyname']."科"]))){
            return $this->error('已存在此科名');
        }else{
            $Family=Family::get($family['familyid']);
            $Family->name=$family['updatefamilyname'];
            $Family->isUpdate(true)->save();
        }
        $this->savefamilydata();
        $this->savegenusdata();
        $family=Family::all();
        $this->assign('family',$family);
        return $this->fetch('setting');
    }

    /**
     * 新增指定科名下的一个属名
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function addGenus(){
        $genus=Request::instance()->param();
        if(!is_null(Genus::get(['name'=>$genus['genusname']])) ||
            !is_null(Genus::get(['name'=>$genus['genusname']."属"]))){
            return $this->error('已存在此属名');
        }else{
            $Genus=new Genus();
            $Genus->name=$genus['genusname'];
            $Genus->familyname=$genus['familyname'];
            $Genus->save();
        }
        $this->savefamilydata();
        $this->savegenusdata();
        $family=Family::all();
        $this->assign('family',$family);
        return $this->fetch('setting');
    }

    /**
     * 修改属的名字
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function updateGenus(){
        $genus=Request::instance()->param();
        if(!is_null(Genus::get(['name'=>$genus['updategenusname']])) ||
            !is_null(Genus::get(['name'=>$genus['updategenusname']."属"]))){
            return $this->error('已存在此属名');
        }else{
            $Genus=Genus::get($genus['genusid']);
            $Genus->name=$genus['updategenusname'];
            $Genus->isUpdate(true)->save();
        }
        $this->savefamilydata();
        $this->savegenusdata();
        $family=Family::all();
        $this->assign('family',$family);
        return $this->fetch('setting');
    }

    //已被重构，可以删除
    //index页查询管理员信息
    public function savedata(){
        $admininfo=Admin::saveData();

        //存储json信息,$test2是json转换后的变量
        $adminjson=json_encode($admininfo,JSON_UNESCAPED_UNICODE);
        $fp=fopen("../public/static/data/admininfo.txt",'w+') or exit("Unable to open file!");
        fwrite($fp,$adminjson);
        fclose($fp);

        return $this->fetch('index');
    }

    /**
     * 修改头像
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function changeheadpic(){
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('headimage');
        // 移动到框架应用根目录/public/uploads/ 目录下
        if($file){
            $info = $file->validate(['ext'=>'jpg,png,jpeg'])
                ->rule('uniqid')
                ->move(ROOT_PATH . 'public' . DS .'static'. DS . 'uploads'. DS .'head');
            if($info){
                // 成功上传后 获取上传信息
            }else{
                // 上传失败获取错误信息
                echo $file->getError();
            }
        }else{
            var_dump($file);
        }
        $Admin=Admin::get(session('adminid'));
        if($Admin->headpic!='default.jpg'){
            $oldpic=$Admin->headpic;
            $Admin->headpic='default.jpg';
            unlink('../public/static/uploads/head/'.$oldpic);
        }
        $Admin->headpic=$info->getSaveName();
        session('headpic',$Admin->headpic);
        $Admin->isUpdate(true)->save();
        $this->assign('admin',$Admin);
        return $this->fetch('account');
    }

    public function test2(){
        echo time();
        echo date('Y-m-d H:i:s');
    }

}