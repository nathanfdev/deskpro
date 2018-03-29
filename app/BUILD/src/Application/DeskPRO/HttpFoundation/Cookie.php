<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\HttpFoundation;

use Symfony\Component\HttpFoundation\Cookie as BaseCookie;

/**
 * @deprecated Use the usual symfony cookies and the method to send them
 *             This still exists just for some legacy controller code
 */
class Cookie extends BaseCookie
{
    const EXPIRE_NEVER  = 'never';
    const EXPIRE_DELETE = 'delete';

    public static function makeDeleteCookie($name)
    {
        return self::makeCookie($name, '', time() - 1000, 0);
    }

    public static function makeCookie($name, $value, $expire, $httpOnly = false, $secure = false)
    {
        return new self($name, $value, $expire, null, null, $secure, $httpOnly);
    }

    public function send()
    {
        header('Set-Cookie: '.$this->__toString(), false);
    }
}
