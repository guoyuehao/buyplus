<?php

namespace Back\Controller;

use Think\Controller;

class CategoryController extends Controller
{
    
    public function addAction(){
        if(IS_POST){
            $model = D('Category');
            if($model->create()){
                $model->add();
                S([
                    'type' => 'Memcache',
                    'host' => '192.168.153.1',
                    'port' => '11211'
                ]);
                S('category_tree',null);
                $this->redirect('list');
            }else{
                session('message',['error'=>1,'erroeInfo'=>$model->getError()]);
                session('data',$_POST);
                $this->redirect('add');
            }
        }else{
            $this->assign('message',  session('message'));
            session('message',null);
            $this->assign('data',  session('data'));
            session('data',null);
            $modelCategory = D('Category');
            $this->assign('list',$modelCategory->getTreeList());
            $this->display('set');
        }
    }
    
    public function editAction(){
        $model = D('Category');
        if(IS_POST){
            if($model->create()){
                $model->save();
                S([
                    'type' => 'Memcache',
                    'host' => '192.168.153.1',
                    'port' => '11211'
                ]);
                S('category_tree',null);
                $this->redirect('list');
            }else{
                session('message',['error'=>1,'errorInfo'=>$model->getError()]);
                session('data',$_POST);
                $this->redirect('edit',['category_id'=>I('post.category_id')]);
            }
        }else{
            $this->assign('message',  session('message'));
            session('message',null);
            $this->assign('data', is_null(session('data'))?$model->find(I('get.category_id')):session('data'));

            session('data',null);
            $modelCategory = D('Category');
            $this->assign('list',$modelCategory->getTreeList());
            $this->display('set');
        }
    }

    public function listAction(){

        $modelCategory = D('Category');

        $this->assign('list', $modelCategory->getTreeList());
        $this->display();
    }
  
    public function mutilAction(){
        $operate = I('post.operate',null);

        $operate = 'delete';
        switch ($operate) {
            case 'delete':
                $model = M('Category');
                $model->where(['category_id'=>['in',I('post.selected')]])->delete();

                S([
                    'type' => 'Memcache',
                    'host' => '192.168.153.1',
                    'port' => '11211'                   
                ]);
                S('category_tree',null);
                break;
        }
        $this->redirect('list');
    }
}