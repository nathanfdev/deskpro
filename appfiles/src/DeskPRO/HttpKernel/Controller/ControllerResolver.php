<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage HttpKernel
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace DeskPRO\HttpKernel\Controller;

use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Bundle\FrameworkBundle\Controller\ControllerInterface;


/**
 * This controller resolver changes instantiation of controllers to pass in the container
 * to the constructor.
 */
class ControllerResolver extends \Symfony\Bundle\FrameworkBundle\Controller\ControllerResolver
{
    protected function createController($controller)
    {
        if (false === strpos($controller, '::')) {
            // must be a controller in the a:b:c notation then
            $controller = $this->converter->fromShortNotation($controller);
        }

        list($class, $method) = explode('::', $controller);

        if (!class_exists($class)) {
            throw new \InvalidArgumentException(sprintf('Class "%s" does not exist.', $class));
        }

		if (is_subclass_of($class, 'DeskPRO\\HttpKernel\\Controller\\Controller')) {
			$controller = new $class($this->container);
		} else {
			$controller = new $class();
			if ($controller instanceof ControllerInterface) {
				$controller->setContainer($this->container);
			}
		}

        return array($controller, $method);
    }
}