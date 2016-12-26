<?php
namespace Back\Model;

use Think\Model;

class GoodsModel extends Model
{
    // 验证规则
    protected $patchValidate = true;
    protected $_validate = [
        ['sku_id','chkSku','请选择合理的库存单位',self::EXISTS_VALIDATE,'callback',self::MODEL_BOTH],
        ['tax_id','chkTax','请选择合理的税类型',self::EXISTS_VALIDATE,'callback',self::MODEL_BOTH],
        // ['length_unit_id', 'chkLengthUnit', '请选择合理的长度单位',self::EXISTS_VALIDATE,'callback',self::MODEL_BOTH],
        // ['weight_unit_id', 'chkWeightUnit', '请选择合理的重量单位',self::EXISTS_VALIDATE,'callback',self::MODEL_BOTH],
        // ['stock_status_id', 'chkStockStatus', '请选择合理的库存单位',self::EXISTS_VALIDATE,'callback',self::MODEL_BOTH],
        // ['brand_id', 'chkBrand', '请选择合理的品牌id',self::EXISTS_VALIDATE,'callback',self::MODEL_BOTH],
        ['category_id', 'chkCategory', '请选择合理的分类',self::EXISTS_VALIDATE,'callback',self::MODEL_BOTH]
    ];

    // 完成规则
    protected $_auto = [
        ['upc','mkUpc',self::MODEL_INSERT,'callback'],
        ['created_at','time',self::MODEL_INSERT,'function'],
        ['updated_at','time',self::MODEL_BOTH,'function'],

    ];

    protected function mkUpc($value){
        if ($value !== '') {
            return $value;
        }
        return time() . mt_rand(100,999) . mt_rand(100,999) . mt_rand(100,999);
    }

    protected function chkSku($value){
        return (bool) M('Sku')->find($value);
    }

    protected function chkTax($value){
        return (bool) M('Tax')->find($value);
    }

    protected function chkCategory($value){
        return (bool) M('Category')->find($value);
    }
}