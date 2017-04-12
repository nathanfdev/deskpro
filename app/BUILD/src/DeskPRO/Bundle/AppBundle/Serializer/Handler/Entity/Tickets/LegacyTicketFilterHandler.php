<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
