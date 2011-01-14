<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Controller
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\ApiBundle\StaticLoader;

class ApiKeyRequestLoader
{
	public static function getApiKeyFromRequest($container, \Symfony\Component\HttpFoundation\Request $request)
	{
		$key_str = false;
		if (!empty($_SERVER['PHP_AUTH_USER']) AND !empty($_SERVER['PHP_AUTH_PW'])) {
			$key_str = $_SERVER['PHP_AUTH_USER'].':'.$_SERVER['PHP_AUTH_PW'];
		} else if ($request->headers->get('X-DeskPRO-API-Key', true)) {
			$key_str = $request->headers->get('X-DeskPRO-API-Key', true);
		} else if (!empty($_REQUEST['API-KEY'])) {
			$key_str = $_REQUEST['API-KEY'];
		}

		if (!$key_str) {
			return null;
		}

		return App::getEntityRepository('DeskPRO:ApiKey')->findByKeyString($key_str);
	}
}