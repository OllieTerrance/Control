<?
// central configration file - rename/copy to config.php to use
$config = array(
    // devices (required): list of all known devices, with static IPs, on the network (ico/ icons)
    "devices" => array(
        // ip address => array(array(label[, icon])[, ...])
        "192.168.1.100" => array(array("Cream", "cream")),
        "192.168.1.101" => array(array("Laptop (Windows)", "windows"), array("Laptop (Ubuntu)", "linux")),
        "192.168.1.102" => array(array("Desktop", "desktop")),
        "192.168.1.254" => array(array("Router", "router"))
    ),
    // media: other resources, e.g. flash drives, external hard disks (ico/ icons)
    "media" => array(
        // array(label, description[, icon])
        array("Data", "310GB data drive @ Desktop", "data"),
        array("Backup", "1.5TB external HDD", "backup")
    ),
    // places: directory shortcut list for the file browser (Font Awesome icons)
    "places" => array(
        // path => label
        "/" => "desktop",
        "/home/user" => "home",
        "/var/www" => "globe",
        "/media/hdd" => "hdd-o"
    ),
    // messages: path of server-writable file to store received messages
    "messages" => "/var/data/cream_messages.txt"
);
