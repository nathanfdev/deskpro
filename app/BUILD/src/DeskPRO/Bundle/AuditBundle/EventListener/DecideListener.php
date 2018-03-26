<?php

namespace DeskPRO\Bundle\AuditBundle\EventListener;

use DeskPRO\Bundle\AuditBundle\Configuration\AuditContext;
use DeskPRO\Bundle\AuditBundle\Configuration\ConfigurationSet;
use DeskPRO\Bundle\AuditBundle\Event\LogEvent;

/**
 * Class DecideListener.
 */
class DecideListener
{
    /**
     * @var ConfigurationSet
     */
    protected $configurationSet;

    /**
     * DecideListener constructor.
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
    public function onPreLog(LogEvent $event)
    {
        $context = $event->getContext();
        if ($this->supportedEntity($context)) {
            $event->setShouldLog(true);
            $event->stopPropagation();
        }
    }

    /**
     * @param AuditContext $context
     *
     * @return bool
     */
    private function supportedEntity(AuditContext $context)
    {
        $result = false;
        if ($this->configurationSet->hasConfigurationFor($context)) {
            $configuration = $this->configurationSet->getConfigurationFor($context);
            $result        = $configuration->calculateConditions();
        }

        return $result;
    }
}
