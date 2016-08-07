<?php
namespace app\api\controller;

use think\Controller;
use app\api\model\User;
use app\api\model\Ip;
use app\api\model\ClickLog;


// 模拟点击器
class Click extends Controller {

	public function click($pid = 0) {
		ignore_user_abort();
		set_time_limit(0);

		// 第一次筛选：状态为今日未获取，期限未到的用户
		$needClikUser = User::where('enabled',true)->where('status' , 0)->where('time_out','>= time',date('Y-m-d'))->select();

		if (empty($needClikUser)) {
			$this->sendErrorEmail($pid,'没用该点击的用户了');
			return 'no user';
		}

		$success_times = 0;
		
		foreach ($needClikUser as $key => $temp) {
			// 第二次判断
			$isWork = User::get(['balls_id' => $temp->balls_id , 'status' => 0]);
			// 其他进程正在处理
			if(empty($isWork))
				continue;

			// 设为工作状态
			$temp->status = 3;
			$temp->save();

			$times = 0;
			do {
				$ip = Ip::order('times asc,connect_time asc')->where('enabled',true)->find();
				// 没有可用IP
				if (empty($ip)){
					$this->sendErrorEmail($pid,'没用可用的代理IP了');
					return 'false';
				}
				// 点击一次
				$resultArr = clickById($temp->balls_id,$ip->ip,$ip->port);
				// 更新代理IP的使用qi
				\think\Db::table('ip')->where('id', $ip->id)->setInc('times');

				if ($resultArr['result'] == "day over") {
					break;
				} else if ($resultArr['result'] == "week over") {
					break;
				} else if ($resultArr['result'] == "db error") {
					break;
				} else {
					\think\Db::table('ip')->where('id', $ip->id)->setField('enabled', false);
				}

				\think\Db::table('ip')->where('id', $ip->id)->setField('connect_time', $resultArr['connect_time']);
				// 循环最多20次
			} while ($times <= 20 && ++$times);

			if ($resultArr['result'] == "week over" ) {
				$log = ClickLog::whereTime('click_time','today')->where('balls_id',$temp->balls_id
				)->find();
				if (empty($log))
					$log = new ClickLog();
				$log->balls_id = $temp->balls_id;
				$log->click_time = time();
				$log->save();
				
				$temp->status = 2;
			} else {
				$log = ClickLog::whereTime('click_time','today')->where('balls_id',$temp->balls_id
				)->find();
				if (empty($log))
					$log = new ClickLog();
				$log->balls_id = $temp->balls_id;
				$log->click_time = time();
				$log->save();

				$temp->status = 1;
			}

			$temp->save();
			$success_times++;
		}

		$this->sendSuccessEmail($pid,$success_times);
		return 'true';
	}

	public function once($id) {
		$user = User::where('enabled',true)->where('time_out','>= time',date('Y-m-d'))->where('balls_id',$id)->find();

		if (empty($user))
			return $this->error('ID已过期或不存在');

		curl($this->request->root(true).url('api/click/onceClick',['id' => $user->balls_id]),1);

		return $this->success('操作准备执行',url('index/admin/info',['id' => $user->balls_id])); 
	}

	public function test() {
		$ip = Ip::order('times asc,connect_time asc')->where('enabled',true)->find();
		\think\Db::table('ip')->where('id', $ip->id)->setInc('times');

	}

	// 点击一个用户
    public function onceClick($id) {
    	ignore_user_abort();
		set_time_limit(0);

		$user = User::get(['enabled' => true , 'balls_id' => $id]);

		if (empty($user)) {
			return 'error';
		}

		if ($user->status != 0)
			return 'error';

		// 设为工作状态
		$user->status = 3;
		$user->save();

		$times = 0;
		do {
			$ip = Ip::where('enabled',true)->order('times asc,connect_time asc')->find();

			if (empty($ip)){
				return 'false';
			}
			// 点击一次
			$resultArr = clickById($user->balls_id,$ip->ip,$ip->port);

			\think\Db::table('ip')->where('id', $ip->id)->setInc('times');

			if ($resultArr['result'] == "day over") {
				break;
			} else if ($resultArr['result'] == "week over") {
				break;
			} else if ($resultArr['result'] == "db error") {
				break;
			} else {
				\think\Db::table('ip')->where('id', $ip->id)->setField('enabled', false);
			}

			\think\Db::table('ip')->where('id', $ip->id)->setField('connect_time', $resultArr['connect_time']);

			// 循环最多20次
		} while ($times <= 20 && ++$times);

		if ($resultArr['result'] == "week over" ) {
			$log = ClickLog::whereTime('click_time','today')->where('balls_id',$user->balls_id
				)->find();
			if (empty($log))
				$log = new ClickLog();
			$log->balls_id = $user->balls_id;
			$log->click_time = time();
			$log->save();

			$user->status = 2;
		} else {
			$log = ClickLog::whereTime('click_time','today')->where('balls_id',$user->balls_id
				)->find();
			if (empty($log))
				$log = new ClickLog();
			$log->balls_id = $user->balls_id;
			$log->click_time = time();
			$log->save();

			$user->status = 1;
		}
		
		$user->save();

		return 'ok';
    }

    // 重置所有状态
    public function setStatus() {

        $needSetUser = User::where("status","<>",2)->select();

        foreach ($needSetUser as $key => $value) {
            $value->status = 0;
            $value->save();
        }

        return 'true';
    }

    // 重置所有状态
    public function setWeek() {

        $needSetUser = User::all(['status' => 2]);

        foreach ($needSetUser as $key => $value) {
            $value->status = 0;
            $value->save();
        }

        return 'true';
    }

    // 发送完成邮件
	protected function sendSuccessEmail($pid = 0,$success_times = 0) {

		$html = "任务".$pid."已顺利完成任务，请首长指示！<br />已完成：".$success_times;
		sendEmail('792598794@qq.com','自动任务完成提示邮件',$html);

		return true;
	}

	// 发送失败邮件
	protected function sendErrorEmail($pid = 0,$msg = '') {

		$html = "任务".$pid."完成任务失败，原因是:".$msg;
		sendEmail('792598794@qq.com','自动任务失败提示邮件',$html);

		return true;
	}

}