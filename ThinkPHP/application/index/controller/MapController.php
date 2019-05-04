<?php


namespace app\index\controller;
use app\index\model\Family;
use app\index\model\Genus;
use app\index\model\Map;
use think\Controller;
use think\Request;

/**
 * Class MapController
 * @package app\index\controller
 */
class MapController extends Controller
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
}