<?php

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets\TicketSaveActions;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\EntityRepository\Organization as OrganizationRepository;
use Application\DeskPRO\Tickets\ExecutorContextInterface;

class VerifyOrgManagers implements TicketSaveActionInterface
{
    /**
     * @var OrganizationRepository
     */
    private $org_repos;

    /**
     * @param OrganizationRepository $org_repos
     */
    public function __construct(OrganizationRepository $org_repos)
    {
        $this->org_repos = $org_repos;
    }

    /**
     * @param Ticket                   $ticket
     * @param ExecutorContextInterface $context
     *
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     */
    public function processTicket(Ticket $ticket, ExecutorContextInterface $context)
    {
        if ($context->getEventType() == 'noop') {
            return;
        }

        if ($ticket->organization) {
            $managers = $this->org_repos->getManagers($ticket->organization);
            foreach ($managers as $manager) {
                if ($manager->getPref('org.manager_auto_add')) {
                    $ticket->addParticipantPerson($manager);
                }
            }
        }
    }
}
