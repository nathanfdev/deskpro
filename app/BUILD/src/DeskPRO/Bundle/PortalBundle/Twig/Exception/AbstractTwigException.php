<?php

namespace DeskPRO\Bundle\PortalBundle\Twig\Exception;

/**
 * Class AbstractTwigException.
 */
abstract class AbstractTwigException extends \RuntimeException
{
    const CODE = 1;

    /**
     * AbstractTwigException constructor.
     *
     * @param string     $message
     * @param \Exception $previous
     */
    public function __construct($message, \Exception $previous = null)
    {
        parent::__construct($message, self::getErrorCode(), $previous);
    }

    /**
     * @return int
     */
    final protected static function getErrorCode()
    {
        return static::CODE;
    }
}
