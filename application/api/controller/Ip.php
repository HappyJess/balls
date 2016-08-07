<?php
namespace app\api\controller;

use app\api\model\Ip as MIp;

class Ip {

	public function updateIp($pid = 1) {
		ignore_user_abort();
		set_time_limit(0);

		$arr = getTodayIp($pid);
		if (empty($arr))
			return 'false';

		foreach ($arr as $key => $value) {
			if ( empty($value['ip']) )
				continue;
			
			$temp = MIp::get(['ip' => $value['ip']]);
			// 已存在或者其他进程在处理
			if (!empty($temp))
				continue;

			$temp = new MIp();

			$temp->save($value);

			$connect_time = testIp($temp->ip,$temp->port);

			if (!empty($connect_time) && $connect_time != 0) {
				$temp->connect_time = $connect_time;
				$temp->enabled = true;
				$temp->save();
			}

		}

		return 'true';
	}


}