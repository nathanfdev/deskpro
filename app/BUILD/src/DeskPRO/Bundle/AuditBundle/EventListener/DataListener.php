<?php

namespace DeskPRO\Bundle\AuditBundle\EventListener;

use Application\DeskPRO\Domain\DomainObject;
use DeskPRO\Bundle\AppBundle\Entity\EntityInterface;
use DeskPRO\Bundle\AuditBundle\Configuration\AuditContext;
use DeskPRO\Bundle\AuditBundle\Configuration\Configuration;
use DeskPRO\Bundle\AuditBundle\Configuration\ConfigurationSet;
use DeskPRO\Bundle\AuditBundle\Document\AuditLogData;
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
        $diff   = [];
        $entity = $context->getEntity();
        $action = $context->getAction();

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

        foreach ($filteredChangeSet as $field => $changes) {
            $filtered = [];
            foreach ($changes as $change) {
                if ($this->fieldFilterService->hasFilters($entity, $field, $action)) {
                    $filtered[] = $this->fieldFilterService->filterField($entity, $field, $action, $change);
                // next lines are just fallback to handle entiies, collections, arrays or dates
                // TODO think how we can refactor it to reduce this fantastic and awesome else if
                } elseif ($change instanceof EntityInterface || $change instanceof DomainObject) {
                    $filtered[] = $this->fieldFilterService->getNamedFilter('entity')->filter($change);
                } elseif ($change instanceof \Traversable || is_array($change)) {
                    $filtered[] = $this->fieldFilterService->getNamedFilter('collection')->filter($change);
                } elseif ($change instanceof \DateTime) {
                    $filtered[] = $this->fieldFilterService->getNamedFilter('date')->filter($change);
                } else {
                    $filtered[] = $change;
                }
            }
            $diff[$field] = $filtered;
        }

        return $diff;
    }
}
