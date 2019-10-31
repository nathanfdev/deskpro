<?php

namespace Orb\Service\Microsoft\Translate\Exceptions;

/**
 * Class EntireTextTooLongException
 *
 * @package Orb\Service\Microsoft\Translate\Exceptions
 */
class EntireTextTooLongException extends \Exception
{
    public function __construct($limit)
    {
        parent::__construct(
            "The entire text included in the request can't exceed {$limit} characters including spaces."
        );
    }
}
