<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\HttpFoundation;

use Symfony\Component\HttpFoundation\Cookie as BaseCookie;

use Application\DeskPRO\App;

class Cookie extends BaseCookie
{
	const EXPIRE_NEVER = 'never';
	const EXPIRE_DELETE = 'delete';

	public static function makeDeleteCookie($name)
	{
		return new self($name, '', 'delete');
	}

	public static function makeCookie($name, $value, $expire, $httpOnly = false, $secure = false)
	{
		return new self($name, $value, $expire, null, null, $secure, $httpOnly);
	}

	public function __construct($name, $value = null, $expire = 0, $path = null, $domain = null, $secure = false, $httpOnly = false)
    {
		if ($path === null) {
			$path = App::getSetting('core.cookie_path');
			if (!$path) {
				$path = '/';
			}
		}

		if ($domain === null) {
			$domain = App::getSetting('core.cookie_domain');
			if (!$domain) {
				$domain = null;
			}
		}

		if ($expire == self::EXPIRE_NEVER) {
			$expire = '+5 years';
		} elseif ($expire == self::EXPIRE_DELETE) {
			$expire = '-1 week';
		}

		parent::__construct($name, $value, $expire, $path, $domain, $secure, $httpOnly);
	}
}