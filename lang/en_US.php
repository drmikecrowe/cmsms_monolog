<?php
$lang['friendlyname'] = 'CMS Monolog';
$lang['postinstall'] = 'IMPORTANT: See help for Monolog installation instructions';
$lang['postuninstall'] = 'CMS Monolog has now been uninstalled.';
$lang['really_uninstall'] = 'Really? Are you sure
you want to unsinstall this fine module?';
$lang['uninstalled'] = 'Module Uninstalled.';
$lang['installed'] = 'Module version %s installed.';
$lang['upgraded'] = 'Module upgraded to version %s.';
$lang['moddescription'] = 'Integrates https://github.com/Seldaek/monolog into CMS Made Simple';

$lang['error'] = 'Error!';
$land['admin_title'] = 'CMS Monolog Admin Panel';
$lang['admindescription'] = 'Configure Monolog';
$lang['accessdenied'] = 'Access Denied. Please check your permissions.';
$lang['Log'] = 'Log';
$lang['Settings'] = 'Settings';
$lang['title_logbrowser'] = 'Log Browser';
$lang['title_settings'] = 'Settings';


$lang['changelog'] = '<ul>
<li>0.3.0 - 21 Feb 2015
    <ul>
    <li>Upgraded to latest datatables</li>
    <li>Now using bower for libraries</li>
    <li>Adding clear filter/refresh functions to main display</li>
    <li>Adding clear-log function in settings tab</li>
    <li>Adding support for Monolog being installed by composer for project (vs. local to this library)</li>
    <li>Including support for database port in config</li>
    <li>New MLog functions (see help)</li>
    <li>All MLog functions now take a variable number of parameters (see help for example)</li>
    <li>Better backtrace support added</li>
    </ul>
</li>
<li>Version 0.2.0 - 4 July 2014. Database Logging release.</li>
</ul>';
$lang['help'] = file_get_contents(__DIR__ . '/help.html');
