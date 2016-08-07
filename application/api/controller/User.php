<?php
namespace app\api\controller;

use think\Controller;
use app\api\model\User as MUser;

class User extends Controller{
    
    public function add() {

        $rule = [
            'url'   =>  'require|url' ,
            'time'  =>  'require|number|between:1,12' ,
        ];

        $validate = new \think\Validate($rule,[
            'url' => '请正确输入您的分享链接' ,
            'time'=> '开通月数在1-12个月之间'
        ]);
        if ( !$validate->check( $this->request->param() ) ) {
            return $this->error($validate->getError());
        }

        $data = getUserInfoByUrl($this->request->param('url'));
        if (empty($data))
            return $this->error('链接错误');

        $temp = MUser::get(['balls_id' => $data['balls_id']]);
        if (empty($temp)) {
            $temp = new MUser();
        }

        $this_time = time();
        if (empty($temp->time_out) || (int)$temp->time_out < $this_time ) {
            $time_out = $this_time + 2678400 * (int)$this->request->param('time');
        } else {
            $time_out = (int)$temp->time_out + 2678400 * (int)$this->request->param('time');
        }

        $temp->time_out = $time_out;
        $temp->save($data);
        
        return $this->success('保存成功',url('index/admin/info',['id' => $temp->balls_id]));
    }

}