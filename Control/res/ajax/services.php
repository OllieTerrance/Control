<?
// query running services
require_once "../php/auth.php";
if (!array_key_exists("services", $config)) return http_response_code(401);
switch ($config["services"]) {
    case "debian":
        print(shell_exec("/usr/sbin/service --status-all"));
        break;
    case "arch":
        print(explode("\n\n", shell_exec("/usr/bin/systemctl --all"))[0]);
        break;
    default:
        return http_response_code(400);
}
