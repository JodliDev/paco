<?php
//error_reporting(0);
require('include/hash_fu.php');

if(isset($_GET['logout'])) {
	setcookie("user", '', time()-3600);
	setcookie("pass", '', time()-3600);
	header('Location: /');
}

?>


<html>
<head>


<script language="javascript" type="text/javascript" >
	
function forward(url) {
	window.location = url;
}
function cookie_string() {
	return ["user="+document.getElementById("user").value.toLowerCase(), "pass=" + btoa(document.getElementById("pass").value)];
}

function send(type, data, finish) {
	var http = new XMLHttpRequest();
    http.onreadystatechange = function(e) { 
        if (this.readyState != 4 || this.status != 200)
			return;
		finish(JSON.parse(this.responseText));
    }
    
    
    http.open(type, "/userinfo", true);
    if(type == "POST")
		http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	
    http.send(data);
}
	
function check_login() {
	var cookie = cookie_string();
	document.cookie = cookie[0] + "; expires="+(new Date(Date.now()+31536000000))+";";
	document.cookie = cookie[1] + "; expires="+(new Date(Date.now()+31536000000))+";";
	
	send("GET", null, function(data) {
			if(data.user)
				forward("/");
			else
				document.getElementById("message").innerText = "Login failed";
		});
}

function create_login() {
	send("POST", "create&"+ cookie_string().join("&"), function(data) {
			document.getElementById("message").innerText = data.message;
		});
}
	
</script>

</head>
<body>
	<div style="width:300px; position:absolute; left:50%; top:30%; margin-left:-150px;">
		<div style="width:49%; float:left; color:grey; font-size:10pt; text-align:center;">Email:</div>
		<div style="width:49%; float:right; color:grey; font-size:10pt; text-align:center;">password:</div>
		<form onsubmit="return false;">
			<input name="user" type="text" value="" id="user" style="width:49%; float:left;"/>
			<input name="pass" type="password" value="" id="pass" style="width:49%; float:right;"/>
			<?php
				if(check_local())
					echo '<input type="button" value="create" onclick="create_login()" style="float:left;"/>';
			?>
			<input type="submit" value="login" onclick="check_login()" style="float:right;"/>
		</form>
	</div>
	<div id="message" style="color:red"></div>

</body>

</html>
