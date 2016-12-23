<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
//排序字段
function UField($route,$sort,$field,$filter=[])
{   
    
    $params['sort_field'] = $field;
    if($sort['field'] != $field){
        $params['sort_type'] = 'asc';
    }else{
        if(strtolower($sort['type'])=='asc'){
            $params['sort_type'] = 'desc';
        }else{
            $params['sort_type'] = 'asc';
        }
    }

    return U($route,  array_merge($filter,$params));
}

//排序箭头控制
function CField($sort,$field)
{
    if($sort[$field]==$field){
        return $sort['type'];
    }else{
        return '';
    }
}
?>
