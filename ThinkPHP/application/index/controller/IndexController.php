<?php
namespace app\index\controller;
use app\index\model\Family;
use app\index\model\Genus;
use app\index\model\Map;
use app\index\model\PlantMap;
use app\index\model\Website;
use think\Controller;
use think\Request;

class IndexController extends Controller
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

    public function index(){
        $map=Map::all();
        $Map=new PlantMap();
        for ($i = 0;$i < 8;$i++) {
            $mapnum[$i] = $Map
                ->alias('m')
                ->join('plant_class c', 'm.plantname=c.plantname')
                ->field('m.mapname,c.familyname,COUNT(c.plantname) as number')
                ->where('m.mapname', '=', $map[$i]['mapname'])
                ->group('m.mapname,c.familyname')
                ->order('number desc')
                ->limit(4)
                ->select();
        }
        $this->assign('mapnum',$mapnum);
//        var_dump($mapnum[0][0]['mapname']);
        return $this->fetch();
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

    public function about(){
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
