<?php
require_once 'config.php';

/*
Reference: https://developer-old.byu.edu/docs/consume-api/use-api/oauth-20/oauth-20-php-sample-code
 */

$authorize_url = $server_url . $base_url . "hub/api/oauth2/authorize";
$identify_url = $internal_url . $base_url . "hub/api/user";
$token_url = $internal_url . $base_url . "hub/api/oauth2/token";

$client_id = "service-" . $service_name;
$client_secret = $service_token;

$service_url = $server_url . $base_url . "services/". $service_name . "/";
$callback_uri = $service_url . "auth.php";
$oauth_cookie_name = $service_name . "-oauth-token";

$user_name = null;
if (isset($_COOKIE[$oauth_cookie_name])) {
    $access_token = $_COOKIE[$oauth_cookie_name];
    $user_name = getIdentity($access_token);
}
if (!$user_name) {
    if ($_GET["code"]) {
	$access_token = getAccessToken($_GET["code"]);
	if (isset($_GET["dest"]))
	    $destination = $_GET["dest"];
	else
	    $destination = $service_url;
	setcookie($oauth_cookie_name, $access_token,0,"","",false,true);
	header('Location: ' . $destination);
	die();
    } else {
	getAuthorizationCode();
	die();
    }
}

if (file_exists('../storage/whitelist')) {
    $file = fopen('../storage/whitelist','r');
    $potential_user_name = $user_name;
    $user_name = null;
    while (!feof($file)) {
	$line = trim(fgets($file, 1024));
	if ($line == $potential_user_name) {
	    $user_name = $potential_user_name;
	    break;
	}
    }
    fclose($file);
}

if (!$user_name)
    exit("Forbidden - contact course instructor for access");

function getAuthorizationCode() {
    global $authorize_url, $client_id, $callback_uri;
    $authorization_redirect_url = $authorize_url . "?response_type=code&client_id=" . $client_id . "&redirect_uri=" . $callback_uri;
    header("Location: " . $authorization_redirect_url);
}

function getAccessToken($authorization_code) {
    global $token_url, $client_id, $client_secret, $callback_uri;

    $header = array("Content-Type: application/x-www-form-urlencoded");
    $content = "grant_type=authorization_code&code=$authorization_code&redirect_uri=$callback_uri&client_id=$client_id&client_secret=$client_secret";

    $curl = curl_init();
    curl_setopt_array($curl, array(
	CURLOPT_URL => $token_url,
	CURLOPT_HTTPHEADER => $header,
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_POST => true,
	CURLOPT_POSTFIELDS => $content
    ));
    $response = curl_exec($curl);
    curl_close($curl);

    // CURL failed...
    if ($response === false)
	return null;
	
    // Check for auth error...
    $result = json_decode($response);    
    if (property_exists($result,'error') && $result->error)
	return null;

    // Should be usable access token
    return $result->access_token;
}

function getIdentity($access_token) {
    global $identify_url;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $identify_url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: token $access_token"]);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    // Don't use the next two in production...
    // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $result = json_decode(curl_exec($ch));
    if (!is_null($result) && property_exists($result,'name'))
	$user_name = $result->{'name'};
    else
	$user_name = null;
    curl_close($ch);
    
    return $user_name;
}
?>
