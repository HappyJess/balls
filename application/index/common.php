<?php
	
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