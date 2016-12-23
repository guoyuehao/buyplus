<?php
namespace Back\Model;
use Think\Model\RelationModel;

/**
 * Description of SetModel
 *
 * @author 郭悦昊
 */
class SettingModel extends RelationModel
{
    protected $_link = [
            'optionList'=>[
                'mapping_type' => self::HAS_MANY,
                'class_name' => 'SettingOption',
                'foreign_key' => 'setting_id'
                
            ]
    ];
}

?>
