<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

// 随机I地址
function randIp(){
	$ip_long = [
		['607649792', '608174079'], //36.56.0.0-36.63.255.255
		['975044608', '977272831'], //58.30.0.0-58.63.255.255
		['999751680', '999784447'], //59.151.0.0-59.151.127.255
		['1019346944', '1019478015'], //60.194.0.0-60.195.255.255
		['1038614528', '1039007743'], //61.232.0.0-61.237.255.255
		['1783627776', '1784676351'], //106.80.0.0-106.95.255.255
		['1947009024', '1947074559'], //116.13.0.0-116.13.255.255
		['1987051520', '1988034559'], //118.112.0.0-118.126.255.255
		['2035023872', '2035154943'], //121.76.0.0-121.77.255.255
		['2078801920', '2079064063'], //123.232.0.0-123.235.255.255
		['-1950089216', '-1948778497'], //139.196.0.0-139.215.255.255
		['-1425539072', '-1425014785'], //171.8.0.0-171.15.255.255
		['-1236271104', '-1235419137'], //182.80.0.0-182.92.255.255
		['-770113536', '-768606209'], //210.25.0.0-210.47.255.255
		['-569376768', '-564133889'], //222.16.0.0-222.95.255.255
	];
	$rand_key = mt_rand(0, 14);
	$huoduan_ip= long2ip(mt_rand($ip_long[$rand_key][0], $ip_long[$rand_key][1]));

	return $huoduan_ip;
}

// 解析url
function convertUrlQuery($query) {
    $queryParts = explode('&', $query);
    $params = array();
    foreach ($queryParts as $param)
    {
        $item = explode('=', $param);
        $params[$item[0]] = $item[1];
    }
    
    return $params;
}

function getUserInfoByUrl($url = '') {
	if (empty($url))
		return null;
	$curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);

    curl_setopt($curl, CURLOPT_TIMEOUT, 10);
    $user_agent = "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:33.0) Gecko/20100101 Firefox/33.0";
    curl_setopt ($curl, CURLOPT_USERAGENT, $user_agent);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);  
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 
    curl_setopt($curl, CURLOPT_HEADER, 1);
    curl_setopt($curl, CURLOPT_NOBODY, 1);
    $str = curl_exec($curl);
    $info = curl_getinfo($curl);
    curl_close($curl);

    // 是否是有效的短网址链接，有效的话必定有redirect_url
    $arr = empty($info['redirect_url']) ? null : parse_url($info['redirect_url']);
    if (empty($arr['query']))
        return null;
    // 解析url参数
    $arr_query = convertUrlQuery($arr['query']);
    // 是否有id参数和account参数
    if (empty($arr_query['id']) || empty($arr_query['Account']))
        return null;
    $temp = [
    	'balls_id' 	=> $arr_query['id'] ,
    	'account' 	=> urldecode($arr_query['Account']) ,
    	'url'		=> $url ,
    ];

    return $temp;
}

function clickById($id = '', $ip, $port){

    $curl = curl_init();

    $url = 'http://cn.battleofballs.com/share?type=1&id=' . $id;

    $header = [
        'CLIENT-IP:127.0.0.1' ,   
        'X-FORWARDED-FOR:'.$ip ,
    ];
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

    curl_setopt($curl, CURLOPT_URL, $url);

    curl_setopt($curl, CURLOPT_TIMEOUT, 10);

    $user_agent = "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:33.0) Gecko/20100101 Firefox/33.0";
    curl_setopt ($curl, CURLOPT_USERAGENT, $user_agent);

    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);  
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    // 设置代理
    curl_setopt($curl,CURLOPT_PROXYTYPE,CURLAUTH_BASIC);
    curl_setopt($curl, CURLOPT_PROXY, $ip);
    curl_setopt($curl, CURLOPT_PROXYPORT, $port);
    curl_setopt($curl, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);

    $str = curl_exec($curl);
    $info = curl_getinfo($curl);
    curl_close($curl);

    return [ 'result' => $str , 'connect_time' => $info['connect_time']];  
}

function getSubstr($str, $leftStr, $rightStr) {
    $left = empty($leftStr) ? 0 : strpos($str, $leftStr);
    $right = empty($rightStr) ? strlen($str) : strpos($str, $rightStr,$left);

    if($left < 0 or $right < $left) return '';

    return substr($str, $left + strlen($leftStr), $right-$left-strlen($leftStr));
}

function getTodayIp($page = 1) {
    // 代理IP抓取地址
    $str = curl('http://www.xicidaili.com/nn/'.$page);
    if (empty($str))
        return null;

    $str = strip_tags($str,"<tbody><tr><td>");
    $str = preg_replace("/<([a-zA-Z]+)[^>]*>/","<\\1>",$str);
    $str = str_replace( ["<br>","\n","\n\r","\r"," ","<tr>"], '', $str);
    
    $ipStrList = explode('</tr>', $str);

    $ipList = [];
    foreach ($ipStrList as $key => $value) {
        $tempStr = str_replace("<td>",'',$value);
        $tempArr = explode('</td>', $tempStr);
        if (empty($tempArr[1]))
            continue;
        $ipList[] = [
            'ip'    => $tempArr[1] , 
            'port'  => $tempArr[2] ,
            'area'  => $tempArr[3] ,
        ];
    }

    return $ipList;
}

function curl($url , $time_out = 10) {
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

function testIp($ip, $port){

    $curl = curl_init();

    $url = 'http://cn.battleofballs.com/';

    curl_setopt($curl, CURLOPT_URL, $url);

    curl_setopt($curl, CURLOPT_TIMEOUT, 4);

    $user_agent = "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:33.0) Gecko/20100101 Firefox/33.0";
    curl_setopt ($curl, CURLOPT_USERAGENT, $user_agent);

    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);  
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    curl_setopt($curl,CURLOPT_PROXYTYPE,CURLAUTH_BASIC);
    curl_setopt($curl, CURLOPT_PROXY, $ip);
    curl_setopt($curl, CURLOPT_PROXYPORT, $port);
    curl_setopt($curl, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);

    $str = curl_exec($curl);
    $info = curl_getinfo($curl);
    curl_close($curl);

    return $info['connect_time'];  
}

function sendEmail($to = '' , $title = '' , $content = '') {
    $mail = new \Email\PHPMailer();
    $mail->IsSMTP(); // 启用SMTP
    $mail->Host=config('mail_host');
    $mail->SMTPAuth = config('mail_smtpauth'); 
    $mail->Username = config('mail_username'); 
    $mail->Password = config('mail_password');
    $mail->From = config('mail_from');
    $mail->FromName = config('mail_fromname'); 
    $mail->AddAddress($to,"管理员");
    $mail->WordWrap = 50; 
    $mail->IsHTML(config('mail_ishtml'));
    $mail->CharSet=config('mail_charset');
    $mail->Subject =$title;
    $mail->Body = $content;
    $mail->AltBody = "这是一个纯文本的身体在非营利的HTML电子邮件客户端";
    return($mail->Send());
}