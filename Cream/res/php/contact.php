<?
// store messages sent from the public page
require_once "common.php";
// ignore empty comments
if (!$_POST["comments"]) {
    http_response_code(400);
    die();
}
$name = $_POST["name"];
$email = $_POST["email"];
$sender = $name ? ($email ? $name . " <" . $email . ">" : $name) : ($email ? $email : "Anonymous");
// write to file
$fp = fopen("/var/data/cream_messages.txt", "a");
fwrite($fp, "[" . date("d/m/Y H:i:s") . " | " . $_SERVER["REMOTE_ADDR"] . "] ");
fwrite($fp, $sender . ":\n");
fwrite($fp, $_POST["comments"] . "\n\n");
fclose($fp);
