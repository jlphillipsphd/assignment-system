<?php
require_once 'config.php';

// Auth setup
$headers = apache_request_headers();
date_default_timezone_set('America/Chicago');
if (!isset($_COOKIE[$cookie_name])) {
    $user_name = null;
    $string = '<script type="text/javascript">';
    $string .= 'window.location = "' . $login_url . '"';
    $string .= '</script>';
    echo $string;
}
else {
    $url = $internal_hub_url . "api/authorizations/cookie/" .  $cookie_name . "/" . str_replace('"', "", $_COOKIE[$cookie_name]);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: token $service_token"]);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    // Don't use the next two in production...
    // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $result = json_decode(curl_exec($ch));
    if (!is_null($result) && property_exists($result,'name')) {
	$user_name = $result->{'name'};
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
	}
    }
    else {
	$user_name = null;
    }
    curl_close($ch);
    // DEBUG ONLY
    /* echo "HEADERS<br/>\n";
     *  foreach ($headers as $key => $value) {
     *    echo "$key : $value<br/>\n";
     * }
     * echo "COOKIE<br/>\n";
     * foreach ($result as $key => $value) {
     *    echo "$key : $value<br/>\n";
     * } */
}
if (!$user_name) {
    exit("Forbidden");
}
?>
