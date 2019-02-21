<?php

namespace DeskPRO\Component\Filesystem;

/**
 * Simple wrapper around common file ops that force the caller
 * to explicitly list expected directory of the file,
 * and also has a blacklist that can be set to prevent mistakes.
 *
 * Blacklisting works by blacklisting specific files or directories or patterns.
 *
 * The whitelist required when you call any wrapped function is a sanity-check to make
 * sure the file is where you think it is.
 *
 * Directory names match sub-directories. E.g., /foo will match /foo/bar/ and /foo/baz/.
 *
 * If the whitelist contains a directory that is more specific than a blacklisted directory,
 * then it will be allowed. Eg:
 *
 *  blacklist: /foo/
 *  whitelist: /foo/bar/
 *  input:     /foo/bar/baz/file.txt
 *  result:    allowed
 *
 * (However, there is no way to override a blacklisted file or file pattern.)
 *
 * That is OK, the point of this is more about making sure a developer makes an
 * effort to target a specific whitelist location. This makes it less likely to be
 * vulnerable to mistakes where user input might contain bad input (e.g., input=/etc/passwd or input=../../config.php)
 */
class SafeFile
{
    /**
     * Use as whitelist to indicate any path is OK (i.e. only checks blacklist).
     */
    const ANY = '*';

    /**
     * Use as whitelist to indicate the path is expected to be an http path.
     */
    const HTTP = 'http://';

    /**
     * Use as a whitelist to indicate the path is expected to be a data path.
     */
    const DATA = 'data://';

    /**
     * @var array
     */
    private static $blacklist = [];

    /**
     * @var bool
     */
    private static $emit_warnings = false;

    /**
     * @param string $path
     */
    public static function addBlacklistDir($path)
    {
        self::$blacklist[] = rtrim(self::normalizePath($path), '/').'/';
    }

    /**
     * @param string $path
     */
    public static function addBlacklistFile($path)
    {
        self::$blacklist[] = self::normalizePath($path);
    }

    /**
     * @param string $pattern
     */
    public static function addBlacklistPattern($pattern)
    {
        self::$blacklist[] = 'regex:/'.$pattern.'/';
    }

    /**
     * Empties the blacklist (used during tests).
     */
    public static function resetBlacklist()
    {
        self::$blacklist = [];
    }

    /**
     * @param bool $onoff
     */
    public static function setEmitWarningsOption($onoff)
    {
        self::$emit_warnings = (bool) $onoff;
    }

    /**
     * @param string $path
     *
     * @return string|string[]
     */
    private static function normalizePath($path)
    {
        if (is_array($path)) {
            $res = [];
            foreach ($path as $p) {
                if (is_string($p) && $p !== '') {
                    $res[] = self::normalizePath($p);
                }
            }

            return $res;
        } else {
            $p = str_replace('\\', '/', $path);
            if (@is_dir($p)) {
                $p = rtrim($p, '/').'/';
            }

            // C:/foo/bar -> /C/foo/bar
            if (PHP_OS === 'Windows') {
                $p = preg_replace('/([A-Za-z]+):/', '/$1', $p);
            }
            $p = strtolower($p);

            return $p;
        }
    }

    /**
     * Check if a path matches anything in the $list of files or directories.
     *
     * @param string          $path
     * @param string[]|string $list
     *
     * @return bool
     */
    public static function matchesList($path, $list)
    {
        if ($list === self::ANY) {
            return true;
        }

        if (!$list) {
            return false;
        }

        if (!is_array($list)) {
            $list = [$list];
        }

        if (in_array(self::ANY, $list, true)) {
            return true;
        }

        $list = self::normalizePath($list);

        $path_test = self::normalizePath($path);

        foreach ($list as $p) {
            if ($p === $path_test) {
                return true;
            }
            if ($p === self::HTTP) {
                if (preg_match('/^https?:\/\//i', $path_test)) {
                    return true;
                }
            }
            if ($p === self::DATA) {
                if (preg_match('/^data:/', $path_test)) {
                    return true;
                }
            }
            if (substr($p, -1, 1) === '/') {
                if ($path_test === $p || $path_test.'/' === $p || strpos($path_test, $p) === 0) {
                    return true;
                }
            }
            if (substr($p, 0, 6) === 'regex:') {
                $pattern = substr($p, 7);
                if (preg_match($pattern, $path_test)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if a string is in the blacklist.
     *
     * @param string          $path
     * @param string|string[] $whitelist
     *
     * @return bool
     */
    public static function matchesBlacklist($path, $whitelist = [])
    {
        $bl = self::$blacklist;
        if ($whitelist) {
            if (!is_array($whitelist)) {
                $whitelist = [$whitelist];
            }

            $whitelist = self::normalizePath($whitelist);

            // Exact match whitelist filename
            foreach ($whitelist as $wp) {
                if ($wp === $path) {
                    return false;
                }
            }

            $bl = array_filter($bl, function ($p) use ($whitelist) {
                foreach ($whitelist as $wp) {
                    // whitelist path exactly overwrites bl
                    if ($wp === $p) {
                        return false;
                    }

                    // A more specific whitelisted directory
                    // overwrites the blacklisted one
                    if (
                        $wp !== self::ANY
                        && $wp !== self::HTTP
                        && $wp !== self::DATA
                        && substr($p, -1, 1) === '/'
                        && substr($wp, -1, 1) === '/'
                        && strpos($wp, $p) === 0
                        && strlen($wp) > strlen($p)
                    ) {
                        return false;
                    }
                }

                return true;
            });
        }

        return self::matchesList($path, $bl);
    }

    /**
     * Check to see if a path is in a known 'safe' place.
     *
     * @param string          $path
     * @param string[]|string $whitelist
     *
     * @return bool
     */
    public static function isValid($path, $whitelist)
    {
        return !self::matchesBlacklist($path, $whitelist) && self::matchesList($path, $whitelist);
    }

    /**
     * Throws an exception if a path is not valid.
     *
     * @param string          $path
     * @param string[]|string $whitelist
     */
    public static function assertValid($path, $whitelist)
    {
        if (!self::isValid($path, $whitelist)) {
            throw new \InvalidArgumentException('Invalid file path');
        }
    }

    /**
     * Wrapper for file_get_contents().
     *
     * @param string          $path
     * @param string[]|string $whitelist
     *
     * @return bool|string
     */
    public static function fileGetContents($path, $whitelist)
    {
        $orig_path = $path;
        $path      = realpath($path);

        if (!$path || !self::isValid($path, $whitelist)) {
            if (self::$emit_warnings) {
                trigger_error("SafeFile::fileGetContents($orig_path) is not valid", E_USER_WARNING);
            }

            return false;
        }

        return file_get_contents($path);
    }

    /**
     * Wrapper for file_get_contents().
     *
     * @param string          $path
     * @param string[]|string $whitelist
     *
     * @return bool|string
     */
    public static function file_get_contents($path, $whitelist)
    {
        return self::fileGetContents($path, $whitelist);
    }

    /**
     * Wrapper for file().
     *
     * @param string          $path
     * @param string[]|string $whitelist
     *
     * @return bool|string
     */
    public static function file($path, $whitelist)
    {
        $orig_path = $path;
        $path      = realpath($path);

        if (!$path || !self::isValid($path, $whitelist)) {
            if (self::$emit_warnings) {
                trigger_error("SafeFile::file($orig_path) is not valid", E_USER_WARNING);
            }

            return false;
        }

        return file($path);
    }

    /**
     * Wrapper for fopen().
     *
     * @param string          $path
     * @param string          $mode
     * @param string[]|string $whitelist
     *
     * @return bool|resource
     */
    public static function fileOpen($path, $mode, $whitelist)
    {
        $orig_path = $path;
        $path      = realpath($path);

        if (!self::isValid($path, $whitelist)) {
            if (self::$emit_warnings) {
                trigger_error("SafeFile::fileOpen($orig_path) is not valid", E_USER_WARNING);
            }

            return false;
        }

        return fopen($path, $mode);
    }

    /**
     * Wrapper for fopen().
     *
     * @param string          $path
     * @param string          $mode
     * @param string[]|string $whitelist
     *
     * @return bool|resource
     */
    public static function fopen($path, $mode, $whitelist)
    {
        return self::fileOpen($path, $mode, $whitelist);
    }
}
