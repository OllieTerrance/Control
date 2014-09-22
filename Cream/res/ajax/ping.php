<?
error_reporting(E_ALL);
// ping a device on the network
require_once "../php/auth.php";
$device = $_POST["device"];
// skip unknown devices
if (!array_key_exists($device, $config["devices"])) return http_response_code(400);
exec("ping -c1 " . escapeshellarg($device), $out, $status);
var_dump($out);
if ($status) return http_response_code(408);
