<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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
