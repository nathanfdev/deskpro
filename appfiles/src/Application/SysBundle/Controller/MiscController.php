<?php

namespace Application\SysBundle\Controller;

use Application\DeskPRO\App;

class MiscController extends \Application\DeskPRO\Controller\AbstractController
{
	public function logJsErrorAction()
	{
		$message    = $this->in->getString('message');
		$ip_address = $this->request->getClientIp();
		$user_agent = empty($_SERVER['HTTP_USER_AGENT']) ? '' : $_SERVER['HTTP_USER_AGENT'];
		$referrer   = empty($_SERVER['HTTP_REFERER']) ? '' : $_SERVER['HTTP_REFERER'];

		$logger = App::createNewLogger('error_log.js', null);
		$logger->log($message, 'WARN', array(
			'ip_address' => $ip_address,
			'user_agent' => $user_agent,
			'referrer'   => $referrer,
		));

		return $this->createJsonResponse(array(
			'logged' => true
		));
	}
}
