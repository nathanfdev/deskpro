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

namespace Application\DeskPRO\HttpKernel\Controller;
use \Symfony\Component\DependencyInjection\ContainerInterface;

use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Response;
use \Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * The base controller
 */
abstract class Controller extends \Symfony\Bundle\FrameworkBundle\Controller\Controller
{
	/**
	 * The request
	 * @var Symfony\Component\HttpFoundation\Request
	 */
	protected $request;

	/**
	 * The response
	 * @var Symfony\Component\HttpFoundation\Response
	 */
	protected $response;

	/**
	 * Event dispatcher
	 * @var Symfony\Component\EventDispatcher\EventDispatcher
	 */
	protected $event_dispatcher;


	public function __construct(ContainerInterface $container)
	{
		$this->setContainer($container);

		$this->request           = $this->get('request');
		$this->response          = $this->get('response');
		$this->event_dispatcher  = $this->get('event_dispatcher');

		$this->init();
	}


	
	/**
	 * An empty callback function
	 */
	protected function init()
	{

	}



	/**
	 * Called by the HttpKernel before a specific action is executed.
	 *
	 * If this method returns a response object, then that repsonse is used and
	 * the original action is NOT called. Any other return value is discarded.
	 *
	 * @param string $action      The action that will be called
	 * @param array  $arguments   The arguments that will be passed in
	 */
	public function preAction($action, $arguments = null)
	{

	}


	
	/**
	 * Called by the HttpKernel after an action has been executed.
	 *
	 * If this method returns a response object, then that response is used
	 * and the original discarded. Any other return value will be discarded and result
	 * in the original response being used.
	 *
	 * @param Symfony\Component\HttpFoundation\Response $response
	 */
	public function postAction($response)
	{

	}



	/**
	 * Redirect to a named route.
	 *
	 * @param string $route
	 * @param array $parameters
	 * @param int $status
	 * @return Response
	 */
	public function redirectRoute($route, array $parameters = array(), $status = 302)
	{
		$url = $this->generateUrl($route, $parameters, true);
		return $this->redirect($url, $status);
	}
}