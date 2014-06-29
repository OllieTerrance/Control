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
    var path = $("#location-dir").val();
    $("#location-back").click(function(e) {
        if (pos <= 0) return;
        $("#location-dir").val(history[pos - 1]);
        back = true;
        $("#location-submit").click();
    });
    $("#location-forward").click(function(e) {
        if (pos >= history.length - 1) return;
        $("#location-dir").val(history[pos + 1]);
        forward = true;
        $("#location-submit").click();
    });
    $("#location-reload").click(function(e) {
        $("#location-dir").val(path);
        $("#location-submit").click();
    });
    $("#location-up").click(function(e) {
        var parts = path.split("/");
        $("#location-dir").val(parts.slice(0, parts.length - 1).join("/"));
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
        $("#files-list").css("opacity", 0.6);
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
                var access = files.splice(0, 1)[0];
                $("#files-list").empty().css("opacity", 1);
                // panel for each file
                $(files).each(function(i, str) {
                    if (!str) return;
                    var root = $("<div/>").addClass("col-lg-2 col-md-3 col-sm-4 col-xs-6");
                    // file = [name, link, mime, size, full date, short date, owner, perms, colour]
                    var file = str.split("//");
                    var icon = (file[2] === "directory" ? "folder-open" : "file");
                    if (file[1]) icon += "-o";
                    var perms = $("<span/>").addClass("pull-right").text(file[6])
                        .mouseover(function(e) {
                            $(this).text(file[7]);
                        }).mouseout(function(e) {
                            $(this).text(file[6]);
                        });
                    var date = $("<span/>").text(file[5])
                        .mouseover(function(e) {
                            $(this).text(file[4]);
                        }).mouseout(function(e) {
                            $(this).text(file[5]);
                        });
                    var panel = $("<div/>").addClass("panel panel-" + file[8]).data("colour", file[8])
                        .append($("<div/>").addClass("panel-heading")
                            .append($("<i/>").addClass("fa fa-" + icon).attr("title", (file[1] ? "→ " + file[1] + "\n" : "") + file[2]))
                            .append(" ").append($("<span/>").text(file[0]).attr("title", file[0])))
                        .append($("<div/>").addClass("panel-body small")
                            .append(perms).append($("<p/>").append(file[3])).append(date));
                    $("#files-list").append(root.append(panel));
                    // single-click to select
                    panel.click(function(e) {
                        if (e.ctrlKey) {
                            panel.toggleClass("panel-" + panel.data("colour")).toggleClass("panel-primary");
                        } else {
                            $("#files-list .panel-primary").each(function(i, pnl) {
                                $(pnl).removeClass("panel-primary").addClass("panel-" + $(pnl).data("colour"));
                            });
                            panel.removeClass("panel-" + panel.data("colour")).addClass("panel-primary");
                        }
                    });
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
                                            info = "File type <code>" + file[2] + "</code> cannot be previewed.";
                                            break;
                                        // file can't be accessed by server user
                                        case 401:
                                            info = "File not accessible to user <code><?=$user;?></code>.";
                                            break;
                                        // file doesn't exist
                                        case 409:
                                            info = "File no longer exists.";
                                            break;
                                    }
                                    $("#files-display-content").empty().append($("<div/>").addClass("alert alert-danger").html(info));
                                }
                            });
                        });
                    }
                });
                if (access !== "w") $("#location-actions").prop("disabled", true);
                // no files in folder
                if ($("#files-list").is(":empty")) {
                    var info = $("<div/>");
                    if (access) {
                        info.addClass("alert alert-info").text("Nothing in this folder.");
                    } else {
                        info.addClass("alert alert-danger").html("Folder not accessible to user <code><?=$user;?></code>.");
                    }
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
    $("#files-newfolder").on("shown.bs.modal", function(e) {
        $("#files-newfolder-name").focus();
    }).on("hidden.bs.modal", function(e) {
        $("#files-newfolder-name").val("");
    });
    $("#files-newfolder-submit").on("click", function(e) {
        $("#files-newfolder-name").prop("disabled", true).parent().removeClass("has-error");
        $("#files-newfolder-submit").prop("disabled", true).val("Loading...");
        $.ajax({
            url: "files.php",
            method: "post",
            data: {
                "dir": path,
                "newfolder": $("#files-newfolder-name").val()
            },
            success: function(data, stat, xhr) {
                $("#files-newfolder-name").prop("disabled", false);
                $("#files-newfolder-submit").prop("disabled", false).empty().append($("<i/>").addClass("fa fa-check")).append(" Create");
                $("#files-newfolder").on("hidden.bs.modal", function(e) {
                    $("#location-dir").val(path);
                    $("#location-submit").click();
                }).modal("hide");
            },
            error: function(xhr, stat, err) {
                window.x = xhr;
                $("#files-newfolder-name").prop("disabled", false).parent().addClass("has-error");
                $("#files-newfolder-submit").val("Already exists!");
                setTimeout(function() {
                    $("#files-newfolder-name").parent().removeClass("has-error");
                }, 150);
                setTimeout(function() {
                    $("#files-newfolder-name").parent().addClass("has-error");
                }, 300);
                setTimeout(function() {
                    $("#files-newfolder-name").parent().removeClass("has-error");
                }, 450);
                setTimeout(function() {
                    $("#files-newfolder-name").focus();
                    $("#files-newfolder-submit").prop("disabled", false).empty().append($("<i/>").addClass("fa fa-check")).append(" Create");
                }, 600);
            },
        });
        e.preventDefault();
    });
    $("#files-upload").on("hidden.bs.modal", function(e) {
        if (!$("#files-upload-list").is(":empty")) {
            $("#location-dir").val(path);
            $("#location-submit").click();
            $("#files-upload-list").empty().hide();
        }
    });
    function uploadFiles(files) {
        $(files).each(function(i, file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $.ajax({
                    url: "files.php",
                    method: "post",
                    data: {
                        "dir": path,
                        "upload": e.target.result,
                        "name": file.name
                    },
                    success: function(resp, stat, xhr) {
                        $("#files-upload-list").show().append("<code>" + file.name + "</code><br>");
                    },
                    error: function(xhr, stat, err) {
                        window.a = arguments;
                    }
                });
            };
            reader.readAsDataURL(files[i]);
        });
    }
    $("#files-upload-drag").on("dragenter", function(e) {
        e.stopPropagation();
        e.preventDefault();
        $(this).css("border-color", "#428bca");
    }).on("dragleave", function(e) {
        e.stopPropagation();
        e.preventDefault();
        $(this).css("border-color", "#dddddd");
    }).on("dragover", function(e) {
        e.stopPropagation();
        e.preventDefault();
    }).on("drop", function(e) {
        e.stopPropagation();
        e.preventDefault();
        uploadFiles(e.originalEvent.dataTransfer.files);
    });
    $("#files-upload-browse").click(function(e) {
        e.preventDefault();
        $("#files-upload-file").click();
    });
    $("#files-upload-file").change(function(e) {
        uploadFiles($("#files-upload-file").prop("files"));
    });
    $(".nav-tab").click(function(e) {
        $(".nav-tab").parent().removeClass("active");
        $(".page").hide();
        $(this).parent().addClass("active");
        $("#page-" + this.id.substr(4)).show();
    });
    var hashChange = function() {
        var tabs = ["home", "files"];
        var tab;
        if (location.hash) {
            var sel = location.hash.substr(1);
            if (tabs.indexOf(sel) > -1) tab = sel;
        }
        if (!tab) {
            tab = tabs[0];
            location.hash = "#home";
        }
        $("#nav-" + tab).click();
        $("#location-submit").click();
    };
    $(document).ready(hashChange);
    $(window).on("hashchange", hashChange);
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
    $("#login").on("shown.bs.modal", function(e) {
        $("#login-password").focus();
    }).on("hidden.bs.modal", function(e) {
        $("#login-password").val("");
    });
    $("#login-submit").on("click", function(e) {
        $("#login-password").prop("disabled", true).parent().removeClass("has-error");
        $("#login-submit").prop("disabled", true);
        $.ajax({
            url: "login.php",
            method: "post",
            data: {"password": $("#login-password").val()},
            success: function(data, stat, xhr) {
                $("#login-password").parent().addClass("has-success");
                $("#login").on("hidden.bs.modal", function(e) {
                    location.reload();
                }).modal("hide");
            },
            error: function(xhr, stat, err) {
                $("#login-password").prop("disabled", false).parent().addClass("has-error");
                setTimeout(function() {
                    $("#login-password").parent().removeClass("has-error");
                }, 150);
                setTimeout(function() {
                    $("#login-password").parent().addClass("has-error");
                }, 300);
                setTimeout(function() {
                    $("#login-password").parent().removeClass("has-error");
                }, 450);
                setTimeout(function() {
                    $("#login-password").focus();
                    $("#login-submit").prop("disabled", false);
                }, 600);
            },
        });
        e.preventDefault();
    });
<?
}
?>
});
