<?php

namespace DeskPRO\Component\Util\Buffer;

/**
 * Buffers streams of data and will call a callback when a full line is made.
 */
class LineBuffer
{
    /**
     * @var string
     */
    private $buf = '';

    /**
     * @var callable
     */
    private $fn;

    /**
     * @param callable $fn Called after each line is received
     */
    public function __construct($fn)
    {
        $this->fn = $fn;
    }

    public function append($str)
    {
        // Normalise lf
        $str = str_replace("\r\n", "\n", $str);

        $this->buf .= $str;
        do {
            $pos = strpos($this->buf, "\n");
            if ($pos !== false) {
                $line = substr($this->buf, 0, $pos);
                call_user_func($this->fn, $line);
                $this->buf = substr($this->buf, $pos + 1);
            }
        } while ($pos !== false);
    }

    /**
     * Flushes the buffer.
     */
    public function flush()
    {
        if ($this->buf !== '') {
            call_user_func($this->fn, $this->buf);
            $this->buf = '';
        }
    }
}
