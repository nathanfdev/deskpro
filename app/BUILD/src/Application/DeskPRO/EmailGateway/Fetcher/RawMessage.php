<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\EmailGateway\Fetcher;

/**
 * This is a raw message fetched from one of the fetchers.
 */
class RawMessage
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $uid;

    /**
     * @var string
     */
    public $server_uid;

    /**
     * Just the header portion of the message.
     *
     * @var string
     */
    public $headers;

    /**
     * The entire raw email (headers+body).
     *
     * @var string
     */
    public $content;

    /**
     * True if $content was left unset because the message was too large.
     *
     * @var bool
     */
    public $too_big = false;

    /**
     * The size of the message.
     *
     * @var int
     */
    public $size = 0;
}
