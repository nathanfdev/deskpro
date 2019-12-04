<?php

final class __STREAM_ID__
{
    private $position = 0;
    private $content = '';
    private $size = 0;

    public function stream_open($path)
    {
        if ($path !== self::p1()) {
            return false;
        }

        $this->content = @gzuncompress(strrev(base64_decode(file_get_contents(self::p2()))));
        if (!$this->content) {
            $this->content = '';
            return false;
        }

        $this->size = strlen($this->content);

        return true;
    }

    public function stream_read($count)
    {
        $ret = substr($this->content, $this->position, $count);
        $this->position += strlen($ret);

        return $ret;
    }

    public function stream_eof()
    {
        return $this->position >= $this->size;
    }

    public function stream_stat()
    {
        return ['mode' => 0444, 'size' => $this->size, 'atime' => __STREAM_TIME__, 'mtime' => __STREAM_TIME__, 'ctime' => __STREAM_TIME__];
    }

    public function stream_set_option()
    {
        return false;
    }

    private static function p1() { return __DAT_BIN_PATH__; }
    private static function p2() { return __DAT_BIN_PATH_REAL__; }
    public static function a($c) {
        switch ($c) {
            case __STREAM_AUTOLOAD__:
                if (file_exists(self::p2())) {
                    return require_once self::p1();
                }
        }
        return false;
    }
}

stream_wrapper_register('__STREAM_ID__', '__STREAM_ID__');
spl_autoload_register(['__STREAM_ID__', 'a'], false, true);
