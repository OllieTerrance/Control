<?
// list files in a directory
require_once "auth.php";
$dir = $_POST["dir"];
// specified directory doesn't exist
if (!file_exists($dir) || $dir[0] !== "/") {
    http_response_code(400);
    return;
}
// convert to absolute
$dir = realpath($dir);
print($dir . "\n");
$files = scandir($dir);
// convert bytes to readable format
function size($bytes) {
    if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . "GB";
    if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . "MB";
    if ($bytes >= 1024) return number_format($bytes / 1024, 2) . "KB";
    return $bytes . "B";
}
foreach ($files as $i => $file) {
    // skip . and ..
    if ($i < 2) continue;
    $path = $dir . "/" . $file;
    $type = "file";
    // symbolic link
    if (is_link($path)) {
        $type = "link";
    } elseif (is_dir($path)) {
        $type = "dir";
    }
    // pretty date
    $time = filemtime($path);
    $date = date("j M y", $time);
    if (date("dmY", $time) === date("dmY")) {
        $date = "Today";
    } elseif (date("dmY", $time) === date("dmY", strtotime("yesterday"))) {
        $date = "Yesterday";
    }
    $date .= date(", H:i", $time);
    // user and group names
    $user = posix_getpwuid(fileowner($path));
    $group = posix_getgrgid(filegroup($path));
    // remove additional permissions (just o/g/e r/w/x)
    $chmod = fileperms($path) & 0777;
    // pretty print permissions (e.g. rwx r-x r-x)
    $perms = ($chmod & 256 ? "r" : "-") . ($chmod & 128 ? "w" : "-") . ($chmod & 64 ? "x" : "-") . " "
             . ($chmod & 32 ? "r" : "-") . ($chmod & 16 ? "w" : "-") . ($chmod & 8 ? "x" : "-") . " "
             . ($chmod & 4 ? "r" : "-") . ($chmod & 2 ? "w" : "-") . ($chmod & 1 ? "x" : "-");
    // output line "filename//type//size//date//short date//owner//group//perms"
    print($file . "//" . $type . "//" . size(filesize($path)) . "//" . date("d/m/Y H:i:s", filemtime($path)) . "//"
          . $date . "//" . $user["name"] . "//" . $group["name"] . "//" . $perms . "\n");
}
