<?php
error_reporting(0);
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
	try {
		$rest_json = file_get_contents('php://input');
		if(!($data = json_decode($rest_json, true)))
			return;
		$headers = apache_request_headers(); //$_SERVER doesnt seem to capture the special Paco-headers
		if(!isset($headers['user_id']) || !isset($headers['http.useragent']) || !isset($headers['paco.version']))
			return;
		if(!($who = (float) $headers['user_id']))
			return;
		
		$appId = strip_input($headers['http.useragent']);
			
		$when = time();
		$pacoVersion = strip_input($headers['paco.version']);

		$output = [];
		$messages = [];
		$count = 0;
		$path_before = 'data/events/inputs/';
		foreach($data as $e) {
			$id = $e['experimentId'];
			$path = $path_before .$id;
			
			if(!file_exists($path)) {
				$output[] = '{"eventId":' .$count .',"status":false,"errorMessage":"Experiment '.$id .' does not exist"}';
				
				++$count;
				continue;
			}
			
			
			
			//*****
			//format resonseTime
			//*****
			if(isset($e['responseTime'])) {
				$date = new DateTime($e['responseTime']);
				$timezone = $date->getTimezone()->getName();
				$responseTime = '"' .$date->format('Y/m/d') .'";"' .$date->format('H:i:s') .'";';
			}
			else
				$responseTime = ';;';
			
			//*****
			//format scheduledTime
			//*****
			if(isset($e['scheduledTime'])) {
				$date = new DateTime($e['scheduledTime']);
				$timezone = $date->getTimezone()->getName();
				$scheduledTime = '"' .$date->format('Y/m/d') .'";"' .$date->format('H:i:s') .'";';
			}
			else
				$scheduledTime = ';;';
				
			
			//*****
			//get responses-array (and check emptyResponse)
			//*****
			$order = $EXPERIMENT_INDEX[$id];
			
			$emptyResponse = 1;
			foreach($e['responses'] as $v) {
				$name = $v['name'];
				if(isset($v['answer']) && strlen($v['answer'])) {
					if($name != 'Form Duration')
						$emptyResponse = 0;
					
					if(file_exists('data/events/media/photo/' .$id .'/' .$name)) {
						$answer = 'data/events/media/photo/' .$id.'/'.$name.'/'.time().'-'.$who.'-'.$count.'.jpg';
						file_put_contents($answer, base64_decode($v['answer']));
					}
					else if(file_exists('data/events/media/audio/' .$id .'/' .$name)) {
						$answer = 'data/events/media/audio/' .$id.'/'.$name.'/'.time().'-'.$who.'-'.$count.'.mp4';
						file_put_contents($answer, base64_decode($v['answer']));
					}
					else
						$answer = $v['answer'];
				}
				else {
					$answer = '';
				}
				$order[$name] = '"' .strip_input($answer) .'"';
			}
			
			
			//*****
			//create output
			//*****
			$write =  '"' .$who .'";"' .$when .'";"' .$appId .'";"' .$pacoVersion .'";'
				.(isset($e['experimentGroupName']) ? '"' .strip_input($e['experimentGroupName']) .'";' : ';') //experimentGroupName;
				.'"' .$timezone .'";'																//timezone;
				.$responseTime .$scheduledTime														//responseTime; scheduledTime;
				//.'"' .((isset($e['scheduledTime']) && !isset($e['responseTime'])) ? 1 : 0) .'";'	//missedSignal;
				.'"' .((isset($e['scheduledTime']) && !count($e['responses'])) ? 1 : 0) .'";'		//missedSignal;
				.'"' .$emptyResponse .'";';															//emptyResponse;
			
			foreach(KEYS_EVENTS as $k) {
				$write .= isset($e[$k]) ? ('"' .strip_input($e[$k]) .'";') : ';';					//experiment-values[];
			}
				
			$write .= implode(';', $order);															//response-array[];
			
			
			//*****
			//write data
			//*****
			if(file_put_contents($path, $write ."\n", FILE_APPEND | LOCK_EX))
				$output[] = '{"eventId":' .$count .',"status":true}';
			else
				$output[] = '{"eventId":' .$count .',"status":false, "errorMessage":"Server-error: Failed to write file"}';
			
			
			//*****
			//server message
			//*****
			if(!isset($messages[$id]) && file_exists('data/messages/' .$id)) {
				$messages[$id] = true;
				$output[] = '{"eventId":"'.$count.'","status":false,"isServerMessage":true,"msgTimestamp":"'.filemtime('data/messages/'.$id).'","errorMessage":"'.file_get_contents('data/messages/'.$id).'"}';
			}
			++$count;
		}
		//echo '[' .implode(',', $output) ',' .implode(',', $messages) .']';
		echo '[' .implode(',', $output) .']';
	}
	catch(Exception $e) {
		echo '{"status":"false","errorMessage":"internal server-error"}';
	}
}

else if(isset($_GET['q'])) {
	$q = $_GET['q']; //This has to change! Why would you ever pass a query in GET..?!
	$id = (int) substr($q, strpos($q, '=')+1, -1);
	if(!$id)
		return;
	
	if(isset($_GET['html']))
		echo '<html><body>No HTML-generation. Please use &quot;Generate CSV&quot;</body></html>';
	else if(isset($_GET['json']))
		echo '{"events": [{"responseTime":"No JSON-generation or web-preview. Please use \"Generate CSV\""}]}';
	else if(isset($_GET['csv'])) {
		
		if(isset($_GET['includePhotos']))
			echo '"' .$id .'&zip=1"'; //hackydiduuuu
		else
			echo $id;
	}
}


?>
