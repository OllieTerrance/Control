<?
// query running services
require_once "auth.php";
print(shell_exec("/usr/sbin/service --status-all"));
