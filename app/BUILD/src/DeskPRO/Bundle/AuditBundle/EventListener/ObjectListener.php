<?php

namespace DeskPRO\Bundle\AuditBundle\EventListener;

use DeskPRO\Bundle\AuditBundle\Configuration\ConfigurationSet;
use DeskPRO\Bundle\AuditBundle\Event\LogEvent;
use DeskPRO\Component\Util\TypeUtils;

/**
 * Class ObjectListener.
 */
class ObjectListener
{
    /**
     * @var ConfigurationSet
     */
    protected $configurationSet;

    /**
     * Constructor.
     *
     * @param ConfigurationSet $configurationSet
     */
    public function __construct(ConfigurationSet $configurationSet)
    {
        $this->configurationSet = $configurationSet;
    }

    /**
     * @param LogEvent $event
     */
    public function setObject(LogEvent $event)
    {
        $log     = $event->getLog();
        $context = $event->getContext();
        $entity  = $context->getEntity();

        $configuration = $this->configurationSet->getConfigurationFor($context);
        $log->setObjectType(TypeUtils::getBaseTypeName($entity));
        $log->setObjectName($configuration->getNamingStrategy()->getName($entity, $log));

        if (method_exists($entity, 'getId')) {
            $log->setObjectId($entity->getId());
        }
    }
}
