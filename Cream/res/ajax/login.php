<?
// handle remote logins
require_once "../php/common.php";
require_once getenv("PHPLIB") . "keystore.php";
session_start();
// clear session and logout
if (array_key_exists("logout", $_GET)) {
    session_destroy();
// correct password
} elseif (sha1($_POST["password"]) === keystore("cream", "login")) {
    $_SESSION["login"] = true;
// incorrect
} else return http_response_code(401);
