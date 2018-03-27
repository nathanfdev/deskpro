<?php

namespace DeskPRO\Bundle\AuditBundle\Configuration;

use DeskPRO\Component\Util\TypeUtils;

/**
 * Class ConfigurationSet.
 */
class ConfigurationSet
{
    /**
     * @var Configuration[]
     */
    private $configurations;

    /**
     * @param Configuration $configuration
     */
    public function registerConfiguration(Configuration $configuration)
    {
        $key = $this->getKey($configuration->getEntityClass(), $configuration->getAction());

        $this->configurations[$key] = $configuration;
    }

    /**
     * @param AuditContext $context
     *
     * @return bool
     */
    public function hasConfigurationFor(AuditContext $context)
    {
        $entityClass = TypeUtils::getEntityClass($context->getEntity());

        return isset($this->configurations[$entityClass.'::'.$context->getAction()]);
    }

    /**
     * @param AuditContext $context
     *
     * @return Configuration||null
     */
    public function getConfigurationFor(AuditContext $context)
    {
        $entityClass = TypeUtils::getEntityClass($context->getEntity());
        $key         = $this->getKey($entityClass, $context->getAction());

        if (isset($this->configurations[$key])) {
            $configuration = $this->configurations[$key];
            $configuration->setContext($context);

            return $configuration;
        }

        return;
    }

    private function getKey($entityClass, $action)
    {
        return sprintf('%s::%s', $entityClass, $action);
    }
}
