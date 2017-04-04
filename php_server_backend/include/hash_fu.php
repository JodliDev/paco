<?php
//error_reporting(0);
function check_local() {
	return $_SERVER['REMOTE_ADDR'] == '127.0.0.1' || $_SERVER['REMOTE_ADDR'] == '::1';
}
function hash_pass($s) {
	return md5(base64_encode(md5(base64_decode($s) .'RaNd0M sTrInG'))); //just doing some random stuff to circumvent rainbow-tables
}
function check_user() {
	$cookie_user = $_COOKIE['user'];
	$cookie_pass = hash_pass($_COOKIE['pass']);

	$user = null;
	if(!file_exists('data/logins'))
		return null;
	$h = fopen('data/logins', 'r');
	while(!feof($h)) {
		$line = substr(fgets($h), 0, -1);
		if($line == '')
			continue;
		$data = explode(':', $line);
		if($data[0] == $cookie_user && $data[1] == $cookie_pass) {
			$user = $data[0];
			break;
		}
	}
	fclose($h);
	return $user;
}


?>