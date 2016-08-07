<?php
namespace app\api\model;

use think\Model;

class User extends Model {
    protected $pk = 'id';

    public function getStatusTextAttr($value,$data) {
		$status = [
			0 => '今日未开始' , 
			1 => '今日已上限' ,
			2 => '本周已上限' ,
			3 => '任务正在进行' ,
		];
		return $status[$data['status']];
	}

	public function getTimeOutAttr($value) {
      if ((int)$value < time())
         return '已到期';
		return date('Y-m-d',$value);
	}

}