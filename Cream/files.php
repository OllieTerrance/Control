<?
error_reporting(E_ALL);
ini_set("display_errors", 1);
// list files in a directory
require_once "auth.php";
// convert bytes to readable format
function size($bytes) {
    if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . "GB";
    if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . "MB";
    if ($bytes >= 1024) return number_format($bytes / 1024, 2) . "KB";
    return $bytes . "B";
}
// MIME type property for finfo_file
$finfo = finfo_open(FILEINFO_MIME_TYPE);
// return the MIME type of a file
function mime($path) {
    global $finfo;
    // skip warnings on failed finfo calls
    $errep = error_reporting(E_ALL & ~E_WARNING);
    // can't get MIME type from unreadable (no permission) or pseudo (e.g. /dev) files
    $mime = finfo_file($finfo, $path);
    error_reporting($errep);
    // default to plain text type if empty
    return $mime ? $mime : "unknown";
}
// return file content by GET
if (isset($_GET["key"])) {
    $key = $_GET["key"];
    // unrecognized or expired key
    if (!array_key_exists($key, $_SESSION["files"])) return http_response_code(404);
    // output raw file
    $file = $_SESSION["files"][$key];
    header("Content-Type: " . finfo_file($finfo, $file));
    print(file_get_contents($file));
    return;
}
$dir = $_POST["dir"];
// specified directory doesn't exist
if (!file_exists($dir) || $dir[0] !== "/") return http_response_code(400);
// convert to absolute
$dir = realpath($dir);
// return file content by POST
if (isset($_POST["file"])) {
    $file = realpath($dir . "/" . $_POST["file"]);
    // specified file doesn't exist
    if (!file_exists($file)) return http_response_code(409);
    // can't be accessed by server user
    if (!is_readable($file)) return http_response_code(403);
    // default empty MIME to plain text
    header("Content-Type: text/plain");
    // split MIME by / and take first half
    switch (current(explode("/", mime($file)))) {
        // output plain text
        case "text":
            print(file_get_contents($file));
            break;
        // output key to access file by GET request
        case "image":
        case "audio":
        case "video":
            if (!array_key_exists("files", $_SESSION)) $_SESSION["files"] = array();
            // pick new key
            $key = substr(base64_encode(mt_rand()), 0, 15);
            while (array_key_exists($key, $_SESSION["files"])) $key = substr(base64_encode(mt_rand()), 0, 15);
            $_SESSION["files"][$key] = $file;
            print($key);
            break;
        // file cannot be previewed
        default:
            return http_response_code(400);
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
        print($file . "//" . $link . "//" . mime($path) . "//" . size(filesize($path)) . "//"
            . date("d/m/Y H:i:s", filemtime($path)) . "//". $date . "//"
            . $user["name"] . "//" . $group["name"] . "//" . $perms . "\n");
    }
}
