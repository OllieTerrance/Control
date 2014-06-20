<?
header("content-type: application/javascript");
$ip = $_SERVER["REMOTE_ADDR"];
session_start();
$local = in_array($_SERVER["HTTP_HOST"], array("cream", "192.168.1.100"));
$remote = array_key_exists("login", $_SESSION);
$access = $local || $remote;
?>$(document).ready(function() {
<?
if ($access) {
?>
    $.ajax({
        url: "services.php",
        success: function(data, stat, xhr) {
            var table = $("<table/>").attr("id", "services").addClass("table table-bordered table-condensed");
            var raw = data.split("\n");
            for (var i in raw) {
                if (!raw[i]) continue;
                var tr = $("<tr/>");
                var colour = "";
                switch (raw[i].charAt(3)) {
                    case "+":
                        colour = "success";
                        break;
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
            $("#services").removeClass("alert-warning").addClass("alert-danger").text("Unable to query server status.");
        }
    });
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
    var history = [];
    var pos = -1;
    var back = false, forward = false;
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
    $("#location-submit").click(function(e) {
        $(".location-ctrl").prop("disabled", true);
        if (!$("#location-dir").val()) $("#location-dir").val("/");
        $.ajax({
            url: "browser.php",
            method: "post",
            data: {"dir": $("#location-dir").val()},
            success: function(data, stat, xhr) {
                var files = data.split("\n");
                path = files.splice(0, 1)[0];
                if (back) {
                    pos--;
                    back = false;
                } else if (forward) {
                    pos++;
                    forward = false;
                } else {
                    history = history.slice(0, pos + 1);
                    if (history[history.length - 1] !== path) {
                        history.push(path);
                        pos++;
                    }
                }
                console.log(history, pos);
                $("#location-dir").val(path).prop("disabled", false);
                $(".location-ctrl").prop("disabled", false);
                $("#files-list").empty();
                for (var i in files) {
                    if (!files[i]) continue;
                    var root = $("<div/>").addClass("col-lg-2 col-md-3 col-sm-4 col-xs-6");
                    var file = files[i].split("//");
                    var title = $("<h3/>").addClass("panel-title")
                                    .append($("<img/>").attr("src", "res/ico/" + file[1] + ".png"))
                                    .append(" " + file[0]);
                    var head = $("<div/>").addClass("panel-heading").append(title);
                    var body = $("<div/>").addClass("panel-body").html(file[2] + "<br/>" + file[3]);
                    var foot = $("<div/>").addClass("panel-footer small")
                                    .append($("<span/>").append(file[4] + ":" + file[5]))
                                    .append($("<span/>").addClass("pull-right").append(file[6]));
                    $("#files-list").append(root.append($("<div/>").addClass("panel panel-default").append(head).append(body).append(foot)));
                }
                $("#location-dir").focus();
            },
            error: function(xhr, stat, err) {
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
            },
        });
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
