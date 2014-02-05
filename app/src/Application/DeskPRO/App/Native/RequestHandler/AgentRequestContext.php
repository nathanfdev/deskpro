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

use Application\DeskPRO\App\Native\NativeApp;
use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\Person;
use Symfony\Component\HttpFoundation\Request;
use Application\AgentBundle\Controller\AbstractController;

class AgentRequestContext
{
	/**
	 * @var \Application\DeskPRO\DependencyInjection\DeskproContainer
	 */
	private $container;

	/**
	 * @var \Symfony\Component\HttpFoundation\Request
	 */
	private $request;

	/**
	 * @var \Application\DeskPRO\App\Native\NativeApp
	 */
	private $native_app;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 */
	private $agent;

	/**
	 * @var string
	 */
	private $action;

	/**
	 * @var \Application\AgentBundle\Controller\AbstractController
	 */
	private $controller;


	/**
	 * @param DeskproContainer $container
	 * @param Request $request
	 * @param AbstractController $controller
	 * @param NativeApp $native_app
	 * @param Person $agent
	 * @param string $action
	 */
	public function __construct(DeskproContainer $container, Request $request, AbstractController $controller,  NativeApp $native_app, Person $agent, $action)
	{
		$this->container  = $container;
		$this->request    = $request;
		$this->controller = $controller;
		$this->native_app = $native_app;
		$this->agent      = $agent;
		$this->action     = $action;
	}


	/**
	 * @return string
	 */
	public function getAction()
	{
		return $this->action;
	}


	/**
	 * @return \Application\DeskPRO\Entity\Person
	 */
	public function getAgent()
	{
		return $this->agent;
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
	 * @return \Application\DeskPRO\Entity\AppPackage
	 */
	public function getPackage()
	{
		return $this->native_app->getPackage();
	}


	/**
	 * @return \Application\DeskPRO\DependencyInjection\DeskproContainer
	 */
	public function getContainer()
	{
		return $this->container;
	}


	/**
	 * @return \Symfony\Component\HttpFoundation\Request
	 */
	public function getRequest()
	{
		return $this->request;
	}


	/**
	 * @return \Application\DeskPRO\DBAL\Connection
	 */
	public function getDb()
	{
		return $this->container->getDb();
	}


	/**
	 * @return \Application\DeskPRO\Input\Reader
	 */
	public function getIn()
	{
		return $this->container->getIn();
	}


	/**
	 * @return \Doctrine\ORM\EntityManager
	 */
	public function getEm()
	{
		return $this->container->getEm();
	}


	/**
	 * @param string $message
	 * @return \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
	 */
	public function createNotFoundException($message = 'Not Found')
	{
		return $this->controller->createNotFoundException();
	}

	/**
	 * Create a JSON response.
	 *
	 * @param string|array $content
	 * @param int $status_code
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function createJsonResponse($content, $status_code = 200)
	{
		return $this->controller->createJsonResponse($content, $status_code);
	}


	/**
	 * Create a JSON response.
	 *
	 * @param string $content
	 * @param int $status_code
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function createResponse($content, $status_code = 200)
	{
		return $this->controller->createResponse($content, $status_code);
	}
}