<?
header("Content-Type: application/javascript");
$ip = $_SERVER["REMOTE_ADDR"];
session_start();
$local = in_array($_SERVER["HTTP_HOST"], array("cream", "192.168.1.100"));
$remote = array_key_exists("login", $_SESSION);
$access = $local || $remote;
// system user of PHP process
$user = current(posix_getpwuid(posix_geteuid()));
?>$(document).ready(function() {
<?
if ($access) {
?>
    // check service status on load
    $.ajax({
        url: "services.php",
        success: function(data, stat, xhr) {
            var table = $("<table/>").attr("id", "services").addClass("table table-bordered table-condensed");
            // lines of " [ x ]  y" where x is +/-, y is name
            var raw = data.split("\n");
            for (var i in raw) {
                if (!raw[i]) continue;
                var tr = $("<tr/>");
                var colour = "";
                switch (raw[i].charAt(3)) {
                    // running
                    case "+":
                        colour = "success";
                        break;
                    // not running
                    case "-":
                        colour = "danger";
                        break;
                }
                tr.addClass(colour);
                tr.append($("<td/>").addClass(colour ? "text-" + colour : "").text(raw[i].substr(8)));
                table.append(tr);
            }
            $("#services").replaceWith(table);
        },
        error: function(xhr, stat, err) {
            $("#services").removeClass("alert-warning").addClass("alert-danger").text("Unable to query status of services.");
        }
    });
    // async logout button
    $("#logout").click(function(e) {
        e.preventDefault();
        $("#logout").text("Loading...").parent().addClass("active");
        $.ajax({
            url: "login.php?logout",
            success: function(data, stat, xhr) {
                location.reload();
            },
            error: function(xhr, stat, err) {
                $("#logout").text("Failed!");
            }
        });
    });
    // file browser
    var history = [];
    var pos = -1;
    var back = false, forward = false, loading = false;
    var path = "/home/user";
    $("#location-back").click(function(e) {
        if (pos <= 0) return;
        $("#location-dir").val(history[pos - 1]);
        back = true;
        $("#location-submit").click();
    });
    $("#location-up").click(function(e) {
        var parts = path.split("/");
        $("#location-dir").val(parts.slice(0, parts.length - 1).join("/"));
        $("#location-submit").click();
    });
    $("#location-forward").click(function(e) {
        if (pos >= history.length - 1) return;
        $("#location-dir").val(history[pos + 1]);
        forward = true;
        $("#location-submit").click();
    });
    $("#location-common a").click(function(e) {
        e.preventDefault();
        $("#location-dir").val($(this).data("path"));
        $("#location-submit").click();
    });
    $("#location-submit").click(function(e) {
        loading = true;
        $(".location-ctrl").prop("disabled", true);
        $("#files-list").css("opacity", 0.5);
        // default to /
        if (!$("#location-dir").val()) $("#location-dir").val("/");
        $.ajax({
            url: "files.php",
            method: "post",
            // location normalisation handled by files.php
            data: {"dir": $("#location-dir").val()},
            success: function(data, stat, xhr) {
                var files = data.split("\n");
                path = files.splice(0, 1)[0];
                // go back in history
                if (back) {
                    pos--;
                    back = false;
                // go forward in history
                } else if (forward) {
                    pos++;
                    forward = false;
                } else {
                    // clear history after current point
                    history = history.slice(0, pos + 1);
                    // not a duplicate, add to history
                    if (history[history.length - 1] !== path) {
                        history.push(path);
                        pos++;
                    }
                }
                $("#location-dir").val(path).prop("disabled", false);
                $(".location-ctrl").prop("disabled", false);
                $("#files-list").empty().css("opacity", 1);
                // panel for each file
                $(files).each(function(i, str) {
                    if (!str) return;
                    var root = $("<div/>").addClass("col-lg-2 col-md-3 col-sm-4 col-xs-6");
                    // file = [name, link, mime, size, date, short date, owner, group, perms]
                    var file = str.split("//");
                    var icon = (file[2] === "directory" ? "folder-open" : "file");
                    if (file[1]) icon += "-o";
                    var head = $("<div/>").addClass("panel-heading")
                        .append($("<i/>").addClass("fa fa-" + icon).attr("title", (file[1] ? "→ " + file[1] + "\n" : "") + file[2]))
                        .append(" ").append($("<span/>").text(file[0]).attr("title", file[0]));
                    var body = $("<div/>").addClass("panel-body small");
                    var perms = $("<span/>").addClass("pull-right").text(file[6] + ":" + file[7]);
                    perms.mouseover(function(e) {
                        $(this).text(file[8]);
                    }).mouseout(function(e) {
                        $(this).text(file[6] + ":" + file[7]);
                    });
                    var date = $("<span/>").text(file[5]);
                    date.mouseover(function(e) {
                        $(this).text(file[4]);
                    }).mouseout(function(e) {
                        $(this).text(file[5]);
                    });
                    body.append(perms).append($("<p/>").append(file[3])).append(date);
                    var panel = $("<div/>").addClass("panel panel-default").append(head).append(body);
                    $("#files-list").append(root.append(panel));
                    // double-click folder to navigate to
                    if (file[2] === "directory") {
                        panel.dblclick(function(e) {
                            if (loading) return;
                            $("#location-dir").val(path + (path === "/" ? "" : "/") + file[0]);
                            $("#location-submit").click();
                        });
                    // double-click file to view preview
                    } else {
                        panel.dblclick(function(e) {
                            if (loading) return;
                            $("#files-display-title").text(file[0]);
                            $("#files-display-content").empty().append($("<div/>").addClass("alert alert-info").text("Loading..."));
                            $("#files-display").modal("show");
                            $.ajax({
                                url: "files.php",
                                method: "post",
                                data: {
                                    "dir": path,
                                    "file": file[0]
                                },
                                dataType: "text",
                                success: function(data, stat, xhr) {
                                    var type = file[2].split("/");
                                    var root;
                                    switch (type[0]) {
                                        case "text":
                                            root = $("<pre/>").text(data);
                                            break;
                                        case "image":
                                            root = $("<img/>").attr("src", "files.php?key=" + data);
                                            break;
                                        case "audio":
                                            var source = $("<source/>").attr("src", "files.php?key=" + data).attr("type", file[2]);
                                            root = $("<audio/>").attr("controls", "").append(source);
                                            break;
                                        case "video":
                                            var source = $("<source/>").attr("src", "files.php?key=" + data).attr("type", file[2]);
                                            root = $("<video/>").attr("controls", "").append(source);
                                            break;
                                    }
                                    $("#files-display-content").empty().append(root);
                                },
                                error: function(xhr, stat, err) {
                                    var info = "Unable to fetch file content.";
                                    switch (xhr.status) {
                                        // file type can't be previewed
                                        case 400:
                                            info = "File type " + file[2] + " cannot be previewed.";
                                            break;
                                        // file can't be accessed by server user
                                        case 403:
                                            info = "File not accessible to user <?=$user;?>.";
                                            break;
                                        // file doesn't exist
                                        case 409:
                                            info = "File no longer exists.";
                                            break;
                                    }
                                    $("#files-display-content").empty().append($("<div/>").addClass("alert alert-danger").text(info));
                                }
                            });
                        });
                    }
                });
                // no files in folder
                if ($("#files-list").is(":empty")) {
                    var info = $("<div/>").addClass("alert alert-info").text("Nothing in this folder.");
                    $("#files-list").append($("<div/>").addClass("col-xs-12").append(info));
                }
                $("#location-dir").focus();
                loading = false;
            },
            error: function(xhr, stat, err) {
                // 400 if directory doesn't exist
                if (xhr.status === 400) {
                    // blink location bar
                    $("#location-dir").prop("disabled", false).parent().addClass("has-error");
                    setTimeout(function() {
                        $("#location-dir").parent().removeClass("has-error");
                    }, 150);
                    setTimeout(function() {
                        $("#location-dir").parent().addClass("has-error");
                    }, 300);
                    setTimeout(function() {
                        $("#location-dir").parent().removeClass("has-error");
                    }, 450);
                    setTimeout(function() {
                        $("#location-dir").focus();
                        $(".location-ctrl").prop("disabled", false);
                    }, 600);
                } else {
                    $("#files-list .alert-danger").remove();
                    var info = $("<div/>").addClass("alert alert-danger").text("Failed to query files (" + xhr.status + " " + err + ").");
                    $("#files-list").prepend($("<div/>").addClass("col-xs-12").append(info));
                    $(".location-ctrl").prop("disabled", false);
                    $("#location-dir").focus();
                }
                $("#files-list").css("opacity", 1);
                loading = false;
            },
        });
    });
    $("#files-display").on("hidden.bs.modal", function(e) {
        $("#files-display-content").empty();
    });
    $(".nav-tab").click(function(e) {
        $(".nav-tab").parent().removeClass("active");
        $(".page").hide();
        $(this).parent().addClass("active");
        $("#" + this.id.substr(4)).show();
    });
    $(document).ready(function() {
        var tabs = ["home", "files"];
        var tab = tabs[0];
        if (location.hash) {
            var sel = location.hash.substr(1);
            if (tabs.indexOf(sel) > -1) tab = sel;
        }
        $("#nav-" + tab).click();
        $("#location-submit").click();
    });
<?
} else {
?>
    $.ajax({
        url: "ip.php",
        success: function(data, stat, xhr) {
            $("#ip").text(data);
            if (data === "<?=$ip;?>") $("#ip-warning").show();
        },
        error: function(xhr, stat, err) {
            $("#ip").replaceWith("unknown");
        }
    });
    $("#login-prompt").on("shown.bs.modal", function(e) {
        $("#password").focus();
    }).on("hidden.bs.modal", function(e) {
        $("#password").val("");
    });
    $("#login-submit").on("click", function(e) {
        $("#password").prop("disabled", true).parent().removeClass("has-error");
        $("#login-submit").prop("disabled", true).val("Loading...");
        $.ajax({
            url: "login.php",
            method: "post",
            data: {"password": $("#password").val()},
            success: function(data, stat, xhr) {
                $("#password").parent().addClass("has-success");
                $("#login-prompt").on("hidden.bs.modal", function(e) {
                    location.reload();
                }).modal("hide");
            },
            error: function(xhr, stat, err) {
                $("#password").prop("disabled", false).parent().addClass("has-error");
                $("#login-submit").val("Try again!");
                setTimeout(function() {
                    $("#password").parent().removeClass("has-error");
                }, 150);
                setTimeout(function() {
                    $("#password").parent().addClass("has-error");
                }, 300);
                setTimeout(function() {
                    $("#password").parent().removeClass("has-error");
                }, 450);
                setTimeout(function() {
                    $("#password").focus();
                    $("#login-submit").prop("disabled", false).val("Login");
                }, 600);
            },
        });
        e.preventDefault();
    });
<?
}
?>
});
