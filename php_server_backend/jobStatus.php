<?php
set_time_limit(600);
header('Cache-Control: no-cache, must-revalidate');

if(!isset($_GET['jobId']) || !($id = (int) $_GET['jobId'])) {
	echo 'Internal error: No experiment-id';
	return;
}

$path = 'data/events/datafiles/' .$id;


exec('cat data/events/keys/'.$id.' data/events/inputs/'.$id.' > data/events/datafiles/'.$id, $output, $error); //Unix
if($error) {
	exec('type data/events/keys/'.$id.' data/events/inputs/'.$id.' > data/events/datafiles/'.$id, $output, $error); //Windows
	if($error) {//no permission for shell-calls
		$h = fopen('data/events/datafiles/'.$id, 'w');
		flock($h, LOCK_EX);
		fwrite($h, file_get_contents('data/events/keys/' .$id) .file_get_contents('data/events/inputs/' .$id));
		flock($h, LOCK_UN);
		fclose($h);
		//readfile('data/events/keys/' .$id);
		//readfile('data/events/inputs/' .$id);
		exit();
	}
}

for($i=600; !file_exists($path); --$i) {
	if(!$i) {//depending on server-configuration, php-execution-timout will most likely fire before that
		echo 'Internal error: Timeout';
		exit();
	}
	sleep(1);
}
require($path);
?>