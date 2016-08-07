<?php
if(!IS_CLI){  
    die("access illegal");
}

use \Workerman\Worker;
use \Workerman\Lib\Timer;
require_once './Workerman/Autoloader.php';

function curl($url,$time_out = 10) {
    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_TIMEOUT, $time_out);

    $user_agent = "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:33.0) Gecko/20100101 Firefox/33.0";
    curl_setopt ($curl, CURLOPT_USERAGENT, $user_agent);

    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);  
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $str = curl_exec($curl);
    curl_close($curl);

    return $str;
}

Worker::$daemonize = true;

$worker = new Worker('http://0.0.0.0:10027');  
$worker->name = 'Click';  
$worker->count = 8;

// 程序运行时间
global $time_start;
$time_start = time();

// 程序循环时间
global $time_interval;
$time_interval = 3600;

$worker->onWorkerStart = function($worker){
	// 循环时间
	global $time_interval;
	
	// 进程8-14处理自动获取代理IP任务
	if ($worker->id === 0 )
	Timer::add($time_interval + $worker->id,'curl',array('http://balls.xtype.cn/api/Ip/updateIp/pid'.$worker->id,5));

	if ($worker->id === 1 )
	Timer::add($time_interval + $worker->id,'curl',array('http://balls.xtype.cn/api/Ip/updateIp/pid'.$worker->id,5));

	if ($worker->id === 2 )
	Timer::add($time_interval + $worker->id,'curl',array('http://balls.xtype.cn/api/Ip/updateIp/pid'.$worker->id,5));

	if ($worker->id === 3 )
	Timer::add($time_interval + $worker->id,'curl',array('http://balls.xtype.cn/api/Ip/updateIp/pid'.$worker->id,5));

	if ($worker->id === 4 )
	Timer::add($time_interval + $worker->id,'curl',array('http://balls.xtype.cn/api/Ip/updateIp/pid'.$worker->id,5));

	if ($worker->id === 5 )
	Timer::add($time_interval + $worker->id,'curl',array('http://balls.xtype.cn/api/Ip/updateIp/pid'.$worker->id,5));

	if ($worker->id === 6 )
	Timer::add($time_interval + $worker->id,'curl',array('http://balls.xtype.cn/api/Ip/updateIp/pid'.$worker->id,5));

};

$worker->onMessage = function($connection, $data) {
	global $time_interval;
	global $time_start;

	$connection->send($time_start);
};

Worker::runAll(); 