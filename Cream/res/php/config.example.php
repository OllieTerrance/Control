<?
// central configuration file - rename/copy to config.php to use
$config = array(
    // hostnames (required): list of hostnames to treat as local
    "hostnames" => array("localhost", "cream"),
    // devices (required): list of all known devices, with static IPs, on the network (ico/ icons)
    "devices" => array(
        // ip address => array(array(label[, icon])[, ...])
        "192.168.1.100" => array(array("Cream", "cream")),
        "192.168.1.101" => array(array("Laptop (Windows)", "windows"), array("Laptop (Ubuntu)", "linux")),
        "192.168.1.102" => array(array("Desktop", "desktop")),
        "192.168.1.254" => array(array("Router", "router"))
    ),
    "ping" => array(
        // ping sequential: wait for each ping to complete before making another request
        // if false, will slow down other AJAX requests (best to avoid using timeout with sequential off)
        "sequential" => true,
        // ping timeout: time for JavaScript to wait for a ping request (when accessing local, remote)
        "timeout" => array(3000, 5000)
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
    // services: service platform to query - supports "debian" (service) or "arch" (systemctl)
    "services" => "debian",
    // messages: path of server-writable file to store received messages
    "messages" => "/var/data/cream_messages.txt"
);
