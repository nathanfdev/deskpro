<?php

/**
 * Orb.
 */

namespace Orb\Log\Writer;

use Orb\Log\LogItem;

/**
 * This writer just echos out the messages.
 */
class Output extends AbstractWriter
{
    /** @var bool */
    protected $html = false;

    /**
     * @param  streamOrUrl     Stream or URL to open as a stream
     * @param  mode            Mode, only applicable if a URL is given
     */
    public function __construct($html = false)
    {
        $this->html = $html;

        $this->addFilter(new \Orb\Log\Filter\SimpleLineFormatter());
    }

    /**
     * Write a message to the log.
     */
    public function _write(LogItem $log_item)
    {
        $msg = $log_item[LogItem::MESSAGE_LINE];
        if ($this->html) {
            $msg = '<pre style="margin:0;padding:0;">'.htmlspecialchars(trim($msg)).'</pre>';
        } else {
            $msg .= "\n";
        }

        echo $msg;
    }
}
