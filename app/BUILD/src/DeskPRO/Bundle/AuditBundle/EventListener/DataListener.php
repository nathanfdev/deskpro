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

namespace DeskPRO\Bundle\AuditBundle\EventListener;

use DeskPRO\Bundle\AuditBundle\Configuration\AuditContext;
use DeskPRO\Bundle\AuditBundle\Configuration\Configuration;
use DeskPRO\Bundle\AuditBundle\Configuration\ConfigurationSet;
use DeskPRO\Bundle\AuditBundle\Entity\AuditLogData;
use DeskPRO\Bundle\AuditBundle\Event\LogEvent;
use DeskPRO\Bundle\AuditBundle\Log\FieldFilter\FieldFilterService;
use DeskPRO\Component\Util\ListUtils;

class DataListener
{
    /**
     * @var ConfigurationSet
     */
    protected $configurationSet;

    /**
     * DataListener constructor.
     *
     * @param ConfigurationSet   $configurationSet
     * @param FieldFilterService $fieldFilterService
     */
    public function __construct(ConfigurationSet $configurationSet, FieldFilterService $fieldFilterService)
    {
        $this->configurationSet   = $configurationSet;
        $this->fieldFilterService = $fieldFilterService;
    }

    public function setData(LogEvent $event)
    {
        $log     = $event->getLog();
        $data    = new AuditLogData();
        $context = $event->getContext();

        if ($this->configurationSet->hasConfigurationFor($context)) {
            $configuration = $this->configurationSet->getConfigurationFor($context);
            $diff          = $this->getDiff($configuration, $context);
        } else {
            $diff = [];
        }
        $log->setData($data->setContext([])->setDiff($diff));
    }

    private function getDiff(Configuration $configuration, AuditContext $context)
    {
        $diff = [];

        if ($configuration->shouldSkipFields()) {
            $fields = $configuration->getFields();

            // to be changed when we will come to PHP5.6, so we can use ARRAY_FILTER_USE_BOTH
            $filteredChangeSet = ListUtils::filter(
                $context->getChangeSet(),
                function ($item, $key) use ($fields) {
                    return in_array($key, $fields, true);
                }
            );
        } else {
            $filteredChangeSet = $context->getChangeSet();
        }

        foreach ($filteredChangeSet as $field => $change) {
            $filtered     = $this->fieldFilterService->filterField($context->getEntity(), $field, $change);
            $diff[$field] = [$filtered[0] => $filtered[1]];
        }

        return $diff;
    }
}
