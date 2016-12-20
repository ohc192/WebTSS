<?php
require_once('functions.php');
$ismac = contains("darwin", php_uname('s'));
$islinux = contains("linux", php_uname('s'));
    
$aGlobalConfig = array
(
    "tssbinary" => $ismac ? "tsschecker_latest_macos" : "tsschecker_latest_linux",
	"interface" => array
	(
		"appURL" => "http://virulent.pw/webtss", // Full url to WebTSS, no trailing slash or space.
		"theme" => "default" // Bootstrap theme for interfaces. Themes are in /css with the naming scheme "*.bootstrap.min.css".
	),
	"cron" => array
	(
		"enableWebAccess" => True // Allow cron triggering through the url.
	),
	"recaptcha" => array
	(
		// Get it from here: https://www.google.com/recaptcha/intro/index.html
		"enabled" => False,
		"siteKey" => "",
		"secretKey" => ""
	),
	"database" => array
	(
		"host" => "127.0.0.1", // use an actual IP address instead of localhost to stop it from using a socket
		"port" => "3306",
		"username" => "webtss",
		"password" => "webtss",
		"database" => "webtss"
	)
);





// Configuration pre-processing.
// Don't touch this.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if(!file_exists(dirname(__FILE__).'/css/'.$aGlobalConfig['interface']['theme'].'.bootstrap.min.css')) { die("The theme '".$aGlobalConfig['interface']['theme']."' does not exist."); }
?>
