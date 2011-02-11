<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage HttpKernel
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\HttpKernel;

use \Symfony\Component\EventDispatcher\Event;
use \Symfony\Component\EventDispatcher\EventDispatcher;
use \Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use \Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Response;

/**
 * This HttpKernel changes how controllers are executed, adding features of pre and post action calls
 * that can be used to perform actions before or after an action, and can override the response object
 * in those cases.
 */
class HttpKernel extends \Symfony\Bundle\FrameworkBundle\HttpKernel
{
	protected function handleRaw(Request $request, $type = self::MASTER_REQUEST)
	{
		// request
        $event = new Event($this, 'core.request', array('request_type' => $type, 'request' => $request));
        $ret = $this->dispatcher->notifyUntil($event);
        if ($event->isProcessed()) {
            return $this->filterResponse($ret, $request, 'A "core.request" listener returned a non response object.', $type);
        }

		// load controller
		if (false === $controller = $this->resolver->getController($request)) {
			throw new NotFoundHttpException('Unable to find the controller.');
		}

		$event = new Event($this, 'core.controller', array('request_type' => $type, 'request' => $request));
        $controller = $this->dispatcher->filter($event, $controller);

        // controller must be a callable
        if (!is_callable($controller)) {
            throw new \LogicException(sprintf('The controller must be a callable (%s).', var_export($controller, true)));
        }

		// controller arguments
        $arguments = $this->resolver->getArguments($request, $controller);

		// is DP controller which has pre/post actions
		if (isset($controller[0]) AND ($controller[0] instanceof \Application\DeskPRO\HttpKernel\Controller\Controller)) {
			$controller_obj = $controller[0];

			// Run pre event
			$event = new Event($this, 'core.deskpro-pre-action', array(
				'request_type' => $type,
				'request' => $request,
				'controller' => $controller_obj,
				'action' => $controller[1],
				'arguments' => $arguments
			));
			$retval = $this->dispatcher->notifyUntil($event);
			if (!$event->isProcessed()) {
				$retval = null;
			}

			// call controller if preaction didnt set one
			if (!$retval OR !($retval instanceof Response)) {
				$retval = call_user_func_array($controller, $arguments);
				$retval = $this->filterResponse($retval, $request, sprintf('The controller must return a response (instead of %s).', is_object($retval) ? 'an object of class '.get_class($retval) : is_array($retval) ? 'an array' : str_replace("\n", '', var_export($retval, true))), $type);
			}

			// Run post event
			$event = new Event($this, 'core.deskpro-post-action', array(
				'request_type' => $type,
				'request' => $request,
				'controller' => $controller_obj,
				'action' => $controller[1],
				'arguments' => $arguments,
				'response' => $retval
			));
			$new_retval = $this->dispatcher->notifyUntil($event);
			if (!$event->isProcessed()) {
				$new_retval = null;
			}

			if ($new_retval AND $new_retval instanceof Response) {
				// Use returned reponse if it exists
				$retval = $new_retval;
			}

		// Regular, any callable controller
		} else {
			$retval = call_user_func_array($controller, $arguments);
		}

		// view
        $event = new Event($this, 'core.view', array('request_type' => $type, 'request' => $request));
        $ret = $this->dispatcher->filter($event, $retval);

        return $this->filterResponse($ret, $request, sprintf('The controller must return a response (instead of %s).', is_object($ret) ? 'an object of class '.get_class($ret) : is_array($ret) ? 'an array' : str_replace("\n", '', var_export($ret, true))), $type);
	}
}