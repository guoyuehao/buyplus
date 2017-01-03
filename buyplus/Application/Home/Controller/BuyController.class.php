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
    public function cartInfoAction()
    {
        $cart = Cart::instance();
        $info = $cart->getInfo();
        $this->ajaxReturn(['error'=>0,'data'=>$info]);
    }
    public function removeFromCartAction()
    {
        $key = I('request.key',null);
        $cart = Cart::instance();
        $cart->removeWare($key);
        $this->ajaxReturn(['error'=>0]);   
    }
    public function cartAction()
    {
        $this->display();
    }
    public function checkoutAction()
    {
        if (!session('member')) {
            session('successUrl',['route'=>'/checkout','param'=>[]]);
            $this->redirect('/login');
        }
        $this->display();
    }
    public function shippingListAction()
    {
        $list = M('Shipping')->where(['enabled'=>1])->select();
        foreach ($list as $k => $shipping) {
            $shippingName = 'Common\Shipping\\' . $shipping['key'];
            $shipping = new $shippingName;
            $list[$k]['price'] = $shipping->price();
        }
        $this->ajaxReturn(['error'=>0,'data'=>($list?$list:[])]);
    }
}