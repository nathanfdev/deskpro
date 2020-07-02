<?php

namespace Application\DeskPRO\Templating;

use Symfony\Component\HttpFoundation\RequestStack;
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

        parent::__construct([], [], $allowedMethods, [], []);
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

        foreach (require __DIR__.'/Sandbox/interfaces.php' as $interface) {
            if ($obj instanceof $interface) {
                return;
            }
        }

        error_log('CHECK FUNCTION '.__FUNCTION__.'   :   '.get_class($obj).'   :   '.$method);

        parent::checkMethodAllowed($obj, $method);
    }

    public function checkSecurity($tags, $filters, $functions)
    {
        error_log('CHECK SECURITY '.__FUNCTION__);
    }

    public function checkPropertyAllowed($obj, $property)
    {
        error_log('CHECK PROPERTY '.__FUNCTION__);
    }
}
