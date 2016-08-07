<?php
namespace app\index\controller;

use think\Controller;

use app\api\model\Admin as MAdmin;
use app\api\model\User as MUser;
use app\api\model\Ip;

class Admin extends Controller {

	public function userList() {

		$user = MUser::order('id desc')->page('1,20')->select();

		$this->view->assign('data',$user);

		return $this->view->fetch('list');
	}

	public function info($id = '') {

		if(empty($id))
			return $this->error('ID不能为空');

		$user = MUser::get(['balls_id' => $id]);

		if (empty($user))
			return $this->error('不存在此用户');

		$this->view->assign('data',$user->append(['status_text'])->toArray());

		$today = \think\Db::table('click_log')->whereTime('click_time','today')->where('balls_id',$user->balls_id)->sum('count');
		$this->view->assign('today',$today);

		$week = \think\Db::table('click_log')->whereTime('click_time','week')->where('balls_id',$user->balls_id)->sum('count');
		$this->view->assign('week',$week);

		$month = \think\Db::table('click_log')->whereTime('click_time','month')->where('balls_id',$user->balls_id)->sum('count');
		$this->view->assign('month',$month);

		$sum = \think\Db::table('click_log')->where('balls_id',$user->balls_id)->sum('count');
		$this->view->assign('sum',$sum);

		return $this->view->fetch('info');
	}

	public function status() {

		$statusRes = curl('http://balls.xtype.cn:10025',2);
		$statusWeek = curl('http://balls.xtype.cn:10026',2);
		$statusIp = curl('http://balls.xtype.cn:10027',2);
		
		$status = empty($statusRes) ? '已停止' : date('Y-m-d H:i:s',$statusRes);
		$statusWeek = empty($statusWeek) ? '已停止' : date('Y-m-d H:i:s',$statusWeek);
		$statusIp = empty($statusIp) ? '已停止' : date('Y-m-d H:i:s',$statusIp);
		
		$count_p = \think\Db::table('ip')->where("enabled",true)->count();
		$count_u = \think\Db::table('user')->count();

		$this->view->assign('count_p',$count_p);
		$this->view->assign('count_u',$count_u);
		$this->view->assign('status',$status);
		$this->view->assign('statusWeek',$statusWeek);
		$this->view->assign('statusIp',$statusIp);


		return $this->view->fetch('status');
	}

	public function serverStatus() {

		return $this->redirect('index/admin/status');
	}

	public function add() {
		$count_u = \think\Db::table('user')->count();
		$this->view->assign('count_u',$count_u);
		return $this->view->fetch('add');
	}

	public function ip() {

		$ip = Ip::order('connect_time asc')->where('enabled',true)->page('1,20')->select();
		$this->view->assign('data',$ip);

		$count = IP::where('enabled',true)->count();
		$this->view->assign('count',$count);

		return $this->view->fetch('ip');
	}

	public function ipInfo($id) {

		$ip = IP::get($id);
		if (empty($ip)) {
			return $this->error('IP不存在');
		}
		$this->view->assign('data',$ip);

		return $this->view->fetch('ip-info');
	}
}