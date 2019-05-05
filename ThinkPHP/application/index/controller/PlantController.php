<?php


namespace app\index\controller;
use app\index\model\Family;
use app\index\model\Genus;
use app\index\model\Map;
use app\index\model\PlantClass;
use app\index\model\PlantInfo;
use app\index\model\PlantMap;
use think\Controller;
use think\Request;

/**
 * Class PlantController
 * @package app\index\controller
 */
class PlantController extends Controller
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);

        $Class=new PlantClass();
        $Class=$Class
            ->field('familyname,count(plantname) as number')
            ->group('familyname')
            ->order('number desc')
            ->limit(8)
            ->select();
        $this->assign('class',$Class);

        $Map=new Map();
        $Map=$Map->field('mapname')->select();
        $this->assign('map',$Map);

        $Mapnum=new Map();
        $Mapnum=$Mapnum->field('mapname,number')->select();
        $this->assign('mapnum',$Mapnum);

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

        $PlantInfo=new PlantInfo();
        $PlantInfo=$PlantInfo
            ->alias('i')
            ->join('plant_class c','c.plantid=i.plantid')
            ->field('i.plantid,i.plantname,c.familyname,c.genusname,i.alias,i.area')
            ->paginate(9,false, ['query' => request()->param()]);
        $this->assign('plant',$PlantInfo);

        return $this->fetch();
    }

    public function search(){
//        if(Request::instance()->isGet()){
//            $search=Request::instance()->param();
//        }else{
//            $map=Request::instance()->param();
//            $search=array();
//            $search['search']='';
//            $search['mapname']='';
//            $search['family_id']='0';
//            $search['genus_id']='0';
////            if($map['page']!='0') {
////            }else
//                if($map['state']=='onlyarea'){
//                $search['mapname']=$map['mapname'];
//                }else
//                    if($map['state']=='areafamily'){
//                        $search['mapname']=$map['mapname'];
//                        $familyid=Family::get(['name'=>$map['familyname']]);
//                        $search['family_id']=$familyid['familyid'];
//                    }else
//                        if($map['state']=='onlyfamily'){
//                            $familyid=Family::get(['name'=>$map['familyname']]);
//                            $search['family_id']=$familyid['familyid'];
//                        }
//        }
//        $search['search']='';
        if(Request::instance()->isGet()){
            $search=Request::instance()->param();
            if($search['state']=='search'){

            }else
                if($search['state']=='onlyfamily'){
                    $search['search']='';
                    $search['mapname']='';
                    $familyid=Family::get(['name'=>$search['familyname']]);
                    $search['family_id']=$familyid['familyid'];
                    $search['genus_id']='0';
                }else
                    if($search['state']=='onlyarea'){
                        $search['search']='';
                        $search['family_id']='0';
                        $search['genus_id']='0';
                    }else
                        if($search['state']=='areafamily'){
                            $search['search']='';
                            $familyid=Family::get(['name'=>$search['familyname']]);
                            $search['family_id']=$familyid['familyid'];
                            $search['genus_id']='0';
                        }
        }


        //        var_dump($search);
        $Plant=new PlantInfo();
        $where['plant_info.plantname|alias|area']=array('like','%'.$search['search'].'%');

        if($search['search']!='' && $search['mapname']!='' && $search['family_id']!='0' && $search['genus_id']!='0'){
            //四个搜索内容都不为空
            $Plant=$Plant
                ->alias('i')
                ->join('plant_class c','c.plantid=i.plantid')
                ->join('plant_map m','m.plantname=i.plantname')
                ->join('family f','f.name=c.familyname')
                ->join('genus g','g.name=c.genusname')
                ->field('i.plantid,i.plantname,c.familyname,c.genusname,i.alias,i.area')
                ->where('m.mapname',$search['mapname'])
                ->where('f.familyid',$search['family_id'])
                ->where('g.genusid',$search['genus_id'])
                ->where($where)
                ->paginate(9,false,['query'=>request()->param()]);
        }else if($search['search']=='' && $search['mapname']!='' && $search['family_id']!='0' && $search['genus_id']!='0'){
            //搜索栏为空，其余三个不为空
            $Plant=$Plant
                ->alias('i')
                ->join('plant_class c','c.plantid=i.plantid')
                ->join('plant_map m','m.plantname=i.plantname')
                ->join('family f','f.name=c.familyname')
                ->join('genus g','g.name=c.genusname')
                ->field('i.plantid,i.plantname,c.familyname,c.genusname,i.alias,i.area')
                ->where('m.mapname',$search['mapname'])
                ->where('f.familyid',$search['family_id'])
                ->where('g.genusid',$search['genus_id'])
                ->paginate(9,false,['query'=>request()->param()]);
        }else if($search['search']!='' && $search['mapname']=='' && $search['family_id']!='0' && $search['genus_id']!='0'){
            //区域为空，其余三个不为空
            $Plant=$Plant
                ->alias('i')
                ->join('plant_class c','c.plantid=i.plantid')
                ->join('family f','f.name=c.familyname')
                ->join('genus g','g.name=c.genusname')
                ->field('i.plantid,i.plantname,c.familyname,c.genusname,i.alias,i.area')
                ->where('f.familyid',$search['family_id'])
                ->where('g.genusid',$search['genus_id'])
                ->where($where)
                ->paginate(9,false,['query'=>request()->param()]);
        }else if($search['search']!='' && $search['mapname']!='' && $search['family_id']=='0' && $search['genus_id']!='0'){
            //科名为空，其余三个不为空
            $Plant=$Plant
                ->alias('i')
                ->join('plant_class c','c.plantid=i.plantid')
                ->join('family f','f.name=c.familyname')
                ->join('genus g','g.name=c.genusname')
                ->field('i.plantid,i.plantname,c.familyname,c.genusname,i.alias,i.area')
                ->where('g.genusid',$search['genus_id'])
                ->where($where)
                ->paginate(9,false,['query'=>request()->param()]);
        }else if($search['search']!='' && $search['mapname']!='' && $search['family_id']!='0' && $search['genus_id']=='0'){
            //属为空，其余三个不为空
            $Plant=$Plant
                ->alias('i')
                ->join('plant_class c','c.plantid=i.plantid')
                ->join('plant_map m','m.plantname=i.plantname')
                ->join('family f','f.name=c.familyname')
                ->field('i.plantid,i.plantname,c.familyname,c.genusname,i.alias,i.area')
                ->where('m.mapname',$search['mapname'])
                ->where('f.familyid',$search['family_id'])
                ->where($where)
                ->paginate(9,false,['query'=>request()->param()]);
        }else if($search['search']=='' && $search['mapname']=='' && $search['family_id']!='0' && $search['genus_id']!='0'){
            //搜索栏和区域为空，其余不为空
            $Plant=$Plant
                ->alias('i')
                ->join('plant_class c','c.plantid=i.plantid')
                ->join('family f','f.name=c.familyname')
                ->join('genus g','g.name=c.genusname')
                ->field('i.plantid,i.plantname,c.familyname,c.genusname,i.alias,i.area')
                ->where('f.familyid',$search['family_id'])
                ->where('g.genusid',$search['genus_id'])
                ->paginate(9,false,['query'=>request()->param()]);
        }else if($search['search']!='' && $search['mapname']=='' && $search['family_id']=='0' && $search['genus_id']!='0'){
            //区域和科为空，其余不为空
            $Plant=$Plant
                ->alias('i')
                ->join('plant_class c','c.plantid=i.plantid')
                ->join('family f','f.name=c.familyname')
                ->join('genus g','g.name=c.genusname')
                ->field('i.plantid,i.plantname,c.familyname,c.genusname,i.alias,i.area')
                ->where('g.genusid',$search['genus_id'])
                ->where($where)
                ->paginate(9,false,['query'=>request()->param()]);
        }else if($search['search']=='' && $search['mapname']!='' && $search['family_id']=='0' && $search['genus_id']!='0'){
            //搜索栏和科为空，其余不为空
            $Plant=$Plant
                ->alias('i')
                ->join('plant_class c','c.plantid=i.plantid')
                ->join('plant_map m','m.plantname=i.plantname')
                ->join('family f','f.name=c.familyname')
                ->join('genus g','g.name=c.genusname')
                ->field('i.plantid,i.plantname,c.familyname,c.genusname,i.alias,i.area')
                ->where('m.mapname',$search['mapname'])
                ->where('g.genusid',$search['genus_id'])
                ->paginate(9,false,['query'=>request()->param()]);
        }else if($search['search']=='' && $search['mapname']!='' && $search['family_id']!='0' && $search['genus_id']=='0'){
            //搜索栏和属为空，其余不为空
            $Plant=$Plant
                ->alias('i')
                ->join('plant_class c','c.plantid=i.plantid')
                ->join('plant_map m','m.plantname=i.plantname')
                ->join('family f','f.name=c.familyname')
                ->field('i.plantid,i.plantname,c.familyname,c.genusname,i.alias,i.area')
                ->where('m.mapname',$search['mapname'])
                ->where('f.familyid',$search['family_id'])
                ->paginate(9,false,['query'=>request()->param()]);
        }else if($search['search']!='' && $search['mapname']=='' && $search['family_id']!='0' && $search['genus_id']=='0'){
            //区域和属为空，其余不为空
            $Plant=$Plant
                ->alias('i')
                ->join('plant_class c','c.plantid=i.plantid')
                ->join('family f','f.name=c.familyname')
                ->field('i.plantid,i.plantname,c.familyname,c.genusname,i.alias,i.area')
                ->where('f.familyid',$search['family_id'])
                ->where($where)
                ->paginate(9,false,['query'=>request()->param()]);
        }else if($search['search']!='' && $search['mapname']!='' && $search['family_id']=='0' && $search['genus_id']=='0'){
            //科属为空，其余不为空
            $Plant=$Plant
                ->alias('i')
                ->join('plant_class c','c.plantid=i.plantid')
                ->join('plant_map m','m.plantname=i.plantname')
                ->field('i.plantid,i.plantname,c.familyname,c.genusname,i.alias,i.area')
                ->where('m.mapname',$search['mapname'])
                ->where($where)
                ->paginate(9,false,['query'=>request()->param()]);
        }else if($search['search']=='' && $search['mapname']=='' && $search['family_id']!='0' && $search['genus_id']=='0'){
            //科不为空，其余为空
            $Plant=$Plant
                ->alias('i')
                ->join('plant_class c','c.plantid=i.plantid')
                ->join('family f','f.name=c.familyname')
                ->field('i.plantid,i.plantname,c.familyname,c.genusname,i.alias,i.area')
                ->where('f.familyid',$search['family_id'])
                ->paginate(9,false,['query'=>request()->param()]);
        }else if($search['search']=='' && $search['mapname']=='' && $search['family_id']=='0' && $search['genus_id']!='0'){
            //属不为空，其余为空
            $Plant=$Plant
                ->alias('i')
                ->join('plant_class c','c.plantid=i.plantid')
                ->join('family f','f.name=c.familyname')
                ->join('genus g','g.name=c.genusname')
                ->field('i.plantid,i.plantname,c.familyname,c.genusname,i.alias,i.area')
                ->where('g.genusid',$search['genus_id'])
                ->paginate(9,false,['query'=>request()->param()]);
        }else if($search['search']=='' && $search['mapname']!='' && $search['family_id']=='0' && $search['genus_id']=='0'){
            //区域不为空，其余为空
            $Plant=$Plant
                ->alias('i')
                ->join('plant_class c','c.plantid=i.plantid')
                ->join('plant_map m','m.plantname=i.plantname')
                ->field('i.plantid,i.plantname,c.familyname,c.genusname,i.alias,i.area')
                ->where('m.mapname',$search['mapname'])
                ->paginate(9,false,['query'=>request()->param()]);
        }else if($search['search']!='' && $search['mapname']=='' && $search['family_id']=='0' && $search['genus_id']=='0'){
            //搜索不为空，其余为空
            $Plant=$Plant
                ->alias('i')
                ->join('plant_class c','c.plantid=i.plantid')
                ->field('i.plantid,i.plantname,c.familyname,c.genusname,i.alias,i.area')
                ->where($where)
                ->paginate(9,false,['query'=>request()->param()]);
        }else if($search['search']=='' && $search['mapname']=='' && $search['family_id']=='0' && $search['genus_id']=='0'){
            //全为空
            $Plant=$Plant
                ->alias('i')
                ->join('plant_class c','c.plantid=i.plantid')
                ->field('i.plantid,i.plantname,c.familyname,c.genusname,i.alias,i.area')
                ->paginate(9,false,['query'=>request()->param()]);
            $this->assign('plant',$Plant);
        }
//        var_dump($Plant);
        if($search['family_id']!='0'){
            $this->assign('familyname',Family::get($search['family_id'])->toArray());
        }
        if($search['genus_id']!='0'){
            $this->assign('genusname',Genus::get($search['genus_id'])->toArray());
        }
        $this->assign('search',$search);
        $this->assign('plant',$Plant);
        return $this->fetch('index');
    }

    public function single(){
        $plant=Request::instance()->param();
        $Plant=new PlantInfo();
        $Plant=$Plant
            ->alias('p')
            ->join('plant_class c','p.plantid=c.plantid')
            ->field('p.plantid,p.plantname,p.alias,p.sciname,p.area,p.models,p.introduce,c.familyname,c.genusname')
            ->where('p.plantid',$plant['plantid'])
            ->select();
        $this->assign('plant',$Plant[0]);

        $plantmap=new PlantMap();
        $plantmap=$plantmap
            ->alias('m')
            ->join('plant_info i','i.plantname=m.plantname')
            ->field('m.mapname')
            ->where('i.plantid',$plant['plantid'])
            ->select();
        $this->assign('mapname',$plantmap);

//        var_dump($plantmap);
        return $this->fetch('single');
    }

    public function test(){
        $Map=new Map();
        $Map=$Map->field('mapname,number')->select();
        $this->assign('map',$Map);
        return $this->fetch();
    }

    public function test2(){
        if(Request::instance()->isGet()){
            $search=Request::instance()->param();
            if($search['state']=='search'){

            }else
                if($search['state']=='onlyfamily'){
                    $search['search']='';
                    $search['mapname']='';
                    $familyid=Family::get(['name'=>$search['familyname']]);
                    $search['family_id']=$familyid['familyid'];
                    $search['genus_id']='0';
                }else
                    if($search['state']=='onlyarea'){
                        $search['search']='';
                        $search['family_id']='0';
                        $search['genus_id']='0';
                    }else
                        if($search['state']=='areafamily'){
                        $search['search']='';
                        $familyid=Family::get(['name'=>$search['familyname']]);
                        $search['family_id']=$familyid['familyid'];
                        $search['genus_id']='0';
                    }
        }
        var_dump($search);
    }

    public function test3(){
        var_dump(Request::instance()->param());
    }
}