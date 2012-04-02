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
*/

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
			'subject'    => $this->in->getString('subject'),
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

	public function sendErrorReportAction()
	{
		$token = $this->in->getString('token');
		if (!\Orb\Util\Util::checkStaticSecurityToken($token, 'dp_submit_error_report')) {
			return $this->createJsonResponse(array('error' => true));
		}

		$error_text = $this->in->getString('error_text');

		$ip_address = $this->request->getClientIp();
		$user_agent = empty($_SERVER['HTTP_USER_AGENT']) ? '' : $_SERVER['HTTP_USER_AGENT'];
		$referrer   = empty($_SERVER['HTTP_REFERER']) ? '' : $_SERVER['HTTP_REFERER'];
		$hash       = $this->in->getString('hash');

		$error_text = "IP: $ip_address\nUser agent: $user_agent\nReferrer: $referrer\nFragment: $hash\n\n$error_text";

		$comment = $this->in->getString('comment');

		$client = new \Zend\Http\Client(null, array('timeout' => 10));
		$client->setMethod(\Zend\Http\Request::METHOD_POST);
		$client->getRequest()->post()->set('error_text', $error_text);
		$client->getRequest()->post()->set('comment', $comment);
		$client->setUri(\DeskPRO\Kernel\License::getLicServer() . '/report-error-manual.json');
		$client->send();

		return $this->createJsonResponse(array('success' => true));
	}
}
