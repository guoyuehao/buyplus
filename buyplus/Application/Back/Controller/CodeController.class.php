<?php
namespace Back\Controller;
use Think\Controller;

class CodeController extends Controller
{
    public function crudAction()
    {
        $step = I('get.step','table');
        if('table' == $step){
           if(IS_POST){
               session('crud',['table'=>I('post.table'),'title'=>I('post.title')]);
               $this->redirect('crud',['step'=>'field']);
           }else{
                $this->assign('step','table');
                $this->display();            
           } 
        }
        elseif('field' == $step){
            $model = M(session('crud.table'));
            $fieldList = $model->getDbFields();
            if(IS_POST){
                //生成控制器和模型名
                $controllerName = $modelName = implode(array_map('ucfirst', explode('_', $model->getModelName())));
                $pkField = $model->getPK();//获取主键
                $fieldController = I('post.field');//获取字段列表
                $tableTitle = session('crud.title');//获取对应展示标题
                //生成控制器代码
                $template = APP_PATH . 'Back/Code/controller.template';
                $content = file_get_contents($template);
                $search = ['__CONTROLLER__','__MODEL__','__PK_FIELD__'];
                $replace = [$controllerName,$modelName,$pkField];
                $content = str_replace($search,$replace,$content);
                $controllerFile = APP_PATH . 'Back/Controller/' . $controllerName . 'Controller.class.php';
                file_put_contents($controllerFile, $content);
                echo '控制器文件：',$controllerName,'生成成功<br>';
                //生成模型代码
                $template = APP_PATH . 'Back/Code/model.template';
                $content = file_get_contents($template);
                $search = ['__CONTROLLER__','__MODEL__','__PK_FIELD__'];
                $replace = [$controllerName,$modelName,$pkField];
                $content = str_replace($search,$replace,$content);
                $file = APP_PATH . 'Back/Model/' . $modelName . 'Model.class.php';
                file_put_contents($file, $content);
                echo '模型文件：',$modelName,'生成成功<br>';
                //生成视图代码
                //生成字段列表
                $theadTdList = $tbodyTdList = $setFieldList =  '';
                foreach ($fieldConertoller as $field => $option){
                    $search = ['__FIELD__','__FIELD_TITLE__'];
                    $replace = [$field,$option['title']!==''?$option['title']:$field];       
                    //判断是否选择显示
                    if (isset($option['is_list'])){
                        //判断是否做排序
                        if(isset($option['is_sort'])){
                            $template = APP_PATH . 'Back/Code/listTheadTdSortView.template';
                        }else{
                            $template = APP_PATH . 'Back/Code/listTheadTdView.template';
                        }
                        $content = file_get_contents($template);
                        $content = str_replace($search, $replace, $content);
                        $theadTdList .= $content;
                        //tbody部分
                        $template = APP_PATH . 'Back/Code/listTbodyTdView.template';
                        $content = file_get_contents($template);
                        $content = str_replace($search, $replace, $content);
                        $tbodyTdList .= $content;
                    }
                    //为set模版生成字段
                    if(isset($option['is_set'])){
                        if($field == $pkField) continue;
                        $template = APP_PATH . 'Back/Code/setFiledView.template';
                        $content = file_get_contents($template);
                        $content = str_repeat($search, $replace,$content);
                        $setFieldList .= $content;
                    }
                }
                //替换list整体
                $template = APP_PATH . 'Back/Code/listView.template';
                $content = file_get_contents($template);
                $search = ['__TITLE__','__THEAD_LIST__','__TBODY_LIST__','__PK_FIELD__'];
                $replace = [$tableTitle,$theadTdList,$tbodyTdList,$pkField];
                $content = str_replace($search, $replace, $content);
                $path = APP_PATH . 'Back/View/' . $controllerName;
                if(!is_dir($path)){
                    mkdir($path);
                }
                $file = $path . '/list.html';
                file_put_contents($file, $content);
                echo '列表视图文件：' . $file . '生成成功<br>';     
                //替换set整体
                $template = APP_PATH . 'Back/Code/setView.template';
                $content = file_get_contents($template);
                $search = ['__TITLE__','__FIELD_LIST__','__PK_FIELD__'];
                $replace = [$tableTitle,$setFieldList,$pkField];
                $content = str_replace($search, $replace, $content);
                $path = APP_PATH . 'Back/View/' . $controllerName;
                if(!is_dir($path)){
                    mkdir($path);
                }
                $file = $path . '/set.html';
                file_put_contents($file, $content);
                echo 'set视图文件：' . $file . '生成成功<br>';
            }else{
                $this->assign('fieldList',$fieldList);
                $this->assign('step','field');
                $this->display();                
            }
        }
    }
}

?>
