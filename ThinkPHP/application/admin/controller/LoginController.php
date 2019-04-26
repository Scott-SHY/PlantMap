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

            return $this->success('login success',url('Index/index'));
        }else{
            return $this->error('username or password incrrect',url('index'));
        }

//        //验证密码是否正确
//        $map=array('username' => $postData['username']);
//        $Admin=Admin::get($map);
//
//        //Admin要么是一个对象，要么是null
//        if(is_null($Admin)){
//            //验证密码
//            if($Admin->getData('password')!==$postData['password']){
//                //用户名密码错误，跳转登陆界面
//                return $this->error('password incrrect',url('index'));
//            }else{
//                //用户名密码正确,将amdinid存入session
//                session('adminid',$Admin->getData('adminid'));
//                return $this->session('login success',url('Index/index'));
//            }
//        }else{
//            //用户名不存在，跳转到登陆页
//            return $this->error('username not exit',url('index'));
//        }
    }

    //注销
    public function logOut(){
        if(Admin::logOut()){
            return $this->success('logout success',url('index'));
        }else{
            return $this->error('logout error',url('index'));
        }
    }

    //注册页
    public function register(){
        return $this->fetch();
    }

    public function test(){
        $Admin=new Admin();
        $admins=$Admin->select();
        var_dump($admins);
//        echo Admin::encryptPassword('admin');
    }

}