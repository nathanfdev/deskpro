<?php

namespace DeskPRO\Bundle\ApiBundle\Util;

/**
 * Class ApiUtil.
 */
class ApiUtil
{
    const API_MODE_SESSION = 'session';
    const API_MODE_KEY     = 'key';
    const API_MODE_TOKEN   = 'token';

    /**
     * @var array
     */
    protected static $modeMap = [
        'portal_session' => self::API_MODE_SESSION,
        'agent_session'  => self::API_MODE_SESSION,
        'api_key'        => self::API_MODE_KEY,
        'api_token'      => self::API_MODE_TOKEN,
    ];

    /**
     * @param $mode
     *
     * @return mixed
     */
    public static function getMode($mode)
    {
        if (isset(self::$modeMap[$mode])) {
            return self::$modeMap[$mode];
        }

        throw new \InvalidArgumentException(
            sprintf(
                'Unsupported api auth mode [ %s ], supported modes are [ %s ]',
                $mode,
                implode(', ', array_keys(self::$modeMap))
            )
        );
    }
}
