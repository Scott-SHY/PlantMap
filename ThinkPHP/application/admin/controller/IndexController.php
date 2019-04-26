<?php
namespace app\admin\controller;
use app\admin\model\Website;
use think\Controller;
use app\admin\model\Admin;
use think\Request;

/**
 * Class Index
 * @package app\admin\controller
 */
class IndexController extends Controller
{
    public function __construct()
    {
        parent::__construct();

        //验证用户是否登陆
        if(!Admin::isLogin()){
            //循环跳转？？
            return $this->error('please login first',url('Login/index'));
        }
    }

    //初始化首页信息
    public function test(){
        $Website=new Website();
        $website=$Website->select();
        $data=$website[0];
        $this->assign("data",$data);
        return $this->fetch('index');
    }

    public function index(){
        //重构，用Model的静态方法
        $webdata=Website::Webinit();
        $this->assign("data",$webdata);

        return $this->fetch();
    }
}
