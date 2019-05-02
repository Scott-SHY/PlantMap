<?php


namespace app\index\controller;

use think\Controller;

/**
 * Class MapController
 * @package app\index\controller
 */
class MapController extends Controller
{
    public function index(){
        return $this->fetch();
    }
}