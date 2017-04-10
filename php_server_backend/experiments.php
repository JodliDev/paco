<?php
//error_reporting(0);
header('Content-Type: application/json;charset=UTF-8');
header('Cache-Control: no-cache, must-revalidate');
require('include/keys_events.php');
require('include/keys_results.php');


function update_index() {
	$path = 'data/experiment_index/';
	$h_folder = opendir($path);
	$output = '';
	
	while($file = readdir($h_folder)) {
		if($file != '..' && $file != '.' && $file != 'merged') {
			$filename = $path .$file;
			$output .= file_get_contents($filename);
		}
	}
	closedir($h_folder);
		
	write_file('data/experiment_index/merged', '{' .substr($output, 0, -1) .'}');
}

function write_file($file, $s) {
	if(!($h = fopen($file, 'w'))
			|| !flock($h, LOCK_EX)
			|| fwrite($h, $s) === false
			|| !flock($h, LOCK_UN)
			|| !fclose($h)) {
		error('Writing the file \'' .$file .'\' failed');
	}
}

function unlink_others($a, $id) {
	foreach($a as $path) {
		if(file_exists('data/experiments/' .$path .'/' .$id))
			unlink('data/experiments/' .$path .'/' .$id);
	}
}

function error() {
	$args = func_get_args();
	$output = '[{"eventId":0,"status":false,"experimentId":0, "errorMessage":"[';
	foreach($args as $msg) {
		$output .= '{\"msg\":\"' .$msg .'\"},';
	}
	echo substr($output, 0, -1) .']"}]';
	exit();
}

function remove_access_keys($key_index, $id) {
	foreach($key_index as $key => $key_list) {
		if(($key_list_id = array_search($id, $key_list)) !== false) {
			unset($key_index[$key][$key_list_id]);
		}
		if(!count($key_index[$key]))
			unset($key_index[$key]);
	}
	write_file('data/experiments/key_restricted/index', json_encode($key_index));
	return $key_index;
}


if($_SERVER['REQUEST_METHOD'] === 'POST') {
	require('include/hash_fu.php');
	if(!check_user())
		return;
	
	if(isset($_GET['delete'])) {
		if(!isset($_GET['id']) || !($id = (int) $_GET['id']))
			return;
		
		if(!unlink('data/experiment_index/' .$id))
			return error('Could not remove data/experiment_index/' .$id, 'No files were deleted', 'Aborting...');
		if(file_exists('data/experiments/key_restricted/' .$id) && !unlink('data/experiments/key_restricted/' .$id))
			return error('Could not remove data/experiments/key_restricted/' .$id, 'experiment_index-file was deleted','Save the experiment again if you wish to restore it', 'Aborting...');
		else if(file_exists('data/experiments/published/' .$id) && !unlink('data/experiments/published/' .$id))
			return error('Could not remove data/experiments/published/' .$id, 'experiment_index-file was deleted','Save the experiment again if you wish to restore it', 'Aborting...');
		else if(file_exists('data/experiments/unpublished/' .$id) && !unlink('data/experiments/unpublished/' .$id))
			return error('Could not remove data/experiments/unpublished/' .$id, 'experiment_index-file was deleted','Save the experiment again if you wish to restore it', 'Aborting...');
		
		if(!unlink('data/events/keys/' .$id))
			return error('Could not remove data/events/keys/' .$id, 'But the experiment was sucessfully removed', 'Aborting...');
		update_index();
		
		if(!unlink('data/events/inputs/' .$id))
			return error('Could not remove data/events/inputs/' .$id, 'But the experiment was sucessfully removed', 'Aborting...');
		
		echo '[{"eventId":0,"status":true}]';
		return;
	}
	else {
		$rest_json = file_get_contents('php://input');
		
		
		if(!($data = json_decode($rest_json, true)))
			return;
		
		if(isset($data['id']))
			$id = $data['id'];
		else
			$id = $data['id'] = time();
		
		$data['version'] = isset($data['version']) ? $data['version']+1 : 1;
		
		
		//*****
		//create variable-keys
		//*****
		$t_inputs = '"who";"when";"appId";"pacoVersion";"experimentGroupName";timezone;responseDate;responseTime;scheduledDate;scheduledTime;missedSignal;emptyResponse;';
		foreach(KEYS_EVENTS as $k) {
			$t_inputs .= '"' .$k .'";';
		}
		
		//$t_index = '';
		$exp_index = [];
		foreach(KEYS_RESULTS as $k) {
			$t_inputs .= '"' .$k .'";';
			//$t_index .= '\'' .$k .'\'=>null,';
			$exp_index[$k] = null;
		}
		
		
		//$i = $max_i = 0;
		//foreach($data['groups'] as $group) {
			//$max_i = max(count($group['inputs']), $max_i);
		//}
		
		//for($i=1; $i<=$max_i; ++$i) {
			//$t_index .= '\'input' .$i .'\'=>null,';
			//$t_inputs .= '"input' .$i .'",';
		//}
		
		//$key_check_array = [];
		//foreach($data['groups'] as $group) {
			//$group_name = $group['name'];
			//if(isset($key_check_array[$group_name]))
				//error('Group name "'.$group_name .'" is assigned more than once');
			//else
				//$key_check_array[$group_name] = true;
			
			//$count = 1;
			//foreach($group['inputs'] as $input) {
				//$name = $input['name'];
				//$t_index .= '\'' .$group_name .'input' .$count .'\'=>null,';
				//$t_inputs .= '"' .$group_name .': ' .$name .'",';
				
				//++$count;
			//}
		//}
		
		
		$key_check_array = [];
		foreach($data['groups'] as $i => &$group) {
			$group_name = $group['name'];
			foreach($group['inputs'] as $input) {
				$name = $input['name'];
				if(isset($key_check_array[$name]))
					error('Variable-Name exists more than once: ' .$name, 'First detected in group: ' .$key_check_array[$input['name']], 'Detected again in group: ' .$group_name);
				else if(in_array($name, KEYS_RESULTS))
					error('Protected Variable-Name: ' .$name, 'Please choose another Variable-Name', 'Detected in group: ' .$group_name);
				else
					$key_check_array[$name] = $group_name;
				$t_inputs .= '"' .$name .'";';
				//$t_index .= '\'' .$name .'\'=>null,';
				$exp_index[$name] = null;
				
				if($input['responseType'] == 'photo') {
					if(!file_exists('data/events/media/photo/' .$id))
						mkdir('data/events/media/photo/' .$id, 0755);
					if(!file_exists('data/events/media/photo/' .$id .'/' .$name))
						mkdir('data/events/media/photo/' .$id .'/' .$name, 0755);
				}
				else if($input['responseType'] == 'audio') {
					if(!file_exists('data/events/media/audio/' .$id))
						mkdir('data/events/media/audio/' .$id, 0755);
					if(!file_exists('data/events/media/audio/' .$id .'/' .$name))
						mkdir('data/events/media/audio/' .$id .'/' .$name, 0755);
				}
			}
			
			
			//adding ids
			$set_id = time();
			if(isset($group['actionTriggers'])) {
				foreach($group['actionTriggers'] as &$trigger) {
					if(!isset($trigger['id']))
						$trigger['id'] = $set_id++;
						//$data['groups'][$i]['actionTriggers']['id'] = $id++;
					if(isset($trigger['actions'])) {
						foreach($trigger['actions'] as &$action) {
							if(!isset($action['id']))
								$action['id'] = $set_id++;
						}
					}
					if(isset($trigger['schedules'])) {
						foreach($trigger['schedules'] as &$schedule) {
							if(!isset($schedule['id']))
								$schedule['id'] = $set_id++;
						}
					}
					if(isset($trigger['cues'])) {
						foreach($trigger['cues'] as &$schedule) {
							if(!isset($schedule['id']))
								$schedule['id'] = $set_id++;
						}
					}
				}
			}
		}
		
		
		
		//*****
		//save experiment
		//*****
		
		$key_index = json_decode(file_get_contents('data/experiments/key_restricted/index'), true);
		if(count($data['accessKeys'])) {
			$data['published'] = false;
			$key_index = remove_access_keys($key_index, $id);
			
			foreach($data['accessKeys'] as $key) {
				if(!isset($key_index[$key]))
					$key_index[$key] = [$id];
				else if(!in_array($id, $key_index[$key]))
					array_push($key_index[$key], $id);
			}
			write_file('data/experiments/key_restricted/index', json_encode($key_index));
			
			write_file('data/experiments/key_restricted/' .$id, json_encode($data));
			if(file_exists('data/experiments/published/' .$id))
				unlink('data/experiments/published/' .$id);
			if(file_exists('data/experiments/unpublished/' .$id))
				unlink('data/experiments/unpublished/' .$id);
		}
		else {
			if(file_exists('data/experiments/key_restricted/' .$id)) {
				unlink('data/experiments/key_restricted/' .$id);
				remove_access_keys($key_index, $id);
			}
			if($data['published']) {
				write_file('data/experiments/published/' .$id, json_encode($data));
				if(file_exists('data/experiments/unpublished/' .$id))
					unlink('data/experiments/unpublished/' .$id);
			}
			else {
				write_file('data/experiments/unpublished/' .$id, json_encode($data));
				if(file_exists('data/experiments/published/' .$id))
					unlink('data/experiments/published/' .$id);
			}
		}
		write_file('data/events/keys/' .$id, substr($t_inputs, 0, -1)."\n");
		//write_file('data/experiment_index/' .$id, '\'' .$id .'\'=>[' .substr($t_index, 0, -1) .'],');
		write_file('data/experiment_index/' .$id, '"' .$id .'":' .json_encode($exp_index) .',');
		if(!file_exists('data/events/inputs/' .$id))
			write_file('data/events/inputs/' .$id, '');
		update_index();
		
		echo '[{"eventId":0,"status":true,"experimentId":' .$id .'}]';
	}
}
else if(isset($_GET['id']) && ($id = (int) $_GET['id'])) {
	$filename = './data/experiments/published/' .$id;
	$filename_hidden = './data/experiments/unpublished/' .$id;
	$filename_restricted = './data/experiments/key_restricted/' .$id;
	if(file_exists($filename))
		echo '{"results":[' .file_get_contents($filename) .']}';
	else if(file_exists($filename_hidden))
		echo '{"results":[' .file_get_contents($filename_hidden) .']}';
	else if(file_exists($filename_restricted))
		echo '{"results":[' .file_get_contents($filename_restricted) .']}';
	
}
else if(isset($_GET['public']) || isset($_GET['admin'])) {
	$headers = apache_request_headers();
	
	$output = [];
	
	$path = 'data/experiments/published/';
	$h_folder = opendir($path);
	while($file = readdir($h_folder)) {
		if($file[0] != '.') {	
			$output[] = file_get_contents($path .$file);
		}
	}
	closedir($h_folder);
	
	
	if(isset($headers['access_key'])) {
		$key = $headers['access_key'];
		$key_index = json_decode(file_get_contents('data/experiments/key_restricted/index'), true);
		if(isset($key_index[$key])) {
			$ids = $key_index[$key];
			
			$path = 'data/experiments/key_restricted/';
			foreach($ids as $id) {
				$output[] = file_get_contents($path .$id);
			}
		}
	}
	else if(!isset($headers['http.useragent'])) { //admin
		$path = 'data/experiments/unpublished/';
		$h_folder = opendir($path);
		while($file = readdir($h_folder)) {
			if($file[0] != '.') {	
				$output[] = file_get_contents($path .$file);
			}
		}
		closedir($h_folder);
		
		
		$path = 'data/experiments/key_restricted/';
		$h_folder = opendir($path);
		while($file = readdir($h_folder)) {
			if($file[0] != '.' && $file != 'index') {	
				$output[] = file_get_contents($path .$file);
			}
		}
		closedir($h_folder);
	}
	
	echo '{"results":[' .implode($output, ',') .']}';
}
else //new, popular, mine, joined, new
	echo '{"results":[]}';



?>
