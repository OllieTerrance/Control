<?
require_once "../php/common.php";
header("Content-Type: application/javascript");
?>$(document).ready(function() {
    // add a progress bar to the top of an element
    function alertBar(colour, content) {
        return $("<div/>").addClass("alert alert-" + colour).html(content);
    }
<?
if ($access) {
?>
    // add a progress bar to the top of an element
    function progressBar() {
        return $("<div/>").addClass("progress")
            .append($("<div/>").addClass("progress-bar progress-bar-info progress-bar-striped active").css("width", "100%"));
    }
    // wrap AJAX calls to update loading block
    var ajaxCount = 0;
    var ajaxActive = 0;
    function ajaxFinish(id) {
        $("#" + id).remove();
        ajaxActive--;
        $("#nav-loading-count").text(ajaxActive);
        if (!ajaxActive) $("#nav-loading").hide();
    }
    function ajaxWrap(label, obj) {
        // incrementing ID on each call
        var id = "nav-loading-" + (ajaxCount++);
        $("#nav-loading-list").append($("<li/>").attr("id", id).append($("<a/>").text(label)));
        // show loading dropdown
        ajaxActive++;
        $("#nav-loading-count").text(ajaxActive);
        $("#nav-loading").show();
        // override success/error callbacks
        var params = $.extend({}, obj, {
            success: function(data, stat, xhr) {
                ajaxFinish(id);
                if (obj.success) obj.success(data, stat, xhr);
            },
            error: function(data, stat, xhr) {
                ajaxFinish(id);
                if (obj.error) obj.error(data, stat, xhr);
            }
        });
        // make the request
        return $.ajax(params);
    }
    // check device status
    var devices = [];
    $("#devices code").each(function(i, code) {
        var device = $(code).text();
        // current and server devices already highlighted 
        if (device === "<?=$client;?>" || device === "<?=$server;?>") return;
        devices.push([device, code]);
    });
<?
    $pingSeq = true;
    $pingTime = false;
    if (array_key_exists("ping", $config)) {
        if (array_key_exists("sequential", $config["ping"])) $pingSeq = $config["ping"]["sequential"];
        if (array_key_exists("timeout", $config["ping"])) $pingTime = $local ? $config["ping"]["timeout"][0] : $config["ping"]["timeout"][1];
    }
?>
    // ping the next device in the list
    function devicePing() {
        var pair = devices.shift();
        // no more devices
        if (!pair) return;
        var abort = false;
        var timeout;
        var req = ajaxWrap("Ping: " + pair[0], {
            url: "res/ajax/ping.php",
            method: "post",
            data: {"device": pair[0]},
            success: function(data, stat, xhr) {
                // ping successful
                $(pair[1]).closest("tr").addClass("info");
                if (timeout) clearTimeout(timeout);
                devicePing();
            },
            error: function(xhr, stat, err) {
                // ping timed out (408)
                $(pair[1]).closest("tr").addClass("danger");
                if (!abort) devicePing();
            }
        });
<?
    if ($pingTime) {
?>
        // give up after unlikely to hear back
        timeout = setTimeout(function() {
            abort = true;
            req.abort();
            devicePing();
        }, <?=$pingTime?>);
<?
    }
?>
    }
<?
    if ($pingSeq) {
?>
    devicePing();
<?
    } else {
?>
    for (var i = devices.length; i > 0; i--) devicePing();
<?
    }
?>
    // list running processes
    var processesProgress = progressBar("#services");
    $("#processes").append(processesProgress);
    ajaxWrap("Processes", {
        url: "res/ajax/ps.php",
        success: function(data, stat, xhr) {
            var table = $("<table/>").addClass("table table-bordered table-condensed");
            table.append($("<thead/>").append($("<tr/>").append($.map(["ID", "User", "Name", "Description"], function(label) {
                return $("<th/>").text(label);
            }))));
            var raw = data.split("\n");
            // [pid, user, name, cmd]
            for (var i in raw) {
                if (!raw[i]) continue;
                var params = raw[i].split("//");
                var tr = $("<tr/>");
                var colour = "";
                switch (params[1]) {
                    case "root":
                        colour = "warning";
                        break;
                    case "<?=$user;?>":
                        colour = "info";
                        break;
                }
                tr.addClass(colour);
                for (var j in params) {
                    if (j == 3) {
                        tr.append($("<td/>").append($("<code/>").text(params[j])));
                    } else {
                        tr.append($("<td/>").addClass(colour ? "text-" + colour : "").text(params[j]));
                    }
                }
                table.append(tr);
            }
            processesProgress.replaceWith(table);
        },
        error: function(xhr, stat, err) {
            processesProgress.replaceWith(alertBar("danger", "Unable to query a list of processes."));
        }
    });
<?
    if (array_key_exists("services", $config) && in_array($config["services"], array("debian", "arch"))) {
?>
    // check service status
    var servicesProgress = progressBar("#services");
    $("#services").append(servicesProgress);
    ajaxWrap("Services", {
        url: "res/ajax/services.php",
        success: function(data, stat, xhr) {
            var table = $("<table/>").addClass("table table-bordered table-condensed");
<?
        switch ($config["services"]) {
            case "debian":
?>
            // lines of " [ x ]  y" where x is +/-, y is name
            var raw = data.split("\n");
            for (var i in raw) {
                if (!raw[i]) continue;
                var tr = $("<tr/>");
                // "+" if running, "-" if not, "?" if unknown
                var ind = raw[i].charAt(3);
                var colour = (ind === "+" ? "success" : (ind === "-" ? "warning" : ""));
                if (colour) tr.addClass(colour);
                tr.append($("<td/>").addClass(colour ? "text-" + colour : "").text(raw[i].substr(8)));
                table.append(tr);
            }
<?
                break;
            case "arch":
?>
            // lines of "* x   y   z" where * indicates down, x is name, y is load, z is active
            var raw = data.split("\n");
            raw.shift();
            for (var i in raw) {
                var parts = raw[i].substr(2).split(/\s+/, 4);
                var tr = $("<tr/>");
                var colour = "success";
                if (parts[1] !== "loaded") colour = "danger";
                else if (parts[2] === "inactive") colour = "warning";
                tr.addClass(colour);
                tr.append($("<td/>").addClass(colour ? "text-" + colour : "").text(parts[0]));
                table.append(tr);
            }
<?
                break;
        }
?>
            servicesProgress.replaceWith(table);
        },
        error: function(xhr, stat, err) {
            servicesProgress.replaceWith(alertBar("danger", "Unable to query status of services."));
        }
    });
<?
    }
?>
    // async logout button
    $("#logout").click(function(e) {
        e.preventDefault();
        $("#logout").text("Loading...").parent().addClass("active");
        $.ajax({
            url: "res/ajax/login.php?logout",
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
        // default to first shortcut place
        var addr = $("#location-dir").val();
        if (!addr) addr = "<?=key($config["places"])?>";
        ajaxWrap("Files: list " + addr, {
            url: "res/ajax/files.php",
            method: "post",
            // location normalisation handled by files.php
            data: {"dir": addr},
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
                            $("#files-display-content").empty().append(progressBar());
                            $("#files-display").modal("show");
                            ajaxWrap("Files: preview " + file[0], {
                                url: "res/ajax/files.php",
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
                                            root = $("<img/>").attr("src", "res/ajax/files.php?key=" + data);
                                            break;
                                        case "audio":
                                            var source = $("<source/>").attr("src", "res/ajax/files.php?key=" + data).attr("type", file[2]);
                                            root = $("<audio/>").attr("controls", "").append(source);
                                            break;
                                        case "video":
                                            var source = $("<source/>").attr("src", "res/ajax/files.php?key=" + data).attr("type", file[2]);
                                            root = $("<video/>").attr("controls", "").append(source);
                                            break;
                                    }
                                    $("#files-display-content").empty().append(root);
                                },
                                error: function(xhr, stat, err) {
                                    var colour = "danger";
                                    var content = "Unable to fetch file content.";
                                    switch (xhr.status) {
                                        // file type can't be previewed
                                        case 400:
                                            content = "File type <code>" + file[2] + "</code> cannot be previewed.";
                                            break;
                                        // file can't be accessed by server user
                                        case 401:
                                            content = "File not accessible to user <code><?=$user;?></code>.";
                                            break;
                                        // file doesn't exist
                                        case 409:
                                            colour = "warning";
                                            content = "File no longer exists.";
                                            break;
                                    }
                                    $("#files-display-content").empty().append(alertBar(colour, content));
                                }
                            });
                        });
                    }
                });
                if (access !== "w") $("#location-actions").prop("disabled", true);
                // no files in folder
                if ($("#files-list").is(":empty")) {
                    var colour = "info";
                    var content = "Nothing in this folder.";
                    if (!access) {
                        colour = "danger";
                        content = "Folder not accessible to user <code><?=$user;?></code>.";
                    }
                    $("#files-list").append($("<div/>").addClass("col-xs-12").append(alertBar(colour, content)));
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
                    var message = alertBar("danger", "Failed to query files (" + xhr.status + " " + err + ").");
                    $("#files-list").prepend($("<div/>").addClass("col-xs-12").append(message));
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
        $("#files-newfolder-submit").prop("disabled", true);
        ajaxWrap("Files: fetch " + path, {
            url: "res/ajax/files.php",
            method: "post",
            data: {
                "dir": path,
                "newfolder": $("#files-newfolder-name").val()
            },
            success: function(data, stat, xhr) {
                $("#files-newfolder-name, #files-newfolder-submit").prop("disabled", false);
                $("#files-newfolder").on("hidden.bs.modal", function(e) {
                    $("#location-dir").val(path);
                    $("#location-submit").click();
                }).modal("hide");
            },
            error: function(xhr, stat, err) {
                window.x = xhr;
                $("#files-newfolder-name").prop("disabled", false).parent().addClass("has-error");
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
                    $("#files-newfolder-submit").prop("disabled", false);
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
                ajaxWrap("Files: upload " + file.name, {
                    url: "res/ajax/files.php",
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
    var tabs = $.makeArray($(".nav-tab").map(function(i, tab) {
        return tab.id.substr(4);
    }));
    var infoTabs = $.makeArray($("#page-info .nav a").map(function(i, tab) {
        return tab.id.substr(9);
    }));
    $(".nav-tab").click(function(e) {
        $(".nav-tab").parent().removeClass("active");
        $(".page").hide();
        $(this).parent().addClass("active");
        $("#page-" + this.id.substr(4)).show();
    });
    $("#nav-info").click(function(e) {
        e.preventDefault();
        skipHash = true;
        location.hash = $("#page-info .nav li.active a").attr("href");
    });
    $("#page-info .nav li a").click(function(e) {
        skipHash = true;
        location.hash = $(this).attr("href");
    });
    var skipHash = false;
    var hashChange = function() {
        if (skipHash) {
            skipHash = false;
            return;
        }
        var tab;
        if (location.hash) {
            var sel = location.hash.substr(1).split("/");
            if (tabs.indexOf(sel[0]) > -1) tab = sel[0];
            if (tab === "files" && !loading) $("#location-submit").click();
            if (tab === "info") {
                var infoTab = $("#page-info .nav li.active a").attr("id").substr(9);
                if (infoTabs.indexOf(sel[1]) > -1) infoTab = sel[1];
                else if (!$("#info-nav-" + infoTab).hasClass("active")) $("#info-nav-" + infoTab).click();
            }
        }
        if (!tab) {
            tab = tabs[0];
            skipHash = true;
            location.hash = "#" + tab;
        }
        if (!$("#nav-" + tab).hasClass("active")) $("#nav-" + tab).click();
    };
    $(window).on("hashchange", hashChange);
    hashChange();
<?
} else {
?>
    $.ajax({
        url: "res/ajax/ip.php",
        success: function(data, stat, xhr) {
            $("#ip").text(data);
            if (data === "<?=$client;?>") $("#ip-warning").show();
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
            url: "res/ajax/login.php",
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
    $("#contact").on("show.bs.modal", function(e) {
        $("#contact-alert").remove();
    }).on("shown.bs.modal", function(e) {
        $("#contact-name").focus();
    }).on("hidden.bs.modal", function(e) {
        $("#contact-name, #contact-email, #contact-comments").val("").prop("disabled", false);
        $("#contact-submit").prop("disabled", false);
    });
    $("#contact-submit").on("click", function(e) {
        $("#contact-name, #contact-email, #contact-comments, #contact-submit").prop("disabled", true).parent().removeClass("has-error");
        $.ajax({
            url: "res/ajax/contact.php",
            method: "post",
            data: {
                "name": $("#contact-name").val(),
                "email": $("#contact-email").val(),
                "comments": $("#contact-comments").val()
            },
            success: function(data, stat, xhr) {
                $("#contact-para").before(alertBar("success", "Your message has been sent.").attr("id", "contact-alert"));
                $("#contact").modal("hide");
            },
            error: function(xhr, stat, err) {
                $("#contact-name, #contact-email, #contact-comments").prop("disabled", false).parent().addClass("has-error");
                setTimeout(function() {
                    $("#contact-name, #contact-email, #contact-comments").parent().removeClass("has-error");
                }, 150);
                setTimeout(function() {
                    $("#contact-name, #contact-email, #contact-comments").parent().addClass("has-error");
                }, 300);
                setTimeout(function() {
                    $("#contact-name, #contact-email, #contact-comments").parent().removeClass("has-error");
                }, 450);
                setTimeout(function() {
                    $("#contact-name").focus();
                    $("#contact-submit").prop("disabled", false);
                }, 600);
            },
        });
        e.preventDefault();
    });
<?
}
?>
});
