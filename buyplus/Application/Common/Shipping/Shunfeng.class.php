<?php
namespace Common\Shipping;
use Common\Interfaces\I_Shipping;

class Shunfeng implements I_Shipping
{
    public function key()
    {
        return 'shunfeng';
    }
    public function title()
    {
        return '顺丰快递';
    }
    public function price()
    {
        return 10;
    }
}