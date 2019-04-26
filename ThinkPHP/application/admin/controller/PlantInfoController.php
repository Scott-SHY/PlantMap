<?php


namespace app\admin\controller;
use app\admin\model\Family;
use app\admin\model\Genus;
use app\admin\model\PlantClass;
use app\admin\model\PlantInfo;
use app\admin\model\PlantMap;
use function MongoDB\BSON\toJSON;
use think\Request;

/**
 * Class PlantInfoController
 * @package app\admin\controller
 */
class PlantInfoController extends IndexController
{
    public function index(){
//        $plants=PlantInfo::paginate(5);
//        $plants=\app\admin\model\PlantInfo::select();
//        $this->assign('plants',$plants);

        //每次调用方法都会更新一遍数据？效率低下，应该把静态方法放在增删改操作之后调用
        //更新数据
        PlantInfo::saveData();

        //模态框下拉列表的数据
        $family=Family::all();
        $genus=Genus::all();
        $this->assign('family',$family);
        $this->assign('genus',$genus);

        return $this->fetch();
    }

    //添加植物信息模态框调用
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

    public function test(){
//        var_dump($_POST);
        //接收post的数据
        $plantdata=Request::instance()->post();
        var_dump($plantdata['map']);

        //插入数据后需要刷新plantinfo文件，并重新绘制表格（未完成）

        //插入信息到plant_class表
        $PlantClass=new PlantClass();
        $PlantClass->familyname=Family::getFamilyName($plantdata['family_id'])->getData('name');
        $PlantClass->genusname=Genus::getGenusName($plantdata['genus_id'])->getData('name');
        $PlantClass->plantname=$plantdata['plantname'];
        $PlantClass->save();

        //插入信息到plant_info表
        $PlantInfo=new PlantInfo();
        $PlantInfo->name=$plantdata['plantname'];
        $PlantInfo->alias=$plantdata['alias'];
        $PlantInfo->sciname=$plantdata['sciname'];
        $PlantInfo->area=$plantdata['area'];
        $PlantInfo->models=$plantdata['models'];
        $PlantInfo->introduce=$plantdata['introduce'];
        $PlantInfo->save();

        //插入信息到plant_map表
        $PlantMap=new PlantMap();
        foreach ($plantdata['map'] as $map) {
            $PlantMap->plantname = $PlantInfo->name;
            $PlantMap->mapname = $map;
            $PlantMap->save();
        }


//        return '插入成功'.$PlantInfo->plantid;
//        $plantname=input('post.plantname');
//        $alias=input('post.alias');
//        $sciname=input('post.sciname');
//        $area=input('post.area');
//        $family=input('post.family_id');
//        $genus=input('post.genus_id');
//        $map=input('post.map/a');
//        var_dump($plantname);
//        var_dump($alias);
//        var_dump($sciname);
//        var_dump($area);
//        var_dump($family);
//        var_dump($genus);
//        var_dump($map);
    }

    public function manage(){
        return $this->fetch();
    }

    public function add(){
        return $this->fetch('manage');
    }

    //重构到静态方法中
    //保存所有植物信息
    public function savedata(){
        $plantinfo=PlantInfo::saveData();

        //存储json信息,$test2是json转换后的变量
        $plantjson=json_encode($plantinfo,JSON_UNESCAPED_UNICODE);
        $fp=fopen("../public/static/data/plantinfo.txt",'w+') or exit("Unable to open file!");
        fwrite($fp,$plantjson);
        fclose($fp);

        return $this->fetch('index');
    }

    public function getData(){
        if(request()->isAjax()){
            //实例化User模型，注意要在上面use
            $UserModel = new PlantInfo();
            //接受请求
            $datatables = request()->post();
            //得到排序的方式
            $order = $datatables['order'][0]['dir'];
            //得到排序字段的下标
            $order_column = $datatables['order'][0]['column'];
            //根据排序字段的下标得到排序字段
            $order_field = $datatables['columns'][$order_column]['data'];
            //得到limit参数
            $limit_start = $datatables['start'];
            $limit_length = $datatables['length'];
            //得到搜索的关键词
            $search = $datatables['search']['value'];

            //查询出所有用户数据
            //如有搜索行为，则按照姓名进行模糊查询
            if ($search){
                $data = $UserModel
                    ->order("$order_field $order")
                    ->limit($limit_start,$limit_length)
                    ->where('name','LIKE',"%$search%")
                    ->select();
                $keyword_all_data = $UserModel
                    ->where('name','LIKE',"%$search%")
                    ->select();
                $total = count($keyword_all_data);   //获取满足关键词的总记录数
            }else{

                //没有关键词，则查询全部
                $data = $UserModel
//                    ->where('plantid',0)   //查询未被标记为删除的数据，可选
//                    ->field('plantid,name,alias')
//                    ->order("name $order")
                    ->limit($limit_start,$limit_length)
                    ->select();
                $total = $UserModel->count(); // 数据总数
            }

            if($data){
                $data = collection($data)->toArray();
            }
            $draw = request()->post('draw');
            $AllData = [
                // ajax的请求次数，创建唯一标识
                'draw' => $draw,
                // 结果数
                'recordsTotal' => count($data),
                // 总数据量
                'recordsFiltered' => $total,
                // 总数据
                'data' => $data,
            ];
            return json($AllData);
        }else{
            //如果不是ajax请求，自行处理
        }
    }
}