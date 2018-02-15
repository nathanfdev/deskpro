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

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Filters;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\Notification\Event\Ticket\TicketUpdatedEvent;
use DeskPRO\Bundle\AppBundle\Notification\NotificationEventManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class FilterChangeSet.
 */
class FilterChangeSet
{
    /**
     * @var \Application\DeskPRO\Entity\Ticket
     */
    private $ticket;

    /**
     * @var int
     */
    private $state_id;

    /**
     * @var \Application\DeskPRO\Entity\LegacyTicketFilter[]
     */
    private $affected_filters = [];

    /**
     * @var FilterChange[]
     */
    private $changed_filters = [];

    /**
     * @var array
     */
    private $field_versions = [];

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var NotificationEventManager
     */
    private $eventManager;

    /**
     * Constructor.
     *
     * @param Ticket                   $ticket
     * @param int                      $state_id
     * @param array                    $affected_filters
     * @param array                    $changed_filters
     * @param array                    $field_versions
     * @param EventDispatcherInterface $eventDispatcher
     * @param NotificationEventManager $eventManager
     */
    public function __construct(
        Ticket $ticket,
        $state_id,
        array $affected_filters,
        array $changed_filters,
        array $field_versions,
        EventDispatcherInterface $eventDispatcher,
        NotificationEventManager $eventManager
    ) {
        $this->ticket           = $ticket;
        $this->state_id         = $state_id;
        $this->affected_filters = $affected_filters;
        $this->changed_filters  = $changed_filters;
        $this->field_versions   = $field_versions;
        $this->eventDispatcher  = $eventDispatcher;
        $this->eventManager     = $eventManager;
    }

    /**
     * @return \Application\DeskPRO\Entity\LegacyTicketFilter[]
     */
    public function getAffectedFilters()
    {
        return $this->affected_filters;
    }

    /**
     * @return \Application\DeskPRO\Tickets\Filters\FilterChange[]
     */
    public function getChangedFilters()
    {
        return $this->changed_filters;
    }

    /**
     * @return array
     */
    public function getFieldVersions()
    {
        return $this->field_versions;
    }

    /**
     * @return int
     */
    public function getStateId()
    {
        return $this->state_id;
    }

    /**
     * @return \Application\DeskPRO\Entity\Ticket
     */
    public function getTicket()
    {
        return $this->ticket;
    }

    /**
     * Get an array of client messages to send to clients about lists updating.
     *
     * @param array $onlineAgentsIds
     */
    public function getListUpdateClientMessages(array $onlineAgentsIds)
    {
        $ticketId   = $this->ticket->getId();
        $operations = [];

        foreach ($this->changed_filters as $filter_change) {
            $filter = $filter_change->getFilter();

            $filterId = $filter['id'];

            $addedTargets = array_values(array_map(
                function (Person $agent) {
                    return $agent->getId();
                },
                $filter_change->getAgentsAdded()
            ));

            if ($addedTargets) {
                $operations[] = [
                    'op'        => 'add',
                    'filter_id' => $filterId,
                    'targets'   => $addedTargets,
                ];
            }

            $removedTargets = array_values(array_map(
                function (Person $agent) {
                    return $agent->getId();
                },
                $filter_change->getAgentsRemoved()
            ));

            if ($removedTargets) {
                $operations[] = [
                    'op'        => 'del',
                    'filter_id' => $filterId,
                    'targets'   => $removedTargets,
                ];
            }
        }

        if ($operations) {
            $this->eventDispatcher->dispatch(
                TicketUpdatedEvent::EVENT_NAME,
                new TicketUpdatedEvent(
                    'agent.filter-update',
                    [
                        'ticket_id'  => $ticketId,
                        'operations' => $operations,
                    ]
                )
            );
        }

        $this->eventManager->deliver(true);
    }
}
