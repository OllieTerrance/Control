<?
// list files in a directory
require_once "auth.php";
$dir = $_POST["dir"];
// specified directory doesn't exist
if (!file_exists($dir) || $dir[0] !== "/") return http_response_code(400);
// convert to absolute
$dir = realpath($dir);
// convert bytes to readable format
function size($bytes) {
    if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . "GB";
    if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . "MB";
    if ($bytes >= 1024) return number_format($bytes / 1024, 2) . "KB";
    return $bytes . "B";
}
// MIME type property for finfo_file
$finfo = finfo_open(FILEINFO_MIME_TYPE);
// return file content
if (isset($_POST["file"])) {
    $file = $dir . "/" . $_POST["file"];
    // specified file doesn't exist
    if (!file_exists($file)) return http_response_code(400);
    // can't be accessed by server user
    if (!is_readable($file)) return http_response_code(403);
    // default empty MIME to plain text
    $mime = finfo_file($finfo, $file);
    if (!$mime) $mime = "text/plain";
    // split MIME by / and take first half
    switch (current(explode("/", $mime))) {
        case "text":
            print(file_get_contents($file));
            break;
        default:
            break;
    }
// list directory contents
} else {
    // print resolved directory name
    print($dir . "\n");
    foreach (scandir($dir) as $i => $file) {
        // skip . and ..
        if ($i < 2) continue;
        $path = $dir . "/" . $file;
        // symbolic link target
        $link = is_link($path) ? readlink($path) : "";
        $mime = finfo_file($finfo, $path);
        // default to plain text type
        if (!$mime) $mime = "text/plain";
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
        // output line "name//link//mime//size//date//short date//owner//group//perms"
        print($file . "//" . $link . "//" . $mime . "//" . size(filesize($path)) . "//"
            . date("d/m/Y H:i:s", filemtime($path)) . "//". $date . "//"
            . $user["name"] . "//" . $group["name"] . "//" . $perms . "\n");
    }
}
