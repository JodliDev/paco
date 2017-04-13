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

$path = 'data/events/generated/' .$id;

$last_change = filemtime('data/events/inputs/'.$id); //if experiment exists, this file hast to exist too
$last_export = file_exists($path .'.csv') ? filemtime($path.'.csv') : 0;


//*****
//Zip media
//(lets do it before te csv because it can be disabled)
//*****
if(isset($_GET['zip'])) {
	$photo_exists = file_exists('data/events/media/photo/' .$id);
	$audio_exists = file_exists('data/events/media/photo/' .$id);

	if($photo_exists || $audio_exists) {
		$last_zip_export = file_exists($path .'.zip') ? filemtime($path.'.zip') : 0;
		if($last_zip_export < $last_change) {
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
	}
}


//*****
//create csv
//*****
if($last_export > $last_change) {
	require($path .'.csv');
	return;
}

if(function_exists('exec')) {
	if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
		pclose(popen('start /B type data\\events\\keys\\'.$id.' data\\events\\inputs\\'.$id.' > data\\events\\generated\\'.$id.'.csv', 'r')); 
	else //Unix
		exec('cat data/events/keys/'.$id.' data/events/inputs/'.$id.' > '.$path.'.csv &');
	
	for($i=600; !file_exists($path.'.csv'); --$i) {
		if(!$i) {//depending on server-configuration, php-execution-timout will most likely fire before that
			echo 'Internal error: Timeout';
			exit();
		}
		sleep(1);
	}
	require($path .'.csv');
}
else {//no permission for external commands
	$output = file_get_contents('data/events/keys/' .$id) .file_get_contents('data/events/inputs/' .$id);
	file_put_contents($path.'.csv', $output, LOCK_EX);
	echo $output;
}
?>