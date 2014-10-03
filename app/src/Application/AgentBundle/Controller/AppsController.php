<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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
 * @category Entities
 */

namespace Application\AgentBundle\Controller;

use Application\DeskPRO\App\Native\RequestHandler\AgentRequestContext;

class AppsController extends AbstractController
{
	/**
	 * {@inheritDoc}
	 */
	public function requireRequestToken($action, $arguments = null)
	{
		return false;
	}

	public function runAction($app_id, $action = 'default')
	{
		$manager = $this->container->getAppManager();

		try {
			$native_app = $manager->getNativeApp($app_id);
		} catch (\InvalidArgumentException $e) {
			throw $this->createNotFoundException($e->getMessage());
		}

		$handler_class = $native_app->getConfig()->getAgentRequestHandlerClass();
		if (!$handler_class) {
			throw $this->createNotFoundException("Bad handler class");
		}

		$context = new AgentRequestContext(
			$this->container,
			$this->request,
			$this,
			$native_app,
			$this->person,
			$action
		);

		$handler = new $handler_class();
		$result = $handler->handleAgentRequest($context);

		return $result;
	}
}