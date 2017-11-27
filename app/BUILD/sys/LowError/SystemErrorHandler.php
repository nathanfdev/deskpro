<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace DpSys\LowError;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\LogoutException;

class SystemErrorHandler
{
    /**
     * @var bool
     */
    private static $isLogging = false;

    /**
     * @var bool
     */
    private static $isHandlingException = false;

    /**
     * @var string[]
     */
    private static $processLog = [];

    /**
     * @var string|null
     */
    private static $bugsnagConfig = [
        'backend_api_key' => null,
        'app_version'     => null,
        'metadata'        => [],
    ];

    /**
     * @var null|\Bugsnag_Client
     */
    private static $bugsnagClient = null;

    /**
     * @var LoggerInterface
     */
    private static $errorLogger = null;

    /**
     * @var bool
     */
    private static $hasFatalHandler = false;

    /**
     * @var array
     */
    private static $fatalErrors = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR];

    /**
     * @var array
     */
    private static $lastError;

    /**
     * @var array
     */
    private static $errorLevelMap = [
        E_ERROR             => LogLevel::CRITICAL,
        E_WARNING           => LogLevel::WARNING,
        E_PARSE             => LogLevel::ALERT,
        E_NOTICE            => LogLevel::NOTICE,
        E_CORE_ERROR        => LogLevel::CRITICAL,
        E_CORE_WARNING      => LogLevel::WARNING,
        E_COMPILE_ERROR     => LogLevel::ALERT,
        E_COMPILE_WARNING   => LogLevel::WARNING,
        E_USER_ERROR        => LogLevel::ERROR,
        E_USER_WARNING      => LogLevel::WARNING,
        E_USER_NOTICE       => LogLevel::NOTICE,
        E_STRICT            => LogLevel::NOTICE,
        E_RECOVERABLE_ERROR => LogLevel::ERROR,
        E_DEPRECATED        => LogLevel::NOTICE,
        E_USER_DEPRECATED   => LogLevel::NOTICE,
    ];

    private static $noShowErrors = false;

    /**
     * Max error log file size (512MB).
     *
     * @var int
     */
    public static $maxErrorLogFileSize = 536870912;

    //###################################################################################################################
    // Exceptions
    //###################################################################################################################

    /**
     * Handle an exception that has bubbled all the way to the top.
     * Usually it'll be caught by an event handler so this isn't normally displayed.
     *
     * @param \Exception $exception
     */
    public static function handleException($exception)
    {
        if (self::$isHandlingException || !self::shouldLog($exception)) {
            return;
        }

        self::$isHandlingException = true;

        $errinfo = self::getExceptionInfo($exception);
        self::logErrorInfo($errinfo);

        self::$isHandlingException = false;

        $message = 'A server error occurred.';
        $info    = 'Refer to the error log for full details (var/logs/error.log inside of the root DeskPRO directory).';

        if ($exception instanceof \PDOException) {
            $message = 'A database error occurred.';
        }

        if (php_sapi_name() === 'cli') {

            // on the cli we can give actual path without any security concerns
            $info = 'Refer to the error log for full details: '.self::getDpEnv()->getUserLogsDir();

            echo $message;
            echo "\n";
            echo $info;

            exit(255);
        } else {
            if (!headers_sent()) {
                header('Content-Type: text/plain');
            }
            http_response_code(Response::HTTP_BAD_REQUEST);
            echo $message;

            if (!defined('DPC_IS_CLOUD')) {
                /** @var Request $req */
                if ($req = self::getDpEnv()->getRuntimeVar('request', null)) {
                    $uri = $req->getPathInfo();
                } else {
                    $uri = @$_SERVER['REQUEST_URI'] ?: '';
                }

                // Show message about the error log if its agent/admin
                if (strpos($uri, '/api/') === 0 || strpos($uri, '/agent/') === 0 || strpos($uri, '/admin/') === 0) {
                    echo "\n";
                    echo $info;
                }
            }

            exit(0);
        }
    }

    /**
     * @param \Exception $exception    The exception to log
     * @param bool       $send         True to send a report to deskpro
     * @param string     $unique_id    An error ID. if this error has been reported before, it will not be reported again
     * @param bool       $noShowErrors Don't show errors override
     */
    public static function logException(\Exception $exception, $send = false, $unique_id = null, $noShowErrors = false)
    {
        if (!self::shouldLog($exception)) {
            return;
        }

        static $gotUniqueIds = [];

        /* @var \DpRun\DpEnv */
        global $DP_ENV;

        if ($unique_id && defined('DP_BUILD_TIME') && !defined('DP_BUILDING')) {
            if (isset($gotUniqueIds[$unique_id])) {
                return;
            }
            $gotUniqueIds[$unique_id] = true;

            try {
                $uniqueExceptions = $DP_ENV->getDatManager()->readDatFile('unique_exceptions', []);
                $found            = false;
                $saveNew          = [];
                foreach ($uniqueExceptions as $hash => $info) {
                    if ($info['ts'] > (time() - 604800)) {
                        $saveNew[$hash] = $info;
                        if ($hash === $unique_id) {
                            $found = true;
                        }
                    }
                }
                if (!$found) {
                    $saveNew[$unique_id] = ['ts' => time(), 'message' => $exception->getMessage()];
                }
                if ($found || count($saveNew) != $uniqueExceptions) {
                }
            } catch (\Exception $e) {
            }
        }

        $einfo = self::getExceptionInfo($exception);
        if (!$send) {
            $einfo['no_send_error'] = true;
        }

        $curNoShowErrors = self::$noShowErrors;

        self::$noShowErrors = $noShowErrors;
        self::logErrorInfo($einfo);
        self::$noShowErrors = $curNoShowErrors;
    }

    private static function shouldLog(/* Throwable */
        $exception)
    {
        if (($exception instanceof HttpException
                && $exception->getStatusCode() >= 400
                && $exception->getStatusCode() < 500)
            || $exception instanceof MethodNotAllowedException
            || $exception instanceof AccessDeniedException
            || $exception instanceof LogoutException
            || self::isProxyException($exception)
        ) {
            return false;
        }

        return true;
    }

    /**
     * @param $exception
     *
     * @return bool
     */
    public static function isProxyException($exception)
    {
        return $exception instanceof \UnexpectedValueException && strpos($exception->getMessage(), 'Invalid Host') === 0;
    }

    public static function logExceptionIfUniqueBacktrace(/*Throwable*/
        $e, $send = false)
    {
        $hashable_trace      = '';
        $formatted_backtrace = debug_backtrace();
        foreach ($formatted_backtrace as $trace) {
            $hashable_trace .= (isset($trace['class']) ? $trace['class'] : 'NOCLASS');
            $hashable_trace .= '::'.(isset($trace['function']) ? $trace['function'] : 'NOFUNC');
            $hashable_trace .= ' (line '.(isset($trace['line']) ? $trace['line'] : 'NOLINE').')';
            $hashable_trace .= PHP_EOL;
        }

        $hash = md5($hashable_trace);

        self::logException(
            $e,
            $send,
            $hash
        );
    }

    /**
     * Gets a standard error info array from an exception.
     *
     * @param \Exception $exception
     *
     * @return array
     */
    public static function getExceptionInfo(/*Throwable*/
        $exception)
    {
        $errno   = $exception->getCode();
        $errstr  = self::stripPathPrefix($exception->getMessage());
        $errfile = self::stripPathPrefix($exception->getFile());
        $errline = $exception->getLine();

        $backtrace   = $exception->getTrace();
        $trace       = self::formatBacktrace($backtrace);
        $contextData = '';

        if (isset($exception->_dp_query)) {
            $errstr .= ' -- Query: '.substr($exception->_dp_query, 0, 2000);

            $contextData .= 'Query: '.substr($exception->_dp_query, 0, 2000);

            if (!empty($exception->_dp_query_params)) {
                $contextData .= "\n\n".self::varToString($exception->_dp_query_params);
            }
        }

        if (!$contextData && isset($exception->_dp_context_data)) {
            $contextData = $exception->_dp_context_data;
        }

        $type    = get_class($exception);
        $summary = "[EXCEPTION] $type:$errno $errstr ($errfile:$errline)";

        $prev = $exception->getPrevious();
        if ($prev) {
            $previnfo = self::getExceptionInfo($prev);
            $summary .= ', '.$previnfo['summary'];
            $trace .= "\n\n(Alt Exception)\n{$previnfo['summary']}\n".$previnfo['trace'];
        }

        $errinfo = [
            'type'              => 'exception',
            'session_name'      => isset($exception->_dp_sn) ? $exception->_dp_sn : self::genSessionName(),
            'exception'         => $exception,
            'exception_type'    => get_class($exception),
            'pri'               => 'ERR',
            'trace'             => $trace,
            'summary'           => $summary,
            'errstr'            => $errstr,
            'errname'           => 'EXCEPTION',
            'errno'             => $errno,
            'errfile'           => $errfile,
            'errline'           => $errline,
            'build'             => self::getDpEnv()->getAppName(),
            'process_log'       => implode("\n", self::$processLog),
            'context_data'      => $contextData,
            'error_time'        => microtime(true),
            'time_to_error'     => defined('DP_START_TIME') ? sprintf('%0.4f', microtime(true) - DP_START_TIME) : 0,
            'client_user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
            'request_id'        => self::getRequestId(),
            'request_method'    => self::getRequestMethod(),
        ];

        $url = '';
        if (defined('DP_REQUEST_URL')) {
            $url = DP_REQUEST_URL;
        }
        if (php_sapi_name() == 'cli' && !empty($_SERVER['argv'])) {
            $url = 'Command: '.implode(' ', $_SERVER['argv']);
        }
        $errinfo['url'] = $url;

        if (self::isNoReportException($exception)) {
            $errinfo['no_send_error'] = true;
        }

        return $errinfo;
    }

    //###################################################################################################################
    // Errors
    //###################################################################################################################

    /**
     * Handle an error. Typically used as the error handler with set_error_handler().
     *
     * @param int    $errno
     * @param string $errstr
     * @param string $errfile
     * @param string $errline
     */
    public static function handleError($errno, $errstr, $errfile, $errline)
    {
        self::$lastError = [
            'type'    => $errno,
            'message' => $errstr,
            'file'    => $errfile,
            'line'    => $errline,
            'ignored' => false,
        ];

        if (!(error_reporting() & $errno)) {
            self::$lastError['ignored'] = true;

            return;
        }

        // Only log fatals if we dont have a fatal error handler already (prevent dupes)
        if (!self::$hasFatalHandler || !in_array($errno, self::$fatalErrors, true)) {
            $errinfo = self::getErrorInfo($errno, $errstr, $errfile, $errline);
            self::logErrorInfo($errinfo);
        }
    }

    /**
     * Enables fatal error handler.
     */
    public static function enableFatalErrorHandler()
    {
        register_shutdown_function([self::class, 'handleFatalError']);
        self::$hasFatalHandler = true;
    }

    public static function handleFatalError()
    {
        $lastError = error_get_last();

        // Some cases we might have logged / ignored an error already
        // that is being caught by this fatal error handler.
        // e.g., trying to parse an invalid image raises an E_PARSE for some reason,
        // so we want to ignore that here.
        if (
            self::$lastError
            && self::$lastError['type'] === $lastError['type']
            && self::$lastError['message'] === $lastError['message']
            && self::$lastError['file'] === $lastError['file']
            && self::$lastError['line'] === $lastError['line']
        ) {
            return;
        }

        if ($lastError && in_array($lastError['type'], self::$fatalErrors, true)) {
            $errinfo = self::getErrorInfo($lastError['type'], $lastError['message'], $lastError['file'], $lastError['line']);
            self::logErrorInfo($errinfo);
        }
    }

    /**
     * Gets a standard error info array from an error.
     *
     * @param int    $errno
     * @param string $errstr
     * @param string $errfile
     * @param string $errline
     *
     * @return array
     */
    public static function getErrorInfo($errno, $errstr, $errfile, $errline)
    {
        switch ($errno) {
            case E_ERROR:
                $pri     = 'ERR';
                $errname = 'E_ERROR';
                break;

            case E_WARNING:
            case E_USER_WARNING:
                $pri     = 'WARN';
                $errname = 'E_WARNING';
                break;

            case E_NOTICE:
            case E_USER_NOTICE:
                $pri     = 'NOTICE';
                $errname = 'E_NOTICE';
                break;

            case E_STRICT:
                $pri     = 'STRICT';
                $errname = 'E_STRICT';
                break;

            case E_RECOVERABLE_ERROR:
                $pri     = 'ERR';
                $errname = 'E_RECOVERABLE_ERROR';
                break;

            case E_DEPRECATED:
            case E_USER_DEPRECATED:
                $pri     = 'NOTICE';
                $errname = 'E_DEPRECATED';
                break;

            default:
                $pri     = 'ERR';
                $errname = 'UNKNOWN:'.$errno;
        }

        $context_data = '';

        $no_send_error = false;

        // Dont output apc warnings (but still log them)
        if (strpos($errstr, 'Unable to allocate memory for pool') !== false) {
            $no_send_error = true;
        }

        $errstr  = self::stripPathPrefix($errstr);
        $errfile = self::stripPathPrefix($errfile);

        $backtrace = debug_backtrace();
        $trace     = self::formatBacktrace($backtrace);

        // Dont send in general perm errors or things to do with the fs storage
        if ((strpos($errstr, 'failed to open stream: Permission denied') !== false || strpos($errstr, 'failed to open stream: No such file or directory') !== false) && strpos($trace, 'FileDescriptor') !== false) {
            $no_send_error = true;
        }

        // Dont send connection errors with smtp
        if (strpos($errfile, 'StreamBuffer.php') !== false && strpos($errstr, 'bytes failed with errno') !== false) {
            $no_send_error = true;
        }

        if (strpos($errstr, 'htmlspecialchars(): Invalid multibyte sequence in argument') !== false) {
            $no_send_error = true;
        }

        // Dont send logs about bad file attachments
        if (strpos($errstr, 'failed to open stream') !== false && (strpos($errstr, '/FileDescriptor/Filesystem.php') !== false || strpos($errstr, '\\FileDescriptor\\Filesystem.php') !== false)) {
            $no_send_error = true;
        }

        // Windows PHP <5.3.6 https://bugs.php.net/bug.php?id=51894
        if (strpos($errstr, 'range(): step exceeds the specified range') !== false) {
            $no_send_error = true;
        }

        // Log but dont report errors about writing chat available trigger
        if (strpos($errstr, 'chat_is_available.trigger') !== false) {
            $no_send_error = true;
        }

        // Ignore range() warning caused by PHP bug https://bugs.php.net/bug.php?id=51894 (fixed in PHP >= 5.3.6)
        if (strpos($errstr, 'step exceeds the specified range') !== false) {
            $no_send_error = true;
        }

        if (strpos($errstr, 'set_time_limit() has been disabled for security reasons') !== false) {
            $no_send_error = true;
        }

        if (strpos($errstr, 'passthru() has been disabled for security reasons') !== false) {
            $no_send_error = true;
        }

        if (strpos($errstr, 'possibly out of disk space') !== false) {
            $no_send_error = true;
        }

        if (strpos($errstr, 'Kerberos error') !== false) {
            return; // completely ignore
        }

        // imap
        if (
            strpos($errstr, 'Can not authenticate to IMAP server') !== false
            || strpos($errstr, 'imap_gc()') !== false
            || strpos($errstr, 'imap_open()') !== false
            || strpos($errstr, 'Unknown: LOGIN failed') !== false
        ) {
            $no_send_error = true;
        }

        // Socket/network errors
        if (
            strpos($errstr, 'stream_socket_enable_crypto():') !== false
            || strpos($errstr, 'SSL: Broken pipe') !== false
            || strpos($errstr, 'SSL: Connection reset by peer') !== false
            || strpos($errstr, 'SSL operation failed') !== false
            || strpos($errstr, 'errno=32 Broken pipe')
            || strpos($errstr, 'SSL: An established connection was aborted') !== false
            || strpos($errstr, 'SSL: An existing connection was forcibly closed by the remote host') !== false
            || strpos($errstr, 'Couldn\'t open stream') !== false
            || strpos($errstr, 'Can\'t connect to') !== false
            || strpos($errstr, 'fsockopen()') !== false
        ) {
            $no_send_error = true;
        }

        // GD errors (gd-png)
        if (
            strpos($errstr, 'imagecreatefromstring') !== false
            || strpos($errstr, 'gd-png') !== false
        ) {
            $no_send_error = true;
        }

        $summary = "[$errname:$errno] $errstr ($errfile:$errline)";

        $url = '';
        if (defined('DP_REQUEST_URL')) {
            $url = DP_REQUEST_URL;
        }

        if (php_sapi_name() == 'cli' && !empty($_SERVER['argv'])) {
            $url = 'Command: '.implode(' ', $_SERVER['argv']);
        }

        return [
            'type'              => 'error',
            'session_name'      => self::genSessionName(),
            'pri'               => $pri,
            'trace'             => $trace,
            'summary'           => $summary,
            'errstr'            => $errstr,
            'errname'           => $errname,
            'errno'             => $errno,
            'errfile'           => $errfile,
            'errline'           => $errline,
            'build'             => self::getDpEnv()->getAppName(),
            'process_log'       => implode("\n", self::$processLog),
            'context_data'      => $context_data,
            'error_time'        => microtime(true),
            'time_to_error'     => defined('DP_START_TIME') ? sprintf('%0.4f', microtime(true) - DP_START_TIME) : 0,
            'no_send_error'     => $no_send_error,
            'client_user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
            'url'               => $url,
            'request_id'        => self::getRequestId(),
            'request_method'    => self::getRequestMethod(),
        ];
    }

    /**
     * Takes care of logging an error. $errinfo is an info array from getExceptionInfo or getErrorInfo.
     *
     * @param array $errinfo
     */
    public static function logErrorInfo($errinfo = null)
    {
        if (!$errinfo) {
            return;
        }
        if (self::$isLogging) {
            return;
        }
        self::$isLogging = true;

        if (!empty($GLOBALS['DP_CONTAINER_IS_BUILDING'])) {
            return;
        }

        self::processErrorInfo($errinfo);
        unset($errinfo['exception']);

        if (!(isset($errinfo['no_send_error']) && $errinfo['no_send_error'])) {
            //==BEGIN:MONITORING==
            if (extension_loaded('newrelic')) {
                if (isset($errinfo['exception'])) {
                    newrelic_notice_error($errinfo['summary'], $errinfo['exception']);
                } else {
                    newrelic_notice_error($errinfo['summary']);
                }
            }
            //==END:MONITORING==
        }

        self::$isLogging = false;
    }

    //###################################################################################################################
    // Logging
    //###################################################################################################################

    /**
     * Logs error info to the data/error.log file and the summary to the PHP error log.
     *
     * @param array $errinfo
     */
    public static function processErrorInfo(array $errinfo)
    {
        $str       = [];
        $extraData = self::getDpEnv()->getConfig('settings.extra_errorlog_fields', []);

        if ($errinfo['type'] == 'exception') {
            $e     = $errinfo['exception'];
            $line  = sprintf('DeskPRO Exception: %s:%s (%s line %s): %s', $errinfo['exception_type'], $e->getCode(), $errinfo['errfile'], $errinfo['errline'], $e->getMessage());
            $str[] = sprintf("Exception: %s %s\n", $e->getCode(), $e->getMessage());
            $str[] = sprintf("\tType: %s\n", $errinfo['exception_type']);
            $str[] = sprintf("\tDate: %s (Running time to error: %s)\n", date('Y-m-d H:i:s'), $errinfo['time_to_error']);
            foreach ($extraData as $k => $v) {
                $str[] = "\t$k: $v\n";
            }
            $str[] = sprintf("\tBuild: %s\n", @$errinfo['build']);
            if (!empty($errinfo['url'])) {
                $str[] = sprintf("\tURL: %s\n", $errinfo['url']);
            }
            if (!empty($errinfo['request_id'])) {
                $str[] = sprintf("\tRequestID: %s\n", $errinfo['request_id']);
            }
            if (!empty($errinfo['request_method'])) {
                $str[] = sprintf("\tRequestMethod: %s\n", $errinfo['request_method']);
            }
            if (!empty($errinfo['client_user_agent'])) {
                $str[] = sprintf("\tUserAgent: %s\n", $errinfo['client_user_agent']);
            }
            $str[] = sprintf("\t-> [#00] %s:%d\n", $errinfo['errfile'], $errinfo['errline']);
        } else {
            $line  = sprintf('DeskPRO Error: %s (%s line %s): %s', $errinfo['errname'], $errinfo['errfile'], $errinfo['errline'], $errinfo['errstr']);
            $str[] = sprintf("Error: %s\n", $errinfo['errstr']);
            $str[] = sprintf("\tType: %s\n", $errinfo['errname']);
            $str[] = sprintf("\tDate: %s (Running time to error: %s)\n", date('Y-m-d H:i:s'), $errinfo['time_to_error']);
            foreach ($extraData as $k => $v) {
                $str[] = "\t$k: $v\n";
            }
            $str[] = sprintf("\tBuild: %s\n", @$errinfo['build']);
            if (!empty($errinfo['url'])) {
                $str[] = sprintf("\tURL: %s\n", $errinfo['url']);
            }
            if (!empty($errinfo['client_user_agent'])) {
                $str[] = sprintf("\tUserAgent: %s\n", $errinfo['client_user_agent']);
            }
            $str[] = sprintf("\t-> [#00] %s:%d\n", $errinfo['errfile'], $errinfo['errline']);
        }

        $errinfo['trace'] = trim($errinfo['trace']);
        if ($errinfo['trace']) {
            $lines = explode("\n", $errinfo['trace']);
            foreach ($lines as $l) {
                $l = trim($l);
                if (substr($l, 0, 5) == '>>>>>') {
                    $str[] = sprintf("\t   %s\n", trim($l));
                } else {
                    $str[] = sprintf("\t-> %s\n", trim($l));
                }
            }
        }

        if (!empty($errinfo['context_data'])) {
            $str[] = "Context Data:\n";
            $str[] = $errinfo['context_data'];
            $str[] = "\n\n";
        }

        $str = trim(implode('', $str));
        $str .= "\n";

        // Prefix each line for easier parsing
        $str  = preg_replace('#^#m', "<DP_LOG:{$errinfo['session_name']}> ", $str);
        $line = preg_replace('#^#m', "<DP_LOG:{$errinfo['session_name']}> ", $line);

        // First line of the log in the logfile must be DP_LOG.BEGIN, as that is used for the
        // "quick" counts in admin interface
        $pos = strpos($str, '<DP_LOG:');
        if ($pos !== false) {
            $str = substr_replace($str, '<DP_LOG.BEGIN:', $pos, strlen('<DP_LOG:'));
        }

        // Always write error line to standard error log
        if (defined('DPC_IS_CLOUD') && defined('DPC_SITE_DOMAIN')) {
            $line = '['.DPC_SITE_DOMAIN.'] '.$line;
        }
        @error_log($line, 0);

        if (self::shouldDisplayErrors()) {
            echo "\n";
            echo $errinfo['summary'];
            echo "\n";
        }

        if (self::getLogDir()) {
            $logFiles = [self::getLogDir().DIRECTORY_SEPARATOR.'error.log'];
        } else {
            // we dont have an env log, so lets try to re-use server error log
            $phpErrLog = ini_get('error_log');
            if ($phpErrLog) {
                $logFiles = [$phpErrLog];
            } else {
                $logFiles = [];
            }
        }

        if ($secondaryLogFile = self::getDpEnv()->getConfig('settings.secondary_errorlog_file')) {
            $logFiles[] = $secondaryLogFile;
        }

        foreach ($logFiles as $errorLogFile) {
            if (
                $errorLogFile
                && is_file($errorLogFile)
                && filesize($errorLogFile) < self::$maxErrorLogFileSize
                && ($fh = @fopen($errorLogFile, 'a')) !== false
            ) {
                @fwrite($fh, $str);
                @fclose($fh);
                @chmod($errorLogFile, 0777);
            }
        }

        try {
            if (self::$errorLogger) {
                if ($errinfo['type'] === 'exception') {
                    $e = $errinfo['exception'];
                    self::$errorLogger->log(
                        LogLevel::ERROR,
                        sprintf('Uncaught Exception %s: "%s" at %s line %s', get_class($e), $e->getMessage(), $e->getFile(), $e->getLine()),
                        ['exception' => $e]
                    );
                } else {
                    $level = isset(self::$errorLevelMap[$errinfo['errno']]) ? self::$errorLevelMap[$errinfo['errno']] : LogLevel::CRITICAL;
                    self::$errorLogger->log($level, $errinfo['errname'].': '.$errinfo['errstr'], ['code' => $errinfo['errno'], 'message' => $errinfo['errstr'], 'file' => $errinfo['errfile'], 'line' => $errinfo['errline']]);
                }
            }

            if (!$errinfo['no_send_error']) { // if no send is false - then send
                if ($bs = self::getBugsnagClient()) {
                    if ($errinfo['type'] === 'exception') {
                        $bs->notifyException($errinfo['exception']);
                    } else {
                        $bs->notifyError($errinfo['errname'], $errinfo['summary']);
                    }
                }
            }
        } catch (\Exception $e) {
        }
    }

    /**
     * Logs debug data to a file.
     *
     * @param string $content
     * @param null   $id
     *
     * @return string
     */
    public static function logDebugDataDump($content, $id = null)
    {
        /* @var \DpRun\DpEnv */
        global $DP_ENV;

        $path = $DP_ENV->getUserDebugDir().DIRECTORY_SEPARATOR.'data_dumps';
        if (!is_dir($path)) {
            @mkdir($path, 0777, true);
        }

        if (!$id) {
            $id = date('YmdHis').'--'.md5(uniqid('', true));
        }

        $dumpPath = $path.DIRECTORY_SEPARATOR.$id.'.dump';
        @file_put_contents($dumpPath, $content);

        return $dumpPath;
    }

    /**
     * Add a log line that'll be saved with an error. This is used with things like the gateway, where
     * if there's an error we'll want to know everything that happened up to the error point.
     *
     * @param $line
     */
    public static function addProcessLog($line)
    {
        self::$processLog[] = $line;
    }

    /**
     * Clears the process log. For example, with the gateway, if a new email is started then the last log
     * might be cleared.
     */
    public static function clearProcessLog()
    {
        self::$processLog = [];
    }

    //###################################################################################################################
    // Config
    //###################################################################################################################

    /**
     * @return \Bugsnag_Client|null
     */
    private static function getBugsnagClient()
    {
        if (self::$bugsnagClient) {
            return self::$bugsnagClient;
        }

        if (self::getDpEnv() instanceof UnknownDpEnv) {
            return;
        }

        if (self::$bugsnagConfig['backend_api_key']) {
            if (!class_exists('Bugsnag_Client', true)) {
                // failed to autoload the class, so ignore
                self::$bugsnagConfig['backend_api_key'] = null;

                return;
            }

            self::$bugsnagClient = new \Bugsnag_Client(self::$bugsnagConfig['backend_api_key']);
            self::$bugsnagClient->setProjectRoot(self::getDpEnv()->getDpRoot());
            self::$bugsnagClient->setAutoNotify(false);
            if (isset(self::$bugsnagConfig['metadata']) && is_array(self::$bugsnagConfig['metadata'])) {
                self::$bugsnagClient->setMetaData(['deskpro' => self::$bugsnagConfig['metadata'], 'deskpro_env' => ['RequestID' => self::getRequestId()]]);
            }

            if (self::$bugsnagConfig['app_version']) {
                self::$bugsnagClient->setAppVersion(self::$bugsnagConfig['app_version']);
            } elseif (($buildNumFile = self::getDpEnv()->getAppDir().'/sys/config/build-name.txt') && file_exists($buildNumFile)) {
                self::$bugsnagClient->setAppVersion(trim(file_get_contents($buildNumFile)));
            } elseif (($buildNumFile = self::getDpEnv()->getAppDir().'/sys/config/build-num.txt') && file_exists($buildNumFile)) {
                self::$bugsnagClient->setAppVersion(trim(file_get_contents($buildNumFile)));
            }
        }

        return self::$bugsnagClient;
    }

    /**
     * @param array $bugsnagConfig
     */
    public static function setBugsnagConfig(array $bugsnagConfig = ['backend_api_key' => null])
    {
        self::$bugsnagConfig = array_replace(self::$bugsnagConfig, $bugsnagConfig);
        self::$bugsnagClient = null;
    }

    /**
     * @param LoggerInterface $logger
     */
    public static function setErrorLogger(LoggerInterface $logger)
    {
        self::$errorLogger = $logger;
    }

    /**
     * @return string
     */
    private static function getLogDir()
    {
        return self::getDpEnv()->getUserLogsDir();
    }

    /**
     * Get the DP environment.
     *
     * NOTE: Only use methods that are added to UnknownDpEnv below.
     *
     * @return \DpRun\DpEnv
     */
    private static function getDpEnv()
    {
        static $unknownEnv;
        /* @var \DpRun\DpEnv */
        global $DP_ENV;

        if (!$DP_ENV) {
            if (!$unknownEnv) {
                $unknownEnv = new UnknownDpEnv();
            }

            return $unknownEnv;
        }

        return $DP_ENV;
    }

    //###################################################################################################################
    // Filtering
    //###################################################################################################################

    /**
     * @param \Exception $exception
     *
     * @return bool
     */
    public static function isNoReportException(/*Throwable*/
        $exception)
    {
        static $ignore = [
            'Swift_TransportException',
            'Swift_IoException',
            'Zend\\Mail\\Protocol\\Exception\\RuntimeException',
            'Symfony\\Component\\HttpKernel\\Exception\\MethodNotAllowedHttpException',
            'Elastica\\Exception\\Connection\\HttpException',
            'Application\\DeskPRO\\\JIRA\\\ApiGeneralException',
            'DeskPRO\Bundle\\AppBundle\\Exception\\HelpdeskInstanceExceptionInterface',
        ];

        foreach ($ignore as $cls) {
            if ($exception instanceof $cls) {
                return true;
            }
        }

        if ($exception instanceof \Doctrine\DBAL\Types\ConversionException && strpos($exception->getMessage(), 'Doctrine Type array') !== false) {
            return true;
        }

        if ($exception instanceof \Doctrine\DBAL\ConnectionException && strpos($exception->getMessage(), 'There is no active transaction') !== false) {
            return true;
        }

        if ($exception instanceof \Imagine\Exception\RuntimeException && strpos($exception->getMessage(), 'Unable to open temporary file') !== false) {
            return true;
        }

        if ($exception instanceof \PDOException || $exception instanceof \Doctrine\DBAL\DBALException) {
            if (strpos($exception->getFile(), 'DbTablePhpPasswordCheck.php') !== false) {
                return true;
            }

            if (strpos($exception->getMessage(), 'Incorrect key file for table') !== false) {
                return true;
            }

            if (strpos($exception->getMessage(), 'A connection attempt failed') !== false) {
                return true;
            }

            if (strpos($exception->getMessage(), 'No connection could be made') !== false) {
                return true;
            }

            // disk is full
            if (strpos($exception->getMessage(), 'Got error 28 from storage engine') !== false) {
                return true;
            }

            // innodb error, probably during recovery of disk issue
            if (strpos($exception->getMessage(), 'Got error -1 from storage engine') !== false) {
                return true;
            }

            // table is full
            if (strpos($exception->getMessage(), 'General error: 1114 The table') !== false) {
                return true;
            }

            if (strpos($exception->getMessage(), 'Lost connection to MySQL') !== false) {
                return true;
            }

            if (strpos($exception->getMessage(), 'not allowed to connect to this MySQL server') !== false) {
                return true;
            }

            if (strpos($exception->getMessage(), 'reading initial communication packet') !== false) {
                return true;
            }

            if (strpos($exception->getMessage(), 'sending authentication information') !== false) {
                return true;
            }

            if (strpos($exception->getMessage(), 'Can\'t create/write to file') !== false) {
                return true;
            }

            if (strpos($exception->getMessage(), 'marked as crashed') !== false) {
                return true;
            }

            if (strpos($exception->getMessage(), 'Error writing file') !== false) {
                return true;
            }

            if (strpos($exception->getMessage(), 'MySQL server has gone away') !== false) {
                return true;
            }

            if (strpos($exception->getMessage(), 'Too many connections') !== false) {
                return true;
            }

            if (strpos($exception->getMessage(), 'has more than \'max_user_connections\' active connections') !== false) {
                return true;
            }

            if (strpos($exception->getMessage(), 'Can\'t connect to MySQL server on') !== false) {
                return true;
            }

            if (strpos($exception->getMessage(), 'Can\'t connect to local MySQL server') !== false) {
                return true;
            }

            if (strpos($exception->getMessage(), 'No such file or directory') !== false) {
                return true;
            }
        }

        if ($exception instanceof \InvalidArgumentException && preg_match('#Command ".*?" is not defined#', $exception->getMessage())) {
            return true;
        }

        if ($exception instanceof \InvalidArgumentException && preg_match('#The command ".*?" does not exist#', $exception->getMessage())) {
            return true;
        }

        // For commands run with bad options
        if ($exception instanceof \RuntimeException && strpos($exception->getMessage(), 'option does not exist') !== false && strpos($exception->getFile(), 'ArgvInput.php') !== false) {
            return true;
        }

        if ($exception instanceof \RuntimeException && strpos($exception->getMessage(), 'Could not open blob for reading') !== false) {
            return true;
        }

        if ($exception instanceof \RuntimeException && strpos($exception->getMessage(), 'Cannot create Imagine instance') !== false) {
            return true;
        }

        // Calling cli with bad args
        if ($exception instanceof \Symfony\Component\Console\Exception\RuntimeException && strpos($exception->getMessage(), 'Too many arguments') !== false) {
            return true;
        }
        if ($exception instanceof \Symfony\Component\Console\Exception\RuntimeException && strpos($exception->getMessage(), 'option does not exist.') !== false) {
            return true;
        }
        if ($exception instanceof \InvalidArgumentException && strpos($exception->getMessage(), 'is ambiguous') !== false) {
            return true;
        }

        return false;
    }

    //###################################################################################################################
    // Utils
    //###################################################################################################################

    /**
     * @return string|null
     */
    private static function getRequestId()
    {
        /** @var \Symfony\Component\HttpFoundation\Request $req */
        if ($req = self::getDpEnv()->getRuntimeVar('request', null)) {
            return $req->attributes->get('request_id', null);
        }

        return null;
    }

    /**
     * @return null|string
     */
    private static function getRequestMethod()
    {
        /** @var \Symfony\Component\HttpFoundation\Request $req */
        if ($req = self::getDpEnv()->getRuntimeVar('request', null)) {
            return $req->getMethod();
        }

        return null;
    }

    /**
     * Used with formatBacktrace to format an array (usually parameters) to a string, being sure not to recurse
     * too deep.
     *
     * @param mixed $var
     * @param int   $_depth
     *
     * @return string
     */
    public static function varToString($var, $_depth = 0)
    {
        if (is_object($var)) {
            if ($var instanceof \Symfony\Component\EventDispatcher\Debug\WrappedListener) {
                return sprintf('<%s<%s>>', get_class($var), self::varToString($var->getWrappedListener()));
            } else {
                return sprintf('<%s>', get_class($var));
            }
        }
        if (is_array($var)) {
            $a        = [];
            $len      = count($var);
            $is_array = true;

            for ($i = 0; $i < $len; ++$i) {
                if (!array_key_exists($i, $var)) {
                    $is_array = false;
                    break;
                }
            }

            foreach ($var as $k => $v) {
                if ($k === 'pass' || $k === 'password' || $k === 'passphrase') {
                    $v = '***';
                }
                if ($_depth > 8) {
                    if ($is_array) {
                        $a[] = '(array)';
                    } else {
                        $a[] = sprintf('%s => %s', $k, '(string)');
                    }
                } else {
                    if ($is_array) {
                        $a[] = self::varToString($v, $_depth + 1);
                    } else {
                        if (!is_numeric($k)) {
                            $k = "'$k'";
                        }
                        $a[] = sprintf('%s => %s', $k, self::varToString($v, $_depth + 1));
                    }
                }
            }
            if ($_depth == 0) {
                return implode(', ', $a);
            } else {
                return sprintf('array(%s)', implode(', ', $a));
            }
        }
        if (is_resource($var)) {
            return '[resource]';
        }
        $str = (string) $var;
        if (strlen($str) > 1000) {
            $str = substr($str, 0, 1000).'...(clipped)';
        }

        return str_replace("\n", '', var_export(self::stripPathPrefix($str), true));
    }

    /**
     * Strips the full path prefix from $content. This makes all paths relative to the root of DeskRPO install.
     *
     * @param string $content
     *
     * @return string
     */
    public static function stripPathPrefix($content)
    {
        $content = str_replace('\\', '/', $content);

        $prefix  = str_replace('\\', '/', DP_ROOT).'/';
        $content = str_replace($prefix, '/app/', $content);

        $prefix  = str_replace('\\', '/', DP_WEB_ROOT).'/';
        $content = str_replace($prefix, '/', $content);

        return $content;
    }

    /**
     * Formats a backtrace.
     *
     * @param array $backtrace
     * @param bool  $no_vars   Dont include vars in the backtrace
     *
     * @return string
     */
    public static function formatBacktrace(array $backtrace, $no_vars = false)
    {
        $trace = '';

        $longest_filename = 0;

        foreach ($backtrace as &$v) {
            if (!empty($v['file'])) {
                $v['orig_file']   = $v['file'];
                $v['file']        = self::stripPathPrefix($v['file']);
                $longest_filename = max($longest_filename, strlen(self::stripPathPrefix($v['file'])));
            }
        }
        unset($v);

        $longest_filename += 15;

        $prev_line = null;

        $x = 0;
        foreach ($backtrace as $k => $v) {
            if (!empty($v['object'])) {
                if (strpos(get_class($v['object']), 'SystemErrorHandler')) {
                    continue;
                }
            }
            if (!empty($v['class'])) {
                if (strpos($v['class'], 'SystemErrorHandler')) {
                    continue;
                }
            }

            ++$x;

            $prefix   = sprintf('[#%02d] ', $x);
            $pre_line = '';
            $line     = '';

            if (!empty($v['file'])) {
                $prefix .= "{$v['file']}:{$v['line']} ";
            } else {
                $prefix .= '<callback> ';
            }

            $show_vars_string = null;

            if (isset($v['object'])) {
                if ($v['object'] instanceof \Twig_Template && method_exists($v['object'], 'getTemplateName')) {
                    $show_vars_string = '<template_context>';
                    try {
                        $tpl = @$v['object']->getTemplateName();
                        if ($tpl) {
                            $line .= '<'.@$v['object']->getTemplateName().'>';

                            if ($prev_line && method_exists($v['object'], 'getDebugInfo')) {
                                $debug_info = @$v['object']->getDebugInfo();
                                if ($debug_info) {
                                    $l = $prev_line + 1;
                                    while (--$l > 0) {
                                        if (isset($debug_info[$l])) {
                                            $pre_line = ">>>>> Template: $tpl:{$debug_info[$l]}";
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                    } catch (\Exception $e) {
                    }
                } elseif ($v['object'] instanceof \Application\DeskPRO\Templating\Engine || $v['object'] instanceof \Symfony\Bundle\TwigBundle\Debug\TimedTwigEngine) {
                    if ($v['function'] == 'render') {
                        $show_vars_string = '<template_context>';
                    }
                } elseif ($v['function'] == 'renderView') {
                    $show_vars_string = '<template_context>';
                }
                $line .= get_class($v['object']).'::';
            } elseif (isset($v['class'])) {
                $line .= $v['class'].'::';
            }

            $line .= "{$v['function']}(";

            if ($no_vars) {
                $show_vars_string = '...';
            }

            if ($show_vars_string) {
                if (!empty($v['args'])) {
                    $line .= $show_vars_string;
                }
            } else {
                if (!empty($v['args'])) {
                    $line .= self::varToString($v['args']);
                }
            }

            $line .= ')';

            if ($pre_line) {
                $trace .= $pre_line."\n";
            }

            $trace .= sprintf("%-{$longest_filename}s", $prefix)."\t---\t".trim($line)."\n";

            $prev_line = null;
            if (isset($v['line'])) {
                $prev_line = $v['line'];
            }
        }

        $trace = preg_replace('#PDO::__construct(.*?)$#m', 'PDO::__construct(...)', $trace);
        $trace = preg_replace_callback('#Pop3::__construct(.*?)$#m', function ($m) {
            $ret = $m[0];
            $ret = preg_replace('#password => .*?, ssl#', 'password => \'***\', ssl', $ret);

            return $ret;
        }, $trace);

        return trim($trace);
    }

    /**
     * @static
     */
    private static function genSessionName()
    {
        return time().substr(strtoupper(md5(uniqid(''))), 0, 6);
    }

    /**
     * @return bool
     */
    private static function shouldDisplayErrors()
    {
        if (defined('DPC_IS_CLOUD') && php_sapi_name() !== 'cli') {
            return false;
        }

        if (self::$noShowErrors) {
            return;
        }

        $v = ini_get('display_errors');

        return $v === '1' || $v === 1 || strtoupper($v) === 'ON' || strtoupper($v) === 'YES';
    }

    /**
     * Calls a callback but mutes any kind of error that might happen inside.
     * Exceptions will be logged through standard error log.
     *
     * @param callable $cb
     *
     * @return mixed
     */
    public static function tryRun($cb)
    {
        self::$noShowErrors = true;

        try {
            return call_user_func($cb);
        } catch (\Exception $e) {
            self::logException($e);

            return;
        } finally {
            self::$noShowErrors = false;
        }
    }
}

/**
 * In some rare cases we might not have a real DpEnv loaded (see getDpEnv above) when an error
 * happens before it could be set. So this is a duck'd class that has the methods used above.
 */
class UnknownDpEnv
{
    public function getDpRoot()
    {
        return '';
    }

    public function getAppName()
    {
        return '';
    }

    public function getAppDir()
    {
        return '';
    }

    public function getUserLogsDir()
    {
        return '';
    }

    public function getRuntimeVar($name, $defaultVal = null)
    {
        return $defaultVal;
    }

    public function getConfig($name, $defaultVal = null)
    {
        return $defaultVal;
    }
}
