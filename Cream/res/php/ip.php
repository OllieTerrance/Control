<?
// query external IP address
require_once "includes/common.php";
print(file_get_contents("http://myip.dnsomatic.com"));
