<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

namespace DeskPRO\Component\Util\Buffer;

/**
 * Buffers streams of data and will call a callback when a full line is made.
 */
class LineBuffer
{
    /**
     * @var
     */
    private $buf = '';

    /**
     * @var callable
     */
    private $fn;

    /**
     * @param callable $fn Called after each line is recieved
     */
    public function __construct($fn)
    {
        $this->fn = $fn;
    }

    public function append($str)
    {
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
        if ($this->buf) {
            call_user_func($this->fn, $this->buf);
            $this->buf = '';
        }
    }
}
