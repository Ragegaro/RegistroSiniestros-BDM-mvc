<?php
$basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
define('urlsite', 'http://' . $_SERVER['HTTP_HOST'] . ($basePath ? $basePath : '') . '/');
define('DB_NAME', 'bdm_aseguradora');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_HOST', 'localhost');
define('DB_HOSTM', 'host=127.0.0.1;port=3307');