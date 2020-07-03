<?php

namespace Application\DeskPRO\Templating;

use Application\DeskPRO\NewSettings\SettingsResolver;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Sandbox\SecurityPolicy;

/**
 * Class SandboxSecurityPolicy
 *
 * Sandboxed Twig may be disabled via $SETTINGS['templating.disable_sandbox'] = true
 *
 * @package Application\DeskPRO\Templating
 */
class SandboxSecurityPolicy extends SecurityPolicy
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var SettingsResolver
     */
    private $settings;

    /**
     * SandboxSecurityPolicy constructor.
     *
     * @param RequestStack $requestStack
     * @param SettingsResolver $settings
     */
    public function __construct(RequestStack $requestStack, SettingsResolver $settings)
    {
        $this->requestStack = $requestStack;
        $this->settings     = $settings;

        parent::__construct(
            [],
            $this->getAllowedFilters(),
            $this->getAllowedMethods(),
            $this->getAllowedProperties(),
            $this->getAllowedFunctions()
        );
    }

    /**
     * {@inheritDoc}
     */
    public function checkMethodAllowed($obj, $method)
    {
        if ($this->isSandboxDisabled()) {
            return;
        }

        parent::checkMethodAllowed($obj, $method);
    }

    /**
     * {@inheritDoc}
     */
    public function checkSecurity($tags, $filters, $functions)
    {
        if ($this->isSandboxDisabled()) {
            return;
        }

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

    /**
     * {@inheritDoc}
     */
    public function checkPropertyAllowed($obj, $property)
    {
        if ($this->isSandboxDisabled()) {
            return;
        }

        parent::checkPropertyAllowed($obj, $property);
    }

    /**
     * Returns TRUE if sandboxed twig is disabled
     *
     * @return bool
     */
    private function isSandboxDisabled()
    {
        if ($this->settings->getGlobalSettings()->get('templating.disable_sandbox', false)) {
            return true;
        }

        $currentPath = $this->requestStack->getMasterRequest()->getPathInfo();
        foreach (require __DIR__.'/Sandbox/whitelists/base_paths.php' as $path) {
            if (preg_match(sprintf('~^%s~', $path), $currentPath)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array
     */
    private function getAllowedMethods()
    {
        $allowedMethods = [];
        $allowedMethods = array_merge($allowedMethods, require __DIR__.'/Sandbox/whitelists/entities.php');
        $allowedMethods = array_merge($allowedMethods, require __DIR__ .'/Sandbox/whitelists/methods.php');

        return $allowedMethods;
    }

    /**
     * @return array
     */
    private function getAllowedFilters()
    {
        return require __DIR__.'/Sandbox/whitelists/filters.php';
    }

    /**
     * @return array
     */
    private function getAllowedFunctions()
    {
        return require __DIR__.'/Sandbox/whitelists/functions.php';
    }

    /**
     * @return array
     */
    private function getAllowedProperties()
    {
        return require __DIR__.'/Sandbox/whitelists/properties.php';
    }
}
