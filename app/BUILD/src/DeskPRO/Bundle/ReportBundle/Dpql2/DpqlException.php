<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2;

use DeskPRO\Bundle\AppBundle\Exception\HelpdeskInstanceExceptionInterface;

/**
 * The exception for an error that occurs when compiling, preparing, or
 * executing a DPQL statement.
 */
class DpqlException extends \Exception implements HelpdeskInstanceExceptionInterface
{
    const CODE_LAYERED_DIRECT_COMPILE_ERROR = 1;

    /**
     * @var array
     */
    protected static $messageMap = [
        self::CODE_LAYERED_DIRECT_COMPILE_ERROR => 'can\'t compile query with LAYER WITH keyword',
    ];

    /**
     * @param $code
     *
     * @return mixed|string
     */
    public static function getMessageByCode($code)
    {
        if (isset(self::$messageMap[$code])) {
            return self::$messageMap[$code];
        }

        return 'general compilation error';
    }
}
