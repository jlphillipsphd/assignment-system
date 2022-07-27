<?php
// Config options - JHub
$server_url = getenv('JHUB_SERVER_URL');
$internal_url = getenv('JHUB_INTERNAL_URL');
$base_url = getenv('JHUB_BASE_URL');
$service_name = getenv('JHUB_SERVICE_NAME');
$service_token = getenv('JHUB_SERVICE_TOKEN');

// Config options - Course
$course_number = getenv('COURSE_NUMBER');
$course_name = getenv('COURSE_NAME');
$course_admins = explode(':',getenv('COURSE_ADMINS'));

// Propagates from the above configuration
$cookie_name = "jupyterhub-services";
$external_hub_url = $server_url . $base_url . "hub/";
$internal_hub_url = $internal_url . $base_url . "hub/";
$logout_url = $external_hub_url . "logout";
$login_url = $external_hub_url . "login?next=%2F" .
	     str_replace('/','%2F', $base_url) .
	     "services%2F" . $service_name;

?>
