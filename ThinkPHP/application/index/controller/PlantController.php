<?php


namespace app\index\controller;
use think\Controller;

/**
 * Class PlantController
 * @package app\index\controller
 */
class PlantController extends Controller
{
    public function index(){

    }

    public function test(){
        return $this->fetch();
    }
}