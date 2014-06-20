<?
error_reporting(E_ALL);
ini_set("display_errors", 1);
require_once "data.php";
$ip = $_SERVER["REMOTE_ADDR"];
session_start();
// local if accessing by internal hostname or IP
$local = in_array($_SERVER["HTTP_HOST"], array("cream", "192.168.1.100"));
// remote if logged in with password
$remote = array_key_exists("login", $_SESSION);
$access = $local || $remote;
?><!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>www@cream</title>
        <link rel="shortcut icon" href="res/ico/cream.png">
        <link href="lib/css/bootstrap.min.css" rel="stylesheet">
        <link href="res/css/cream.css" rel="stylesheet">
    </head>
    <body>
        <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
            <div class="container-fluid">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#nav-collapse">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href>www@cream</a>
                </div>
                <div id="nav-collapse" class="collapse navbar-collapse">
<?
if ($access) {
?>
                    <ul class="nav navbar-nav">
                        <li><a id="nav-home" class="nav-tab" href="#home">Home</a></li>
                        <li><a id="nav-files" class="nav-tab" href="#files">Files</a></li>
                    </ul>
<?
}
?>
                    <ul class="nav navbar-nav navbar-right">
<?
$desc = "Your IP: " . $ip;
if ($local) {
    $host = "Unknown device";
    $ico = "";
    // known device, show name/icon
    if (array_key_exists($ip, $devices)) {
        if (count($devices[$ip]) === 1) {
            $host = $devices[$ip][0][0];
            if (array_key_exists(1, $devices[$ip][0])) $ico = '<img src="res/ico/' . $devices[$ip][0][1] . '.png"/> ';
        } else {
            $hosts = array();
            foreach ($devices[$ip] as $xdev) {
                array_push($hosts, $xdev[0]);
                if (array_key_exists(1, $xdev)) $ico .= '<img src="res/ico/' . $xdev[1] . '.png"/>';
            }
            $ico .= " ";
            $host = implode("/", $hosts);
        }
    }
    $desc = $ico . $host . " (" . $ip . ")";
} elseif ($remote) {
    $desc = "External access (" . $ip . ")";
}
?>
                        <li><a href="http://<?=$ip?>/"><?=$desc;?></a></li>
<?
if ($remote) {
?>
                        <li><a id="logout" href>Logout</a></li>
<?
}
?>
                    </ul>
                </div>
            </div>
        </nav>
        <div class="container">
<?
if ($access) {
?>
            <div id="home" class="page row">
<?
} else {
?>
            <div class="row">
<?
}
?>
                <div class="col-lg-4 col-sm-3">
                    <img id="logo" class="img-responsive" src="res/img/cream.png"/>
                </div>
<?
if ($access) {
?>
                <div class="col-lg-2 col-lg-offset-3 col-md-3 col-md-offset-2 col-sm-4">
                    <h2>Services</h2>
                    <div id="services" class="alert alert-warning">Loading...</div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-5">
                    <h2>Devices</h2>
                    <table class="table table-bordered table-striped">
<?
    foreach ($devices as $xip => $xdevs) {
        // highlight green if current device, blue if first device (i.e. server)
        $class = ($xip === $ip) ? ' class="success"' : (($xip === current(array_keys($devices))) ? ' class="info"' : "");
        $devs = array();
        // iterate device list for current IP
        foreach ($xdevs as $xdev) {
            $ico = array_key_exists(1, $xdev) ? '<img src="res/ico/' . $xdev[1] . '.png"/> ' : "";
            array_push($devs, $ico . $xdev[0]);
        }
?>
                        <tr<?=$class;?>>
                            <td><?=implode("<br/>", $devs);?></td>
                            <td><code><?=$xip;?></code></td>
                        </tr>
<?
    }
?>
                    </table>
                    <h2>Media</h2>
                    <table class="table table-bordered table-striped">
<?
    foreach ($media as $xdevs) {
        $ico = array_key_exists(2, $xdevs) ? '<img src="res/ico/' . $xdevs[2] . '.png"/> ' : "";
?>
                        <tr>
                            <td><?=$ico;?><?=$xdevs[0];?></td>
                            <td><?=$xdevs[1];?></td>
                        </tr>
<?
    }
?>
                    </table>
                </div>
<?
} else {
?>
                <div class="col-lg-7 col-lg-offset-1 col-sm-8 col-sm-offset-1">
                    <h2>External access?</h2>
                    <p>You are currently viewing this page externally.  In order to view more details, you need to be viewing this page from a device on the network.</p>
                    <div id="ip-warning" class="alert alert-info">Your external IP address seems to match that of the server, so you are likely already connected successfully.  Go ahead and try accessing <a class="alert-link" href="http://cream/">by hostname</a> or <a class="alert-link" href="http://192.168.1.100/">internal IP address</a> to continue.</div>
                    <p>Alternatively, you can <a href data-target="#login-prompt" data-toggle="modal">login</a> to the server with a password.</p>
                    <h2>Expecting a website here?</h2>
                    <p>You have reached this page by navigating to <code><?=$_SERVER["HTTP_HOST"];?></code>.  This happens when attempting to access a domain name that points to this server's external IP address (currently <code id="ip">...</code>), but does not have an appropriate virtual host configured locally.</p>
                </div>
<?
}
?>
            </div>
<?
if ($access) {
?>
            <div id="files" class="page">
                <h2>File browser</h2>
                <form class="row" role="form">
                    <div class="col-xs-12">
                        <div class="input-group">
                            <div class="input-group-btn">
                                <button id="location-back" type="button" class="location-ctrl btn btn-default"><img src="res/ico/back.png"/></button>
                                <button id="location-up" type="button" class="location-ctrl btn btn-default"><img src="res/ico/up.png"/></button>
                                <button id="location-forward" type="button" class="location-ctrl btn btn-default"><img src="res/ico/forward.png"/></button>
                            </div>
                            <input id="location-dir" class="location-ctrl form-control" value="/home/user" placeholder="/">
                            <div class="input-group-btn">
                                <button id="location-submit" type="submit" class="location-ctrl btn btn-primary"><img src="res/ico/enter.png"/></button>
                                <button type="button" class="location-ctrl btn btn-primary dropdown-toggle" data-toggle="dropdown">
                                    <span class="caret"></span>
                                    <span class="sr-only">Toggle dropdown</span>
                                </button>
                                <ul id="location-common" class="dropdown-menu pull-right" role="menu">
                                    <li class="dropdown-header">Common</li>
                                    <li><a href data-path="/home/user">Home <code>/home/user</code></a></li>
                                    <li><a href data-path="/var/www">WWW <code>/var/www</code></a></li>
                                    <li><a href data-path="/var/res">Libraries <code>/var/res</code></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </form>
                <div id="files-list" class="row"></div>
            </div>
<?
}
?>
        </div>
<?
if (!$access) {
?>
        <div id="login-prompt" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="login-prompt" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Enter password</h4>
                    </div>
                    <form role="form">
                        <div class="modal-body form-group">
                            <input id="password" type="password" class="form-control">
                        </div>
                        <div class="modal-footer">
                            <input id="login-submit" type="submit" class="btn btn-primary" value="Login">
                        </div>
                    </form>
                </div>
            </div>
        </div>
<?
}
?>
        <script src="lib/js/jquery.min.js"></script>
        <script src="lib/js/bootstrap.min.js"></script>
        <script src="res/js/cream.js.php"></script>
    </body>
</html>
