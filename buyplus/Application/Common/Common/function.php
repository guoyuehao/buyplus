<?php
function CC($key,$value=null){
        if(!is_null($value)){
            return $GLOBALS['config'][$key] = $value;
        }
        if(isset($GLOBALS['config'][$key])){
            return $GLOBALS['config'][$key];
        }else{
            $model = M('Setting');
            return $model->where(['key'=>$key])->getField('value');
        }
}
?>
