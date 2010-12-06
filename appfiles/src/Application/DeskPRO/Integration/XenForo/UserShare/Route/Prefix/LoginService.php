<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage XenForo
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Application\DeskPRO\Integration\XenForo\UserShare\Route\Prefix;

class LoginService implements \XenForo_Route_Interface
{
	public function match($routePath, \Zend_Controller_Request_Http $request, \XenForo_Router $router)
	{
		$match = $router->getRouteMatch('DeskPRO\Integration\XenForo\UserShare\ControllerService\Login', $routePath);

		return $match;
	}
}