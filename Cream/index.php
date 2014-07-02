<?
require_once "res/php/common.php";
?><!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>www@cream</title>
        <link rel="shortcut icon" href="res/ico/cream.png">
        <link href="lib/css/bootstrap.min.css" rel="stylesheet">
        <link href="lib/css/font-awesome.min.css" rel="stylesheet">
        <link href="res/css/cream.css.php" rel="stylesheet">
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
                        <li><a id="nav-home" class="nav-tab" href="#home"><i class="fa fa-home"></i> Home</a></li>
                        <li><a id="nav-files" class="nav-tab" href="#files"><i class="fa fa-folder-open"></i> Files</a></li>
                    </ul>
<?
}
?>
                    <ul class="nav navbar-nav navbar-right">
<?
$desc = '<i class="fa fa-globe"></i> Your IP: ' . $ip;
if ($local) {
    $host = '<i class="fa fa-question"></i> Unknown device';
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
    $desc = '<i class="fa fa-globe"></i> External access (' . $ip . ')';
}
?>
                        <li><a href="http://<?=$ip?>/"><?=$desc;?></a></li>
<?
if ($remote) {
?>
                        <li><a id="logout" href><i class="fa fa-sign-out"></i> Logout</a></li>
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
            <div id="page-home" class="page row">
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
        $class = ($xip === $ip) ? ' class="success"' : (($xip === $server) ? ' class="info"' : "");
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
<?
    if (!empty($media)) {
?>
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
<?
    }
?>
                </div>
<?
} else {
?>
                <div class="col-lg-7 col-lg-offset-1 col-sm-8 col-sm-offset-1">
                    <h2>External access?</h2>
                    <p>You are currently viewing this page externally.  In order to view more details, you need to be viewing this page from a device on the network.</p>
                    <div id="ip-warning" class="alert alert-info">Your external IP address seems to match that of the server, so you are likely already connected successfully.  Go ahead and try accessing <a class="alert-link" href="http://cream/">by hostname</a> or <a class="alert-link" href="http://<?=$server;?>/">internal IP address</a> to continue.</div>
                    <p>Alternatively, you can <a href data-target="#login" data-toggle="modal">login</a> to the server with a password.</p>
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
            <div id="page-files" class="page">
                <div class="btn-group pull-right">
                    <button id="location-actions" type="button" class="location-ctrl btn btn-default dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-hand-o-right"></i>
                        <span class="hidden-xs">Actions</span>
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu pull-right" role="menu">
                        <li><a href data-target="#files-newfolder" data-toggle="modal"><i class="fa fa-fw fa-plus-square"></i> New folder</a></li>
                        <li><a href data-target="#files-upload" data-toggle="modal"><i class="fa fa-fw fa-cloud-upload"></i> Upload here</a></li>
                    </ul>
                </div>
                <div class="btn-group pull-right">
                    <button id="location-reload" type="button" class="location-ctrl btn btn-default">
                        <i class="fa fa-refresh"></i>
                        <span class="hidden-xs">Reload</span>
                    </button>
                    <button id="location-up" type="button" class="location-ctrl btn btn-default">
                        <i class="fa fa-arrow-up"></i>
                        <span class="hidden-xs">Up one level</span>
                    </button>
<?
if (!empty($places)) {
?>
                    <button type="button" class="location-ctrl btn btn-default dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-rocket"></i>
                        <span class="hidden-xs">Places</span>
                        <span class="caret"></span>
                    </button>
                    <ul id="location-common" class="dropdown-menu pull-right" role="menu">
<?
    foreach ($places as $place => $icon) {
?>
                        <li><a href data-path="<?=$place;?>"><i class="fa fa-fw fa-<?=$icon;?>"></i> <code><?=$place;?></code></a></li>
<?
    }
?>
                    </ul>
<?
}
?>
                </div>
                <h2>File browser</h2>
                <form class="row" role="form">
                    <div class="col-xs-12">
                        <div class="input-group">
                            <div class="input-group-btn">
                                <button id="location-back" type="button" class="location-ctrl btn btn-default" title="Back"><i class="fa fa-arrow-left"></i></button>
                                <button id="location-forward" type="button" class="location-ctrl btn btn-default" title="Forward"><i class="fa fa-arrow-right"></i></button>
                            </div>
                            <input id="location-dir" class="location-ctrl form-control" value="/" placeholder="/">
                            <div class="input-group-btn">
                                <button id="location-submit" type="submit" class="location-ctrl btn btn-primary">
                                    <i class="fa fa-arrow-circle-right"></i>
                                    <span class="hidden-xs">Go</span>
                                </button>
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
if ($access) {
?>
        <div id="files-newfolder" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Enter folder name</h4>
                    </div>
                    <form role="form">
                        <div class="modal-body form-group">
                            <p id="files-newfolder-hint" class="help-block">Note that user <code><?=$user;?></code> must have write access to the current directory.</p>
                            <input id="files-newfolder-name" type="text" class="form-control">
                        </div>
                        <div class="modal-footer">
                            <button id="files-newfolder-submit" type="submit" class="btn btn-primary"><i class="fa fa-check"></i> Create</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-times"></i> Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div id="files-upload" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Upload files</h4>
                    </div>
                    <div class="modal-body">
                        <div id="files-upload-drag">
                            <p>Drag files here to upload, or <a id="files-upload-browse" href>click here</a> to browse.</p>
                            <p id="files-upload-list"></p>
                        </div>
                        <input id="files-upload-file" type="file" class="hidden" multiple>
                    </div>
                </div>
            </div>
        </div>
        <div id="files-display" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 id="files-display-title" class="modal-title"></h4>
                    </div>
                    <div id="files-display-content" class="modal-body"></div>
                </div>
            </div>
        </div>
<?
} else {
?>
        <div id="login" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Enter password</h4>
                    </div>
                    <form role="form">
                        <div class="modal-body form-group">
                            <input id="login-password" type="password" class="form-control">
                        </div>
                        <div class="modal-footer">
                            <button id="login-submit" type="submit" class="btn btn-primary"><i class="fa fa-key"></i> Login</button>
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
