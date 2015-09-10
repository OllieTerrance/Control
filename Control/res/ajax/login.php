<?
// handle remote logins
require_once "../php/common.php";
// no remote login allowed
if (!array_key_exists("password", $config)) return http_response_code(400);
// clear session and logout
if (array_key_exists("logout", $_GET)) {
    session_destroy();
// correct password
} elseif (hash("sha256", $_POST["password"]) === $config["password"]) {
    $_SESSION["login"] = true;
// incorrect
} else return http_response_code(401);
