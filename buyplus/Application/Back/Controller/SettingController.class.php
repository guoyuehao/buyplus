<?php
namespace Back\Controller;
use Think\Controller;
/**
 * Description of SettingController
 *
 * @author 郭悦昊
 */
class SettingController  extends Controller
{
    public function setAction(){

        if(!$groupList = S('gruorList')){
                $modelGroup = M('SettingGroup');
                $groupList = $modelGroup->order('sort_number')->select();
                S('groupList',$groupList);
        }
        $this->assign('groupList',$groupList);
        if(!$settingGroupList = S('settingGroupList')){
            $modelSetting = D('setting');
            $settingList = $modelSetting
            ->alias('s')
             ->join('left join __SETTING_TYPE__ st using(setting_type_id)')
            ->order('sort_number')
            ->relation(true)
            ->select();
            foreach ($settingList as $setting){
                 $settingGroupList[$setting['setting_group_id']][] = $setting;
           }           
        }

        $this->assign('settingGroupList',$settingGroupList);
        $this->display();
    }
    public function updateAction(){
        $settingList = I('post.setting',[]);
        $model = M('Setting');
        $allSettingList = $model->getField('setting_id',true);
        foreach ($allSettingList as $setting_id => $value){
            $value = isset($settingList[$setting_id]) ? $settingList[$setting_id] : ' ';
          
            $model->save([
                'setting_id' => $setting_id,
                'value' => is_array($value) ? implode(',', $value) : $value
            ]);         
        }
        S(['type'=>'File']);
        S('groupList',null);
        S('settingGroupList',null);        
        $this->redirect('set');
    }
    public function ajaxAction(){
        $data['setting_id'] = I('request.setting_id');
        $value = I('request.value');
        $data['value'] = is_array($value)?implode(',', $value):$value;
        $model = M('Setting');
        S(['type'=>'File']);
        S('groupList',null);
        S('settingGroupList',null);  
        if($model->save($data)){
            $this->ajaxReturn(['error'=>0]);
        }else{
            $this->ajaxReturn(['error'=>1,'errorInfo'=>$model->getError()]);
        } 
    }
}

?>
