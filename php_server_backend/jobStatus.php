<?php
//error_reporting(0);
set_time_limit(600);
header('Cache-Control: no-cache, must-revalidate');


function files_to_zip(&$zip, $media_path) {
	$h_folder = opendir($media_path);
	while($input_folder = readdir($h_folder)) {
		if($input_folder[0] != '.') {	
			$h_input = opendir($media_path .$input_folder);
			while($file = readdir($h_input)) {
				if($file[0] != '.') {	
					echo $media_path .$input_folder .'/' .$file;
					$zip->addFile($media_path .$input_folder .'/' .$file, 'photo/'.$input_folder.'/'.$file);
				}
			}
		}
	}
}

if(!isset($_GET['jobId']) || !($id = (int) $_GET['jobId'])) {
	echo 'Internal error: No experiment-id';
	return;
}

$path = 'data/events/datafiles/' .$id;


if(function_exists('exec')) {
	if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
		pclose(popen('start /B type data\\events\\keys\\'.$id.' data\\events\\inputs\\'.$id.' > data\\events\\datafiles\\'.$id.'.csv', 'r')); 
	else //Unix
		exec('cat data/events/keys/'.$id.' data/events/inputs/'.$id.' > '.$path.'.csv &');
	
	for($i=600; !file_exists($path); --$i) {
		if(!$i) {//depending on server-configuration, php-execution-timout will most likely fire before that
			echo 'Internal error: Timeout';
			exit();
		}
		sleep(1);
	}
}
else {//no permission for external commands
	$output = file_get_contents('data/events/keys/' .$id) .file_get_contents('data/events/inputs/' .$id);
	$h = fopen($path.'.csv', 'w');
	flock($h, LOCK_EX);
	fwrite($h, $output);
	flock($h, LOCK_UN);
	fclose($h);
	echo $output;
}


//TODO: FInd a way to output zips to the web
$photo_exists = file_exists('data/events/media/photo/' .$id);
$audio_exists = file_exists('data/events/media/photo/' .$id);

if($photo_exists || $audio_exists) {
	$zip = new ZipArchive();

	if (!$zip->open($path.'.zip', ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE)) {
		echo 'Failed to create archive';
		exit('Failed to create archive');
	}


	if($photo_exists)
		files_to_zip($zip, 'data/events/media/photo/' .$id .'/');
	if($audio_exists)
		files_to_zip($zip, 'data/events/media/audio/' .$id .'/');


	if (!$zip->status == ZIPARCHIVE::ER_OK) {
		echo 'Failed to write files to zip';
		exit('Failed to write files to zip');
	}

	$zip->close();
}


require($path);
?>