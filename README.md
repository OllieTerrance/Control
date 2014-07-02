Introduction
============

Cream (as in the Sonic the Hedgehog character) is a custom status page used on my Raspberry Pi web server.  It allows viewing the status of running services, connected devices, files and more.


Running from source
===================

This project requires the following libraries:

* [Bootstrap](http://getbootstrap.com)
* [Font Awesome](http://fontawesome.io)
* [jQuery](http://jquery.com)

Batteries are not included - the CSS and JavaScript files need to be placed in a `lib` folder with appropriate `css`, `js` and `fonts` subfolders (check `index.php` for where files are linked to).


Data file
=========

The `res/php/data.php` file holds lists of devices and IP addresses, media devices, and common places.  It should look like the following:

```php
<?
$devices = array(
    "IP address" => array(array("device name", "icon"), /* ... */),
    // ...
);
$media = array(
    array("device name", "description", "icon"),
    // ...
);
$places = array(
    "directory path" => "icon",
    // ...
);
```

For devices and media, icons are PNGs, referenced by name (minus extension), and stored in `res/ico`.  Place icons come from Font Awesome, leaving off the `fa-` prefix.

Devices is required, the rest are optional (not specifying them will hide them from view).  The first device listed should be the server Cream is running on.  Multiple device names can share an IP address (e.g. a dual-boot computer), and will be displayed together.
