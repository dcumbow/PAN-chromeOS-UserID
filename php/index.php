<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// ip and port of palo alto syslog agent
$paAgentIP = '#yourPANBoxManagementIP#';
$paAgentPort = 5006;

$logFile = "#your log file location ex. d:\logs\crospaagent\crospaagent-#" . date('Y-m-d', $_SERVER['REQUEST_TIME']) . ".log";
$logLevel = 0; //0=errors, 1=info, 2=debug
$logStamp = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);
$logDelim = "\t";
$logHandle = fopen($logFile, "a");

$err = "";
$extver = "null";
$osver = "null";

function logWrite($entry, $level) {
	global $logStamp, $logFile, $logLevel, $logDelim, $logHandle, $extver, $osver, $err;
	
	if ($logLevel >= $level) {
		fwrite($logHandle, "L=" . $level . $logDelim . "T=" . $logStamp . $logDelim . "X=" . $extver . $logDelim . "C=" . $osver . $logDelim . "E=" . $err . $logDelim . $entry . "\r\n");
	}
}


// derive the client ip address. use the leftmost XFF value if present, and if not, the remote address
if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
	$clientXFFs = explode(",", $_SERVER['HTTP_X_FORWARDED_FOR']);
	$clientIP = $clientXFFs[0];
}
else {
	$clientXFF = "";
	$clientIP = $_SERVER['REMOTE_ADDR'];
}


// will accept data through post or get to this page
if (!empty($_POST['ips'])) {
	$params = $_POST['username'] . ":" . $_POST['ips'] . ":" . $_POST['id'];
	$ips = explode("|", $_POST['ips']);
	$usr = $_POST['username'];
	$id = $_POST['id'];
	$err = isset($_POST['error']) ? $_POST['error'] : '';
	$extver = isset($_POST['extver']) ? $_POST['extver'] : 'Unknown';
	$osver = isset($_POST['osver']) ? $_POST['osver'] : 'Unknown';
	logWrite("POST" . $logDelim . "CLI=" . $clientIP . $logDelim . "PARAMS=" . $params, 2);
}
elseif (!empty($_GET['ips'])) {
	$params = $_GET['username'] . ":" . $_GET['ips'] . ":" . $_GET['id'];
	$ips = explode("|", $_GET['ips']);
	$usr = $_GET['username'];
	$id = $_GET['id'];
	$err = isset($_GET['error']) ? $_GET['error'] : '';
	$extver = isset($_GET['extver']) ? $_GET['extver'] : 'Unknown';
	$osver = isset($_GET['osver']) ? $_GET['osver'] : 'Unknown';
	logWrite("GET" . $logDelim . "CLI=" . $clientIP . $logDelim . "PARAMS=" . $params, 2);
}	
else {
	logWrite("NO_POST_GET" . $logDelim . "CLI=" . $clientIP . $logDelim . "No paramaters found.", 0);
	exit;
}

$err = "CLI=" . $clientIP . $logDelim . "ERR=" . $err;

// build validation id string - ours has been removed. Create whatever logic here to match what you created in the chrome extension for the GetID() function.
// or if you don't care about validation, leave it statically set to true. 
$valID = true;

// test posted id to calculated id and exit if no match. Right above this, you can either leave $valID = true to always accept whatever you're given, or 
// you can create some logic in the chrome extension to create a validation string, pass it in the POST as $id, and match that logic here.
if (!$valID) {
	logWrite("BAD_ID" . $logDelim . "CLI=" . $clientIP . $logDelim . "IPARRAY=" . implode("|", $ips) . $logDelim . "USR=" . $usr . $logDelim . "POSTID=" . $id . $logDelim . "CALCID=" . $valID . $logDelim . "CALCUSR=" . $valUSR . $logDelim . "ID MISMATCH", 0);
	exit;
}

//extract user id from email address
$usra = explode("@", $usr);
$usr = $usra[0];


// Always log if username is equal to chromeuser. This is our way of knowing if a real account is being discovered, or if the extension is failing to obtain 
// the user account and just sending the defaults. We want to see that in logs and figure out the issue.  This user is set by default in the extension.
if (($usr == "chromeuser")) {
	logWrite("NO_USER" . $logDelim . "CLI=" . $clientIP . $logDelim . "IPARRAY=" . implode("|", $ips) . $logDelim . "USR=" . $usr . $logDelim . "POSTID=" . $id . $logDelim . "CALCID=" . $valID . $logDelim . "CALCUSR=" . $valUSR . $logDelim . "NO USER", 0);
}

//connect to PA agent with syslog
$syslogMsg = "UserIPMap;User=" . $usr . ";IP=" . $clientIP . ";";
require_once('syslog.php');
$syslog = new Syslog();
$syslog->SetFacility(23);
$syslog->SetSeverity(7);
$syslog->SetHostname('#ThisWebServerHostname#');
$syslog->SetFqdn('#FQDNofThisWebServer#');
$syslog->SetIpFrom('#ThisIPAddreess#');
$syslog->SetProcess('webautomation');
$syslog->SetContent($syslogMsg);
$syslog->SetServer($paAgentIP);
$syslog->Send();
logWrite("AGENT_WRITE" . $logDelim . "CLI=" . $clientIP . $logDelim . "USR=" . $usr . $logDelim . "MAPPING SENT TO SYSLOG", 1);

fclose($logHandle);

?>