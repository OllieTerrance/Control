<?
require_once "../php/common.php";
header("Content-Type: text/css");
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
.modal-body .alert, .modal-body .progress {
    margin-bottom: 0;
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
#nav-loading, .page {
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
#files-display-content img {
    max-width: 100%;
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
#page-info ul.nav {
    margin: 20px 0;
}
ul.nav-pills li a:hover {
    background-color: #f8f8f8 !important;
    color: #333 !important;
}
ul.nav-pills li.active a {
    background-color: #e7e7e7 !important;
    color: #555 !important;
}
ul.nav-pills li.active a:hover {
    color: #333 !important;
}
#processes td:not(:last-child) {
    width: 0%;
    white-space: nowrap;
}
#processes td:last-child {
    word-break: break-all;
}
<?
} else {
?>
#ip-warning {
    display: none;
}
#login .modal-body, #contact .modal-body {
    padding-bottom: 0;
}
<?
}
?>
