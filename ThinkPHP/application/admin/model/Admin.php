<?php


namespace app\admin\model;
use think\Model;

/**
 * Class Admin
 * @package app\admin\model
 */
class Admin extends Model
{
    /**
     * 用户登录
     * @param string $username 用户名
     * @param string $password 密码
     * @return bool 成功返回true，失败返回false
     * @throws
     */
    static public function login($username,$password){

        //验证用户是否存在
        $map=array('username' => $username);
        $Admin=self::get($map);

        if(!is_null($Admin)){
            //账号禁用状态
            if($Admin->getData('authority')==3) {
                return false;
            }

            //验证密码是否正确
            if($Admin->checkPassword($password)){
                //登录
                session('adminid',$Admin->getData('adminid'));
                session('username',$Admin->getData('username'));
                session('authority',$Admin->getData('authority'));
                session('headpic',$Admin->getData('headpic'));
                return true;
            }
        }
        return false;
    }

    /**
     * 验证密码是否正确
     * @param string $password 密码
     * @return bool
     */
    public function checkPassword($password){
        if($this->getData('password')==$this::encryptPassword($password)){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 设计加密算法
     * @param string $password 加密前密码
     * @return string 加密后密码
     */
    static public function encryptPassword($password){
//        if (!is_string($password)){
//            throw new \RuntimeException("传入变量类型非字符串,错误码2",2);
//        }
        //还可以使用其他加密算法
        return sha1(md5($password).'shaohangyu');
    }

    /**
     * 注销
     * @return bool 成功true，失败false
     */
    static public function logOut(){
        session('adminid',null);
        return true;
    }

    /**
     * 判断用户是否登陆
     * @return bool 已登陆true
     * @throws
     */
    static public function isLogin(){
        $adminid=session('adminid');
        if(isset($adminid)){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 查询管理员信息
     * @return bool 数组
     * @throws
     */
    static public function saveData(){
        //整个过程重构为静态方法
        //查询plant_info和plant_class的信息并保存到txt中
        $AdminInfo=new self();

        //查询id,头像，用户名，权限，IP,登陆时间
        $admininfo=$AdminInfo
            ->alias('a')
            ->field('a.adminid,a.headpic,a.username,CASE a.authority WHEN 1 THEN "超级管理员" WHEN 2 THEN "管理员" ELSE "未启用" END as authority,a.IP,a.logintime')
            ->select();

        $adminjson=json_encode($admininfo,JSON_UNESCAPED_UNICODE);
        $fp=fopen("../public/static/data/admininfo.txt",'w+') or exit("Unable to open file!");
        fwrite($fp,$adminjson);
        fclose($fp);

        return true;
    }

    /**
     * 转换管理员等级
     * @param $authority 管理员等级
     * @return string 等级名
     * @throws
     */
    public function getAuthorityLevel($authority){
        if ($authority==1)
            return "超级管理员";
        else
            if($authority==2)
                return "管理员";
            else
                return "未启用";
    }
}