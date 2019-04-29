<?php


namespace app\admin\controller;
use think\Request;
use app\admin\model\Admin;
use think\Controller;

/**
 * Class Login
 * @package app\admin\controller
 */
class LoginController extends Controller
{

    //用户登录表单
    //登录页
    public function index(){
        //验证用户是否登陆
//        $adminid=session('adminid');
//        if(!Admin::isLogin()){
//            return $this->error('please login first',url('Login/index'));
//        }

//        $name=input('name');

//        //获取查询信息
//        $name=input('get.name');
//
//        //每页显示五条数据
//        $pageSize=5;
//
//        //实例化Admin
//        $Admin=new Admin();
//
//        //按条件查询并调用分页
//        $admins=$Admin->where('username','like','%'.$name.'%')->paginate($pageSize,false,[
//            'query'=>[
//                'username'=>$name,
//            ],
//        ]);
//
//        //向V层传数据
//        $this->assign('admins',$admins);
//
//        //取回打包后的数据
//        $html=$this->fetch();
//
//        //将数据返回给用户
//        return $html;

        //显示登录表单
//        return $this->fetch();

//        //新建Admin对象,自动关联admin数据表
//        $Admin=new Admin();
//        $admins=$Admin->select();
//
//        //向V层传数据
//        $this->assign('admin',$admins);
//
//        //取回打包后的数据
//        $html=$this->fetch();
//
//        //将数据返回给用户
//        return $html;
//        $admin=$admins[0];
//        var_dump($admin->getData('username'));

        //显示admin表所有内容
//        $admin=Db::name('admin')->select();
//        var_dump($admin);

        //返回登陆页
        return $this->fetch();
    }

    //注册页
    public function register(){
        return $this->fetch();
    }

    //登录
    public function login(){

        //登陆后需要刷新admininfo文件，并重新绘制表格(已完成)

        //接收post信息
        $postData=Request::instance()->post();

        //改用M层Admin静态函数
        if(Admin::login($postData['username'],$postData['password'])){
            //更新登录时间
            $Admin=new Admin();
            $Admin->where('adminid',session('adminid'))
                ->update(['logintime'=>date('Y-m-d H:i:s'),'IP'=>Request::instance()->ip()]);
            Admin::saveData();

            return $this->success('登陆成功',url('Index/index'));
        }else{
            return $this->error('用户名或密码不正确',url('index'));
        }
    }

    //注销
    public function logOut(){
        if(Admin::logOut()){
            return $this->success('注销成功',url('index'));
        }else{
            return $this->error('注销失败',url('index'));
        }
    }

    //注册页
    public function addAdmin(){
        $register=Request::instance()->param();
        if(!is_null(Admin::get(['username'=>$register['username']]))){
            return $this->error('用户名已存在',url('register'));
        }else{
            if($register['password']!=$register['againpassword']){
                return $this->error('密码不一致',url('register'));
            }
        }
        $Admin=new Admin();
        $Admin->authority=2;
        $Admin->username=$register['username'];
        $Admin->password=Admin::encryptPassword($register['password']);
        $Admin->IP=Request::instance()->ip();
        $Admin->save();

        return $this->success('注册成功',url('index'));
    }

    public function test(){
        $Admin=new Admin();
        $admins=$Admin->select();
        var_dump($admins);
//        echo Admin::encryptPassword('admin');
    }

}