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

namespace Application\ApiBundle\Event;


use \Application\ApiBundle\Request\RequestAuth;
use Application\DeskPRO\Entity\ApiKeyLog;
use Application\DeskPRO\HttpFoundation\Request;
use Application\DeskPRO\HttpKernel\Event\PrePostEvent;
use Application\DeskPRO\ORM\EntityManager;
use Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Symfony\Component\HttpFoundation\Response;

class LogApiCallListener
{
	public function onControllerPostAction(PrePostEvent $event, $eventName, ContainerAwareEventDispatcher $dispatcher)
	{
		/** @var $auth RequestAuth */
		if (!$auth = $dispatcher->getContainer()->get('deskpro.api.request_auth')) {
			return;
		}

		if (!$key = $auth->getApiUser()->api_key) {
			return;
		}

		/** @var Response $response */
		if (!$response = $event->get('response')) {
			return;
		}

		/** @var Request $request */
		$request = $event->get('request');

		/** @var EntityManager $em */
		$em = $dispatcher->getContainer()->get('doctrine.orm.entity_manager');
		$log = new ApiKeyLog();
		$log->key = $key;
		$log->request = array(
			'path' => $request->getPathInfo(),
			'method' => $request->getMethod(),
			'payload' => $request->request->all(),
		);
		$log->response = array(
			'status' => $response->getStatusCode(),
			'content' => $response->getContent(), // parse json to array?
		);
		$em->persist($log);
		$em->flush();
	}
} 