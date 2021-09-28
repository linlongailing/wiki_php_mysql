<?php

namespace app\wiki\model;

use think\Model;
use think\Db;

class WikiModel extends Model
{
    protected $name = 'wiki';
    // wiki添加
    public function wikiAdd($wiki_arr = [])
    {
        return $this->insertGetId($wiki_arr);
    }

    // wiki删除
    public function wikiDel($id)
    {
        return $this->where('id', $id)->delete();
    }

    // wiki修改
    public function wikiUpdate($id, $update_arr)
    {
        return $this->where('id', $id)->update($update_arr);
    }

    // 条件更新
    public function wikiUpdateCondition($where, $update_arr)
    {
        return $this->where($where)->update($update_arr);
    }

    // 获取总数量
    public function wikiCount($where)
    {
        return $this->where($where)->count();
    }

    // 用户查询
    public function wikiQuery($where, $field = '*', $order = 'id asc', $page = 0)
    {
        $res = $this->field($field)->where($where)->order($order)->select();
        if (!empty($page)) {
            $res = $this->field($field)->where($where)->order($order)->paginate($page);
        }
        return $res;
    }

    // 根据wikiid查询
    public function wikiQueryById($id, $field = '*')
    {
        return $this->field($field)->where('id', $id)->find();
    }

    // 分页查询
    public function wikiPageQuery($where, $field = '*', $order = 'id asc', $offset = 0, $page = 10)
    {
        $sql = "SELECT $field FROM fg_wiki a, (select id from fg_wiki $where order by $order LIMIT $offset, $page) b where a.id = b.id";
        return Db::query($sql);
    }
}
