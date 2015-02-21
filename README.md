CMSMonolog
============

This project embeds [Monolog 1.10.x-dev](https://github.com/Seldaek/monolog) into CMS Made Simple.  From the Monolog website:

> Monolog sends your logs to files, sockets, inboxes, databases and various web services. See the complete list of handlers below. Special handlers allow you to build advanced logging strategies.

> This library implements the PSR-3 interface that you can type-hint against in your own libraries to keep a maximum of interoperability. You can also use it in your applications to make sure you can always use another compatible logger at a later time.

For support, please submit [an issue](https://github.com/drmikecrowe/cmsms_monolog/issues) for assistance.

TARGET AUDIENCE
================

This module will benefit developers.  Standard CMSMS installations will NOT benefit from this, as standard CMSMS logs will go to Monolog.

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

### Method #1 (local instance):

        use Monolog\Logger;

        /** @var CMSMonolog $mlog */
        $mlog = cmsms()->GetModuleInstance("CMSMonolog");
        if ( $mlog ) {
            $level = (IS_DEV ? Logger::DEBUG : Logger::INFO);
            $this->generalLogger = $mlog->GetDatabaseLogger($this->GetName(),$level);
        }

        (snip)

        $this->generalLogger->addDebug(__METHOD__,"This is my debug logging message");
        or
        $this->generalLogger->addWarning(__METHOD__,"This is my warning logging message");

### Method #2 (Static Class):

        MLog::d($channel,$message,$context);          // add a debug message
        or
        MLog::i("General","This is my info logging message,__METHOD__);

### Method #3 (Replace CMSMS audit function):

        MLog::audit($uid, $this->GetName(), "User $username/$uid was created from referral $sponsorname");


SCREENSHOTS
-------------------

### Settings Tab
![Settings Tab](http://i.imgur.com/ao92TAr.png)

### Log Viewers
![Log Viewer](http://i.imgur.com/gjVxm1e.png)




License
-------

Monolog is licensed under the MIT License - see the `LICENSE` file for details

