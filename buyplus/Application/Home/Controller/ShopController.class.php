<?php

namespace Home\Controller;
use Home\Cart\Cart;
use Think\Controller;

class ShopController extends Controller
{
    public function indexAction()
    {   
        $this->display();
    }
    public function categoryAction()
    {
        switch (I('request.operate','')) {
            case 'nestedList':
                $this->ajaxReturn(['error'=>0,'data'=>D('Category')->getNestetList()]);
            break;
            
            default:
                $this->ajaxReturn(['error'=>1,'errorInfo'=>'操作不支持']);
            break;
        }
    }
    public function goodsAction()
    {
        switch (I('request.operate','')) {
            case 'new':
                $rows = M('Goods')->where(['status'=>'1','deleted'=>'0'])->order('created_at desc, sort_number')->limit(I('request.limit',CC('goods_new_number')))->select();
                if ($rows) {
                    $rows = array_map(function($row){
                        $row['url'] = U('show/' . $row['goods_id']);
                        return $row;
                    }, $rows);
                    $this->ajaxReturn(['error'=>0,'data'=>$rows]);
                }else{
                    $this->ajaxReturn(['error'=>1,'errorInfo'=>'查询失败']);
                }
            break;
            case 'show':
                $modelGoods = D('Goods');
                $row = $modelGoods
                    ->field('g.*, ss.title stock_status_title')
                    ->alias('g')
                    ->join('left join __STOCK_STATUS__ ss using(stock_status_id)')
                    ->relation(true)
                    ->where(['status'=>'1', 'deleted'=>'0', 'goods_id'=>I('request.goods_id')])
                    ->find();
                if ($row) {
                    // 查询货品信息
                    $productList = M('Product')
                        ->field('p.product_id, p.promoted, pd.value price_drift, product_price, product_quantity, group_concat(a.attribute_title, \':\', ao.option_value) as `option`')
                        ->alias('p')
                        ->join('left join __PRODUCT_GOODS_ATTRIBUTE_OPTION__ pgao using(product_id)')
                        ->join('left join __GOODS_ATTRIBUTE_OPTION__ gao using(goods_attribute_option_id)')
                        ->join('left join __ATTRIBUTE_OPTION__ ao using(attribute_option_id)')
                        ->join('left join __ATTRIBUTE__ a using(attribute_id)')
                        ->join('left join __PRICE_DRIFT__ pd using(price_drift_id)')
                        ->where(['goods_id'=>I('request.goods_id'), 'enabled'=>1])
                        ->group('p.product_id')
                        ->select();

                    $row['productList'] = $productList ? $productList : [];

                    $this->ajaxReturn(['error'=>0,'data'=>$row]);
                }else{
                    $this->ajaxReturn(['error'=>1,'errorInfo'=>'查询失败']);
                }
            break;
            default:
                $this->ajaxReturn(['error'=>1, 'errorInfo'=>'操作不支持']);
            break;
        }
    }
    public function showAction()
    {
        $this->assign('goods_id',I('get.goods_id'));
        $this->display();
    }
    public function breadcrumbAction()
    {
        $goods_id = I('request.goods_id',null);
        $modelGoods = M('Goods');
        $category_id = $modelGoods->where(['goods_id'=>$goods_id])->getField('category_id');
        $modelCategory = M('Category');
        $breadcrumb = [];    
        do{
            $category = $modelCategory->find($category_id);
            $category['url'] = U('/category/' . $category_id);
            array_unshift($breadcrumb, $category);
            $category_id = $category['parent_id'];
        }while($category['parent_id'] != 0);

        $this->ajaxReturn(['error'=>0, 'data'=>$breadcrumb]);
    }
}