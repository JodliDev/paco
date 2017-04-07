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
	$headers = apache_request_headers(); //$_SERVER doesnt seem to capture the special Paco-headers
	if(!isset($headers['user_id']) || !isset($headers['http.useragent']) || !isset($headers['paco.version']))
		return;
	if(!($who = (int) $headers['user_id']))
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
		
		//*****
		//format resonseTime
		//*****
		if(isset($e['responseTime'])) {
			$timezone = explode('+', strip_input($e['responseTime']));
			$date_time = explode(' ', $timezone[0]);
			if(count($timezone) != 2 || count($date_time) != 2)
				return;
			$responseTime = '"' .$date_time[0] .'";"' .$date_time[1] .'";';
		}
		else
			$responseTime = ';;';
		
		//*****
		//format scheduledTime
		//*****
		if(isset($e['scheduledTime'])) {
			$timezone = explode('+', strip_input($e['scheduledTime']));
			$date_time = explode(' ', $timezone[0]);
			if(count($timezone) != 2 || count($date_time) != 2)
				return;
			$scheduledTime = '"' .$date_time[0] .'";"' .$date_time[1] .'";';
		}
		else
			$scheduledTime = ';;';
			
		
		//*****
		//get responses-array (and check emptyResponse)
		//*****
		$order = $EXPERIMENT_INDEX[$id];
		
		$emptyResponse = 1;
		foreach($e['responses'] as $v) {
			if(isset($v['answer'])) {
				$answer = $v['answer'];
				$emptyResponse = 0;
			}
			else
				$answer = '';
			$order[$v['name']] = '"' .strip_input($answer) .'|DEBUG:' .$v['name'] .'"';
		}
		
		
		//*****
		//create output
		//*****
		$write =  '"' .$who .'";"' .$when .'";"' .$appId .'";"' .$pacoVersion .'";'
			.(isset($e['experimentGroupName']) ? '"' .strip_input($e['experimentGroupName']) .'";' : ';') //experimentGroupName;
			.'"+' .$timezone[1] .'";'															//timezone;
			.$responseTime .$scheduledTime														//responseTime; scheduledTime;
			.'"' .((isset($e['scheduledTime']) && !isset($e['responseTime'])) ? 1 : 0) .'";'	//missedSignal;
			.'"' .$emptyResponse .'";';															//emptyResponse;
		
		foreach(KEYS_EVENTS as $k) {
			$write .= isset($e[$k]) ? ('"' .strip_input($e[$k]) .'";') : ';';					//experiment-values[];
		}
			
		$write .= implode(';', $order);															//response-array[];
		
		
		//*****
		//write data
		//*****
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