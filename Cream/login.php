<?
require_once("/var/res/php/keystore.php");
session_start();
if (array_key_exists("logout", $_GET)) {
    session_destroy();
} elseif (sha1($_POST["password"]) === keystore("cream", "login")) {
    $_SESSION["login"] = true;
} else {
    http_response_code(401);
}
