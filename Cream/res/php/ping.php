<?
// ping a device on the network
require_once "auth.php";
$device = $_POST["device"];
if (!array_key_exists($device, $devices)) {
    http_response_code(400);
    die();
}
exec("ping -c1 " . escapeshellarg($device), $out, $status);
if ($status) http_response_code(408);
