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

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets\TicketSaveActions;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Doctrine\ORM\EntityManager;

/**
 * Class RunFilterUpdates.
 */
class RunFilterUpdates implements TicketSaveActionInterface, ErrorCheckedInterface
{
    /**
     * @var DeskproContainer
     */
    protected $container;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @param DeskproContainer $container
     */
    public function __construct(DeskproContainer $container)
    {
        $this->container = $container;
        $this->em        = $this->container->get('doctrine.orm.default_entity_manager');
    }

    /**
     * @param Ticket                   $ticket
     * @param ExecutorContextInterface $context
     */
    public function processTicket(Ticket $ticket, ExecutorContextInterface $context)
    {
        if ($context->getEventType() == 'noop') {
            $context->getLogger()->info('[RunFilterUpdates] None (noop)');

            return;
        }

        /** @var \Application\DeskPRO\EntityRepository\Person $personRepo */
        $personRepo     = $this->em->getRepository(Person::class);
        $onlineAgentIds = $personRepo->getActiveAgents(true);

        $detector        = $this->container->getTicketFilterChangeDetector();
        $change_set      = $detector->getFilterChangeSet($ticket, $context);
        $client_messages = $change_set->getListUpdateClientMessages($onlineAgentIds);

        $rows     = [];
        $channels = [];
        $agents   = [];

        foreach ($client_messages as $cm) {
            $channel  = $cm->getChannel();
            $person   = $cm->getForPerson();
            $personId = $person ? $person->getId() : null;

            $channels[$channel]      = true;
            $agents[(int) $personId] = true;
            $rows[]                  = [
                'channel'           => $channel,
                'auth'              => $cm->getAuth(),
                'data'              => serialize($cm->getData()),
                'created_by_client' => $cm->getCreatedByClient() ?: '',
                'for_client'        => $cm->getForClient() ?: null,
                'date_created'      => $cm->getDateCreated()->format('Y-m-d H:i:s'),
                'for_person_id'     => $personId,
            ];
        }

        if ($rows) {
            $ts = microtime(true);
            $context->getLogger()->info(sprintf('[RunFilterUpdates] Inserting %d client_messages for %d agents in channels: %s', count($client_messages), count($agents), implode(', ', array_keys($channels))));
            $this->container->getDb()->batchInsert('client_messages', $rows);
            $context->getLogger()->info(sprintf('[RunFilterUpdates] Done inserts in %.3fs', microtime(true) - $ts));
        } else {
            $context->getLogger()->info('[RunFilterUpdates] None (empty)');
        }
    }
}
