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
 */
namespace DeskPRO\Bundle\AppBundle\ActionEngine\Applicators\Tickets;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\ActionEngine\Applicators\ActionApplicatorInterface;

class ApplySetFollowersAction extends AbstractTicketApplicator implements ActionApplicatorInterface
{
    /** @var  Person[] */
    private $followers;

    /**
     * @param Ticket[] $tickets
     */
    public function apply(array $tickets)
    {
        if (empty($this->options['set_followers'])) {
            foreach ($tickets as $ticket) {
                $ticket->resetParticipants();
                $context = $this->tm->createAgentExecutorContext(null, 'unset_followers', 'mass_actions');
                $this->tm->saveTicket($ticket, $context);
            }
        } else {
            $this->init();
            foreach ($tickets as $ticket) {
                $ticket->setAgentParticipants($this->followers);
                $context = $this->tm->createAgentExecutorContext(null, 'set_followers', 'mass_actions');
                $this->tm->saveTicket($ticket, $context);
            }
        }
    }

    private function init()
    {
        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('p')
            ->from('DeskPRO:Person', 'p')
            ->andWhere('p.id IN (:ids)')
            ->setParameter('ids', $this->options['set_followers']);
        $this->followers = $qb->getQuery()->getResult();
    }
}
