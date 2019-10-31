<?php

namespace Orb\Service\Microsoft\Translate\Exceptions;

/**
 * Class TextValueTooLongException
 *
 * @package Orb\Service\Microsoft\Translate\Exceptions
 */
class TextValueTooLongException extends \Exception
{
    /**
     * TextValueTooLongException constructor.
     *
     * @param int $limit
     */
    public function __construct($limit)
    {
        parent::__construct("The text value can't exceed {$limit} characters including spaces.");
    }
}
