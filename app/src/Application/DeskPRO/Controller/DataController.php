<?php

namespace Application\DeskPRO\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

use Orb\Util\Util;
use Orb\Util\Arrays;

class DataController extends AbstractController
{
	public function interfaceDataAction()
	{
		$what = $this->in->getCleanValueArray('types', 'string', 'discard');

		$js = array();


		$js = implode("\n", $js);
		$response = App::getResponse();
		$response->headers->set('Content-Type', 'application/javascript');
		$response->setContent($js);

		return $response;
	}

	public function logJsErrorAction()
	{
		$message    = $this->in->getString('message');
		$ip_address = $this->request->getClientIp();
		$user_agent = empty($_SERVER['HTTP_USER_AGENT']) ? '' : $_SERVER['HTTP_USER_AGENT'];
		$referrer   = empty($_SERVER['HTTP_REFERER']) ? '' : $_SERVER['HTTP_REFERER'];
		$hash       = $this->in->getString('hash');

		$logger = App::createNewLogger('error_log.js', null);
		$logger->log($message, 'WARN', array(
			'message'    => $this->in->getString('message'), // full message as the summary string is limited to 1000 chars
			'hash'       => $hash,
			'ip_address' => $ip_address,
			'user_agent' => $user_agent,
			'referrer'   => $referrer,
		));

		return $this->createJsonResponse(array(
			'logged' => true
		));
	}
}
