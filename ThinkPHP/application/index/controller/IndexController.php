<?php
namespace app\index\controller;
use app\index\model\Family;
use app\index\model\Genus;
use app\index\model\Website;
use think\Controller;

class IndexController extends Controller
{
    public function index()
    {
        return $this->fetch();
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
