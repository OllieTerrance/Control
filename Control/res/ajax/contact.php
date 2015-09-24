<?
// store messages sent from the public page
require_once "../php/common.php";
// disabled if messages path not set
if (!array_key_exists("messages", $config)) return http_response_code(401);
// ignore empty comments
if (!$_POST["comments"]) return http_response_code(400);
$name = $_POST["name"];
$email = $_POST["email"];
$sender = $name ? ($email ? $name . " <" . $email . ">" : $name) : ($email ? $email : "Anonymous");
$comments = $_POST["comments"];
if ($config["messages"] === true) {
    // send via mail
    mail(get_current_user(), "[Control] Contact form submission from " . $sender . " (" . $_SERVER["REMOTE_ADDR"] . ")", $comments, "From: " . $sender);
} else {
    // write to file
    $fp = fopen($config["messages"], "a");
    fwrite($fp, "[" . date("d/m/Y H:i:s") . " | " . $_SERVER["REMOTE_ADDR"] . "] ");
    fwrite($fp, $sender . ":\n");
    fwrite($fp, $comments . "\n\n");
    fclose($fp);
}
