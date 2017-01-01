<?php
namespace Home\Model;
use Think\Model;

class CategoryModel extends Model
{
    public function getNestetList()
    {
       $list = $this->where(['is_used'=>1,'is_nav'=>1])->order('sort_number')->select(); 
       $nestedList = $this->getNested($list);
       // dump($nestedList);
       return $nestedList;
    }
    public function getNested($list,$category_id=0)
    {   
        $children = [];
        foreach ($list as $row) {
            if ($row['parent_id']==$category_id) {
                $row['children'] = $this->getNested($list,$row['category_id']);
                $children[] = $row;
            }
        }
        return $children;
    }
}