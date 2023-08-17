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

?>
