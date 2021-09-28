<?php

namespace app\wiki\controller;

use app\user\model\MemberModel;
use app\wiki\model\WikiClassModel;
use app\wiki\model\WikiModel;
use think\Db;
use cmf\controller\AdminBaseController;

class IndexController extends AdminBaseController
{
    // wiki列表
    public function index()
    {
        $request = $this->request;
        $keyword = $request->param('keyword', '');

        $keyword = htmlspecialchars($keyword);

        $wiki_model = new WikiModel();

        $where = [];
        if (!empty($keyword)) {
            $where[] = "title like '%{$keyword}%'";
        }
        $whereStr = "";
        if (!empty($where)) {
            if (count($where) > 1) {
                foreach ($where as $val) {
                    $whereStr .= $val . ' and ';
                }

                $whereStr = substr($whereStr, 0, -5);
            } else {
                $whereStr = $where[0];
            }
        }
        $wiki_list = $wiki_model->wikiQuery($whereStr, '*', 'id asc', 10);

        $wiki_class_model = new WikiClassModel();
        $wiki = $wiki_list;
        if (!empty($wiki)) {
            foreach ($wiki as $k => $p) {
                // 获取分类层级
                $class = $wiki_class_model->classQueryById($p['class_id'], 'title,parent_id');

                $title = '';
                // 获取父级分类
                if (!empty($class['parent_id'])) {
                    $parent_class = $wiki_class_model->classQueryById($class['parent_id'], 'title');
                    $title = $parent_class['title'];
                }

                $wiki[$k]['cate'] = empty($class['parent_id']) ? $class['title'] : $title . ' > ' . $class['title'];
            }
        }

        // 获取分页显示
        $page = $wiki_list->render();
        $wiki_list = $wiki;

        $this->assign('keyword', $keyword);
        $this->assign('page', $page);
        $this->assign('wiki_list', $wiki_list);
        return $this->fetch();
    }

    // 发布wiki
    public function publish()
    {
        $request = $this->request;
        $wiki_model = new WikiModel();
        if ($request->isPost()) {
            $id = $request->param('id', 0);
            $class = $request->param('class', 0);
            $title = $request->param('title', '', 'htmlspecialchars');
            $content = $request->param('content', '');

            $id = intval($id);
            $class = intval($class);
            $title = trim($title);

            if (empty($class)) {
                $this->error('分类不能为空');
            }
            if (empty($title)) {
                $this->error('标题不能为空');
            }
            if (empty($content)) {
                $this->error('内容不能为空');
            }

            if (!empty($id)) {
                $post_arr = ['class_id' => $class, 'title' => $title, 'content' => $content];
                $res = $wiki_model->wikiUpdate($id, $post_arr);
                if (!$res) {
                    $this->error('修改失败');
                }
                $this->success('修改成功', url('index/index'));
            } else {
                $post_arr = ['class_id' => $class, 'title' => $title, 'content' => $content, 'time' => time()];
                $res = $wiki_model->wikiAdd($post_arr);
                if (!$res) {
                    $this->error('发布失败');
                }
                $this->success('发布成功', url('index/index'));
            }
        }

        $id = $request->param('id', 0);
        $id = intval($id);
        if (!empty($id)) {
            $wiki_info = $wiki_model->wikiQueryById($id);
            $this->assign('wiki', $wiki_info);
        }

        // 获取分类
        $class_model = new WikiClassModel();
        $wiki_class = $class_model->classQuery(['parent_id' => 0], 'id,title');
        $wiki_class = $wiki_class->toArray();
        if (!empty($wiki_class)) {
            foreach ($wiki_class as $key => $class) {
                // 获取子分类
                $child_class = $class_model->classQuery(['parent_id' => $class['id']], 'id,title');
                $child_class = $child_class->toArray();

//                if (!empty($child_class)) {
//                    foreach ($child_class as $k => $c) {
//                        // 获取孙分类
//                        $grandson_class = $class_model->classQuery(['parent_id' => $c['id']], 'id,title,time');
//                        $grandson_class = $grandson_class->toArray();
//                        $child_class[$k]['grandson'] = $grandson_class;
//                    }
//                }

                $wiki_class[$key]['child'] = $child_class;
            }
        }

        $this->assign('class', $wiki_class);
        return $this->fetch();
    }

    // 帖子删除
    public function wikiDel()
    {
        $request = $this->request;
        $wiki_model = new WikiModel();
        $post_id = $request->param('id', 0);
        $post_id = intval($post_id);
        $res = $wiki_model->wikiDel($post_id);
        if (!$res) {
            $this->error('删除失败');
        }
        $this->success('删除成功');
    }

    // 文章分类列表
    public function cate()
    {
        $request = $this->request;

        $class_model = new WikiClassModel();
        $class_list = $class_model->classQuery("parent_id =0", 'id,title,time');
        $class_list = $class_list->toArray();
        if (!empty($class_list)) {
            foreach ($class_list as $key => $class) {
                // 获取子分类
                $child_class = $class_model->classQuery(['parent_id' => $class['id']], 'id,title,time');
                $child_class = $child_class->toArray();

                if (!empty($child_class)) {
                    foreach ($child_class as $k => $c) {
                        // 获取孙分类
                        $grandson_class = $class_model->classQuery(['parent_id' => $c['id']], 'id,title,time');
                        $grandson_class = $grandson_class->toArray();
                        $child_class[$k]['grandson'] = $grandson_class;
                    }
                }
                $class_list[$key]['child'] = $child_class;
            }
        }

        $this->assign('class_list', $class_list);
        return $this->fetch('index/class');
    }

    // 文章分类，两级分类
    public function add()
    {
        $request = $this->request;
        $class_model = new WikiClassModel();
        if ($request->isPost()) {
            $category_id = $request->param('id', 0);
            $parent_id = $request->param('parent', 0);
            $title = $request->param('title', '', 'htmlspecialchars');

            $category_id = intval($category_id);
            $parent_id = intval($parent_id);
            $title = trim($title);

            if (empty($title)) {
                $this->error('标题不能为空');
            }
            // 判断父级分类
            if (!empty($parent_id)) {
                $parent_class = $class_model->classQueryById($parent_id, 'id');
                if (empty($parent_class)) {
                    $this->error('分类错误');
                }
                if ($category_id == $parent_id) {
                    $this->error('分类错误');
                }
            }

            if (!empty($category_id)) {
                $category_arr = ['title' => $title, 'parent_id' => $parent_id];
                $res = $class_model->classUpdate($category_id, $category_arr);
                if (!$res) {
                    $this->error('修改失败');
                }
                $this->success('修改成功', url('index/cate'));
            } else {
                $category_arr = ['parent_id' => $parent_id, 'title' => $title, 'time' => time()];
                $res = $class_model->classAdd($category_arr);
                if (!$res) {
                    $this->error('添加失败');
                }
                $this->success('添加成功', url('index/cate'));
            }
        }

        $category_id = $request->param('id', 0);
        $category_id = intval($category_id);
        if (!empty($category_id)) {
            $class_info = $class_model->classQueryById($category_id, 'id,title,parent_id');
            $this->assign('category', $class_info);
        }

        // 获取父级分类
        $parent = $class_model->classQuery(['parent_id' => 0], 'id,title');
        $parent = $parent->toArray();
        if (!empty($parent)) {
            foreach ($parent as $key => $class) {
                // 获取子分类
                $child_class = $class_model->classQuery(['parent_id' => $class['id']], 'id,title');
                $child_class = $child_class->toArray();
                $parent[$key]['child'] = $child_class;
            }
        }

        $this->assign('parent', $parent);
        return $this->fetch('index/add');
    }

    // 文章分类删除
    public function categoryDel()
    {
        $request = $this->request;
        $class_model = new WikiClassModel();
        $category_id = $request->param('id', 0);
        $category_id = intval($category_id);

        $class = $class_model->classQueryById($category_id, 'id');
        if (empty($class)) {
            $this->error('分类不存在');
        }
        // 判断该分类下是否存在wiki
        $wiki_model = new wikiModel();
        $wiki = $wiki_model->wikiQuery(['class_id' => $category_id], 'id');
        $wiki = $wiki->toArray();
        if (!empty($wiki)) {
            $this->error('该分类存在文章');
        }
        $res = $class_model->classDel($category_id);
        if (!$res) {
            $this->error('删除失败');
        }
        $this->success('删除成功');
    }
}
