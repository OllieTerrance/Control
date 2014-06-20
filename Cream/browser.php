<?
$dir = $_REQUEST["dir"];
if (!file_exists($dir) || $dir[0] !== "/") {
    http_response_code(400);
    return;
}
$dir = realpath($dir);
print($dir . "\n");
$files = scandir($dir);
function size($bytes) {
    if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . "GB";
    if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . "MB";
    if ($bytes >= 1024) return number_format($bytes / 1024, 2) . "KB";
    return $bytes . "B";
}
foreach ($files as $i => $file) {
    if ($i < 2) continue;
    $path = $dir . "/" . $file;
    $type = "file";
    if (is_link($path)) {
        $type = "link";
    } elseif (is_dir($path)) {
        $type = "dir";
    }
    $user = posix_getpwuid(fileowner($path));
    $group = posix_getgrgid(filegroup($path));
    $chmod = fileperms($path) & 0777;
    $perms = "";
    $perms .= ($chmod & 256 ? "r" : "-") . ($chmod & 128 ? "w" : "-") . ($chmod & 64 ? "x" : "-") . " "
              . ($chmod & 32 ? "r" : "-") . ($chmod & 16 ? "w" : "-") . ($chmod & 8 ? "x" : "-") . " "
              . ($chmod & 4 ? "r" : "-") . ($chmod & 2 ? "w" : "-") . ($chmod & 1 ? "x" : "-");
    print($file . "//" . $type . "//" . size(filesize($path)) . "//" . date("d/m/Y H:i:s", filemtime($path)) . "//"
          . $user["name"] . "//" . $group["name"] . "//" . $perms . "\n");
}
