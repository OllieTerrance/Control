<?
// list all running processes
require_once "../php/auth.php";
exec("ps -A o pid,user,comm,command", $out);
$columns = array_shift($out);
foreach ($out as $line) {
    $pid = trim(substr($line, 0, 5));
    $user = trim(substr($line, 6, 8));
    $name = trim(substr($line, 15, 15));
    $cmd = trim(substr($line, 31));
    // skip list command
    if (strpos($cmd, "ps -A o pid,user,comm,command") !== FALSE) continue;
    print($pid . "//" . $user . "//" . $name . "//" . $cmd . "\n");
}
