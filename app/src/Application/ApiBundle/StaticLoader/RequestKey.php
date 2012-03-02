<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Controller
 */

namespace Application\ApiBundle\StaticLoader;

use Application\DeskPRO\App;

class RequestKey
{
	public static function getApiKeyFromRequest()
	{
		$em = App::getOrm();
		$request = App::getRequest();

		static $api_key = null;

		if ($api_key !== null) return $api_key;

		$key_str = false;
		if (!empty($_SERVER['PHP_AUTH_USER']) AND !empty($_SERVER['PHP_AUTH_PW'])) {
			$key_str = $_SERVER['PHP_AUTH_USER'].':'.$_SERVER['PHP_AUTH_PW'];
		} else if ($request->headers->get('X-DeskPRO-API-Key', true)) {
			$key_str = $request->headers->get('X-DeskPRO-API-Key', true);
		} else if (!empty($_REQUEST['API-KEY'])) {
			$key_str = $_REQUEST['API-KEY'];
		}

		if (!$key_str) {
			$api_key = null;
			return null;
		}

		$api_key = $em->getRepository('DeskPRO:ApiKey')->findByKeyString($key_str);
		if (!$api_key) $api_key = null;

		if ($api_key) {
			App::setCurrentPerson($api_key['person']);
		}

		return $api_key;
	}
}
