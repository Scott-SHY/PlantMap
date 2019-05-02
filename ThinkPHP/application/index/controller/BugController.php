<?php


namespace app\index\controller;
use app\admin\model\Family;
use app\admin\model\Genus;
use app\admin\model\Website;
use app\index\model\Bug;
use think\Controller;
use think\Request;


/**
 * Class BugController
 * @package app\index\controller
 */
class BugController extends Controller
{
    public function index(){
        return $this->fetch();
    }

    public function submitbug(){
        $bug=Request::instance()->param();
//        var_dump($bug);

        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('pic');
        // 移动到框架应用根目录/public/uploads/ 目录下
        if($file){
            $info = $file->validate(['ext'=>'jpg,png,jpeg'])
                ->rule('uniqid')
                ->move(ROOT_PATH . 'public' . DS .'static'. DS . 'uploads'. DS .'bug');
            if($info){
                // 成功上传后 获取上传信息
            }else{
                // 上传失败获取错误信息
                echo $file->getError();
            }
        }else{
            var_dump($file);
        }

        $Bug=new Bug();
        $Bug->title=$bug['title'];
        $Bug->content=$bug['content'];
        $Bug->type=$bug['type'];
        $Bug->pic=$info->getSaveName();
        $Bug->state='未解决';
        $Bug->IP=Request::instance()->ip();
        $Bug->stime=date('Y-m-d H:i:s');
        $Bug->save();

        return $this->fetch('feed');
    }

    public function feed(){
        $Website=array();
        $plantnum=Website::field('plantnum')->select();
        $familynum=Family::field('count(*) as familynum')->select();
        $genusnum=Genus::field('count(*) as genusnum')->select();
        $Website=[
            'plantnum'=>$plantnum[0]->getData('plantnum'),
            'familynum'=>$familynum[0]->getData('familynum'),
            'genusnum'=>$genusnum[0]->getData('genusnum'),
        ];
        $this->assign('website',$Website);
        return $this->fetch();
    }
}