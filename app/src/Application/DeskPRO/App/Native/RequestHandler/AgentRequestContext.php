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
 * @category Entities
 */

namespace Application\DeskPRO\App\Native\RequestHandler;

use Application\AgentBundle\Controller\AbstractController;
use Application\DeskPRO\App\Native\NativeApp;
use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\Person;
use Symfony\Component\HttpFoundation\Request;

class AgentRequestContext extends AbstractRequestContext
{
	/**
	 * @var \Application\DeskPRO\App\Native\NativeApp
	 */
	private $native_app;

	/**
	 * @param DeskproContainer $container
	 * @param Request $request
	 * @param AbstractController $controller
	 * @param NativeApp $native_app
	 * @param Person $agent
	 * @param $action
	 */
	public function __construct(
		DeskproContainer $container,
		Request $request,
		AbstractController $controller,
		NativeApp $native_app,
		Person $agent,
		$action
	)
	{
		$this->native_app = $native_app;
		parent::__construct(
			$container,
			$request,
			$controller,
			$agent,
			$native_app->getPackage(),
			$action
		);
	}


	/**
	 * @return \Application\DeskPRO\Entity\AppInstance
	 */
	public function getNativeApp()
	{
		return $this->native_app;
	}


	/**
	 * @return \Application\DeskPRO\Entity\AppInstance
	 */
	public function getApp()
	{
		return $this->native_app->getApp();
	}


	/**
	 * @param string $name
	 * @return mixed
	 */
	public function getAppService($name)
	{
		return $this->getContainer()->getAppManager()->getService($name, $this->getApp());
	}
}