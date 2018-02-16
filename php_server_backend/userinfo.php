<?php
error_reporting(0);
try {
	header('Cache-Control: no-cache, must-revalidate');
	header('Content-Type: application/json');

	require('include/hash_fu.php');

	if(isset($_POST['create'])) {
		if(!check_local()) {
			echo '{"message":"New users can only be created from localhost",';
		}
		else if(!isset($_POST['user']) || strlen($_POST['user'])<2 || !isset($_POST['pass']) || strlen($_POST['pass'])<2) {
			echo '{"message":"Variable-error!",';
		}
		else {
			$user = $_POST['user'];
			$pass = hash_pass($_POST['pass']);
			
			
			
			if(!file_exists('.logins') && !file_put_contents('data/.htaccess', 'Options +Indexes
AuthType Basic
AuthName "Password Protected Area"
AuthUserFile '.dirname($_SERVER['SCRIPT_FILENAME']).'/data/.logins
Require valid-user')) {
				echo '{"message":"write-error. Login-File could not be created!",';
			}
			else if(!file_put_contents('data/.logins', $user .':' .$pass ."\n", FILE_APPEND))
				echo '{"message":"write-error. Login-data could not be saved!",';
			else
				echo'{"user":"' .$user .'","message":"Login saved!\nIf you want to remove a login, you have to remove the according line in the \"data/logins\"-file",'; 
		}
	}
	else if(isset($_COOKIE['user']) && isset($_COOKIE['pass'])) {
		$user = check_user();
		echo $user ? '{"user":"' .$user .'",' : '{';
	}
	else
		echo '{';
}
catch(Exception $e) {
	echo '{"message":"Internal error!",';
}
?>"login":"change_login", "logout":"change_login?logout"}
