<?
require_once "res/php/common.php";
$title = (array_key_exists("title", $config) ? $config["title"] : "Cream");
?><!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <meta name="mobile-web-app-capable" content="yes">
        <title><?=$title?></title>
        <link rel="icon" sizes="16x16" href="res/img/cream-16.png">
        <link rel="icon" sizes="196x196" href="res/img/cream-196.png">
        <link rel="icon" sizes="128x128" href="res/img/cream-128.png">
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
                    <a class="navbar-brand" href><?=$title?></a>
                </div>
                <div id="nav-collapse" class="collapse navbar-collapse">
<?
if ($access) {
?>
                    <ul class="nav navbar-nav">
                        <li><a id="nav-home" class="nav-tab" href="#home"><i class="fa fa-home"></i> Home</a></li>
                        <li><a id="nav-files" class="nav-tab" href="#files"><i class="fa fa-folder-open"></i> Files</a></li>
                        <li><a id="nav-info" class="nav-tab" href="#info"><i class="fa fa-info-circle"></i> Info</a></li>
                    </ul>
<?
}
?>
                    <ul class="nav navbar-nav navbar-right">
<?
if ($access) {
?>
                        <li id="nav-loading" class="dropdown">
                            <a href class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-refresh fa-spin"></i> <span id="nav-loading-count"></span></a>
                            <ul id="nav-loading-list" class="dropdown-menu" role="menu"></ul>
                        </li>
<?
}
$desc = '<i class="fa fa-globe"></i> Your IP: ' . $client;
if ($local) {
    $host = '<i class="fa fa-question"></i> Unknown device';
    $ico = "";
    // known device, show name/icon
    if (array_key_exists($client, $config["devices"])) {
        if (count($config["devices"][$client]) === 1) {
            $host = $config["devices"][$client][0][0];
            if (array_key_exists(1, $config["devices"][$client][0])) $ico = '<img src="res/ico/' . $config["devices"][$client][0][1] . '.png"/> ';
        } else {
            $hosts = array();
            foreach ($config["devices"][$client] as $dev) {
                array_push($hosts, $dev[0]);
                if (array_key_exists(1, $dev)) $ico .= '<img src="res/ico/' . $dev[1] . '.png"/>';
            }
            $ico .= " ";
            $host = implode("/", $hosts);
        }
    }
    $desc = $ico . $host . " (" . $client . ")";
} elseif ($remote) {
    $desc = '<i class="fa fa-globe"></i> External access (' . $client . ')';
}
?>
                        <li><a href="http://<?=$client?>/"><?=$desc;?></a></li>
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
                <div class="col-sm-3">
                    <img id="logo" class="img-responsive" src="res/img/cream.png"/>
                </div>
                <div class="col-lg-3 col-lg-offset-6 col-md-4 col-md-offset-5 col-sm-5 col-sm-offset-4">
                    <h2>Devices</h2>
                    <table id="devices" class="table table-bordered table-striped">
<?
    foreach ($config["devices"] as $ip => $devs) {
        // highlight green if current device, blue if first device (i.e. server)
        $class = ($ip === $client) ? ' class="success"' : (($ip === $server) ? ' class="info"' : "");
        $fdevs = array();
        // iterate device list for current IP
        foreach ($devs as $dev) {
            $ico = array_key_exists(1, $dev) ? '<img src="res/ico/' . $dev[1] . '.png"/> ' : "";
            array_push($fdevs, $ico . $dev[0]);
        }
?>
                        <tr<?=$class;?>>
                            <td><?=implode("<br/>", $fdevs);?></td>
                            <td><code><?=$ip;?></code></td>
                        </tr>
<?
    }
?>
                    </table>
<?
    if (!empty($config["media"])) {
?>
                    <h2>Media</h2>
                    <table class="table table-bordered table-striped">
<?
        foreach ($config["media"] as $devs) {
            $ico = array_key_exists(2, $devs) ? '<img src="res/ico/' . $devs[2] . '.png"/> ' : "";
?>
                        <tr>
                            <td><?=$ico;?><?=$devs[0];?></td>
                            <td><?=$devs[1];?></td>
                        </tr>
<?
        }
?>
                    </table>
<?
    }
?>
                </div>
            </div>
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
$startDir = "/";
if (!empty($config["places"])) {
    $startDir = key($config["places"]);
?>
                    <button type="button" class="location-ctrl btn btn-default dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-rocket"></i>
                        <span class="hidden-xs">Places</span>
                        <span class="caret"></span>
                    </button>
                    <ul id="location-common" class="dropdown-menu pull-right" role="menu">
<?
    foreach ($config["places"] as $place => $icon) {
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
                            <input id="location-dir" class="location-ctrl form-control" value="<?=$startDir?>" placeholder="/">
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
            <div id="page-info" class="page">
                <ul class="nav nav-pills nav-justified" role="tablist">
                    <li class="active"><a id="info-nav-processes" href="#info/processes" data-toggle="tab" data-target="#processes"><i class="fa fa-bar-chart"></i> Processes</a></a></li>
                    <li><a id="info-nav-services" href="#info/services" data-toggle="tab" data-target="#services"><i class="fa fa-check"></i> Services</a></a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="processes"></div>
                    <div class="tab-pane" id="services"></div>
                </div>
            </div>
<?
} else {
?>
            <div class="row">
                <div class="col-lg-4 col-sm-3">
                    <img id="logo" class="img-responsive" src="res/img/cream.png"/>
                </div>
                <div class="col-lg-7 col-lg-offset-1 col-sm-8 col-sm-offset-1">
                    <h2>External access?</h2>
                    <p>You are currently viewing this page externally.  In order to view more details, you need to be viewing this page from a device on the network.</p>
                    <div id="ip-warning" class="alert alert-info">Your external IP address seems to match that of the server, so you are likely already connected successfully.  Go ahead and try accessing <a class="alert-link" href="http://<?=$config["hostnames"][0]?>/">by internal hostname</a> to continue.</div>
<?
    if (array_key_exists("password", $config)) {
?>
                    <p>Alternatively, you can <a href data-target="#login" data-toggle="modal">login</a> to the server with a password.</p>
<?
    }
?>
                    <h2>Expecting a website here?</h2>
                    <p>You have reached this page by navigating to <code><?=$_SERVER["HTTP_HOST"];?></code>.  This may happen when attempting to access a domain name that points to this server's external IP address (currently <code id="ip">...</code>), but does not have an appropriate virtual host configured locally.</p>
<?
    if (array_key_exists("messages", $config)) {
?>
                    <h2>Contact the webmaster?</h2>
                    <p id="contact-para">For any questions or comments, <a href data-target="#contact" data-toggle="modal">click here</a> to leave a message.</p>
<?
    }
?>
                </div>
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
    if (array_key_exists("password", $config)) {
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
    if (array_key_exists("messages", $config)) {
?>
        <div id="contact" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Contact webmaster</h4>
                    </div>
                    <form role="form">
                        <div class="modal-body">
                            <div class="form-group">
                                <input id="contact-name" type="text" class="form-control" placeholder="Name">
                            </div>
                            <div class="form-group">
                                <input id="contact-email" type="email" class="form-control" placeholder="Email">
                            </div>
                            <div class="form-group">
                                <textarea id="contact-comments" class="form-control" placeholder="Comments (required)" required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button id="contact-submit" type="submit" class="btn btn-primary"><i class="fa fa-envelope"></i> Send</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
<?
    }
}
?>
        <script src="lib/js/jquery.min.js"></script>
        <script src="lib/js/bootstrap.min.js"></script>
        <script src="res/js/cream.js.php"></script>
    </body>
</html>
