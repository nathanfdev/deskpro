<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
