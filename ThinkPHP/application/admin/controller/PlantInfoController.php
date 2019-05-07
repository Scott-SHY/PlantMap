<?php


namespace app\admin\controller;
use app\admin\model\Family;
use app\admin\model\Genus;
use app\admin\model\Map;
use app\admin\model\PicInfo;
use app\admin\model\PlantAdmin;
use app\admin\model\PlantClass;
use app\admin\model\PlantInfo;
use app\admin\model\PlantMap;
use function MongoDB\BSON\toJSON;
use think\Exception;
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

//        模态框下拉列表的数据
        $family=Family::all();
        $genus=Genus::all();
        $this->assign('family',$family);
        $this->assign('genus',$genus);

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

    /**
     * 插入数据，index和manage的插入共用
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function insert(){

        //模态框下拉列表的数据
        $family=Family::all();
        $genus=Genus::all();
        $this->assign('family',$family);
        $this->assign('genus',$genus);

//        var_dump($_POST);
        //接收post的数据
        $plantdata=Request::instance()->post();
//        var_dump($plantdata['map']);

        //插入数据后需要刷新plantinfo文件，并重新绘制表格（未完成）

        // 获取表单上传模型 例如上传了001.usdz
        $models = request()->file('models');
        // 移动到框架应用根目录/public/uploads/ 目录下
        if($models){
            $minfo = $models->validate(['ext'=>'usdz'])
                ->rule('uniqid')
                ->move(ROOT_PATH . 'public' . DS .'static'. DS . 'uploads'. DS .'models');
            if($minfo){
                // 成功上传后 获取上传信息
            }else{
                echo $models->getError();
            }
        }else{
            var_dump($models);
        }
        // 获取表单上传图片 例如上传了001.jpg
        $pic = request()->file('pic');
        // 移动到框架应用根目录/public/uploads/ 目录下
        if($pic){
            $pinfo = $pic->validate(['ext'=>'usdz'])
                ->rule('uniqid')
                ->move(ROOT_PATH . 'public' . DS .'static'. DS . 'uploads'. DS .'plant');
            if($pinfo){
                // 成功上传后 获取上传信息
            }else{
                // 上传失败获取错误信息
                echo $pic->getError();
            }
        }else{
            var_dump($pic);
        }

        //插入信息到plant_info表
        $PlantInfo=new PlantInfo();
        $PlantInfo->plantname=$plantdata['plantname'];
        $PlantInfo->alias=$plantdata['alias'];
        $PlantInfo->sciname=$plantdata['sciname'];
        $PlantInfo->area=$plantdata['area'];
        $PlantInfo->models=$minfo->getSaveName();
        $PlantInfo->introduce=$plantdata['introduce'];
//        var_dump($PlantInfo);
        $PlantInfo->save();

        $PicInfo=new PicInfo();
        $PicInfo->plantid=$PlantInfo->plantid;
        $PicInfo->plantpic=$pic->getSaveName();
        $PicInfo->plantnum=1;
        $PicInfo->save();

        //插入信息到plant_class表
        $PlantClass=new PlantClass();
        $PlantClass->familyname=Family::getFamilyName($plantdata['family_id'])->getData('name');
        $PlantClass->genusname=Genus::getGenusName($plantdata['genus_id'])->getData('name');
        $PlantClass->plantname=$plantdata['plantname'];
        $PlantClass->plantid=$PlantInfo->plantid;
//        var_dump($PlantClass);
        $PlantClass->save();

        //插入信息到plant_map表
//        $PlantMap=new PlantMap();
//        var_dump($PlantInfo->name);
        foreach ($plantdata['map'] as $map) {
            $PlantMap=new PlantMap();
            $PlantMap->plantname = $PlantInfo->plantname;
            $PlantMap->mapname = $map;
//            var_dump($PlantMap);
            $PlantMap->save();
        }

        //插入信息到plant_admin表
        $PlantAdmin=new PlantAdmin();
        $plantid=$PlantInfo
                ->field('plantid')
                ->where('plantname',$plantdata['plantname'])
                ->select();
        $PlantAdmin->plantid=$plantid[0]->getData('plantid');
        $PlantAdmin->adminid=session('adminid');
        $PlantAdmin->createtime=date('Y-m-d H:i:s');
        $PlantAdmin->updatetime=date('Y-m-d H:i:s');
        $PlantAdmin->save();
//        var_dump($PlantAdmin);


        //保存数据的调用次序还需修改
        PlantInfo::saveData();
        return $this->fetch('index');

    }

    public function add(){
//        //模态框下拉列表的数据
//        $family=Family::all();
//        $genus=Genus::all();
//        $this->assign('family',$family);
//        $this->assign('genus',$genus);
//
//        //根据传递过来的plantname匹配对应PlantInfo对象
//        $plantname=Request::instance()->param();
//        $PlantInfo=PlantInfo::get($plantname);
//        $this->assign('plantinfo',$PlantInfo);
//
//        //根据传递过来的plantname匹配对应PlantMap多个对象
//        $PlantMap=PlantMap::all($plantname);
//        $this->assign('plantmap',$PlantMap);
//
//        //根据传递过来的plantname匹配对应Map里的值
//        $map=Map::all();
//        $this->assign('map',$map);
//
//        //根据传递过来的plantname匹配对应MPlantClass里的值
//        $PlantClass=PlantClass::get($plantname);
//        $this->assign('plantclass',$PlantClass);
//
        return $this->fetch();
    }

    /**
     * 获取index页编辑操作传递的参数，若直接打开则显示id为1的植物信息
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function manage(){
        //模态框下拉列表的数据
        $family=Family::all();
        $genus=Genus::all();
        $this->assign('family',$family);
        $this->assign('genus',$genus);

        //根据传递过来的plantname匹配对应PlantInfo对象
//        $plantname=Request::instance()->param();
        if(Request::instance()->param()){
            $plantname=Request::instance()->param();
        }else{
            $plantname=array();
            $plantname['plantname']="二球悬铃木";
        }
//        if(key($plantname)){
//            throw new Exception('where express error:'.var_export($plantname,true));
//        }
        $PlantInfo=PlantInfo::get($plantname);
        $this->assign('plantinfo',$PlantInfo);

        //根据传递过来的plantname匹配对应PlantMap多个对象
        $PlantMap=PlantMap::all($plantname);
        $this->assign('plantmap',$PlantMap);

        //根据传递过来的plantname匹配对应Map里的值
        $Map=Map::all();
        $this->assign('map',$Map);

        //根据传递过来的plantname匹配对应PlantClass里的值
        $plantclass=PlantClass::get($plantname);
        $PlantClass=new PlantClass();
        $PlantClass=$PlantClass
            ->alias('c')
            ->join('family f','f.name=c.familyname')
            ->join('genus g','g.name=c.genusname')
            ->field('c.plantname,c.familyname,c.genusname,f.familyid,g.genusid')
            ->where('c.plantname',$plantclass['plantname'])
            ->select();
        $this->assign('plantclass',$PlantClass[0]);

//        var_dump($PlantInfo);
//        var_dump($PlantMap);
//        var_dump($PlantClass[0]);
        return $this->fetch();
    }

    public function test(){
//        $plant=Request::instance()->param();
//        var_dump($plant);
//        //判断PlantClass表中是否存在数据
//        if(is_null($PlantClass=PlantClass::get($plant['plantid']))){
//            return $this->error('PlantClass不存在id='.$plant['plantid'].'的数据');
//        }
//        $PlantClass->plantname=$plant['plantname'];
//        $PlantClass->familyname=Family::where('familyid',$plant['familyid'])->value('name');
//        $PlantClass->genusname=Genus::where('genusid',$plant['genusid'])->value('name');
        $plantclass['plantname']='测试ss';
        $PlantClass=new PlantClass();
        $PlantClass=$PlantClass
            ->alias('c')
            ->join('family f','f.name=c.familyname')
            ->join('genus g','g.name=c.genusname')
            ->field('c.plantname,c.familyname,c.genusname,f.familyid,g.genusid')
            ->where('c.plantname',$plantclass['plantname'])
            ->select();
        var_dump($PlantClass[0]);
    }

    /**
     * 更新数据
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function update(){
        //接收更新信息
        $plant=Request::instance()->param();

        //先判断需要plantname作为主键的表，因为plantname会被修改，需要确定plantid对应的原名称
        //判断PlantInfo表中是否存在数据
        if(is_null($PlantInfo=PlantInfo::get($plant['plantid']))){
            return $this->error('PlantInfo不存在id='.$plant['plantid'].'的数据');
        }

        //判断PlantClass表中是否存在数据
        if(is_null($PlantClass=PlantClass::get($plant['plantid']))){
            return $this->error('PlantClass不存在id='.$plant['plantid'].'的数据');
        }

        //判断PlantMap表中是否存在数据
        if(is_null($PlantMap=PlantMap::all(['plantname'=>$PlantInfo->plantname]))){
            return $this->error('PlantMap不存在plantname='.$PlantInfo->plantname.'的数据');
        }

        //判断PlantAdmin表中是否存在数据
        if(is_null($PlantAdmin=PlantAdmin::get(['plantid'=>$plant['plantid']]))){
            return $this->error('PlantAdmin不存在id='.$plant['plantid'].'的数据');
        }


        //写入PlantClass新数据
        $PlantClass->plantname=$plant['plantname'];
        $PlantClass->familyname=Family::where('familyid',$plant['familyid'])->value('name');
        $PlantClass->genusname=Genus::where('genusid',$plant['genusid'])->value('name');
        $PlantClass->isUpdate(true)->save();
        $PlantClass=$PlantClass
            ->alias('c')
            ->join('family f','f.name=c.familyname')
            ->join('genus g','g.name=c.genusname')
            ->field('c.plantname,c.familyname,c.genusname,f.familyid,g.genusid')
            ->where('c.plantname',$plant['plantname'])
            ->select();
//        var_dump($PlantClass);

        //写入PlantInfo新数据
        $PlantInfo->plantname=$plant['plantname'];
        $PlantInfo->alias=$plant['alias'];
        $PlantInfo->sciname=$plant['sciname'];
        $PlantInfo->area=$plant['area'];
        $PlantInfo->models=$plant['models'];
        $PlantInfo->introduce=$plant['introduce'];
        $PlantInfo->isUpdate(true)->save();
//        var_dump($PlantInfo);


        //写入PlantAdmin新数据
        $PlantAdmin->adminid=session('adminid');
        $PlantAdmin->updatetime=date('Y-m-d H:i:s');
        $PlantAdmin->isUpdate(true)->save();
//        var_dump($PlantAdmin);


        //写入PlantMap新数据
        //应该先删除原有数据，再写入新的数据
        PlantMap::destroy(['plantname'=>$PlantInfo->plantname]);
        foreach ($plant['map'] as $map) {
            $PlantMap=new PlantMap();
            $PlantMap->plantname = $plant['plantname'];
            $PlantMap->mapname = $map;
            $PlantMap->save();
        }

        //模态框下拉列表的数据
        $family=Family::all();
        $genus=Genus::all();
        $this->assign('family',$family);
        $this->assign('genus',$genus);
        $this->assign('plantinfo',$PlantInfo);
        $this->assign('plantclass',$PlantClass[0]);
        $Map=Map::all();
        $this->assign('map',$Map);

        return $this->fetch('manage');
//        return $this->success('update ok!',url('index'));

    }

    /**
     * 删除数据，针对manage页面的删除操作
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function delete(){
        $plant=Request::instance()->param();
        PlantInfo::destroy($plant['plantid']);

        $family=Family::all();
        $genus=Genus::all();
        $this->assign('family',$family);
        $this->assign('genus',$genus);
        PlantInfo::saveData();

        return $this->fetch('index');
    }

    /**
     * 删除数据，针对index页面的删除操作
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function deleteT(){
        $plant=Request::instance()->param();
        $plantid=PlantInfo::get(['plantname'=>$plant['plantname']]);
        PlantInfo::destroy($plantid->plantid);

        $family=Family::all();
        $genus=Genus::all();
        $this->assign('family',$family);
        $this->assign('genus',$genus);
        PlantInfo::saveData();

        return $this->fetch('index');
    }

    public function picture(){
        return $this->fetch();
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
}