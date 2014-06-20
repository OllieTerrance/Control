Introduction
============

Cream (as in the Sonic the Hedgehog character) is a custom status page used on my Raspberry Pi web server.  It allows viewing the status of running services, connected devices, files and more.


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
