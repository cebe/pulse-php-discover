pulse-php-discover
==================

A PHP implementation of the [pulse/syncthing](https://ind.ie/pulse) [cluster discovery protocol](https://github.com/syncthing/syncthing/blob/master/protocol/DISCOVERY.md).

**Current status is work in progress, also if it already works in simple local environment it is
untested for other purpose, may fail and descrtoy data, so use with care!**

Requirements
------------

PHP 5.4 or higher.

Installation
------------

Using [Composer](http://getcomposer.org):

    composer require cebe/pulse-php-discover

Usage
-----

Run multiple of these and watch them connect to each other:

```
php test.php
```

If you have pulse installed in your network it will also take part in the party :)

This is only the discovery protocol so nothing is going to be shared between the nodes, the
only thing that happens is that they know about each other.

License
-------

GPLv3, see [LICENSE](LICENSE) file for more details.
