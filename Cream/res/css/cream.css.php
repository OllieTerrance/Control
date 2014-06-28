<?
header("Content-Type: text/css");
$ip = $_SERVER["REMOTE_ADDR"];
session_start();
$local = in_array($_SERVER["HTTP_HOST"], array("cream", "192.168.1.100"));
$remote = array_key_exists("login", $_SESSION);
$access = $local || $remote;
?>body {
    padding-top: 60px;
}
img {
    vertical-align: text-top;
}
td {
    vertical-align: middle !important;
}
.alert {
    margin-bottom: 10px;
}
.modal-body .alert {
    margin-bottom: 5px;
}
.modal {
    overflow-y: auto;
}
#logo {
    margin-top: 20px;
}
@media (max-width: 767px) {
    #logo {
        max-height: 200px;
        margin-left: auto;
        margin-right: auto;
    }
}
<?
if ($access) {
?>
.page {
    display: none;
}
#page-files .btn-group.pull-right {
    margin-left: 8px;
}
#files-list {
    margin-top: 15px;
    cursor: default;
    user-select: none;
    -o-user-select:none;
    -moz-user-select: none;
    -khtml-user-select: none;
    -webkit-user-select: none;
}
#files-list .panel-heading {
    text-overflow: ellipsis;
    white-space: nowrap;
    overflow: hidden;
}
#files-display-content {
    padding-bottom: 15px;
}
#files-display-content pre {
    margin-bottom: 5px;
}
#files-display-content video, #files-display-content audio {
    width: 100%;
}
#files-newfolder-hint {
    margin: 10px 0 0;
}
#files-newfolder-name {
    margin-top: 15px;
}
#files-upload-drag {
    margin-bottom: 0;
    padding: 40px 0;
    text-align: center;
    border: 5px dashed #dddddd;
}
#files-upload-drag {
    margin-bottom: 0;
    padding: 40px 0;
    text-align: center;
    border: 5px dashed #dddddd;
}
#files-upload-drag p {
    margin: 0;
}
#files-upload-drag #files-upload-list {
    display: none;
    margin-top: 10px;
}
<?
} else {
?>
#ip-warning {
    display: none;
}
#login-password {
    margin-top: 15px;
}
<?
}
?>
