<?php
namespace Back\Controller;
use Think\Controller;
use Think\Page;
use Think\Upload;
use Think\Image;


class BrandController extends Controller
{
    public function addAction()
    {
        if (IS_POST) {
            $model = D('Brand');
            if($model->create()){
                $toolUpload = new Upload();
                $toolUpload->exts = ['jpg','png','jpeg','git'];
                $toolUpload->maxSize = 2*1024*1024;
                $toolUpload->rootPath = APP_PATH . 'Upload/';
                $toolUpload->savePath = 'Brand/';
                $uploadInfo = $toolUpload->uploadOne($_FILES['logo']);
                if($uploadInfo){
                   $model->logo = $uploadInfo['savepath'] . $uploadInfo['savename'];
                   $toolImg = new Image();
                   if(!is_dir('./Public/Thumb/'.$uploadInfo['savepath'])){
                       mkdir('./Public/Thumb/'.$uploadInfo['savepath'],0764,true);
                   }
                   $toolImg->open(APP_PATH . 'Upload/'.$model->logo);
                   $toolImg->save('./Public/Thumb/'.$model->logo);
                }

                $model->add();
                $this->redirect('list');
            }else{
                session('message',['error'=>1, 'errorInfo'=>$model->getError()]);
                session('data',$_POST);
                $this->redirect('add');
            }
        }  else {
            $this->assign('message',session('message'));            
            $this->assign('data',session('data'));
            session('message',null);
            session('data',null);
            $this->display('set');
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
        $pagesize = 4;
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
        
        //排序
        $sort = [
            'field' => I('get.sort_field','sort_number'),
            'type' => I('get.sort_type','asc')
            ];
        if(!empty($sort)){
            $sortString = $sort['field'] . ' ' . $sort['type'];
            $model->order($sortString);
        }
        $this->assign('sort',$sort);
        
        $this->assign('pageHtml',$toolPage->show());        
        $list = $model->where($cond)->select();
        $this->assign('list',$list);
        $this->display();
    }
    //批量处理
    public function multiAction()
    {
        $operate = I('post.operate','delete');
        switch ($operate){
            case 'delete':
                $model = M('Brand');
                
                foreach ($model->where(['brand_id'=>['in',I('post.selected')]])->getField('logo',true) as $logo){
                    @unlink(APP_PATH .'Upload/' . $logo);
                    @unlink('./Public/Thumb/' . $logo);
                }
                $model->where(['brand_id'=>['in',I('post.selected')]])->delete();
                break;           
        }
        $this->redirect('list');
    }
    //编辑模块
    public function editAction()
    {
        $model = M('Brand');
        if(IS_POST){
            if($model->create()){
                $toolUpload = new Upload();
                $toolUpload->exts = ['jpg','png','jpeg','git'];
                $toolUpload->maxSize = 2*1024*1024;
                $toolUpload->rootPath = APP_PATH . 'Upload/';
                $toolUpload->savePath = 'Brand/';
                $uploadInfo = $toolUpload->uploadOne($_FILES['logo']);
                if($uploadInfo){
                   $model->logo = $uploadInfo['savepath'] . $uploadInfo['savename'];
                   $toolImg = new Image();
                   if(!is_dir('./Public/Thumb/'.$uploadInfo['savepath'])){
                       mkdir('./Public/Thumb/'.$uploadInfo['savepath'],0764,true);
                   }
                   $toolImg->open(APP_PATH . 'Upload/'.$model->logo);
                   $toolImg->save('./Public/Thumb/'.$model->logo);
                   
                $oldLogo = $model->where(['brand_id'=>I('post.brand_id')])->getField('logo');
                @unlink(APP_PATH . 'Upload/' . $oldLogo );
                @unlink('./Public/Thumb/' . $oldLogo);
                }
                $model->save();
                $this->redirect('list');
            }else{
                session('message',['error'=>1, 'errorInfo'=>$model->getError()]);
                session('data',$_POST);
                $this->redirect('set',['brand_id'=>I('post.brand_id')]);
            }           
        }else{
            $this->assign('message',  session('message'));
            session('message',null);
            $this->assign('data',is_null(session('data'))?$model->find(I('get.brand_id')):  session('data'));
            session('data',null);
            $this->display('set');
        }
    }
    
    public function ajaxAction(){
        $operate = I('request.operate',null);
        if(is_null($operate)){
            $this->ajaxReturn(['error'=>1,'errorinfo'=>'没有确定的操作']);
        }
        
        switch ($operate){
            case 'titleUnique':
                $model = M('Brand');
                if($row = $model->getByTitle(I('request.title',''))){
                    if($row['brand_id'] == I('request.brand_id')){
                        echo 'true';
                    }else{
                        echo 'false';
                    }
                }  else {
                    echo 'true';
                }
                break;
        }
    }
}
?>
