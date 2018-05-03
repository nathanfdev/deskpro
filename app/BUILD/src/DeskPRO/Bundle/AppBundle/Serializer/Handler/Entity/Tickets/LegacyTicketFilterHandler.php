<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\Tickets;

use Application\DeskPRO\Entity\LegacyTicketFilter as LegacyTicketFilterEntity;
use DeskPRO\Bundle\AppBundle\DataService\Tickets\LegacyFilterSet\LegacyTicketFilterSet;
use DeskPRO\Bundle\AppBundle\DataService\Tickets\LegacyFilterSet\LegacyTicketFilterSetDataService;
use DeskPRO\Bundle\AppBundle\Serializer\Deferred\CallbackDeferredProperty;
use DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\AbstractEntityHandler;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\LegacyTicketFilter as LegacyTicketFilterModel;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;

/**
 * Class LegacyTicketFilterHandler.
 */
class LegacyTicketFilterHandler extends AbstractEntityHandler
{
    /**
     * @var LegacyTicketFilterSetDataService
     */
    private $dataService;

    /**
     * Constructor.
     *
     * @param LegacyTicketFilterSetDataService $dataService
     */
    public function __construct(LegacyTicketFilterSetDataService $dataService)
    {
        $this->dataService = $dataService;
    }

    /**
     * {@inheritdoc}
     */
    public static function getClassNames()
    {
        return LegacyTicketFilterEntity::class;
    }

    /**
     * {@inheritdoc}
     *
     * @param LegacyTicketFilterEntity $entity
     */
    public function createModel($entity, SideloadSerializationContext $context)
    {
        $set      = $this->getSet($entity);
        $deferred = new CallbackDeferredProperty([$this, 'getSet'], [$entity]);
        $model    = new LegacyTicketFilterModel($entity, $set);

        $context->getSideloadStore()->addCustomSideload('ticket_filter_set', $set->getId(), $deferred, $model);

        return $model;
    }

    /**
     * @param LegacyTicketFilterEntity $entity
     *
     * @return LegacyTicketFilterSet
     */
    public function getSet(LegacyTicketFilterEntity $entity)
    {
        $type = $this->dataService->getFilterSetType($entity);

        return $this->dataService->getFilterSet($type);
    }
}
