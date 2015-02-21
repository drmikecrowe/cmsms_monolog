CMSMonolog
============

This project embeds [Monolog](https://github.com/Seldaek/monolog) into CMS Made Simple.  From the Monolog website:

> Monolog sends your logs to files, sockets, inboxes, databases and various web services. See the complete list of handlers below. Special handlers allow you to build advanced logging strategies.

> This library implements the PSR-3 interface that you can type-hint against in your own libraries to keep a maximum of interoperability. You can also use it in your applications to make sure you can always use another compatible logger at a later time.

For support, please submit [an issue](https://github.com/drmikecrowe/cmsms_monolog/issues) for assistance.

TARGET AUDIENCE
================

This module will benefit developers.  Standard CMSMS installations will NOT benefit from this, as standard CMSMS logs will not go to Monolog.

CAUTION
========

The paint is still wet on this one, please test thoroughly before using on a live site

Feedback/constructive criticism welcome

FEATURES
-------------

* Implements standard file logging to tmp/cmsms-monolog-2014-07-14.log (for example)
* Also logs to database table (cms_monolog).
* Multiple log levels supported (DEBUG, INFO, WARNING, ERROR, CRITICAL, ALERT, EMERGENCY)
* Logging level (to file/database)
* Can email warning/error (and above) messages to admin
* Error exceptions are logged using [jTraceEx enhanced backtrace function](http://www.php.net/manual/en/exception.gettraceasstring.php#114980)
* Database logs may be dynamically viewed/filtered
 * Log viewer uses [DataTables](http://datatables.net/) and ajax to display/filter logs logs
 * Logs may be filtered using MySQL regex
 * IP address of current user appended

USAGE
--------

### Static Methods:

        MLog::d(channel, debug message, variable1, variable2, ...);
    example
        MLog::i(__METHOD__,"This is my info logging message",$params);



SCREENSHOTS
-------------------

### Settings Tab
![Settings Tab](http://i.imgur.com/ao92TAr.png)

### Log Viewers
![Log Viewer](http://i.imgur.com/gjVxm1e.png)




License
-------

Monolog is licensed under the MIT License - see the `LICENSE` file for details

