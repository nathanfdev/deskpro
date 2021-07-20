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
     * Use this as the whitelist when you don't care where a file is read from. In other words, this option
     * makes SafeFile only verify the file is not in the blacklist.
     *
     * You should avoid using this option because it means SafeFile can only verify the file is not in
     * the blacklist, but "not blacklisted" is not the same thing as being safe. The real safety provided
     * by SafeFile is when you know where a file should be, and SafeFile can validate that.
     *
     * So if you need to use UNSPECIFIED, or see it in a code review, you should challenge it. There are few cases
     * where you "don't know" where a file should be. E.g. if it's a cache file, then you know it should come from the
     * cache directory; if it's a temp file, you know it should come from the temp directory; etc.
     */
    const UNSPECIFIED = '?';

    /**
     * List of stream types that are considered "allowable" before
     * further checks are made
     */
    const STREAM_WHITELIST = [
        'http://',
        'https://',
        'data://',
        'file://',
    ];

    /**
     * @var array
     */
    public static $blacklist = [];

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
     * @param string|string[] $path
     *
     * @return string|string[]
     */
    public static function normalizePath($path)
    {
        if (empty($path) || !is_string($path)) {
            return null;
        }

        // remove 'file://' prefix if its there
        if (stripos($path, 'file://') === 0) {
            $path = substr($path, 7);
        }

        $trailingSlash = substr($path, -1, 1) === '/' || substr($path, -1, 1) === '\\';

        // Drive letter
        $drive = '';
        if (\strlen($path) > 2 && ':' === $path[1] && ('/' === $path[2] || '\\' === $path[2]) && ctype_alpha($path[0])) {
            $drive = substr($path, 0, 2);
            $path  = substr($path, 2);
        }

        $isAbsolute = $path[0] === '/' || $path[0] === '\\';

        $p = str_replace('\\', '/', trim($path));

        // if its got any protocol, its not a local file so reject it
        if (strpos($p, '://') !== false) {
            return null;
        }

        // 'data:' is special in php in that it doesnt need to be data://
        if (stripos($p, 'data:') === 0) {
            return null;
        }

        $p = rtrim($p, '/');

        if (!$isAbsolute) {
            $p = self::normalizePath(getcwd()) . '/' . $p;
        }

        $fileInfo = new \SplFileInfo($drive.$p);
        if ($fileInfo->getRealPath()) {
            return $fileInfo->getRealPath() . ($fileInfo->isDir() ? '/' : '');
        }

        return self::expandLinksHelper(self::resolveRelPathHelper($drive.$p))
            . ($trailingSlash ? '/' : '');
    }

    private static function expandLinksHelper($path)
    {
        // Drive letter
        $drive = '';
        if (\strlen($path) > 2 && ':' === $path[1] && '/' === $path[2] && ctype_alpha($path[0])) {
            $drive = substr($path, 0, 2);
            $path  = substr($path, 2);
        }

        $pathSegments = explode('/', trim($path, '/'));
        $noExistSegments = [];

        while (count($pathSegments)) {
            array_unshift($noExistSegments, array_pop($pathSegments));
            $current = self::normalizePath($drive . '/' . implode('/', $pathSegments));

            $dirInfo = new \SplFileInfo($current);
            if ($dirInfo->getRealPath()) {

                // part of the path isnt a dir, so this cant be a valid path
                if (!$dirInfo->isDir()) {
                    return null;
                }

                return $dirInfo->getRealPath() . '/' . implode('/', $noExistSegments);
            }
        }

        // no parts of the path exist, so can only return it asis
        return $drive.$path;
    }

    /**
     * Get the real canonicalized path to a file.
     *
     * @internal
     *
     * @param string $path
     *
     * @return string
     */
    private static function resolveRelPathHelper($path)
    {
        // Drive letter
        $drive = '';
        if (\strlen($path) > 2 && ':' === $path[1] && '/' === $path[2] && ctype_alpha($path[0])) {
            $drive = substr($path, 0, 2);
            $path  = substr($path, 2);
        }

        $pathSegments = explode('/', trim($path, '/'));
        $result       = [''];

        foreach ($pathSegments as $segment) {
            if ('..' === $segment) {
                array_pop($result);
            } elseif ('.' !== $segment) {
                $result[] = $segment;
            }
        }

        return $drive.implode('/', $result);
    }

    /**
     * Check if a path matches anything in the $list of files or directories.
     *
     * @param string          $path
     * @param string[]|string $list
     *
     * @return bool
     */
    private static function matchesList($normalPath, $list)
    {
        if ($list === self::UNSPECIFIED) {
            return true;
        }

        if (!$list) {
            return false;
        }

        if (!is_array($list)) {
            $list = [$list];
        }

        if (in_array(self::UNSPECIFIED, $list, true)) {
            return true;
        }

        foreach ($list as $p) {
            if ($p === $normalPath) {
                return true;
            }
            if (substr($p, -1, 1) === '/') {
                if ($normalPath === $p || $normalPath.'/' === $p || strpos($normalPath, $p) === 0) {
                    return true;
                }
            }
            if (substr($p, 0, 6) === 'regex:') {
                $pattern = substr($p, 7);
                if (preg_match($pattern, $normalPath)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if a string is in the blacklist.
     *
     * @param string          $normalPath
     * @param string|string[] $whitelist
     *
     * @return bool
     */
    private static function matchesBlacklist($normalPath, $whitelist = [])
    {
        $bl = self::$blacklist;
        if ($whitelist) {
            if (!is_array($whitelist)) {
                $whitelist = [$whitelist];
            }

            // Exact match whitelist filename
            foreach ($whitelist as $wp) {
                if ($wp === $normalPath) {
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
                        $wp !== self::UNSPECIFIED
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

        return self::matchesList($normalPath, $bl);
    }

    /**
     * Check to see if a path is in a known 'safe' place.
     *
     * @param string          $path
     * @param string[]|string $whitelist
     * @param boolean         $expectExist True if to check that it actually exists
     *
     * @return bool
     */
    public static function isValid($path, $whitelist, $expectExist = false)
    {
        $path = self::normalizePath($path);

        if (empty($path) || !is_string($path)) {
            return false;
        }

        if ($expectExist && !file_exists($path)) {
            return false;
        }

        if ($whitelist) {
            if (!is_array($whitelist)) {
                $whitelist = [$whitelist];
            }
            $whitelist = array_map(function ($p) {
                return $p === '?' ? '?' : self::normalizePath($p);
            }, $whitelist);
        }

        return !self::matchesBlacklist($path, $whitelist) && self::matchesList($path, $whitelist);
    }

    /**
     * Throws an exception if a path is not valid.
     *
     * @param string          $path
     * @param string[]|string $whitelist
     * @param boolean         $expectExist True if to check that it actually exists
     */
    public static function assertValid($path, $whitelist, $expectExist = false)
    {
        if (!self::isValid($path, $whitelist, $expectExist)) {
            throw new \InvalidArgumentException('Invalid file path');
        }
    }

    /**
     * @param string          $path
     * @param string[]|string $whitelist
     *
     * @return \SplFileInfo
     */
    public static function makeSplFileInfo($path, $whitelist)
    {
        self::assertValid($path, $whitelist);

        return new \SplFileInfo(self::normalizePath($path));
    }

    /**
     * @param string          $path
     * @param string[]|string $whitelist
     *
     * @return \SplFileInfo
     */
    public static function makeSfFile($path, $whitelist)
    {
        self::assertValid($path, $whitelist);

        return new \Symfony\Component\HttpFoundation\File\File(self::normalizePath($path));
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
        if (!self::isValid($path, $whitelist, true)) {
            if (self::$emit_warnings) {
                trigger_error("SafeFile::fileGetContents($path) is not valid", E_USER_WARNING);
            }

            return false;
        }

        return file_get_contents(self::normalizePath($path));
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
        if (!self::isValid($path, $whitelist, true)) {
            if (self::$emit_warnings) {
                trigger_error("SafeFile::file($path) is not valid", E_USER_WARNING);
            }

            return false;
        }

        return file(self::normalizePath($path));
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
        if (!self::isValid($path, $whitelist)) {
            if (self::$emit_warnings) {
                trigger_error("SafeFile::fileOpen($path) is not valid", E_USER_WARNING);
            }

            return false;
        }

        return fopen(self::normalizePath($path), $mode);
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


    /**
     * @param string          $path
     * @param string          $to
     * @param string|string[] $whitelist
     *
     * @return bool
     */
    public static function copy($path, $to, $whitelist)
    {
        if (!self::isValid($path, $whitelist)) {
            if (self::$emit_warnings) {
                trigger_error("SafeFile::copy($path) is not valid", E_USER_WARNING);
            }

            return false;
        }

        if (!self::isValid($to, $whitelist)) {
            if (self::$emit_warnings) {
                trigger_error("SafeFile::copy(*, $to) is not valid", E_USER_WARNING);
            }

            return false;
        }

        return copy(self::normalizePath($path), self::normalizePath($to));
    }

    /**
     * @param string          $path
     * @param string|string[] $whitelist
     *
     * @return bool
     */
    public static function file_exists($path, $whitelist)
    {
        if (!self::isValid($path, $whitelist)) {
            if (self::$emit_warnings) {
                trigger_error("SafeFile::file_exists($path) is not valid", E_USER_WARNING);
            }

            return false;
        }

        return file_exists(self::normalizePath($path));
    }

    /**
     * @param string          $path
     * @param mixed           $data
     * @param string|string[] $whitelist
     *
     * @return int|false
     */
    public static function file_put_contents($path, $data, $whitelist)
    {
        if (!self::isValid($path, $whitelist)) {
            if (self::$emit_warnings) {
                trigger_error("SafeFile::file_put_contents($path) is not valid", E_USER_WARNING);
            }

            return false;
        }

        return file_put_contents(self::normalizePath($path), $data);
    }

    /**
     * @param string          $path
     * @param string|string[] $whitelist
     *
     * @return int|false
     */
    public static function fileatime($path, $whitelist)
    {
        if (!self::isValid($path, $whitelist)) {
            if (self::$emit_warnings) {
                trigger_error("SafeFile::fileatime($path) is not valid", E_USER_WARNING);
            }

            return false;
        }

        return fileatime(self::normalizePath($path));
    }

    /**
     * @param string          $path
     * @param string|string[] $whitelist
     *
     * @return int|false
     */
    public static function filectime($path, $whitelist)
    {
        if (!self::isValid($path, $whitelist)) {
            if (self::$emit_warnings) {
                trigger_error("SafeFile::filectime($path) is not valid", E_USER_WARNING);
            }

            return false;
        }

        return filectime(self::normalizePath($path));
    }

    /**
     * @param string          $path
     * @param string|string[] $whitelist
     *
     * @return false|int
     */
    public static function filegroup($path, $whitelist)
    {
        if (!self::isValid($path, $whitelist)) {
            if (self::$emit_warnings) {
                trigger_error("SafeFile::filegroup($path) is not valid", E_USER_WARNING);
            }

            return false;
        }

        return filegroup(self::normalizePath($path));
    }

    /**
     * @param string          $path
     * @param string|string[] $whitelist
     *
     * @return int|false
     */
    public static function fileinode($path, $whitelist)
    {
        if (!self::isValid($path, $whitelist)) {
            if (self::$emit_warnings) {
                trigger_error("SafeFile::fileinode($path) is not valid", E_USER_WARNING);
            }

            return false;
        }

        return fileinode(self::normalizePath($path));
    }

    /**
     * @param string          $path
     * @param string|string[] $whitelist
     *
     * @return int|false
     */
    public static function filemtime($path, $whitelist)
    {
        if (!self::isValid($path, $whitelist)) {
            if (self::$emit_warnings) {
                trigger_error("SafeFile::filemtime($path) is not valid", E_USER_WARNING);
            }

            return false;
        }

        return filemtime(self::normalizePath($path));
    }

    /**
     * @param string          $path
     * @param string|string[] $whitelist
     *
     * @return false|int
     */
    public static function fileowner($path, $whitelist)
    {
        if (!self::isValid($path, $whitelist)) {
            if (self::$emit_warnings) {
                trigger_error("SafeFile::fileowner($path) is not valid", E_USER_WARNING);
            }

            return false;
        }

        return fileowner(self::normalizePath($path));
    }

    /**
     * @param string          $path
     * @param string|string[] $whitelist
     *
     * @return int|false
     */
    public static function fileperms($path, $whitelist)
    {
        if (!self::isValid($path, $whitelist)) {
            if (self::$emit_warnings) {
                trigger_error("SafeFile::fileperms($path) is not valid", E_USER_WARNING);
            }

            return false;
        }

        return fileperms(self::normalizePath($path));
    }

    /**
     * @param string          $path
     * @param string|string[] $whitelist
     *
     * @return int|false
     */
    public static function filesize($path, $whitelist)
    {
        if (!self::isValid($path, $whitelist)) {
            if (self::$emit_warnings) {
                trigger_error("SafeFile::filesize($path) is not valid", E_USER_WARNING);
            }

            return false;
        }

        return filesize(self::normalizePath($path));
    }

    /**
     * @param string          $path
     * @param string|string[] $whitelist
     *
     * @return string|false
     */
    public static function filetype($path, $whitelist)
    {
        if (!self::isValid($path, $whitelist)) {
            if (self::$emit_warnings) {
                trigger_error("SafeFile::filetype($path) is not valid", E_USER_WARNING);
            }

            return false;
        }

        return filetype(self::normalizePath($path));
    }

    /**
     * @param string          $path
     * @param string|string[] $whitelist
     *
     * @return bool
     */
    public static function is_dir($path, $whitelist)
    {
        if (!self::isValid($path, $whitelist)) {
            if (self::$emit_warnings) {
                trigger_error("SafeFile::is_dir($path) is not valid", E_USER_WARNING);
            }

            return false;
        }

        return is_dir(self::normalizePath($path));
    }

    /**
     * @param string          $path
     * @param string|string[] $whitelist
     *
     * @return bool
     */
    public static function is_executable($path, $whitelist)
    {
        if (!self::isValid($path, $whitelist)) {
            if (self::$emit_warnings) {
                trigger_error("SafeFile::is_executable($path) is not valid", E_USER_WARNING);
            }

            return false;
        }

        return is_executable(self::normalizePath($path));
    }

    /**
     * @param string          $path
     * @param string|string[] $whitelist
     *
     * @return bool
     */
    public static function is_file($path, $whitelist)
    {
        if (!self::isValid($path, $whitelist)) {
            if (self::$emit_warnings) {
                trigger_error("SafeFile::is_file($path) is not valid", E_USER_WARNING);
            }

            return false;
        }

        return is_file(self::normalizePath($path));
    }

    /**
     * @param string          $path
     * @param string|string[] $whitelist
     *
     * @return bool
     */
    public static function is_link($path, $whitelist)
    {
        if (!self::isValid($path, $whitelist)) {
            if (self::$emit_warnings) {
                trigger_error("SafeFile::is_link($path) is not valid", E_USER_WARNING);
            }

            return false;
        }

        return is_link(self::normalizePath($path));
    }

    /**
     * @param string          $path
     * @param string|string[] $whitelist
     *
     * @return bool
     */
    public static function is_readable($path, $whitelist)
    {
        if (!self::isValid($path, $whitelist)) {
            if (self::$emit_warnings) {
                trigger_error("SafeFile::is_readable($path) is not valid", E_USER_WARNING);
            }

            return false;
        }

        return is_readable(self::normalizePath($path));
    }

    /**
     * @param string          $path
     * @param string|string[] $whitelist
     *
     * @return bool
     */
    public static function is_writable($path, $whitelist)
    {
        if (!self::isValid($path, $whitelist)) {
            if (self::$emit_warnings) {
                trigger_error("SafeFile::is_writable($path) is not valid", E_USER_WARNING);
            }

            return false;
        }

        return is_writable(self::normalizePath($path));
    }

    /**
     * @param string          $path
     * @param string|string[] $whitelist
     *
     * @return array|false
     */
    public static function lstat($path, $whitelist)
    {
        if (!self::isValid($path, $whitelist)) {
            if (self::$emit_warnings) {
                trigger_error("SafeFile::lstat($path) is not valid", E_USER_WARNING);
            }

            return false;
        }

        return lstat(self::normalizePath($path));
    }

    /**
     * @param string          $path
     * @param string|string[] $whitelist
     * @param int             $permissions
     * @param bool            $recursive
     * @param resource|null   $context
     *
     * @return bool
     */
    public static function mkdir($path, $whitelist, $permissions = 0777, $recursive = false, $context = null)
    {
        if (!self::isValid($path, $whitelist)) {
            if (self::$emit_warnings) {
                trigger_error("SafeFile::mkdir($path) is not valid", E_USER_WARNING);
            }

            return false;
        }

        return mkdir(
            self::normalizePath($path),
            $permissions,
            $recursive,
            $context
        );
    }

    /**
     * @param string          $path
     * @param string|string[] $whitelist
     * @param bool            $process_sections
     * @param int             $scanner_mode
     *
     * @return bool
     */
    public static function parse_ini_file($path, $whitelist, $process_sections = false, $scanner_mode = \INI_SCANNER_NORMAL)
    {
        if (!self::isValid($path, $whitelist)) {
            if (self::$emit_warnings) {
                trigger_error("SafeFile::parse_ini_file($path) is not valid", E_USER_WARNING);
            }

            return false;
        }

        return parse_ini_file(
            self::normalizePath($path),
            $process_sections,
            $scanner_mode
        );
    }

    /**
     * @param string          $path
     * @param string|string[] $whitelist
     *
     * @return int|false
     */
    public static function readfile($path, $whitelist)
    {
        if (!self::isValid($path, $whitelist)) {
            if (self::$emit_warnings) {
                trigger_error("SafeFile::readfile($path) is not valid", E_USER_WARNING);
            }

            return false;
        }

        return readfile(self::normalizePath($path));
    }

    /**
     * @param string          $from
     * @param string          $to
     * @param string|string[] $whitelist
     * @param resource|null   $context
     *
     * @return bool
     */
    public static function rename($from, $to, $whitelist, $context = null)
    {
        if (!self::isValid($from, $whitelist)) {
            if (self::$emit_warnings) {
                trigger_error("SafeFile::rename($from) is not valid", E_USER_WARNING);
            }

            return false;
        }

        if (!self::isValid($to, $whitelist)) {
            if (self::$emit_warnings) {
                trigger_error("SafeFile::rename(*, $to) is not valid", E_USER_WARNING);
            }

            return false;
        }

        return rename(self::normalizePath($from), self::normalizePath($to), $context);
    }

    /**
     * @param string          $path
     * @param string|string[] $whitelist
     * @param resource|null   $context
     *
     * @return bool
     */
    public static function rmdir($path, $whitelist, $context = null)
    {
        if (!self::isValid($path, $whitelist)) {
            if (self::$emit_warnings) {
                trigger_error("SafeFile::rmdir($path) is not valid", E_USER_WARNING);
            }

            return false;
        }

        return rmdir(self::normalizePath($path), $context);
    }

    /**
     * @param string          $path
     * @param string|string[] $whitelist
     *
     * @return array|false
     */
    public static function stat($path, $whitelist)
    {
        if (!self::isValid($path, $whitelist)) {
            if (self::$emit_warnings) {
                trigger_error("SafeFile::stat($path) is not valid", E_USER_WARNING);
            }

            return false;
        }

        return stat(self::normalizePath($path));
    }

    /**
     * @param string          $path
     * @param string|string[] $whitelist
     * @param int|null        $mtime
     * @param int|null        $atime
     *
     * @return bool
     */
    public static function touch($path, $whitelist, $mtime = null, $atime = null)
    {
        if (!self::isValid($path, $whitelist)) {
            if (self::$emit_warnings) {
                trigger_error("SafeFile::touch($path) is not valid", E_USER_WARNING);
            }

            return false;
        }

        return touch(self::normalizePath($path), $mtime, $atime);
    }

    /**
     * @param string          $path
     * @param string|string[] $whitelist
     * @param resource|null   $context
     *
     * @return bool
     */
    public static function unlink($path, $whitelist, $context)
    {
        if (!self::isValid($path, $whitelist)) {
            if (self::$emit_warnings) {
                trigger_error("SafeFile::unlink($path) is not valid", E_USER_WARNING);
            }

            return false;
        }

        return unlink(self::normalizePath($path), $context);
    }
}

