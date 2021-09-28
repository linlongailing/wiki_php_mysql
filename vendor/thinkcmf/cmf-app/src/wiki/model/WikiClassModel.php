<?php

namespace app\wiki\model;

use think\Model;
use think\Db;

class WikiClassModel extends Model
{
     protected $name = 'wiki_class';
    // 分类添加
    public function classAdd($class_arr = [])
    {
        return $this->insertGetId($class_arr);
    }

    // 分类删除
    public function classDel($id)
    {
        return $this->where('id', $id)->delete();
    }

    // 分类修改
    public function classUpdate($id, $update_arr)
    {
        return $this->where('id', $id)->update($update_arr);
    }

    // 条件更新
    public function classUpdateCondition($where, $update_arr)
    {
        return $this->where($where)->update($update_arr);
    }

    // 获取总数量
    public function classCount($where)
    {
        return $this->where($where)->count();
    }

    // 用户查询
    public function classQuery($where, $field = '*', $order = 'id asc', $page = 0)
    {
        $res = $this->field($field)->where($where)->order($order)->select();
        if (!empty($page)) {
            $res = $this->field($field)->where($where)->order($order)->paginate($page);
        }
        return $res;
    }

    // 根据分类id查询
    public function classQueryById($id, $field = '*')
    {
        return $this->field($field)->where('id', $id)->find();
    }

    // 分页查询
    public function classPageQuery($where, $field = '*', $order = 'id asc', $offset = 0, $page = 10)
    {
        $sql = "SELECT $field FROM fg_wiki_class a, (select id from fg_wiki_class $where order by $order LIMIT $offset, $page) b where a.id = b.id";
        return Db::query($sql);
    }
}
