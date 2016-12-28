<?php


namespace Back\Controller;

use Think\Controller;
use Think\Page;

class AttributeController extends Controller
{

    public function addAction()
    {
            if (IS_POST) {

            $model = D('Attribute');
            if ($model->create()) {// 校验
                $attribute_id = $model->add();// 添加
                $option_list = preg_split('/\r\n|\n/', I('post.option_list',''));
                $optionRows = [];
                foreach ($option_list as $value) {
                    $optionRows[] = ['attribute_id' => $attribute_id, 'option_value' => $value];
                }
                M('AttributeOption')->addAll($optionRows);
                $this->redirect('list');// 重定向到列表动作
            } else {
                // 将错误信息存储到session中, 便于下个页面输出错误消息
                session('message', ['error'=>1, 'errorInfo'=>$model->getError()]);
                session('data', $_POST);
                $this->redirect('add'); // 重定向到添加
            }
        } else {
//            获取到, 分配到模板, 删除, 做一个一次性的session会话数据
            $this->assign('message', session('message'));
            session('message', null);// 删除该信息
            $this->assign('data', session('data'));
            session('data', null);
            //获取输入类型和商品类型选项数据
            $this->assign('typeList',M('Type')->select());
            $this->assign('attributeTypeList',M('AttributeType')->select());
            $this->display('set');
        }
    }

    /**
     * 更新
     */
    public function editAction()
    {

        $model = D('Attribute');
        if (IS_POST) {
            if ($model->create()) {// 校验
                $model->save();// 更新
                //获取属性选项列表
                $optionList = preg_split('/\r\n|\n/', I('post.option_list',''));
                $oldOptionList = M('AttributeOption')
                ->where(['attribute_id'=>I('post.attribute_id')])
                ->getField('option_value',true);
                //找到新的
                $newOptionRows = array_map(function($value) use($oldOptionList) {
                    if (! in_array($value, $oldOptionList)) {
                        return [
                            'attribute_id' => I('post.attribute_id'),
                            'option_value' => $value
                        ];
                    }
                },$optionList);
                foreach ($newOptionRows as $key => $value) {
                    if (is_null($value)) {
                        unset($newOptionRows[$key]);
                    }
                }
                $newOptionRows = array_values($newOptionRows);
                
                M('AttributeOption')->addAll($newOptionRows);
                //找到需要删除的将其删除
                $delList=[];
                foreach ($oldOptionList as $old) {
                    if(! in_array($old,$optionList)){
                        $delList[] = $old;
                    }
                }
                if(! empty($delList)){
                    M('AttributeOption')
                    ->where([
                            'attribute_id' => I('post.attribute_id'),
                            'option_value' =>['in',$delList]
                        ])
                    ->delete();
                }

                $this->redirect('list');// 重定向到列表动作
            } else {
                // 将错误信息存储到session中, 便于下个页面输出错误消息
                session('message', ['error'=>1, 'errorInfo'=>$model->getError()]);
                session('data', $_POST);
                $this->redirect('edit', ['attribute_id'=>I('post.attribute_id')]); // 重定向到添加
           }
       } else {
           $this->assign('message', session('message'));
           session('message', null);// 删除该信息
           // 获取当前编辑的内容, 如果是编辑错误,则显示错误的内容, 如果是没有错误, 则显示原始数据内容
           $this->assign('data', is_null(session('data')) ? $model->find(I('get.attribute_id')) : session('data'));
           session('data', null);
           //获取输入类型和商品类型选项数据
            $this->assign('typeList',M('Type')->select());
            $this->assign('attributeTypeList',M('AttributeType')->select());
            $optionList = M('AttributeOption')->where(['attribute_id'=>I('get.attribute_id')])->getField('option_value',true);
            
            $this->assign('optionList',implode("\n",$optionList));

           // 展示
           $this->display('set');
       }
    }

    public function listAction()
    {

        $model = M('Attribute');

        // 一: 查询条件
        $cond = [];// 初始化查询条件
        $filter = []; // 初始化一个用于记录查询查询的数组, 分配到视图模板中
        // 自己完成的部分, 特殊的业务逻辑
        // 继续判断其他的字段, 入$cond和$filter数组即可
        // 所有检索结束, 分配搜索条件
        $this->assign('filter', $filter);


        // 二: 考虑分页
        $pagesize = 4;// 每页记录数
        // 计算总页数
        $total = $model->where($cond)->count();// 所有符合条件的记录数
        $totalPage = ceil($total/$pagesize);// 计算总页数
        $p = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';// 当前的翻页参数
        $page = I('get.'.$p, '1', 'intval');
        // 考虑是否越界
        if ($page < 1) {// 小于第一页
            $page = 1;
        }
        if ($page > $totalPage) { // 考虑大于总页数
            $page = $totalPage;
        }
        // 为模型设置分页操作, 页码和每页记录数作为参数
        $model->page("$page,$pagesize");
        $toolPage = new Page($total,$pagesize);
        $toolPage->setConfig('header', '第%OFFSET%条记录到第%OFFSETS%条/共%TOTAL_ROW%条（共%TOTAL_PAGE%页）');      
        $toolPage->setConfig('theme', '<div class="col-sm-6 text-left"><ul class="pagination">%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%</ul></div><div class="col-sm-6 text-right">%HEADER%</div>');
        $this->assign('pageHtml', $toolPage->show());

        // 三: 考虑排序
        $sort = [
            'field' => I('get.sort_field', null),
            'type' => I('get.sort_type', 'asc'),
        ];// 默认的排序方式
//        确定排序字符串
        if (! is_null($sort['field'])) {// 没有排序字段
            $sortString = $sort['field'] . ' ' . $sort['type'];
            $model->order($sortString);
        }
//        将当前的排序方式, 分配到模板中
        $this->assign('sort', $sort);


        // 四: 执行查询
        $list = $model->where($cond)->select();
        $this->assign('list', $list);

        $this->display();
    }

    /**
     * 提供批量处理操作
     */
    public function multiAction()
    {

        $operate = I('post.operate', null);


        // 先处理删除
        $operate = 'delete';
        switch ($operate) {
            case 'delete':
                $model = M('Attribute');
                $model->where(['attribute_id'=>['in', I('post.selected')]])->delete();
                break;
        }
        $this->redirect('list');
    }
}