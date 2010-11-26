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
use \Symfony\Bundle\FrameworkBundle\Controller\ContainerAware;
use \Symfony\Bundle\FrameworkBundle\Controller\ContainerAwareInterface;


/**
 * This controller resolver changes instantiation of controllers to pass in the container
 * to the constructor.
 */
class ControllerResolver extends \Symfony\Bundle\FrameworkBundle\Controller\ControllerResolver
{
    protected function createController($controller)
    {
        if (false === strpos($controller, '::')) {
            $count = substr_count($controller, ':');
            if (2 == $count) {
                // controller in the a:b:c notation then
                $controller = $this->converter->fromShortNotation($controller);
            } elseif (1 == $count) {
                // controller in the service:method notation
                list($service, $method) = explode(':', $controller);

                return array($this->container->get($service), $method);
            } else {
                throw new \LogicException(sprintf('Unable to parse the controller name "%s".', $controller));
            }
        }

        list($class, $method) = explode('::', $controller);

        if (!class_exists($class)) {
            throw new \InvalidArgumentException(sprintf('Class "%s" does not exist.', $class));
        }

		if (is_subclass_of($class, 'DeskPRO\\HttpKernel\\Controller\\Controller')) {
			$controller = new $class($this->container);
		} else {
			$controller = new $class();
			if (is_subclass_of($class, 'Symfony\\Component\\DependencyInjection\\ContainerAwareInterface')) {
			//if ($controller instanceof ContainerAwareInterface OR $controller instanceof ContainerAware) {
				$controller->setContainer($this->container);
			} else {
				die($class);
			}
		}

        return array($controller, $method);
    }
}