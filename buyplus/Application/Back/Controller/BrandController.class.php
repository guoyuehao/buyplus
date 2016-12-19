<?php
namespace Back\Controller;
use Think\Controller;
use Think\Page;


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
        $model = M('Brand');
        
        //查询条件初始化
        $cond = [];
        $filter = [];   //记录查询到的数组用于分配
        if(null !== $title = I('get.filter_title',null,'trim')){
            $cond['title'] = ['like',$title.'%'];
            $filter['filter_title'] = $title;
        }
        $this->assign('filter',$filter);
        $pagesize = 1;
        $total = $model->where($cond)->count();
        $totalpage =  ceil($total/$pagesize);
        $p = C('VAR_PAGE')?C('VAR_PAGE'):'p';
        $page = I('get.'.$p,'1','intval');
        if($page<1){
            $page = 1;
        }
        if($page>$totalpage){
            $page = $totalpage;
        }
        $model->page("$page,$pagesize");
        $toolPage = new Page($total,$pagesize);
        $toolPage->setConfig('header', '第%OFFSET%条记录到第%OFFSETS%条/共%TOTAL_ROW%条（共%TOTAL_PAGE%页）');      
        $toolPage->setConfig('theme', '<div class="col-sm-6 text-left"><ul class="pagination">%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%</ul></div><div class="col-sm-6 text-right">%HEADER%</div>');
        
        $this->assign('pageHtml',$toolPage->show());        
        $list = $model->where($cond)->select();
        $this->assign('list',$list);
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
