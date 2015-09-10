<?
// general initialisation for all scripts
session_start();
require_once "config.php";
// detect IP addresses of server and client
$server = $_SERVER["SERVER_ADDR"];
$client = $_SERVER["REMOTE_ADDR"];
// local if accessing by internal hostname or IP
$local = array_key_exists("HTTP_HOST", $_SERVER) && in_array($_SERVER["HTTP_HOST"], $config["hostnames"]);
// remote if logged in with password
$remote = array_key_exists("login", $_SESSION);
$access = $local || $remote;
// system user of PHP process
$user = current(posix_getpwuid(posix_geteuid()));
