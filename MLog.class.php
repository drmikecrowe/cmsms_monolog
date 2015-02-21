<?php

/**
 *  BASED ON:  https://github.com/pricemaker/photon/master/lib/Log.php
 *
 *    Provides singleton convenience methods for the monolog library
 *
 *    Internally tracks Monolog channels so they can be accessed through
 *    a singleton function. By default sets a StreamHandler to the file
 *    specified in the `wave.php` configuration file at the default level.
 *
 * @author Patrick patrick@hindmar.sh
 **/

use \Monolog\Logger,
    \Monolog\Handler\AbstractHandler,
    \Monolog\Handler\StreamHandler,
    \Monolog\Formatter\LineFormatter;


class MLog
{

    private static $default_level = array();
    private static $email_level = false;
    protected static $channels = array();

    /**
     * Getting backtrace
     *
     * @param int $ignore ignore calls
     *
     * @return string
     */
    public static function getBacktrace($count = 3, $ignore = 2)
    {
        $trace = "";
        foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS) as $k => $v) {
            if ($k < $ignore) {
                continue;
            }
            try {
                $parts = explode('/', $v['file']);
                $file = array_pop($parts);

                // PRS.module.php(209): PRSInterface->ProcessOrderUpdated()
                // PRS.module.php/ProcessOrderUpdated()@209
                // PRS.module.php/PRSInterface->ProcessOrderUpdated()@209:
                $trace .= '#' . ($k - $ignore) . ' ' . $file . '(' . $v['line'] . '): ' . (isset($v['class']) ? $v['class'] . '->' : '') . $v['function'] . '()<br/>';
            } catch ( Exception $ex ) {}
            if ( --$count == 0 ) break;
        }

        return $trace;
    }

    /**
     * @doc http://www.leaseweblabs.com/2013/10/smart-alternative-phps-var_dump-function/
     * @param $variable -- the variable to print
     * @param int $strlen -- Maximum length to return (defaults to 1000)
     * @param int $width -- Maximum width (defaults to 25)
     * @param int $depth -- Maximum depth (defaults to 10)
     * @return string
     */
    public function var_debug($variable, $strlen = 15000, $width = 25, $depth = 2, $i = 0, &$objects = array())
    {
        $search = array("\0", "\a", "\b", "\f", "\t", "\v");
        $replace = array("\0", "\a", "\b", "\f", "\t", "\v");

        $string = '';

        switch (gettype($variable)) {
            case 'boolean':
                $string .= $variable ? 'true' : 'false';
                break;
            case 'integer':
                $string .= $variable;
                break;
            case 'double':
                $string .= $variable;
                break;
            case 'resource':
                $string .= '[resource]';
                break;
            case 'NULL':
                $string .= "null";
                break;
            case 'unknown type':
                $string .= '???';
                break;
            case 'string':
                $len = strlen($variable);
                $variable = str_replace($search, $replace, substr($variable, 0, $strlen), $count);
                $variable = substr($variable, 0, $strlen);
                if ($len < $strlen) {
                    $string .= $variable;
                } else {
                    $string .= 'string(' . $len . '): "' . $variable . '"...';
                }
                break;
            case 'array':
                $len = count($variable);
                if ($i >= $depth) {
                    $string .= 'array(' . $len . ') {...}';
                } else {
                    if (!$len) {
                        $string .= 'array(0) {}';
                    } else {
                        $keys = array_keys($variable);
                        $spaces = str_repeat(' ', $i * 2);
                        $string .= "array($len) " . '{';
                        $count = 0;
                        foreach ($keys as $key) {
                            if ($count == $width) {
                                $string .= "\n" . $spaces . "  ...";
                                break;
                            }
                            $string .= "\n" . $spaces . "  [$key] => ";
                            $string .= self::var_debug($variable[$key], $strlen, $width, $depth, $i + 1, $objects);
                            $count++;
                        }
                        $string .= "\n" . $spaces . '}';
                    }
                }
                break;
            case 'object':
                $id = array_search($variable, $objects, true);
                if ($id !== false) {
                    $string .= get_class($variable) . '#' . ($id + 1) . ' {...}';
                } else {
                    if ($i >= $depth) {
                        $string .= get_class($variable) . ' {...}';
                    } else {
                        $id = array_push($objects, $variable);
                        $array = (array)$variable;
                        $spaces = str_repeat(' ', $i * 2);
                        $string .= get_class($variable) . "#$id\n" . $spaces . '{';
                        $properties = array_keys($array);
                        foreach ($properties as $property) {
                            $name = str_replace("\0", ':', trim($property));
                            $string .= "\n" . $spaces . "  [$name] => ";
                            $string .= self::var_debug($array[$property], $strlen, $width, $depth, $i + 1, $objects);
                        }
                        $string .= "\n" . $spaces . '}';
                    }
                }
                break;
        }

        return $string;
    }

    /**
     * Better trace -- from http://www.php.net/manual/en/exception.gettraceasstring.php#114980
     *
     * @param $e Exection to print trace for
     * @param array $seen
     * @return array|string
     */

    public static function jTraceEx($e, $seen = null)
    {
        $starter = $seen ? 'Caused by: ' : '';
        $result = array();
        if (!$seen) {
            $seen = array();
        }
        $trace = $e->getTrace();
        $prev = $e->getPrevious();
        $result[] = sprintf('%s%s: %s', $starter, get_class($e), $e->getMessage());
        $file = $e->getFile();
        $line = $e->getLine();
        while (true) {
            $current = "$file:$line";
            if (is_array($seen) && in_array($current, $seen)) {
                $result[] = sprintf(' ... %d more', count($trace) + 1);
                break;
            }
            $result[] = sprintf(
                ' at %s%s%s(%s%s%s)',
                count($trace) && array_key_exists('class', $trace[0]) ? str_replace('\\', '.', $trace[0]['class']) : '',
                count($trace) && array_key_exists('class', $trace[0]) && array_key_exists(
                    'function',
                    $trace[0]
                ) ? '.' : '',
                count($trace) && array_key_exists('function', $trace[0]) ? str_replace(
                    '\\',
                    '.',
                    $trace[0]['function']
                ) : '(main)',
                $line === null ? $file : basename($file),
                $line === null ? '' : ':',
                $line === null ? '' : $line
            );
            if (is_array($seen)) {
                $seen[] = "$file:$line";
            }
            if (!count($trace)) {
                break;
            }
            $file = array_key_exists('file', $trace[0]) ? $trace[0]['file'] : 'Unknown Source';
            $line = array_key_exists('file', $trace[0]) && array_key_exists(
                'line',
                $trace[0]
            ) && $trace[0]['line'] ? $trace[0]['line'] : null;
            array_shift($trace);
        }
        $result = join("\n", $result);
        if ($prev) {
            $result .= "\n" . self::jTraceEx($prev, $seen);
        }

        return $result;
    }

    /**
     * @return int The default log level
     **/
    public static function getDefaultLevel($channel)
    {
        if (!array_key_exists($channel, static::$default_level)) {
            $ML = cmsms()->GetModuleInstance("CMSMonolog");
            if ($ML) {
                static::$default_level[$channel] = $ML->GetPreference("default_level");
                if ($ML->GetPreference("email_on_warning")) {
                    static::$email_level = Logger::WARNING;
                } else {
                    if ($ML->GetPreference("email_on_warning")) {
                        static::$email_level = Logger::ERROR;
                    } else {
                        static::$email_level = 1000;
                    }
                }
            } else {
                static::$default_level[$channel] = Logger::WARNING;
            }
        }
        return static::$default_level[$channel];
    }

    /**
     * Create a new channel with the specified Handler
     *
     * @param $channel
     * @param bool $to_database If this should be the database handler (cms_monolog table) or to the rotating log handler
     * @return mixed
     */
    public static function createChannel($channel, $default_level = -1)
    {
        /** @var CMSMonolog $ML */
        $ML = cmsms()->GetModuleInstance("CMSMonolog");
        if ($ML) {
            $to_database = $ML->GetPreference('use_database_default', true);
            if ($to_database) {
                static::$channels[$channel] = $ML->GetDatabaseLogger($channel, self::getDefaultLevel($channel));
            } else {
                static::$channels[$channel] = $ML->GetRotatingLogger($channel, self::getDefaultLevel($channel));
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
    public static function getChannel($name, $create = true)
    {
        if (!isset(static::$channels[$name])) {
            if ($create === true) {
                return static::createChannel(basename($name));
            } else {
                return self::e(__METHOD__, "Invalid channel $name provided");
            }
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
    public static function setChannel($name, Logger $instance)
    {
        return static::$channels[$name] = $instance;
    }

    private static function SendEmail($email, $body)
    {
        $emails = explode(",", $email);
        $cmsmailer = cmsms()->GetModuleInstance('CMSMailer');
        if (!$cmsmailer) {
            return;
        }
        $cmsmailer->reset();
        foreach ($emails as $email2) {
            $cmsmailer->AddAddress($email2);
        }
        $cmsmailer->SetBody($body);
        $cmsmailer->SetSubject("Error Occurred in " . $_SERVER['HTTP_HOST']);
        $cmsmailer->IsHTML(stripos($body, "<p") !== false);
        $cmsmailer->Send();
        $cmsmailer->reset();
    }

    private static function EmailAdmin($message)
    {
        /** @var CMSMonolog $ML */
        $ML = cmsms()->GetModuleInstance("CMSMonolog");
        $email = $ML->GetPreference("admin_emails", null);
        if ($email) {
            $tmp = array_slice(debug_backtrace(), 2);
            $arrTrace = array();
            foreach ($tmp as $tmp2) {
                if (isset($tmp2['file'])) {
                    $arrTrace[] = $tmp2['file'] . ':' . $tmp2['line'];
                }
            }
            $backTrace = "\nBacktrace: " . (print_r($arrTrace, true));

            $servera = array();
            foreach (array('REQUEST_URI', 'DOCUMENT_ROOT', 'HTTP_HOST', 'REMOTE_ADDR') as $k) {
                $servera[$k] = $_SERVER[$k];
            }
            $server = "Server Variables: " . (print_r($servera, 1)) . "\n\n";
            self::SendEmail($email, "$message\n\n$backTrace\n\n$server\n");
        }
    }

    public static function quick($channel, $message, $level = Logger::INFO)
    {
        $channel = static::getChannel($channel);
        $res = $channel->addRecord($level, $message);
        return $res;
    }

    /**
     *    A shorthand for writing a message to a given channel
     *
     * @param string $channelName The channel to write to
     * @param string $message The message to write
     * @param string|array $context Details of the context.  Converted to array
     * @param int $level The level of the message (debug, info, notice, warning, error, critical)
     *
     * @return Bool Whether the message has been written
     **/
    public static function write($channelName, $message, $context = "", $level = Logger::INFO)
    {
        $channel = static::getChannel($channelName);
        if (!is_array($context)) {
            if ($context == "") {
                $context = array();
            } else {
                $context = array($context);
            }
        }
        if (count($context) == 1 && $context[0] == "") {
            $context = array();
        }
        $text = self::var_debug($message);
        $res = $channel->addRecord($level, $text, $context);
        if ($level >= static::$email_level) {
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
    public static function d()
    {
        for ($message = "", $l = func_get_args(), $i = 1; $i < func_num_args(); $i++) {
            $message .= self::var_debug($l[$i]) . "\n";
        };
        self::write($l[0], $message.'<br/>'.self::getBacktrace(), Logger::DEBUG);
    }

    public static function i()
    {
        for ($message = "", $l = func_get_args(), $i = 1; $i < func_num_args(); $i++) {
            $message .= self::var_debug($l[$i]) . "\n";
        };
        self::write($l[0], $message.'<br/>'.self::getBacktrace(), Logger::INFO);
    }

    public static function w()
    {
        for ($message = "", $l = func_get_args(), $i = 1; $i < func_num_args(); $i++) {
            $message .= self::var_debug($l[$i]) . "\n";
        };
        self::write($l[0], $message.'<br/>'.self::getBacktrace(), Logger::WARNING);
    }

    public static function e()
    {
        for ($message = "", $l = func_get_args(), $i = 1; $i < func_num_args(); $i++) {
            $message .= self::var_debug($l[$i]) . "\n";
        };
        self::write($l[0], $message.'<br/>'.self::getBacktrace(), Logger::ERROR);
    }

    public static function c()
    {
        for ($message = "", $l = func_get_args(), $i = 1; $i < func_num_args(); $i++) {
            $message .= self::var_debug($l[$i]) . "\n";
        };
        self::write($l[0], $message.'<br/>'.self::getBacktrace(), Logger::CRITICAL);
    }

    public static function a()
    {
        for ($message = "", $l = func_get_args(), $i = 1; $i < func_num_args(); $i++) {
            $message .= self::var_debug($l[$i]) . "\n";
        };
        self::write($l[0], $message.'<br/>'.self::getBacktrace(), Logger::ALERT);
    }

    public static function audit($itemid, $itemname, $action)
    {
        $username = $userid = "";
        if (isset($_SESSION["cms_admin_user_id"])) {
            $userid = $_SESSION["cms_admin_user_id"];
        } else {
            if (isset($_SESSION['login_user_id'])) {
                $userid = $_SESSION['login_user_id'];
                $username = $_SESSION['login_user_username'];
            }
        }

        if (isset($_SESSION["cms_admin_username"])) {
            $username = $_SESSION["cms_admin_username"];
        }
        MLog::i(
            "CMSMS",
            $action,
            array("itemid" => $itemid, "itemname" => $itemname, "username" => $username, "userid" => $userid)
        );
    }

}

//https://gist.githubusercontent.com/JCook21/3824584/raw/gistfile1.php
/**
 * Create a closure to handle uncaught exceptions
 */
set_exception_handler(
    $handler = function (Exception $e) use (&$handler) {
        $message = sprintf(
            'Uncaught exception of type %s thrown in file %s at line %s%s.',
            get_class($e),
            $e->getFile(),
            $e->getLine(),
            $e->getMessage() ? sprintf(' with message "%s"', $e->getMessage()) : ''
        );
        MLog::e("ERROR", MLog::jTraceEx($e));
        /**
         * If there was a previous nested exception call this function recursively
         * to log that too.
         */
        if ($prev = $e->getPrevious()) {
            $handler($prev);
        }
    }
);

/**
 * Set a custom error handler to make sure that errors are logged to Graylog.
 * Allows any non-fatal errors to be logged to the Graylog2 server.
 */
set_error_handler(
    function ($errno, $errstr, $errfile, $errline, array $errcontext) {
        if (in_array($errno, array(E_NOTICE, E_STRICT, E_DEPRECATED))) {
            return;
        }
        $message = 'Error of level ';
        switch ($errno) {
            case E_NOTICE:
                $message .= 'E_NOTICE';
                return;
            case E_WARNING:
                $message .= 'E_WARNING';
                return;
            case E_STRICT:
                $message .= 'E_STRICT';
                break;
            case E_USER_ERROR:
                $message .= 'E_USER_ERROR';
                break;
            case E_USER_WARNING:
                $message .= 'E_USER_WARNING';
                break;
            case E_USER_NOTICE:
                $message .= 'E_USER_NOTICE';
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
            default:
                $message .= sprintf('Unknown error level, code of %d passed', $errno);
        }
        $message .= sprintf(
            '. Error message was "%s" in file %s at line %d.',
            $errstr,
            $errfile,
            $errline
        );
        MLog::quick("ERROR", sprintf("Error %d: %s in %s@%d", $errno, $errstr, $errfile, $errline).'<br/>'.MLog::getBacktrace());

        return true; //Returning false will mean that PHP's error handling mechanism will not be bypassed.
    }
);

/**
 * This function will be called before the script exits.
 * This allows us to catch and log any fatal errors in the Graylog2 server.
 * This is needed as the set_error_handler function cannot be used to handle
 * any of the errors in the array below.
 */
register_shutdown_function(
    function () {
        $codes = array(
            1 => 'E_ERROR',
            4 => 'E_PARSE',
            16 => 'E_CORE_ERROR',
            32 => 'E_CORE_WARNING',
            64 => 'E_COMPILE_ERROR',
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
                MLog::i("ERROR", $message, "");
            } else {
                MLog::e("ERROR", $message, "");
            }
        }
    }
);
if (function_exists('override_function')) {
    override_function('audit', '$itemid, $itemname, $action', 'MLog::audit($itemid, $itemname, $action);');
}
