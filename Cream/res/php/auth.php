<?
// require authentication, either local or remote
require_once "common.php";
if (!$access) {
    http_response_code(403);
    die();
}
