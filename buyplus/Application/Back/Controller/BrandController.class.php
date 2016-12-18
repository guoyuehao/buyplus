<?php
namespace Back\Controller;
use Think\Controller;

class BrandController extends Controller
{
    public function addAction()
    {
        if (IS_POST) {
            $model = D('Brand');
            if($model->create()){
                $model->add();
                $this->redirect('list');
            }else{
                session('message',['error'=>1, 'errorInfo'=>$model->getError()]);
                session('date',$_POST);
                $this->redirect('add');
            }
        }  else {
            $this->assign('message',session('message'));            
            $this->assign('date',session('date'));
            session('message',null);
            session('date',null);
            $this->display();
        }
    }
    
    public function listAction() {
        $this->display();
    }
    
    public function ajaxAction(){
        $operate = I('request.operate',null);
        if(is_null($operate)){
            $this->ajaxReturn(['error'=>1,'errorinfo'=>'没有确定的操作']);
        }
        
        switch ($operate){
            case 'titleUnique':
                $model = M('Brand');
                if($model->getByTitle(I('request.title',''))){
                    echo 'false';
                }  else {
                    echo 'true';
                }
                break;
        }
    }
}
?>
