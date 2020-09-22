<?php

namespace DpRun;

/**
 * Class DpFsProxyStreamWrapper
 *
 * To promote the use of read-only filesystems, this stream wrapper does two things:
 *   - Where a file is found in the local FS, read it
 *   - Where a file doesn't exist and a write is attempted, read/write from memory
 * The idea is that we should always allow a file to be written, but not written to the local FS. This means that
 * we might catch any files that weren't pre-compiled (for whatever reason, maybe failed to be warmed if it was
 * a cache file).
 *
 * @package DpRun
 */
class DpFsProxyStreamWrapper
{
    /**
     * Logging for development purposes
     */
    const IS_LOGGING = false;

    /**
     * Default dummy file size
     */
    const DEFAULT_DUMMY_FILE_SIZE = (4096 * 4096);

    /**
     * @var string
     */
    private static $protocol;

    /**
     * @var array Used as a quick and dirty in-memory cache
     */
    private static $cache = [];

    /**
     * @var string Used for tracking a virtual file namespace instead of a resource handle
     */
    private $virtualNamespace;

    /**
     * @var string Used for tracking a virtual file instead of a resource handle
     */
    private $virtualPath;

    /**
     * @var resource
     */
    private $handle;

    /**
     * @var resource
     */
    private $dirHandle;

    /**
     * Register this stream wrapper
     *
     * @param string $protocol
     * @return bool
     */
    public static function register($protocol = 'dpfsproxy')
    {
        return stream_wrapper_register(self::$protocol = $protocol, __CLASS__);
    }

    /**
     * Substitution of \realpath() that works with this stream wrapper
     *
     * @param string $path
     * @return false|string
     */
    public static function realpath($path)
    {
        if (!self::$protocol) {
            throw new \RuntimeException(__CLASS__.'::register() must be called before realpath() can be invoked');
        }

        if (self::isProxyFsPath($path)) {
            return \realpath(str_replace(self::$protocol.'://'.self::getNamespace($path), '', $path));
        }

        return \realpath($path);
    }

    /**
     * @see https://www.php.net/manual/en/class.streamwrapper.php#streamwrapper.synopsis
     */
    public function dir_opendir($path, $options)
    {
        $this->log('CALL '.__METHOD__);

        if (!self::isProxyFsPath($path)) {
            return $this->dirHandle = \opendir($path);
        }

        $this->virtualPath      = self::getPath($path);
        $this->virtualNamespace = self::getNamespace($path);

        return true;
    }

    /**
     * @see https://www.php.net/manual/en/class.streamwrapper.php#streamwrapper.synopsis
     */
    public function dir_readdir()
    {
        $this->log('CALL '.__METHOD__);

        if ($this->virtualPath && $this->virtualNamespace) {
            return ''; // do nothing, as it's likely that the directory contents are fragmented anyway
        }

        return \readdir($this->dirHandle);
    }

    /**
     * @see https://www.php.net/manual/en/class.streamwrapper.php#streamwrapper.synopsis
     */
    public function dir_rewinddir()
    {
        $this->log('CALL '.__METHOD__);

        if ($this->virtualPath && $this->virtualNamespace) {
            return true;
        }

        return \readdir($this->dirHandle);
    }

    /**
     * @see https://www.php.net/manual/en/class.streamwrapper.php#streamwrapper.synopsis
     */
    public function dir_closedir()
    {
        $this->log('CALL '.__METHOD__);

        if ($this->virtualPath && $this->virtualNamespace) {
            $this->virtualPath = $this->virtualNamespace = null;

            return true;
        }

        \closedir($this->dirHandle);

        $this->dirHandle = null;

        return true;
    }

    /**
     * @see https://www.php.net/manual/en/class.streamwrapper.php#streamwrapper.synopsis
     */
    public function stream_metadata($path, $option, $value)
    {
        $this->log('CALL '.__METHOD__);

        $resolvedPath = self::getPath($path);

        switch ($option) {
            case STREAM_META_TOUCH:
                return \touch($resolvedPath, $value[0], $value[1]);
            case STREAM_META_OWNER:
            case STREAM_META_OWNER_NAME:
                return \chown($resolvedPath, $value);
            case STREAM_META_GROUP:
            case STREAM_META_GROUP_NAME:
                return \chgrp($resolvedPath, $value);
            case STREAM_META_ACCESS:
                return \chmod($resolvedPath, $value);
        }

        throw new \RuntimeException("Unknown option [{$option}] during ".__METHOD__." for {$path}");
    }

    /**
     * @see https://www.php.net/manual/en/class.streamwrapper.php#streamwrapper.synopsis
     */
    public function stream_set_option($option , $arg1 , $arg2)
    {
        $this->log('CALL '.__METHOD__);

        if ($this->virtualPath && $this->virtualNamespace) {
            return true;
        }

        switch ($option) {
            case STREAM_OPTION_BLOCKING:
                return \stream_set_blocking($this->handle, (bool) $arg1);
            case STREAM_OPTION_READ_TIMEOUT:
                return \stream_set_timeout($this->handle, $arg1, $arg2);
            case STREAM_OPTION_WRITE_BUFFER:
                return \stream_set_write_buffer($this->handle, $arg2);
        }

        return true;
    }

    /**
     * @see https://www.php.net/manual/en/class.streamwrapper.php#streamwrapper.synopsis
     */
    public function stream_open($path, $mode, $options, &$opened_path)
    {
        $this->log('CALL: '.__METHOD__." MODE: $mode, PATH: ".$path);

        $resolvedPath = self::getPath($path);
        $namespace    = self::getNamespace($path);

        if (!self::exists($resolvedPath)) {
            $this->virtualNamespace = $namespace;
            $this->virtualPath      = $resolvedPath;

            if (self::isReadMode($mode)) {
                // What happens when we're reading only and we have no cache? Probably return false
                if (!isset(self::$cache[$namespace][$resolvedPath])) {
                    return false;
                }

                // Record the lat known max position
                self::$cache[$namespace][$resolvedPath][2] = self::$cache[$namespace][$resolvedPath][0];

                // Reset the pointer as this is "r|r+" mode
                self::$cache[$namespace][$resolvedPath][0] = 0;
            }

            if (!isset(self::$cache[$namespace][$resolvedPath])) {
                self::$cache[$namespace][$resolvedPath] = [0, null];
            }

            return true;
        } elseif (self::isWriteMode($mode)) {
            $this->virtualNamespace = $namespace;
            $this->virtualPath      = $resolvedPath;

            if (!isset(self::$cache[$namespace][$resolvedPath])) {
                self::$cache[$namespace][$resolvedPath] = [0, null];
            }

            return true;
        }

        return $this->handle = \fopen($resolvedPath, $mode);
    }

    /**
     * @see https://www.php.net/manual/en/class.streamwrapper.php#streamwrapper.synopsis
     */
    public function stream_flush()
    {
        $this->log('CALL '.__METHOD__);

        if ($this->virtualNamespace && $this->virtualPath) {
            return true;
        }

        return \fflush($this->handle);
    }

    /**
     * @see https://www.php.net/manual/en/class.streamwrapper.php#streamwrapper.synopsis
     */
    public function stream_stat()
    {
        $this->log('CALL '.__METHOD__);

        if ($this->virtualNamespace && $this->virtualPath) {
            if (self::isFile($this->virtualPath)) {
                $size = isset(self::$cache[$this->virtualNamespace][$this->virtualPath][2])
                    ? self::$cache[$this->virtualNamespace][$this->virtualPath][2]
                    : self::DEFAULT_DUMMY_FILE_SIZE
                ;

                return self::dummyStatFile($size);
            }

            return self::dummyStatDir();
        }

        return \fstat($this->handle);
    }

    /**
     * @see https://www.php.net/manual/en/class.streamwrapper.php#streamwrapper.synopsis
     */
    public function stream_read($count)
    {
        $this->log('CALL '.__METHOD__);

        if ($this->virtualNamespace && $this->virtualPath) {
            list ($position, $data) = self::$cache[$this->virtualNamespace][$this->virtualPath];

            $read = substr($data, $position, $count);
            $read = $read === false ? '' : $read;

            self::$cache[$this->virtualNamespace][$this->virtualPath][0] = $position + strlen($read);

            return $read;
        }

        return \fread($this->handle, $count);
    }

    /**
     * @see https://www.php.net/manual/en/class.streamwrapper.php#streamwrapper.synopsis
     */
    public function stream_write($data)
    {
        $this->log('CALL '.__METHOD__);

        if ($this->virtualNamespace && $this->virtualPath) {
            if (!isset(self::$cache[$this->virtualNamespace][$this->virtualPath])) {
                throw new \RuntimeException("Virtual cache unallocated for {$this->virtualPath}");
            }

            list ($position, $existingData) = self::$cache[$this->virtualNamespace][$this->virtualPath];

            $left  = substr((string) $existingData, 0, $position);
            $right = substr((string) $existingData, $position + strlen($data));

            self::$cache[$this->virtualNamespace][$this->virtualPath] = [
                $position + strlen($data),
                $left.$data.$right,
            ];

            return strlen($data);
        }

        return \fwrite($this->handle, $data);
    }

    /**
     * @see https://www.php.net/manual/en/class.streamwrapper.php#streamwrapper.synopsis
     */
    public function stream_eof()
    {
        $this->log('CALL '.__METHOD__);

        if ($this->virtualNamespace && $this->virtualPath) {
            list ($position, $data) = self::$cache[$this->virtualNamespace][$this->virtualPath];

            return $position >= strlen($data);
        }

        return \feof($this->handle);
    }

    /**
     * @see https://www.php.net/manual/en/class.streamwrapper.php#streamwrapper.synopsis
     */
    public function stream_close()
    {
        $this->log('CALL '.__METHOD__);

        if ($this->virtualNamespace && $this->virtualPath) {
            $this->virtualNamespace = $this->virtualPath = null;

            return true;
        }

        $result = \fclose($this->handle);

        $this->handle = null;

        return $result;
    }

    /**
     * @see https://www.php.net/manual/en/class.streamwrapper.php#streamwrapper.synopsis
     */
    public function stream_seek($offset, $whence)
    {
        $this->log('CALL '.__METHOD__);

        return \fseek($this->handle, $offset, $whence);
    }

    /**
     * @see https://www.php.net/manual/en/class.streamwrapper.php#streamwrapper.synopsis
     */
    public function url_stat($path, $flags)
    {
        $this->log('CALL '.__METHOD__.' PATH: '.$path);

        $resolvedPath = self::getPath($path);

        if (!self::exists($resolvedPath)) {
            if (self::isFile($resolvedPath)) {
                $namespace = self::getNamespace($path);

                if (isset(self::$cache[$namespace][$resolvedPath])) {
                    return self::dummyStatFile();
                }

                return false;
            }

            return self::dummyStatDir();
        }

        if (self::isFile($resolvedPath)) {
            return @\stat($resolvedPath);
        }

        // Always return a readable directory, no matter what. Virtual cache requires this so that write
        // operations are allowed
        return self::dummyStatDir();
    }

    /**
     * @see https://www.php.net/manual/en/class.streamwrapper.php#streamwrapper.synopsis
     */
    public function rename($path_from, $path_to)
    {
        $this->log('CALL '.__METHOD__);

        $fromNamespace    = self::getNamespace($path_from);
        $resolvedFromPath = self::getPath($path_from);

        if (isset(self::$cache[$fromNamespace][$resolvedFromPath])) {
            $toNamespace    = self::getNamespace($path_to);
            $resolvedToPath = self::getPath($path_to);

            self::$cache[$toNamespace][$resolvedToPath] = self::$cache[$fromNamespace][$resolvedFromPath];

            $this->virtualNamespace = $toNamespace;
            $this->virtualPath      = $resolvedToPath;

            unset(self::$cache[$fromNamespace][$resolvedFromPath]);

            return true;
        }

        return \rename($resolvedFromPath, self::getPath($path_to));
    }

    /**
     * @see https://www.php.net/manual/en/class.streamwrapper.php#streamwrapper.synopsis
     */
    public function mkdir($path, $mode, $options)
    {
        $this->log('CALL '.__METHOD__);

        if (self::isProxyFsPath($path)) {
            return true;
        }

        return \mkdir(self::getPath($path), $mode, $options & STREAM_MKDIR_RECURSIVE);
    }

    /**
     * @see https://www.php.net/manual/en/class.streamwrapper.php#streamwrapper.synopsis
     */
    public function unlink($path)
    {
        $this->log('CALL '.__METHOD__);

        if (self::isProxyFsPath($path)) {
            $resolvedPath = self::getPath($path);
            $namespace    = self::getNamespace($path);

            if (isset(self::$cache[$namespace][$resolvedPath])) {
                unset(self::$cache[$namespace][$resolvedPath]);
            }

            return true;
        }

        return \unlink(self::getPath($path));
    }

    /**
     * @param string $message
     */
    private function log($message)
    {
        if (self::IS_LOGGING) {
            error_log($message);
        }
    }

    /**
     * Parses the stream URL for the file path name
     *
     * @param string $path
     * @return string
     */
    private static function getPath($path)
    {
        static $paths = [];

        return isset($paths[$path])
            ? $paths[$path]
            : $paths[$path] = \parse_url($path)['path']
        ;
    }

    /**
     * Parses the stream URL for the logical namespace
     *
     * @param string $path
     * @return string
     */
    private static function getNamespace($path)
    {
        static $namespaces = [];

        return isset($namespaces[$path])
            ? $namespaces[$path]
            : $namespaces[$path] = \parse_url($path)['host']
        ;
    }

    /**
     * Does a file or directory exist?
     *
     * @param string $path
     * @return bool
     */
    private static function exists($path)
    {
        if (\is_dir($path)) {
            return true;
        }

        return \file_exists($path);
    }

    /**
     * Is the fopen() mode a read?
     *
     * @param string $mode
     * @return bool
     */
    private static function isReadMode($mode)
    {
        return \strpos($mode, 'r') !== false;
    }

    /**
     * Is the fopen() mode a write?
     *
     * @param string $mode
     * @return bool
     */
    private static function isWriteMode($mode)
    {
        return ((\strpos($mode, 'w') !== false)
            || (\strpos($mode, 'a') !== false)
            || (\strpos($mode, 'c') !== false)
            || (\strpos($mode, 'x') !== false)
        );
    }

    /**
     * Is this a file path?
     *
     * @param string $path
     * @return bool
     */
    private static function isFile($path)
    {
        return \strpos($path, '.') !== false;
    }

    /**
     * Is this path using the proxy fs stream wrapper?
     *
     * @param string $path
     * @return bool
     */
    private static function isProxyFsPath($path)
    {
        if (!self::$protocol) {
            throw new \RuntimeException(__CLASS__.'::register() must be called before isProxyFsPath() can be invoked');
        }

        return \strpos($path, self::$protocol.'://') !== false;
    }

    /**
     * Dummy stats for an archetypal file
     *
     * @param int $size
     * @return int[]
     */
    private static function dummyStatFile($size = self::DEFAULT_DUMMY_FILE_SIZE)
    {
        return [
            'dev' => 771,
            'ino' => 488704,
            'mode' => 33188,
            'nlink' => 1,
            'uid' => 0,
            'gid' => 0,
            'rdev' => 0,
            'size' => (int) $size,
            'atime' => 1061067181,
            'mtime' => 1056136526,
            'ctime' => 1056136526,
            'blksize' => 4096,
            'blocks' => 8,
        ];
    }

    /**
     * Dummy stats for an archetypal directory
     *
     * @return int[]
     */
    private static function dummyStatDir()
    {
        return [
            'dev' => 40,
            'ino' => 13153498,
            'mode' => 16895,
            'nlink' => 2,
            'uid' => 0,
            'gid' => 0,
            'rdev' => 0,
            'size' => 4096,
            'atime' => 1600189568,
            'mtime' => 1600189418,
            'ctime' => 1600189418,
            'blksize' => 32768,
            'blocks' => 8,
        ];
    }
}
