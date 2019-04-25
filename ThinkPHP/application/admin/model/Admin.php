<?php


namespace app\admin\model;
use think\model;

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
            //验证密码是否正确
            if($Admin->checkPassword($password)){
                //登录
                session('adminid',$Admin->getData('adminid'));
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
     * @return admininfo 数组
     * @throws
     */
    static public function saveData(){
        //查询plant_info和plant_class的信息并保存到txt中
        $AdminInfo=new self();

        //查询id,头像，用户名，权限，IP
        $admininfo=$AdminInfo
            ->alias('a')
            ->field('a.adminid,a.headpic,a.username,CASE a.authority WHEN 1 THEN "超级管理员" ELSE "管理员" END as authority,a.IP')
            ->select();

        return $admininfo;
    }
}