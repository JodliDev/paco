<?php
//error_reporting(0);
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

if(!file_exists('data/experiment_index/merged'))
	return;
require('include/keys_events.php');
$EXPERIMENT_INDEX = json_decode(file_get_contents('data/experiment_index/merged'), true);

function check_user($who) {
	return preg_match('/^\d*\.\d*$/', $who);
}

function strip_input($s) {
	//it should be ok to save userinput "as is" to the filesystem as long as its not used otherwise:
	return str_replace('"', '\'', $s);
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
	$rest_json = file_get_contents('php://input');
	if(!($data = json_decode($rest_json, true)))
		return;
	
	$headers = apache_request_headers();
	if(!isset($headers['user_id']) || !isset($headers['http.useragent']) || !isset($headers['paco.version']))
		return;
	$who = strip_input($headers['user_id']);
	if(!check_user($who))
		return;
	
	$appId = strip_input($headers['http.useragent']);
		
	$when = time();
	$pacoVersion = strip_input($headers['paco.version']);

	$output = [];
	$count = 0;
	$path_before = 'data/events/inputs/';
	foreach($data as $e) {
		$id = $e['experimentId'];
		$path = $path_before .$id;
		
		if(!file_exists($path)) {
			$output[] = '{"eventId":' .$count .',"status":false,"errorMessage":"Experiment does not exist"}';
			
			++$count;
			continue;
		}
		
		$write =  '"' .$who .'";"' .$when .'";"' .$appId .'";"' .$pacoVersion .'";';
		$group_name = isset($e['experimentGroupName']) ? $e['experimentGroupName'] : '';
		$write .= '"' .strip_input($group_name) .'";';
		
		foreach(KEYS_EVENTS as $k) {
			$write .= isset($e[$k]) ? ('"' .strip_input($e[$k]) .'";') : ';';
		}
		
		$order = $EXPERIMENT_INDEX[$id];
		
		foreach($e['responses'] as $v) {
			//if(substr($v['name'], 0, 5) == 'input')
				//$order[$group_name .$v['name']] = '"' .strip_input($v['answer']) .'"';
			//else
			$answer = isset($v['answer']) ? $v['answer'] : '';
			$order[$v['name']] = '"' .strip_input($answer) .'|DEBUG:' .$v['name'] .'"';
		}
		$write .= implode(';', $order);
		
		
		if(($h = fopen($path, 'a'))
				&& flock($h, LOCK_EX)
				&& fwrite($h, $write ."\n")
				&& flock($h, LOCK_UN)
				&& fclose($h)
			)
			$output[] = '{"eventId":' .$count .',"status":true}';
		else
			$output[] = '{"eventId":' .$count .',"status":false}';
		++$count;
	}
	echo '[' .implode(',', $output) .']';
}

else if(isset($_GET['q'])) {
	$q = $_GET['q']; //This has to change! Why would you ever pass a query in GET..?
	$id = (int) substr($q, strpos($q, '=')+1, -1);
	if(!$id)
		return;
	
	if(isset($_GET['html']))
		echo '<html><body>No HTML-generating. Please use &quot;Generate CSV&quot;</body></html>';
	else if(isset($_GET['json']))
		echo '{"events": [{"responseTime":"No JSON-generating or web-preview. Please use \"Generate CSV\""}]}';
	else if(isset($_GET['csv']))
		echo $id;
}


?>