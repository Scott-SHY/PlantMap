<?php


namespace app\admin\controller;
use app\admin\model\PlantClass;
use app\admin\model\PlantInfo;
use function MongoDB\BSON\toJSON;

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

        return $this->fetch();
    }

    public function manage(){
        return $this->fetch();
    }

    public function savedata(){
//        //查询plant_info和plant_class的信息并保存到txt中
//        $PlantInfo=new PlantInfo();
//
//        //查询名称，别名，学名，区域，科，属，介绍
//        //join()函数用来关联两个表
//        //where这里不用也可以
//        $plantinfo=$PlantInfo
//            ->alias('i')
//            ->join('plant_class c','c.plantname=i.name')
//            ->field('i.name,i.alias,i.sciname,i.area,c.familyname,c.genusname,i.introduce')
//            ->where('i.name=c.plantname')
//            ->select();

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