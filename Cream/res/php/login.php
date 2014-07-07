<?
// handle remote logins
require_once "common.php";
require_once "/var/res/php/keystore.php";
session_start();
// clear session and logout
if (array_key_exists("logout", $_GET)) {
    session_destroy();
// correct password
} elseif (sha1($_POST["password"]) === keystore("cream", "login")) {
    $_SESSION["login"] = true;
// incorrect
} else return http_response_code(401);
