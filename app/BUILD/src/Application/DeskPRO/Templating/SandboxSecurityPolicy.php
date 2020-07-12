<?php

namespace Application\DeskPRO\Templating;

use Application\DeskPRO\NewSettings\SettingsResolver;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Sandbox\SecurityPolicy;

/**
 * Class SandboxSecurityPolicy
 *
 * Sandboxed Twig may be disabled via $SETTINGS['templating.disable_sandbox'] = true
 *
 * If you want to use "learning mode", set $SETTINGS['templating.enable_sandbox_learning'] = true;
 * Learning mode will allow you to "collect" the security exceptions in the server logs so that they may be added as a
 * group to the whitelist. WARNING: learning mode will effectively disable sandbox mode, making it fail silently.
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
     * @param RequestStack     $requestStack
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
        if (get_class($obj) === \stdClass::class) { // exclude inheritance and whitelist \stdClass
            return;
        }

        if ($this->isSandboxDisabled()) {
            return;
        }

        if ($this->isNamespaceAllowed($obj)) {
            return;
        }

        try {
            parent::checkMethodAllowed($obj, $method);
        } catch (SecurityError $e) {
            if (!$this->isInLearningMode()) {
                throw $e;
            }

            $this->leaningLogEntry("METHOD", sprintf('%s::%s', get_class($obj), $method));
        }
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
                if (!$this->isInLearningMode()) {
                    throw new SecurityNotAllowedFilterError(sprintf('Filter "%s" is not allowed.', $filter), $filter);
                }

                $this->leaningLogEntry("FILTER", $filter);
            }
        }

        foreach ($functions as $function) {
            if (!\in_array($function, $this->allowedFunctions)) {
                if (!$this->isInLearningMode()) {
                    throw new SecurityNotAllowedFunctionError(sprintf('Function "%s" is not allowed.', $function), $function);
                }

                $this->leaningLogEntry("FUNCTION", $function);
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function checkPropertyAllowed($obj, $property)
    {
        if (get_class($obj) === \stdClass::class) { // exclude inheritance and whitelist \stdClass
            return;
        }

        if ($this->isSandboxDisabled()) {
            return;
        }

        if ($this->isNamespaceAllowed($obj)) {
            return;
        }

        try {
            parent::checkPropertyAllowed($obj, $property);
        } catch (SecurityError $e) {
            if (!$this->isInLearningMode()) {
                throw $e;
            }

            $this->leaningLogEntry("PROPERTY", sprintf('%s::%s', get_class($obj), $property));
        }
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

        if (!$this->requestStack->getMasterRequest()) {
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
     * @param object $obj
     * @return bool
     */
    private function isNamespaceAllowed($obj)
    {
        foreach (require __DIR__.'/Sandbox/whitelists/namespaces.php' as $namespace) {
            if (strpos(get_class($obj), $namespace) === 0) {
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
        $allowedMethods = array_merge($allowedMethods, require __DIR__.'/Sandbox/whitelists/methods.php');

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

    /**
     * @return bool
     */
    private function isInLearningMode()
    {
        return $this->settings->getGlobalSettings()->get('templating.enable_sandbox_learning', false);
    }

    /**
     * @param string $type
     * @param string $message
     */
    private function leaningLogEntry($type, $message)
    {
        error_log(sprintf('[Twig Sandbox Security Exception] %s %s', $type, $message));
    }
}
