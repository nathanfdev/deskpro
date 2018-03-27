<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Component\Util;

use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Process;

/**
 * Utility methods used aid debugging or logging.
 */
class DebugUtils
{
    private function __construct()
    {
    }

    /**
     * Given a logger, test to see if it might be handling a certain log level.
     *
     * This is useful when you want to add a log line, but generating the log line
     * is computationally intensive so you might not want to do it when the logger isn't
     * enabled. E.g., when in Debug mode, you want a log line, but in prod, you don't.
     *
     * Since the PSR LoggerInterface doesn't have a way to check for which log levels
     * are handled, this will always return true. But if you are using a Monolog
     * instance (which is the most common), we can check.
     *
     * @param LoggerInterface $logger
     * @param                 $level
     *
     * @return bool
     */
    public static function isLoggerHandling(LoggerInterface $logger, $level)
    {
        if ($logger instanceof Logger) {
            return $logger->isHandling($level);
        }

        return true;
    }

    /**
     * @param \Exception $e
     *
     * @return string
     */
    public static function getExceptionSummary(\Exception $e)
    {
        $lines = [];

        while ($e) {
            $code = $e->getCode();
            if ($lines) {
                $prefix = '|'.str_repeat('--', count($lines)).' ';
            } else {
                $prefix = '';
            }

            if ($code === 0 || $code === null || $code === false || $code === '0' || $code === '') {
                $lines[] = sprintf(
                    '%s<%s> %s',
                    $prefix,
                    get_class($e),
                    $e->getMessage()
                );
            } else {
                $lines[] = sprintf(
                    '%s<%s> [%s] %s',
                    $prefix,
                    get_class($e),
                    $e->getCode(),
                    $e->getMessage()
                );
            }

            $e = $e->getPrevious();
        }

        return implode("\n", $lines);
    }

    /**
     * @param mixed $var      The var to dump
     * @param int   $maxDepth Max depth to descend to within arrays
     * @param int   $_depth   Internal. Current depth
     *
     * @return string
     */
    public static function varToString($var, $maxDepth = 6, $_depth = 0)
    {
        if (is_object($var)) {
            return sprintf('<%s>', get_class($var));
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
                if ($_depth > $maxDepth) {
                    if ($is_array) {
                        $a[] = '(array)';
                    } else {
                        $a[] = sprintf('%s => %s', $k, '(string)');
                    }
                } else {
                    if ($is_array) {
                        $a[] = self::varToString($v, $maxDepth, $_depth + 1);
                    } else {
                        if (!is_numeric($k)) {
                            $k = "'$k'";
                        }
                        $a[] = sprintf('%s => %s', $k, self::varToString($v, $maxDepth, $_depth + 1));
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

        return var_export($str, true);
    }

    /**
     * Checks a PHP file for syntax errors by running the linter.
     *
     * @param string      $file   Full path to the PHP file to check
     * @param string|null $out    The output from the linter check will be placed in here
     * @param string      $phpBin The path to the PHP binary to use
     *
     * @return bool
     */
    public static function lintPhpFile($file, &$out = null, $phpBin = 'php')
    {
        $proc = new Process(sprintf("'%s' -l '%s'", $phpBin, $file));
        $proc->run(function ($type, $l) use (&$out) {
            $out .= $l;
        });

        return $proc->isSuccessful();
    }
}
