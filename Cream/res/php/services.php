<?
// query running services
require_once "includes/auth.php";
print(shell_exec("/usr/sbin/service --status-all"));
