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
 * @subpackage HttpKernel
 */

namespace Application\DeskPRO\HttpKernel;

use Application\DeskPRO\App;
use Application\DeskPRO\HttpKernel\Event\PrePostEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Scope;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\FinishRequestEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernel as BaseHttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class HttpKernel extends BaseHttpKernel
{
	/**
	 * @var \Symfony\Component\DependencyInjection\ContainerInterface
	 */
	protected $container;
	/**
	 * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
	 */
	protected $dispatcher;
	/**
	 * @var \Symfony\Component\HttpKernel\Controller\ControllerResolverInterface
	 */
	protected $resolver;
	/**
	 * @var \Symfony\Component\HttpFoundation\RequestStack
	 */
	protected $requestStack;


	/**
	 * @param EventDispatcherInterface    $dispatcher
	 * @param ContainerInterface          $container
	 * @param ControllerResolverInterface $controllerResolver
	 * @param RequestStack                $requestStack
	 */
	public function __construct(EventDispatcherInterface $dispatcher, ContainerInterface $container, ControllerResolverInterface $controllerResolver, RequestStack $requestStack = null)
	{
		$this->dispatcher   = $dispatcher;
		$this->container    = $container;
		$this->resolver     = $controllerResolver;
		$this->requestStack = $requestStack ?: new RequestStack();

		if (!$container->hasScope('request')) {
			$container->addScope(new Scope('request'));
		}
	}


	/**
	 * @param Request $request
	 * @param int     $type
	 * @param bool    $catch
	 * @return Response
	 * @throws \Exception
	 * @throws \Exception
	 */
	public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
	{
		$request->headers->set('X-Php-Ob-Level', ob_get_level());

		$this->container->enterScope('request');
		$this->container->set('request', $request, 'request');

		try {
			$response = $this->handleRaw($request, $type);

			$this->container->set('request', null, 'request');
			$this->container->leaveScope('request');

			return $response;
		} catch (\Exception $e) {
			if (false === $catch) {
				$this->finishRequest($request, $type);

				$this->container->set('request', null, 'request');
				$this->container->leaveScope('request');

				throw $e;
			}

			$this->container->set('request', null, 'request');
			$this->container->leaveScope('request');

			return $this->handleException($e, $request, $type);
		}
	}


	/**
	 * [Copied from BaseHttpKernel]
	 *
	 * @param Request  $request
	 * @param Response $response
	 */
	public function terminate(Request $request, Response $response)
	{
		$this->dispatcher->dispatch(KernelEvents::TERMINATE, new PostResponseEvent($this, $request, $response));
	}


	/**
	 * Custom DeskPRO code. Same as BaseHttpKernel except we run pre/post methods, and also add in handling for newrelic
	 *
	 * @param Request $request
	 * @param int     $type
	 * @return Response
	 * @throws \LogicException
	 * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
	 */
	private function handleRaw(Request $request, $type = self::MASTER_REQUEST)
	{
		$this->requestStack->push($request);

		// request
		$event = new GetResponseEvent($this, $request, $type);
		$this->dispatcher->dispatch(KernelEvents::REQUEST, $event);

		if ($event->hasResponse()) {
			return $this->filterResponse($event->getResponse(), $request, $type);
		}

		// load controller
		if (false === $controller = $this->resolver->getController($request)) {
			throw new NotFoundHttpException(sprintf('Unable to find the controller for path "%s". Maybe you forgot to add the matching route in your routing configuration?', $request->getPathInfo()));
		}

		$event = new FilterControllerEvent($this, $controller, $request, $type);
		$this->dispatcher->dispatch(KernelEvents::CONTROLLER, $event);
		$controller = $event->getController();

		if (isset($controller[0]) && $controller[0]) {
			dp_pagelog_set('page_id', get_class($controller[0]) . '::' . $controller[1]);
		}

		// controller arguments
		$arguments = $this->resolver->getArguments($request, $controller);

		if (isset($controller[0]) AND $controller[0] instanceof \Application\DeskPRO\HttpKernel\Controller\Controller) {
			//==BEGIN:MONITORING==
			if (extension_loaded('newrelic')) {
				$ctrl_name = preg_replace('#^Application\\\\(.*?)(?:Bundle)?\\\\Controller\\\\(.*?)Controller$#', '$1:$2', get_class($controller[0]));
				$ctrl_name .= ':' . preg_replace('#Action$#', '', $controller[1]);
				newrelic_name_transaction($ctrl_name);

				$args_str = array();
				foreach ($arguments as $a) {
					if (is_scalar($a)) {
						if (is_bool($a)) {
							$args_str[] = $a ? 'true' : 'false';
						} else {
							$args_str[] = $a;
						}
					} else if (is_array($a)) {
						$single_array = true;
						foreach ($a as $suba) {
							if (!is_scalar($suba)) { $single_array = false; break; }
						}
						if ($single_array) {
							$args_str[] = '[' . implode(', ', $a) . ']';
						} else {
							$args_str[] = '[array:' . count($a) . ']';
						}
					} else if (is_object($a)) {
						$args_str[] = get_class($a);
					} else {
						$args_str[] = gettype($a);
					}
				}
				newrelic_add_custom_parameter('route_params', implode(', ', $args_str));
			}
			//==END:MONITORING==

			// Run pre event
			$event = new PrePostEvent(array(
				'request_type' => $type,
				'request' => $request,
				'controller' => $controller[0],
				'action' => $controller[1],
				'arguments' => $arguments
			));
			$controller[0]->DeskPRO_onControllerPreAction($event);
			$response = null;
			if ($event->hasResponse()) {
				$response = $event->getResponse();
			} else {
				$this->dispatcher->dispatch('DeskPRO_onControllerPreAction', $event);
				if ($event->hasResponse()) {
					$response = $event->getResponse();
				}
			}

			// call controller if preaction didnt return a response
			if (!$response) {
				try {
					$response = call_user_func_array($controller, $arguments);
				} catch (\Exception $e) {
					$e_response = $controller[0]->handleActionException($e);
					if (!$e_response || !($e_response instanceof Response)) {
						throw $e;
					}
					$response = $e_response;
				}
			}

			// Run post event
			$event = new PrePostEvent(array(
				'request_type' => $type,
				'request' => $request,
				'controller' => $controller[0],
				'action' => $controller[1],
				'arguments' => $arguments,
				'response' => $response
			));

			$controller[0]->DeskPRO_onControllerPostAction($event);
			if ($event->hasResponse()) {
				$response = $event->getResponse();
			} else {
				$this->dispatcher->dispatch('DeskPRO_onControllerPostAction', $event);
				if ($event->hasResponse()) {
					$response = $event->getResponse();
				}
			}
		} else {
			$response = call_user_func_array($controller, $arguments);
		}

		// view
		if (!$response instanceof Response) {
			$event = new GetResponseForControllerResultEvent($this, $request, $type, $response);
			$this->dispatcher->dispatch(KernelEvents::VIEW, $event);

			if ($event->hasResponse()) {
				$response = $event->getResponse();
			}

			if (!$response instanceof Response) {
				$msg = sprintf('The controller must return a response (%s given).', $this->varToString($response));

				// the user may have forgotten to return something
				if (null === $response) {
					$msg .= ' Did you forget to add a return statement somewhere in your controller?';
				}
				throw new \LogicException($msg);
			}
		}

		return $this->filterResponse($response, $request, $type);
	}


	/**
	 * [Copied from BaseHttpKernel]
	 *
	 * @param Response $response
	 * @param Request  $request
	 * @param int      $type
	 * @return Response
	 */
	private function filterResponse(Response $response, Request $request, $type)
	{
		$event = new FilterResponseEvent($this, $request, $type, $response);

		$this->dispatcher->dispatch(KernelEvents::RESPONSE, $event);

		$this->finishRequest($request, $type);

		return $event->getResponse();
	}


	/**
	 * [Copied from BaseHttpKernel]
	 *
	 * @param Request $request
	 * @param int     $type
	 */
	private function finishRequest(Request $request, $type)
	{
		$this->dispatcher->dispatch(KernelEvents::FINISH_REQUEST, new FinishRequestEvent($this, $request, $type));
		$this->requestStack->pop();
	}


	/**
	 * [Copied from BaseHttpKernel]
	 *
	 * @param \Exception $e
	 * @param Request    $request
	 * @param int        $type
	 * @return Response
	 * @throws \Exception
	 * @throws \InvalidArgumentException
	 */
	private function handleException(\Exception $e, $request, $type)
	{
		$event = new GetResponseForExceptionEvent($this, $request, $type, $e);
		$this->dispatcher->dispatch(KernelEvents::EXCEPTION, $event);

		// a listener might have replaced the exception
		$e = $event->getException();

		if (!$event->hasResponse()) {
			$this->finishRequest($request, $type);

			throw $e;
		}

		$response = $event->getResponse();

		// the developer asked for a specific status code
		if ($response->headers->has('X-Status-Code')) {
			$response->setStatusCode($response->headers->get('X-Status-Code'));

			$response->headers->remove('X-Status-Code');
		} elseif (!$response->isClientError() && !$response->isServerError() && !$response->isRedirect()) {
			// ensure that we actually have an error response
			if ($e instanceof HttpExceptionInterface) {
				// keep the HTTP status code and headers
				$response->setStatusCode($e->getStatusCode());
				$response->headers->add($e->getHeaders());
			} else {
				$response->setStatusCode(500);
			}
		}

		try {
			return $this->filterResponse($response, $request, $type);
		} catch (\Exception $e) {
			return $response;
		}
	}


	/**
	 * [Copied from BaseHttpKernel]
	 *
	 * @param $var
	 * @return string
	 */
	private function varToString($var)
	{
		if (is_object($var)) {
			return sprintf('Object(%s)', get_class($var));
		}

		if (is_array($var)) {
			$a = array();
			foreach ($var as $k => $v) {
				$a[] = sprintf('%s => %s', $k, $this->varToString($v));
			}

			return sprintf("Array(%s)", implode(', ', $a));
		}

		if (is_resource($var)) {
			return sprintf('Resource(%s)', get_resource_type($var));
		}

		if (null === $var) {
			return 'null';
		}

		if (false === $var) {
			return 'false';
		}

		if (true === $var) {
			return 'true';
		}

		return (string) $var;
	}
}
