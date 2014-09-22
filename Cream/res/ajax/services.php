<?
// query running services
require_once "../php/auth.php";
print(shell_exec("/usr/sbin/service --status-all"));
