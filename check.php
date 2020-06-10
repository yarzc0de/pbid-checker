<?php
header("Content-Type: application/json");
if(isset($_GET['id']) AND isset($_GET['pass'])) {
	echo check(trim($_GET['id']), trim($_GET['pass']));
} else {
	echo json_encode(array("error" => true, "msg" => "Wrong format"), JSON_PRETTY_PRINT);
}

function check($user, $pass)
{
    $headers = array();
    $headers[] = "Connection: keep-alive";
    $headers[] = "Cache-Control: max-age=0";
    $headers[] = "Origin: https://www.pointblank.id";
    $headers[] = "Upgrade-Insecure-Requests: 1";
    $headers[] = "Content-Type: application/x-www-form-urlencoded";
    $headers[] = "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36";
    $headers[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8";
    $headers[] = "Referer: https://www.pointblank.id/login/form";
    $headers[] = "Accept-Encoding: gzip, deflate, br";
    $headers[] = "Accept-Language: en-US,en;q=0.9";
	$cek = yarzcurl('https://www.pointblank.id/login/process', 'loginFail=0&userid='.$user.'&password='.$pass, false, $headers, false, true);
	if(strpos($cek[1],'document.location.replace("/login/form")'))
	{
		return json_encode(array("error" => true, "msg" => "Wrong id/password"), JSON_PRETTY_PRINT);
	} else {
		$kuki = '';
		preg_match_all('/Set-Cookie: (.*?);/', $cek[0], $cookies);
		foreach($cookies[1] as $cookie)
		{
			$kuki .= $cookie."; ";
		}
		$head = array();
		$head[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8';
		$head[] = 'Accept-Encoding: gzip, deflate, br';
		$head[] = 'Accept-Language: en-US,en;q=0.9';
		$head[] = 'Connection: keep-alive';
		$head[] = 'Cookie: '.$kuki;
		$head[] = 'Host: pointblank.id';
		$head[] = 'Referer: https://pointblank.id/';
		$head[] = 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/72.0.3626.119 Safari/537.36';
		// Try to get rank's
		$ranks = yarzcurl('https://pointblank.id/ranking/individual/list', false, false, $head, false, true);
		preg_match('/<p class="tit"><a href="(.*?)">/', $ranks[1], $tier);
		$tier = str_replace(array("javascript:getDetail",";","(",")","'"), array("","","","",""), $tier[1]);
		list($id,$ids,$nick) = explode(",", trim($tier));
		$li = explode('<li class="rank">', $ranks[1]);
		$li = explode('</li>', $li[1]);
		preg_match('/<img src="(.*?)" alt="Rank icon">/', $li[0], $reng);
		$reng = str_replace($reng[0], "", $li[0]);
		$reng = trim($reng) ? trim($reng) : "No Detected";
		preg_match('/<li class="exp"><span class="stit">EXP<\/span>(.*?)<\/li>/', $ranks[1], $exp);
		$exp = trim($exp[1]) ? trim($exp[1]) : "No Detected";

		$result = array();
		$result['username'] = $user;
		$result['password'] = $pass;
		$result['data'] = array();
		$result['data']['nickname'] = trim($nick) ? trim($nick) : "No Detected";
		$result['data']['rank'] = $reng;
		$result['data']['exp'] = $exp;
		return json_encode($result, JSON_PRETTY_PRINT);
	}
}

function yarzcurl($url, $fields=false, $cookie=false, $httpheader=false, $proxy=false, $encoding=false, $timeout=false)
    { 
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_HEADER, 1);
	if($fields !== false)
	{ 
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
	}
	if($encoding !== false)
	{ 
		curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
	}
	if($cookie !== false)
	{ 
		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
	}
	if($httpheader !== false)
	{ 
		curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
	}
	if($proxy !== false)
	{ 
		curl_setopt($ch, CURLOPT_PROXY, $proxy);
		curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
	}
	if($timeout !== false)
	{ 
       curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0); 
       curl_setopt($ch, CURLOPT_TIMEOUT, 6); //timeout in seconds		
	}
	$response = curl_exec($ch);
	$header = substr($response, 0, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
	$body = substr($response, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
	curl_close($ch);
	return array($header, $body);
}