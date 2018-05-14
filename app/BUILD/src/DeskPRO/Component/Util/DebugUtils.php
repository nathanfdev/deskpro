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
    const VARMODE_DETAIL = 1;
    const VARMODE_TYPE   = 2;

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
     * @param $withTrace $withTrace True to include (short) trace info
     *
     * @return string
     */
    public static function getExceptionSummary(\Exception $e, $withTrace = false)
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

            if ($withTrace) {
                $lines[] = self::formatStackTrace($e->getTrace());
            }

            $e = $e->getPrevious();
        }

        return implode("\n", $lines);
    }

    /**
     * @param array $stackTrace
     *
     * @return string
     */
    public static function formatStackTrace(array $stackTrace, $varMode = 0)
    {
        $trace = '';

        $longestFilename = 0;

        foreach ($stackTrace as $v) {
            if (!empty($v['file'])) {
                $longestFilename = max($longestFilename, strlen($v['file']) + strlen($v['line']) + 1);
            }
        }

        $longestFilename += 5;

        $prevLine = null;

        $x = 0;
        foreach ($stackTrace as $k => $v) {
            ++$x;

            $prefix  = sprintf('[#%02d] ', $x);
            $preLine = '';
            $line    = '';

            if (!empty($v['file'])) {
                $prefix .= "{$v['file']}:{$v['line']} ";
            } else {
                $prefix .= '<callback> ';
            }

            $showVarsString = null;

            if (isset($v['object'])) {
                if ($v['object'] instanceof \Twig_Template && method_exists($v['object'], 'getTemplateName')) {
                    $showVarsString = '<template_context>';
                    try {
                        $tpl = @$v['object']->getTemplateName();
                        if ($tpl) {
                            $line .= '<'.@$v['object']->getTemplateName().'>';

                            if ($prevLine && method_exists($v['object'], 'getDebugInfo')) {
                                $debug_info = @$v['object']->getDebugInfo();
                                if ($debug_info) {
                                    $l = $prevLine + 1;
                                    while (--$l > 0) {
                                        if (isset($debug_info[$l])) {
                                            $preLine = ">>>>> Template: $tpl:{$debug_info[$l]}";
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
                        $showVarsString = '<template_context>';
                    }
                } elseif ($v['function'] == 'renderView') {
                    $showVarsString = '<template_context>';
                }
                $line .= get_class($v['object']).'::';
            } elseif (isset($v['class'])) {
                $line .= $v['class'].'::';
            }

            $line .= "{$v['function']}(";

            if ($showVarsString) {
                if (!$varMode) {
                    $showVarsString = '...';
                }
                if (!empty($v['args'])) {
                    $line .= $showVarsString;
                }
            } elseif ($varMode && !empty($v['args'])) {
                switch ($varMode) {
                    case self::VARMODE_DETAIL:
                        $line .= self::varToString($v['args']);
                        break;
                    case self::VARMODE_TYPE:
                        $parts = [];
                        foreach ($v['args'] as $arg) {
                            $parts[] = TypeUtils::getVarType($arg);
                        }
                        $line .= implode(', ', $parts);
                        break;
                }
            }

            $line .= ')';

            if ($preLine) {
                $trace .= $preLine."\n";
            }

            $trace .= sprintf("%-{$longestFilename}s", $prefix)."\t---\t".trim($line)."\n";

            $prevLine = null;
            if (isset($v['line'])) {
                $prevLine = $v['line'];
            }
        }

        return trim($trace);
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
