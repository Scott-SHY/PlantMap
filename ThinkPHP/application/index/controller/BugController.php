<?php


namespace app\index\controller;
use app\index\model\Family;
use app\index\model\Genus;
use app\index\model\Website;
use app\index\model\Bug;
use app\index\model\Map;
use think\Controller;
use think\Request;


/**
 * Class BugController
 * @package app\index\controller
 */
class BugController extends Controller
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $Map=new Map();
        $Map=$Map->field('mapname')->select();
        $this->assign('map',$Map);

        $family=Family::all();
        $genus=Genus::all();
        $this->assign('family',$family);
        $this->assign('genus',$genus);
    }

    /**
     * 获取视图中post请求，根据科下拉栏的值更改属下拉栏的内容
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getGenus(){
        //二级菜单属名显示
        if(request()->isPost()){
            //这个familyid要和ajax中的data值相同
            $familyid=input('familyid');

            $genusid=new Genus();
            $genusselect=$genusid
                ->alias('g')
                //关联查询family表的name
                ->join('family f','f.name=g.familyname')
                //结果过滤，只留下属id和属名
                ->field('g.genusid,g.name')
                //查找科id是所选择的id
                ->where('f.familyid',$familyid)
                ->select();

            return json($genusselect);
        }
    }

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