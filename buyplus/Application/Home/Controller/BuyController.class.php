<?php
namespace Home\Controller;
use Home\Cart\Cart;
use Think\Controller;

class BuyController extends Controller
{   
    public function addToCartAction()
    {
        $goods_id = I('request.goods_id',null);
        $product_id = I('request.product_id',null);
        $buy_quantity = I('request.buy_quantity',1,'intval');

        $cart = Cart::instance();
        $cart->addWare($goods_id,$product_id,$buy_quantity);
        $this->ajaxReturn(['error'=>0]);
    }
    
}