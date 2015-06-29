Introduction
============

Control is a custom status page used on my Raspberry Pi web server.  It allows viewing the status of running services, connected devices, files and more.


Running from source
===================

This project requires the following libraries:

* [Bootstrap](http://getbootstrap.com)
* [Font Awesome](http://fontawesome.io)
* [jQuery](http://jquery.com)

Batteries are not included - the CSS and JavaScript files need to be placed in a `lib` folder with appropriate `css`, `js` and `fonts` subfolders (check `index.php` for where files are linked to).


Configuration file
==================

The `res/php/includes/config.php` file holds lists of devices and IP addresses, media devices, common places and more.  You can use the `config.example.php` file as a starting point.

Devices is required, the rest are optional (not specifying will disable the relevant component).
