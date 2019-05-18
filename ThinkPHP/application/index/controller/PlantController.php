<?php


namespace app\index\controller;
use app\index\model\Family;
use app\index\model\Genus;
use app\index\model\Map;
use app\index\model\PicInfo;
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
            ->join('pic_info p','p.plantid=i.plantid')
            ->field('i.plantid,i.plantname,c.familyname,c.genusname,i.alias,i.area,p.plantpic')
            ->where('p.plantnum',1)
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
                ->join('pic_info p','p.plantid=i.plantid')
                ->field('i.plantid,i.plantname,c.familyname,c.genusname,i.alias,i.area,p.plantpic')
                ->where('m.mapname',$search['mapname'])
                ->where('f.familyid',$search['family_id'])
                ->where('g.genusid',$search['genus_id'])
                ->where('p.plantnum',1)
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
                ->join('pic_info p','p.plantid=i.plantid')
                ->field('i.plantid,i.plantname,c.familyname,c.genusname,i.alias,i.area,p.plantpic')
                ->where('m.mapname',$search['mapname'])
                ->where('f.familyid',$search['family_id'])
                ->where('g.genusid',$search['genus_id'])
                ->where('p.plantnum',1)
                ->paginate(9,false,['query'=>request()->param()]);
        }else if($search['search']!='' && $search['mapname']=='' && $search['family_id']!='0' && $search['genus_id']!='0'){
            //区域为空，其余三个不为空
            $Plant=$Plant
                ->alias('i')
                ->join('plant_class c','c.plantid=i.plantid')
                ->join('family f','f.name=c.familyname')
                ->join('genus g','g.name=c.genusname')
                ->join('pic_info p','p.plantid=i.plantid')
                ->field('i.plantid,i.plantname,c.familyname,c.genusname,i.alias,i.area,p.plantpic')
                ->where('f.familyid',$search['family_id'])
                ->where('g.genusid',$search['genus_id'])
                ->where('p.plantnum',1)
                ->where($where)
                ->paginate(9,false,['query'=>request()->param()]);
        }else if($search['search']!='' && $search['mapname']!='' && $search['family_id']=='0' && $search['genus_id']!='0'){
            //科名为空，其余三个不为空
            $Plant=$Plant
                ->alias('i')
                ->join('plant_class c','c.plantid=i.plantid')
                ->join('family f','f.name=c.familyname')
                ->join('genus g','g.name=c.genusname')
                ->join('pic_info p','p.plantid=i.plantid')
                ->field('i.plantid,i.plantname,c.familyname,c.genusname,i.alias,i.area,p.plantpic')
                ->where('g.genusid',$search['genus_id'])
                ->where('p.plantnum',1)
                ->where($where)
                ->paginate(9,false,['query'=>request()->param()]);
        }else if($search['search']!='' && $search['mapname']!='' && $search['family_id']!='0' && $search['genus_id']=='0'){
            //属为空，其余三个不为空
            $Plant=$Plant
                ->alias('i')
                ->join('plant_class c','c.plantid=i.plantid')
                ->join('plant_map m','m.plantname=i.plantname')
                ->join('family f','f.name=c.familyname')
                ->join('pic_info p','p.plantid=i.plantid')
                ->field('i.plantid,i.plantname,c.familyname,c.genusname,i.alias,i.area,p.plantpic')
                ->where('m.mapname',$search['mapname'])
                ->where('f.familyid',$search['family_id'])
                ->where('p.plantnum',1)
                ->where($where)
                ->paginate(9,false,['query'=>request()->param()]);
        }else if($search['search']=='' && $search['mapname']=='' && $search['family_id']!='0' && $search['genus_id']!='0'){
            //搜索栏和区域为空，其余不为空
            $Plant=$Plant
                ->alias('i')
                ->join('plant_class c','c.plantid=i.plantid')
                ->join('family f','f.name=c.familyname')
                ->join('genus g','g.name=c.genusname')
                ->join('pic_info p','p.plantid=i.plantid')
                ->field('i.plantid,i.plantname,c.familyname,c.genusname,i.alias,i.area,p.plantpic')
                ->where('f.familyid',$search['family_id'])
                ->where('g.genusid',$search['genus_id'])
                ->where('p.plantnum',1)
                ->paginate(9,false,['query'=>request()->param()]);
        }else if($search['search']!='' && $search['mapname']=='' && $search['family_id']=='0' && $search['genus_id']!='0'){
            //区域和科为空，其余不为空
            $Plant=$Plant
                ->alias('i')
                ->join('plant_class c','c.plantid=i.plantid')
                ->join('family f','f.name=c.familyname')
                ->join('genus g','g.name=c.genusname')
                ->join('pic_info p','p.plantid=i.plantid')
                ->field('i.plantid,i.plantname,c.familyname,c.genusname,i.alias,i.area,p.plantpic')
                ->where('g.genusid',$search['genus_id'])
                ->where('p.plantnum',1)
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
                ->join('pic_info p','p.plantid=i.plantid')
                ->field('i.plantid,i.plantname,c.familyname,c.genusname,i.alias,i.area,p.plantpic')
                ->where('m.mapname',$search['mapname'])
                ->where('g.genusid',$search['genus_id'])
                ->where('p.plantnum',1)
                ->paginate(9,false,['query'=>request()->param()]);
        }else if($search['search']=='' && $search['mapname']!='' && $search['family_id']!='0' && $search['genus_id']=='0'){
            //搜索栏和属为空，其余不为空
            $Plant=$Plant
                ->alias('i')
                ->join('plant_class c','c.plantid=i.plantid')
                ->join('plant_map m','m.plantname=i.plantname')
                ->join('family f','f.name=c.familyname')
                ->join('pic_info p','p.plantid=i.plantid')
                ->field('i.plantid,i.plantname,c.familyname,c.genusname,i.alias,i.area,p.plantpic')
                ->where('m.mapname',$search['mapname'])
                ->where('f.familyid',$search['family_id'])
                ->where('p.plantnum',1)
                ->paginate(9,false,['query'=>request()->param()]);
        }else if($search['search']!='' && $search['mapname']=='' && $search['family_id']!='0' && $search['genus_id']=='0'){
            //区域和属为空，其余不为空
            $Plant=$Plant
                ->alias('i')
                ->join('plant_class c','c.plantid=i.plantid')
                ->join('family f','f.name=c.familyname')
                ->join('pic_info p','p.plantid=i.plantid')
                ->field('i.plantid,i.plantname,c.familyname,c.genusname,i.alias,i.area,p.plantpic')
                ->where('f.familyid',$search['family_id'])
                ->where('p.plantnum',1)
                ->where($where)
                ->paginate(9,false,['query'=>request()->param()]);
        }else if($search['search']!='' && $search['mapname']!='' && $search['family_id']=='0' && $search['genus_id']=='0'){
            //科属为空，其余不为空
            $Plant=$Plant
                ->alias('i')
                ->join('plant_class c','c.plantid=i.plantid')
                ->join('plant_map m','m.plantname=i.plantname')
                ->join('pic_info p','p.plantid=i.plantid')
                ->field('i.plantid,i.plantname,c.familyname,c.genusname,i.alias,i.area,p.plantpic')
                ->where('m.mapname',$search['mapname'])
                ->where('p.plantnum',1)
                ->where($where)
                ->paginate(9,false,['query'=>request()->param()]);
        }else if($search['search']=='' && $search['mapname']=='' && $search['family_id']!='0' && $search['genus_id']=='0'){
            //科不为空，其余为空
            $Plant=$Plant
                ->alias('i')
                ->join('plant_class c','c.plantid=i.plantid')
                ->join('family f','f.name=c.familyname')
                ->join('pic_info p','p.plantid=i.plantid')
                ->field('i.plantid,i.plantname,c.familyname,c.genusname,i.alias,i.area,p.plantpic')
                ->where('f.familyid',$search['family_id'])
                ->where('p.plantnum',1)
                ->paginate(9,false,['query'=>request()->param()]);
        }else if($search['search']=='' && $search['mapname']=='' && $search['family_id']=='0' && $search['genus_id']!='0'){
            //属不为空，其余为空
            $Plant=$Plant
                ->alias('i')
                ->join('plant_class c','c.plantid=i.plantid')
                ->join('family f','f.name=c.familyname')
                ->join('genus g','g.name=c.genusname')
                ->join('pic_info p','p.plantid=i.plantid')
                ->field('i.plantid,i.plantname,c.familyname,c.genusname,i.alias,i.area,p.plantpic')
                ->where('g.genusid',$search['genus_id'])
                ->where('p.plantnum',1)
                ->paginate(9,false,['query'=>request()->param()]);
        }else if($search['search']=='' && $search['mapname']!='' && $search['family_id']=='0' && $search['genus_id']=='0'){
            //区域不为空，其余为空
            $Plant=$Plant
                ->alias('i')
                ->join('plant_class c','c.plantid=i.plantid')
                ->join('plant_map m','m.plantname=i.plantname')
                ->join('pic_info p','p.plantid=i.plantid')
                ->field('i.plantid,i.plantname,c.familyname,c.genusname,i.alias,i.area,p.plantpic')
                ->where('m.mapname',$search['mapname'])
                ->where('p.plantnum',1)
                ->paginate(9,false,['query'=>request()->param()]);
        }else if($search['search']!='' && $search['mapname']=='' && $search['family_id']=='0' && $search['genus_id']=='0'){
            //搜索不为空，其余为空
            $Plant=$Plant
                ->alias('i')
                ->join('plant_class c','c.plantid=i.plantid')
                ->join('pic_info p','p.plantid=i.plantid')
                ->field('i.plantid,i.plantname,c.familyname,c.genusname,i.alias,i.area,p.plantpic')
                ->where($where)
                ->paginate(9,false,['query'=>request()->param()]);
        }else if($search['search']=='' && $search['mapname']=='' && $search['family_id']=='0' && $search['genus_id']=='0'){
            //全为空
            $Plant=$Plant
                ->alias('i')
                ->join('plant_class c','c.plantid=i.plantid')
                ->join('pic_info p','p.plantid=i.plantid')
                ->field('i.plantid,i.plantname,c.familyname,c.genusname,i.alias,i.area,p.plantpic')
                ->where('p.plantnum',1)
                ->paginate(9,false,['query'=>request()->param()]);
//            $this->assign('plant',$Plant);
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

        $picinfo=new PicInfo();
        $picinfo=$picinfo
            ->where('plantid',$plant['plantid'])
            ->select();
        $this->assign('picinfo',$picinfo);

//        var_dump($picinfo);
        return $this->fetch('single');
    }

    public function identify(){
        $data['score']='';
        $data['name']='';
        $pic['pic']='white.png';
        $this->assign('pic',$pic);
        $this->assign('data',$data);

        return $this->fetch();
    }

    public function ai(){
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

    /**
     * 调用百度植物识别api
     * @return mixed
     */
    public function plantai(){

        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('pic');
        if($file){
            $info = $file->validate(['ext'=>'jpg,png,jpeg'])
                ->rule('uniqid')
                ->move(ROOT_PATH . 'public' . DS .'static'. DS . 'uploads'. DS .'ai');
            if($info){
                // 成功上传后 获取上传信息
            }else{
                // 上传失败获取错误信息
                echo $file->getError();
            }
        }else{
            $pic['pic']='white.png';
            $this->assign('pic',$pic);
            return $this->fetch('identify');
        }
//        var_dump($file);
        $APP_ID = '16265011';
        $API_KEY = 'Fge8aeljnNAQPL1iu7WIn0u6';
        $SECRET_KEY = 'E6vTkDO4W2SLZKdxtIEymXjGSYbE1xRd';
        vendor('Ai.AipImageClassify');
        $client = new \AipImageClassify($APP_ID, $API_KEY, $SECRET_KEY);
        $image = file_get_contents(ROOT_PATH . 'public' . DS .'static'. DS . 'uploads'. DS .'ai'. DS .$info->getSaveName());
        $data=$client->plantDetect($image);
//        var_dump($data['result']);
        $this->assign('data',$data['result']);

        $pic['pic']=$info->getSaveName();
        $this->assign('pic',$pic);
        return $this->fetch('identify');

//        var_dump("image_url=".ROOT_PATH . 'public' . DS .'static'. DS . 'uploads'. DS .'plant'. DS .$info->getSavename());
//        $host = "https://plantapi.xingseapp.com";
//        $path = "/item/identification";
//        $method = "POST";
//        $appcode = "cf2fbecd11de4ea69b826b32bbd09628";
//        $headers = array();
//        array_push($headers, "Authorization:APPCODE " . $appcode);
//        //根据API的要求，定义相对应的Content-Type
//        array_push($headers, "Content-Type".":"."application/x-www-form-urlencoded; charset=UTF-8");
//        $querys = "";
//        $bodys = "image_url=".ROOT_PATH . 'public' . DS .'static'. DS . 'uploads'. DS .'plant'. DS .$info->getSavename();
//        $url = $host . $path;
//
//        $curl = curl_init();
//        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
//        curl_setopt($curl, CURLOPT_URL, $url);
//        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
//        curl_setopt($curl, CURLOPT_FAILONERROR, false);
//        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($curl, CURLOPT_HEADER, true);
//        if (1 == strpos("$".$host, "https://"))
//        {
//            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
//            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
//        }
//        curl_setopt($curl, CURLOPT_POSTFIELDS, $bodys);
//        var_dump(curl_exec($curl));
    }
}