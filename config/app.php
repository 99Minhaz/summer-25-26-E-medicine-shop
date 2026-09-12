<?php

date_default_timezone_set('Asia/Dhaka');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$app_name = "E-Medical Shop";

define("APP_NAME", $app_name);
define("UPLOAD_MAX_BYTES", 2 * 1024 * 1024);
