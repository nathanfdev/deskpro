<?php

namespace Application\DeskPRO\Templating;

use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Sandbox\SecurityPolicy;

/**
 * Class SandboxSecurityPolicy
 *
 * @package Application\DeskPRO\Templating
 */
class SandboxSecurityPolicy extends SecurityPolicy
{
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;

        $allowedMethods = [];

        $allowedMethods = array_merge($allowedMethods, require __DIR__.'/Sandbox/entities.php');
        $allowedMethods = array_merge($allowedMethods, require __DIR__ . '/Sandbox/methods.php');

        $allowedFilters = require __DIR__.'/Sandbox/filters.php';
        $allowedFunctions = require __DIR__.'/Sandbox/functions.php';
        $allowedProperties = require __DIR__.'/Sandbox/properties.php';

        parent::__construct([], $allowedFilters, $allowedMethods, $allowedProperties, $allowedFunctions);
    }

    public function checkMethodAllowed($obj, $method)
    {
        foreach (require __DIR__.'/Sandbox/base_paths.php' as $path) {
            if (preg_match(sprintf('~^%s~', $path), $this->requestStack->getMasterRequest()->getPathInfo())) {
                return;
            }
        }

        foreach (require __DIR__.'/Sandbox/namespaces.php' as $namespace) {
            if (preg_match(sprintf('/^%s/', addslashes($namespace)), get_class($obj))) {
                return;
            }
        }

        error_log('CHECK FUNCTION '.__FUNCTION__.'   :   '.get_class($obj).'   :   '.$method);

        parent::checkMethodAllowed($obj, $method);
    }

    public function checkSecurity($tags, $filters, $functions)
    {
        foreach ($filters as $filter) {
            if (!\in_array($filter, $this->allowedFilters)) {
                throw new SecurityNotAllowedFilterError(sprintf('Filter "%s" is not allowed.', $filter), $filter);
            }
        }

        foreach ($functions as $function) {
            if (!\in_array($function, $this->allowedFunctions)) {
                throw new SecurityNotAllowedFunctionError(sprintf('Function "%s" is not allowed.', $function), $function);
            }
        }
    }

    public function checkPropertyAllowed($obj, $property)
    {
        error_log('CHECK PROPERTY '.__FUNCTION__.'   :   '.get_class($obj).'    :    '.$property);

        parent::checkPropertyAllowed($obj, $property);
    }
}
