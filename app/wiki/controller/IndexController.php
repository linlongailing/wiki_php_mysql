<?php

namespace app\wiki\controller;

use app\wiki\service\WikiService;
use cmf\controller\HomeBaseController;

header("content-Type:application/json;charset=utf-8");
header("Access-Control-Allow-Origin:*");
header("Access-Control-Allow-Credentials:true");

class IndexController extends HomeBaseController
{
    // 获取wiki分类
    public function category()
    {
        $wiki_service = new WikiService();
        $res = $wiki_service->getClass();
        exit(json_encode($res));
    }

    // 获取分类下的子列表
    public function child()
    {
        $request = $this->request;
        $id = $request->param('id');

        $wiki_service = new WikiService();
        $res = $wiki_service->getChild($id);
        exit(json_encode($res));
    }

    // 获取wiki详情
    public function article()
    {
        $request = $this->request;
        $id = $request->param('id');

        $wiki_service = new WikiService();
        $res = $wiki_service->getArticle($id);
        exit(json_encode($res));
    }
}
