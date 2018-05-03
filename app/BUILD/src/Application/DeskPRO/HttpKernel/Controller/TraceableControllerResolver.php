<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\HttpKernel\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\ContainerAware;
use Symfony\Bundle\FrameworkBundle\Controller\ContainerAwareInterface;

/**
 * This controller resolver changes instantiation of controllers to pass in the container
 * to the constructor.
 */
class TraceableControllerResolver extends \Symfony\Component\HttpKernel\Controller\TraceableControllerResolver
{
    protected function createController($controller)
    {
        if (false === strpos($controller, '::')) {
            $count = substr_count($controller, ':');
            if (2 == $count) {
                $controller = $this->parser->parse($controller);
            } elseif (1 == $count) {
                list($service, $method) = explode(':', $controller);

                return [$this->container->get($service), $method];
            } else {
                throw new \LogicException(sprintf('Unable to parse the controller name "%s".', $controller));
            }
        }

        list($class, $method) = explode('::', $controller);

        if (!class_exists($class)) {
            throw new \InvalidArgumentException(sprintf('Class "%s" does not exist.', $class));
        }

        if (is_subclass_of($class, 'Application\\DeskPRO\\HttpKernel\\Controller\\Controller')) {
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

        return [$controller, $method];
    }
}
