<?php

class __STREAM_ID__
{
    private $position = 0;
    private $content = '';
    private $size = 0;

    public function stream_open($path)
    {
        $pos = strpos($path, '://');
        if ($pos === false) {
            return false;
        }

        $realPath = substr($path, $pos + 3);
        if ($realPath[0] !== '/' && $realPath[0] != '\\') {
            return false;
        }

        $raw = file_get_contents($realPath);
        if ($raw === false) {
            return false;
        }

        $this->content = @gzuncompress(strrev(base64_decode($raw)));
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
}

stream_wrapper_register("__STREAM_ID__", "__STREAM_ID__");
