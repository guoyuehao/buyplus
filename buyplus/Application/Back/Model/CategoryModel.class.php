<?php

namespace Back\Model;

use Think\Model;


class CategoryModel extends Model
{
    protected $patchValidate = true;
    protected $_validate = [
         ['parent_id', 'nonChild', '不能设置后代分类为上级分类', self::EXISTS_VALIDATE, 'callback', self::MODEL_UPDATE],            
    ];
    
    public function nonChild($parent_id){
        if($parent_id == 0){
            return true;
        }
        $category_id = I('post.category_id');
        if($parent_id == $category_id){
            return false;
        }
        return $this->checkParents($parent_id,$category_id);
    }

    public function checkParents($parent_id,$category_id){
        if($parent_id == 0){
            return true;
        }
        $parent_id = $this->where(['category_id'=>$parent_id])->getField('parent_id');
        if($parent_id ==$category_id){
            return false;
        }else{
            return $this->checkParents($parent_id, $category_id);
        }
    }
    
    public function getTreeList()
    {
        // 初始缓存配置
        S([
            'type' => 'Memcache',
            'host'  => '192.168.153.128',
            'port'  => '11211',
        ]);

        if(! $tree = S('category_tree')) {
            // 缓存不存在
            // 获取所有的分类
            $list = $this->order('sort_number')->select();
            // 递归处理
            $tree = $this->tree($list);

            // 增加设置缓存
            S('category_tree', $tree);// 默认永久有效 
        }

        return $tree;
    }

    protected function tree($list, $category_id=0, $level=0)
    {
        static $tree = [];
        foreach($list as $row) {
            if ($row['parent_id'] == $category_id) {
                $row['level'] = $level;
                $tree[] = $row;
                $this->tree($list, $row['category_id'], $level+1);
            }
        }
        return $tree;
    }
}