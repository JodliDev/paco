<?php
//error_reporting(0);
header('Cache-Control: no-cache, must-revalidate');
header('Content-Type: application/json');

require('include/hash_fu.php');

if(isset($_POST['create'])) {
	if(check_local()) {
		echo '{"message":"New users can only be created from localhost",';
	}
	else if(!isset($_POST['user']) || strlen($_POST['user'])<2 || !isset($_POST['pass']) || strlen($_POST['pass'])<2) {
		echo '{"message":"Variable-error!",';
	}
	else {
		$user = $_POST['user'];
		$pass = hash_pass($_POST['pass']); //just doing some random stuff to circumvent rainbow-tables
		
		$h = fopen('data/logins', 'a');
		fwrite($h, $user .':' .$pass ."\n");
		fclose($h);
		echo'{"user":"' .$user .'","message":"Login saved!\nIf you want to remove a login, you have to remove the according line in the \"data/logins\"-file",'; 
	}
}
else if(isset($_COOKIE['user']) && isset($_COOKIE['pass'])) {
	$user = check_user();
	echo $user ? '{"user":"' .$user .'",' : '{';
}
else
	echo '{';
?>"login":"change_login", "logout":"change_login?logout"}