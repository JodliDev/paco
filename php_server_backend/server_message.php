<?php
error_reporting(0);
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

function strip_input($s) {
	//it should be ok to save userinput "as is" to the filesystem as long as its not used otherwise:
	return str_replace('"', '\'', $s);
}


if($_SERVER['REQUEST_METHOD'] === 'POST') {
	try {
		require('include/hash_fu.php');
		if(!check_user()) {
			echo '{"status":false, "errorMessage":"[{\"msg\":\"Not logged in!\"}]"}';
			exit();
		}
		$rest_json = file_get_contents('php://input');
		$data = json_decode($rest_json, true);
		$id = (int) $data['id'];
		if(!$id)
			echo '{"status":false, "errorMessage":"[{\"msg\":\"Missing id\"}]"}';
		
		if(isset($_GET['set'])) {
			$msg = strip_input($data['msg']);
			if(file_put_contents('data/messages/' .$id, $msg)) {
				echo '{"status":true, "timestamp":"'.filemtime('data/messages/'.$id).'"}';
			}
			else
				echo '{"status":false, "errorMessage":"[{\"msg\":\"message could not be saved\"}]"}';
		}
		else if(isset($_GET['delete'])) {
			if(file_exists('data/messages/' .$id)) {
				if(unlink('data/messages/' .$id))
					echo '{"status":true,"timestamp":"0"}';
				else
					echo '{"status":false, "errorMessage":"[{\"msg\":\"Could not delete file data/messages/' .$id .'\"}]"}';
			}
			else
				echo '{"status":true,"timestamp":"0"}';
		}
		else
			echo '{"status":false, "errorMessage":"[{\"msg\":\"Missing values\"}]"}';
	}
	catch(Exception $e) {
		echo '{"status":false, "errorMessage":"[{\"msg\":\"Internal server error\"}]"}';
	}
}

else if(isset($_GET['get'])) {
	$id = (int) $_GET['id'];
	if(file_exists('data/messages/' .$id))
		echo '{"msg":"' .file_get_contents('data/messages/' .$id) .'","timestamp":"'.filemtime('data/messages/'.$id).'"}';
	else
		echo '{"msg":"","timestamp":"0"}';
	
}

?>
