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

The `data.php` file holds a list of devices and IP addresses.  It should look like the following:

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
```

Multiple device names can share an IP address (e.g. a dual-boot computer), and will be displayed together.
