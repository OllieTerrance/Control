<?
// include this file to require checking access before returning data
session_start();
// local if accessing by internal hostname or IP
$local = in_array($_SERVER["HTTP_HOST"], array("cream", "192.168.1.100"));
// remote if logged in with password
$remote = array_key_exists("login", $_SESSION);
// terminate if not allowed to access
if (!$local && !$remote) {
    http_response_code(403);
    die();
}
