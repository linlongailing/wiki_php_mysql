<?php

namespace app\wiki\service;

use app\wiki\model\WikiClassModel;
use app\wiki\model\WikiModel;
use think\Db;

class WikiService
{
    // 获取父级分类
    public function getClass()
    {
        $result = ['code' => 401, 'msg' => '操作失败', 'data' => []];

        $wiki_class_model = new WikiClassModel();
        $wiki_class = $wiki_class_model->classQuery(['parent_id' => 0], 'id,title');
        $wiki_class = $wiki_class->toArray();

        $result['code'] = 200;
        $result['msg'] = "获取成功";
        $result['data'] = $wiki_class;
        return $result;
    }

    // 获取分类下子级列表
    public function getChild($parent_id)
    {
        $result = ['code' => 401, 'msg' => '操作失败', 'data' => []];

        if (empty($parent_id)) {
            $result['msg'] = '分类ID错误';
            return $result;
        }

        $wiki_class_model = new WikiClassModel();
        $wiki_class = $wiki_class_model->classQueryById($parent_id, 'id');
        if (empty($wiki_class)) {
            $result['msg'] = '分类错误';
            return $result;
        }
        $wiki_class = $wiki_class_model->classQuery(['parent_id' => $parent_id], 'id,title,time,1 as cate,0 as anchor');
        $wiki_class = $wiki_class->toArray();

        // 获取该分类下的文章
        $wiki_model = new WikiModel();
        $wiki = $wiki_model->wikiQuery(['class_id' => $parent_id], 'id,title,time,0 as cate,content,0 as anchor');
        $wiki = $wiki->toArray();
        if(!empty($wiki)){
            foreach ($wiki as $k=>$w){
                $content = htmlspecialchars_decode(htmlspecialchars_decode($w['content']));
                // 提取锚点信息
                preg_match_all('/<a name="(.*?)"><\/a>(.*?)</i',
                    $content, $matches,PREG_SET_ORDER);
                if(!empty($matches)){
                    $wiki[$k]['anchor']=1;
                }
                unset($wiki[$k]['content']);
            }
        }

        $data = array_merge($wiki_class, $wiki);
        array_multisort(array_column($data, 'time'), SORT_ASC, $data);

        if (!empty($data)) {
            foreach ($data as $key => $d) {
                $data[$key]['time'] = date("Y-m-d H:i:s", $d['time']);
            }
        }

        $result['code'] = 200;
        $result['msg'] = "获取成功";
        $result['data'] = $data;
        return $result;
    }

    // 获取文章
    public function getArticle($id)
    {
        $result = ['code' => 401, 'msg' => '操作失败', 'data' => []];

        if (empty($id)) {
            $result['msg'] = 'wiki ID错误';
            return $result;
        }
        $wiki_model = new WikiModel();
        $wiki = $wiki_model->wikiQueryById($id, 'id,title,content,time');
        $wiki = empty($wiki) ? [] : $wiki;
        if (!empty($wiki)) {
            $content = htmlspecialchars_decode(htmlspecialchars_decode($wiki['content']));
            // 提取锚点信息
            preg_match_all('/<a name="(.*?)"><\/a>(.*?)</i',
                $content, $matches,PREG_SET_ORDER);

            $content = preg_replace('/<pre.*?>/', '<pre><code>', $content);
            $content = preg_replace('/<a name="/', '<a id="', $content);
            $content = str_replace('</pre>', '</code></pre>', $content);

            $wiki['anchor']=[];
            if(!empty($matches)){
                $aa=[];
                foreach ($matches as $k=>$match){
                    $aa[$k]=['id'=>$match[1],'title'=>$match[2]];
                }
                $wiki['anchor']=$aa;
            }
            $wiki['content'] = $content;
            $wiki['time'] = date('Y-m-d H:i:s', $wiki['time']);
        }

        $result['code'] = 200;
        $result['msg'] = "获取成功";
        $result['data'] = $wiki;
        return $result;
    }
}
