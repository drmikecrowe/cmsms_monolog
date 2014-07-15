<?php

/**
 *  BASED ON:  https://github.com/pricemaker/photon/master/lib/Log.php
 *
 *	Provides singleton convenience methods for the monolog library
 *
 *	Internally tracks Monolog channels so they can be accessed through
 *	a singleton function. By default sets a StreamHandler to the file
 *	specified in the `wave.php` configuration file at the default level.
 *
 *	@author Patrick patrick@hindmar.sh
 **/

use \Monolog\Logger,
    \Monolog\Handler\AbstractHandler,
    \Monolog\Handler\StreamHandler,
    \Monolog\Formatter\LineFormatter;


class MLog extends Logger {

    private static $default_level = false;
    private static $email_level = false;
    protected static $channels = array();

    /**
     * Better trace -- from http://www.php.net/manual/en/exception.gettraceasstring.php#114980
     *
     * @param $e Exection to print trace for
     * @param null $seen
     * @return array|string
     */

    public static function jTraceEx($e, $seen=null) {
        $starter = $seen ? 'Caused by: ' : '';
        $result = array();
        if (!$seen) $seen = array();
        $trace  = $e->getTrace();
        $prev   = $e->getPrevious();
        $result[] = sprintf('%s%s: %s', $starter, get_class($e), $e->getMessage());
        $file = $e->getFile();
        $line = $e->getLine();
        while (true) {
            $current = "$file:$line";
            if (is_array($seen) && in_array($current, $seen)) {
                $result[] = sprintf(' ... %d more', count($trace)+1);
                break;
            }
            $result[] = sprintf(' at %s%s%s(%s%s%s)',
                count($trace) && array_key_exists('class', $trace[0]) ? str_replace('\\', '.', $trace[0]['class']) : '',
                count($trace) && array_key_exists('class', $trace[0]) && array_key_exists('function', $trace[0]) ? '.' : '',
                count($trace) && array_key_exists('function', $trace[0]) ? str_replace('\\', '.', $trace[0]['function']) : '(main)',
                $line === null ? $file : basename($file),
                $line === null ? '' : ':',
                $line === null ? '' : $line);
            if (is_array($seen))
                $seen[] = "$file:$line";
            if (!count($trace))
                break;
            $file = array_key_exists('file', $trace[0]) ? $trace[0]['file'] : 'Unknown Source';
            $line = array_key_exists('file', $trace[0]) && array_key_exists('line', $trace[0]) && $trace[0]['line'] ? $trace[0]['line'] : null;
            array_shift($trace);
        }
        $result = join("\n", $result);
        if ($prev)
            $result  .= "\n" . jTraceEx($prev, $seen);

        return $result;
    }

    /**
     *	@return int The default log level
     **/
    public static function getDefaultLevel(){
        if(!static::$default_level) {
            $ML = cmsms()->GetModuleInstance("CMSMonolog");
            if ( $ML ) {
                static::$default_level = $ML->GetPreference("default_level");
                if ( $ML->GetPreference("email_on_warning") ) {
                    static::$email_level = Logger::WARNING;
                } else if ( $ML->GetPreference("email_on_warning") ) {
                    static::$email_level = Logger::ERROR;
                } else {
                    static::$email_level = 1000;
                }
            } else {
                static::$default_level = Logger::WARNING;
            }
        }
        return static::$default_level;
    }

    /**
     * Create a new channel with the specified Handler
     *
     * @param $channel
     * @param bool $to_database If this should be the database handler (cms_monolog table) or to the rotating log handler
     * @return mixed
     */
    public static function createChannel($channel, $to_database=false, $default_level = -1){
        /** @var CMSMonolog $ML */
        $ML = cmsms()->GetModuleInstance("CMSMonolog");
        if ( $ML ) {
            self::getDefaultLevel();
            if ( $to_database ) {
                static::$channels[$channel] = $ML->GetDatabaseLogger($channel,self::getDefaultLevel());
            } else {
                static::$channels[$channel] = $ML->GetRotatingLogger($channel,self::getDefaultLevel());
            }
        }
        return static::$channels[$channel];
    }

    /**
     * @param string $name
     * @param bool $create Create the channel if it does not exist (default=true)
     *
     * @return  \Monolog\Logger A Logger instance for the given channel or `null` if not found
     */
    public static function getChannel($name, $create = true){
        if(!isset(static::$channels[$name])){
            if($create === true) return static::createChannel($name);
            else return null;
        }
        return static::$channels[$name];
    }

    /**
     * Set a Logger instance for a channel
     *
     * @param string $name The channel name to set to
     * @param \Monolog\Logger $instance
     *
     * @return \Monolog\Logger
     */
    public static function setChannel($name, Logger $instance){
        return static::$channels[$name] = $instance;
    }

    private static function SendEmail($email, $body) {
        $emails = explode(",", $email);
        $cmsmailer = cmsms()->GetModuleInstance('CMSMailer');
        if ( !$cmsmailer ) { return; }
        $cmsmailer->reset();
        foreach ($emails as $email2) {
            $cmsmailer->AddAddress($email2);
        }
        $cmsmailer->SetBody($body);
        $cmsmailer->SetSubject("Error Occurred in ".$_SERVER['HTTP_HOST']);
        $cmsmailer->IsHTML(stripos($body, "<p") !== FALSE);
        $cmsmailer->Send();
        $cmsmailer->reset();
    }

    private static function EmailAdmin($message) {
        /** @var CMSMonolog $ML */
        $ML = cmsms()->GetModuleInstance("CMSMonolog");
        $email = $ML->GetPreference("admin_emails",null);
        if ( $email ) {
            $tmp = array_slice(debug_backtrace(),2);
            $arrTrace = array();
            foreach($tmp as $tmp2) {
                if (isset($tmp2['file']))	$arrTrace[] = $tmp2['file'].':'.$tmp2['line'];
            }
            $backTrace = "\nBacktrace: ".(print_r($arrTrace,true));

            $servera = array();
            foreach ( array('REQUEST_URI','DOCUMENT_ROOT','HTTP_HOST','REMOTE_ADDR') as $k ) {
                $servera[$k] = $_SERVER[$k];
            }
            $server = "Server Variables: ".(print_r($servera,1))."\n\n";
            self::SendEmail($email, "$message\n\n$backTrace\n\n$server\n");
        }
    }

    public static function quick($channel, $message, $level = Logger::INFO){
        $channel = static::getChannel($channel);
        $res = $channel->addRecord($level, $message);
        return $res;
    }

    /**
     *	A shorthand for writing a message to a given channel
     *
     *	@param string $channel The channel to write to
     *	@param string $message The message to write
     *	@param int $level The level of the message (debug, info, notice, warning, error, critical)
     *
     *	@return Bool Whether the message has been written
     **/
    public static function write($channel, $message, $context = "", $level = Logger::INFO){
        $channel = static::getChannel($channel);
        if ( !is_array($context) ) {
            if ( $context == "" )
                $context = array();
            else
                $context = array($context);
        }
        if ( count($context)==1 && $context[0]=="" ) $context=array();
        $res = $channel->addRecord($level, $message, $context);
        if ( $level >= static::$email_level ) {
            self::EmailAdmin($message);
        }
        return $res;
    }

    /**
     * Static helper function to log at specific levels
     *
     * @param $channel
     * @param $message
     */
    public static function d($channel,$message,$context="") { self::write($channel,$message,$context,Logger::DEBUG);}
    public static function i($channel,$message,$context="") { self::write($channel,$message,$context,Logger::INFO);}
    public static function w($channel,$message,$context="") { self::write($channel,$message,$context,Logger::WARNING);}
    public static function e($channel,$message,$context="") { self::write($channel,$message,$context,Logger::ERROR);}
    public static function c($channel,$message,$context="") { self::write($channel,$message,$context,Logger::CRITICAL);}
    public static function a($channel,$message,$context="") { self::write($channel,$message,$context,Logger::ALERT);}

    public static function audit($itemid, $itemname, $action) {
        $username = $userid = "";
        if (isset($_SESSION["cms_admin_user_id"]))
        {
            $userid = $_SESSION["cms_admin_user_id"];
        }
        else
        {
            if (isset($_SESSION['login_user_id']))
            {
                $userid = $_SESSION['login_user_id'];
                $username = $_SESSION['login_user_username'];
            }
        }

        if (isset($_SESSION["cms_admin_username"]))
        {
            $username = $_SESSION["cms_admin_username"];
        }
        MLog::i("CMSMS",$action,array("itemid"=>$itemid,"itemname"=>$itemname,"username"=>$username,"userid"=>$userid));
    }

}

//https://gist.githubusercontent.com/JCook21/3824584/raw/gistfile1.php
/**
 * Create a closure to handle uncaught exceptions
 */
set_exception_handler($handler = function(Exception $e) use (&$handler) {
    $message = sprintf(
        'Uncaught exception of type %s thrown in file %s at line %s%s.',
        get_class($e),
        $e->getFile(),
        $e->getLine(),
        $e->getMessage() ? sprintf(' with message "%s"', $e->getMessage()) : ''
    );
    MLog::e("ERROR",MLog::jTraceEx($e));
    /**
     * If there was a previous nested exception call this function recursively
     * to log that too.
     */
    if ($prev = $e->getPrevious()) {
        $handler($prev);
    }
});

/**
 * Set a custom error handler to make sure that errors are logged to Graylog.
 * Allows any non-fatal errors to be logged to the Graylog2 server.
 */
set_error_handler(function($errno, $errstr, $errfile, $errline, array $errcontext){
    $message = 'Error of level ';
    switch ($errno) {
        case E_USER_ERROR:
            $message .= 'E_USER_ERROR';
            break;
        case E_USER_WARNING:
            $message .= 'E_USER_WARNING';
            break;
        case E_USER_NOTICE:
            $message .= 'E_USER_NOTICE';
            break;
        case E_STRICT:
            $message .= 'E_STRICT';
            break;
        case E_RECOVERABLE_ERROR:
            $message .= 'E_RECOVERABLE_ERROR';
            break;
        case E_DEPRECATED:
            $message .= 'E_DEPRECATED';
            break;
        case E_USER_DEPRECATED:
            $message .= 'E_USER_DEPRECATED';
            break;
        case E_NOTICE:
            $message .= 'E_NOTICE';
            break;
        case E_WARNING:
            $message .= 'E_WARNING';
            break;
        default:
            $message .= sprintf('Unknown error level, code of %d passed', $errno);
    }
    $message .= sprintf(
        '. Error message was "%s" in file %s at line %d.',
        $errstr,
        $errfile,
        $errline
    );
    MLog::quick("ERROR",sprintf("Error %d: %s in %s@%d",$errno, $errstr, $errfile, $errline));

    return true;//Returning false will mean that PHP's error handling mechanism will not be bypassed.
});

/**
 * This function will be called before the script exits.
 * This allows us to catch and log any fatal errors in the Graylog2 server.
 * This is needed as the set_error_handler function cannot be used to handle
 * any of the errors in the array below.
 */
register_shutdown_function(function(){
    $codes = array(
        1   => 'E_ERROR',
        4   => 'E_PARSE',
        16  => 'E_CORE_ERROR',
        32  => 'E_CORE_WARNING',
        64  => 'E_COMPILE_ERROR',
        128 => 'E_COMPILE_WARNING'
    );
    $error = error_get_last();
    if (is_array($error) && array_key_exists($error['type'], $codes)) {
        $message = sprintf(
            'Error of type %s raised in file %s at line %d with message "%s"',
            $codes[$error['type']],
            $error['file'],
            $error['line'],
            $error['message']
        );
        if (in_array($error['type'], array(32, 128))) {
            //These errors are warnings and should be logged at a lower level.
            MLog::i("ERROR",$message,"");
        } else {
            MLog::e("ERROR",$message,"");
        }
    }
});
if ( function_exists('override_function'))
    override_function('audit','$itemid, $itemname, $action','MLog::audit($itemid, $itemname, $action);');
