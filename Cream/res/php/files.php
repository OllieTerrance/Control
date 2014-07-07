<?
// list files in a directory
require_once "includes/auth.php";
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
    // default to unknown type if empty
    return $mime ? $mime : (is_dir($path) ? "directory" : "unknown");
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
// check current directory exists
if (!array_key_exists("dir", $_POST)) return http_response_code(400);
$dir = $_POST["dir"];
if (!file_exists($dir) || $dir[0] !== "/") return http_response_code(400);
// convert to absolute
$dir = realpath($dir);
// create new folder
if (isset($_POST["newfolder"])) {
    $folder = realpath($dir) . "/" . $_POST["newfolder"];
    var_dump($folder);
    // specified folder already exists or is invalid
    if (file_exists($folder) || strstr($_POST["newfolder"], "/")) return http_response_code(400);
    // create the folder
    if (!mkdir($folder, 0775)) return http_response_code(400);
// upload files to directory
} elseif (isset($_POST["upload"])) {
    $name = isset($_POST["name"]) ? $_POST["name"] : "upload";
    $file = realpath($dir) . "/" . $name;
    // decode data URI and store to file
    $content = file_get_contents($_POST["upload"]);
    file_put_contents($file, $content);
// return file content by POST
} elseif (isset($_POST["file"])) {
    $file = realpath($dir . "/" . $_POST["file"]);
    // specified file doesn't exist
    if (!file_exists($file)) return http_response_code(409);
    // can't be accessed by server user
    if (!is_readable($file)) return http_response_code(401);
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
    // print resolved directory name and if readable/writable
    print($dir . "\n");
    print((is_writable($dir) ? "w" : (is_readable($dir) ? "r" : "")) . "\n");
    if (!is_readable($dir)) return;
    foreach (scandir($dir) as $i => $file) {
        // skip . and ..
        if ($i < 2) continue;
        $path = $dir . "/" . $file;
        $link = "";
        $size = "unknown";
        $time = 0;
        $date = "unknown";
        $fdate = "unknown";
        $owner = "unknown";
        $perms = "unknown";
        if (file_exists($path)) {
            // symbolic link target
            $link = is_link($path) ? readlink($path) : "";
            $size = size(filesize($path));
            // pretty date
            $time = filemtime($path);
            $date = date("j M y", $time);
            if (date("dmY", $time) === date("dmY")) {
                $date = "Today";
            } elseif (date("dmY", $time) === date("dmY", strtotime("yesterday"))) {
                $date = "Yesterday";
            }
            $date .= date(", H:i", $time);
            $fdate = date("d/m/Y H:i:s", $time);
            // user and group names
            $owner = posix_getpwuid(fileowner($path));
            $group = posix_getgrgid(filegroup($path));
            $users = $owner["name"] . ($owner["name"] === $group["name"] ? "" : ":" . $group["name"]);
            // remove additional permissions (just o/g/e r/w/x)
            $chmod = fileperms($path) & 0777;
            // pretty print permissions (e.g. rwx r-x r-x)
            $perms = ($chmod & 256 ? "r" : "-") . ($chmod & 128 ? "w" : "-") . ($chmod & 64 ? "x" : "-") . " "
                . ($chmod & 32 ? "r" : "-") . ($chmod & 16 ? "w" : "-") . ($chmod & 8 ? "x" : "-") . " "
                . ($chmod & 4 ? "r" : "-") . ($chmod & 2 ? "w" : "-") . ($chmod & 1 ? "x" : "-");
        }
        if (is_readable($path)) {
            $colour = $owner["name"] === $user ? "success" : (is_writable($path) ? "info" : "default");
        } else {
            $colour = file_exists($path) ? "danger" : "warning";
        }
        // output line "name//link//mime//size//full date//short date//users//perms//colour"
        print($file . "//" . $link . "//" . mime($path) . "//" . $size . "//". $fdate . "//". $date
             . "//" . $users . "//" . $perms . "//" . $colour . "\n");
    }
}
