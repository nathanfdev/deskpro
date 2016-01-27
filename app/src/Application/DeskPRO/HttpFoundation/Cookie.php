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

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\HttpFoundation;

use Symfony\Component\HttpFoundation\Cookie as BaseCookie;

/**
 * @deprecated Use the usual symfony cookies and the method to send them
 *             This still exists just for some legacy controller code.
 */
class Cookie extends BaseCookie
{
    const EXPIRE_NEVER  = 'never';
    const EXPIRE_DELETE = 'delete';

    public static function makeDeleteCookie($name)
    {
        return self::makeCookie($name, '', 'delete', 0);
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
