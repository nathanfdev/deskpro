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

namespace Application\DeskPRO\WorkerProcess\Job;

use DeskPRO\Bundle\AppBundle\Entity\TicketFollowUp;
use DpSys\LowError\SystemErrorHandler;

/**
 * Class TicketFollowUps.
 */
class TicketFollowUps extends AbstractJob
{
    const DEFAULT_INTERVAL = 60;

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $em = $this->getContainer()->getEm();
        $qb = $em->createQueryBuilder();
        $qb
            ->select('f')
            ->from(TicketFollowUp::class, 'f')
            ->where(
                'f.status = :status',
                'f.dateToRun <= :date_to_run'
            )
            ->setParameter('status', TicketFollowUp::STATUS_PENDING)
            ->setParameter('date_to_run', new \DateTime())
            ->setMaxResults(100)
        ;

        $ticketManager = $this->getContainer()->getTicketManager();

        /** @var TicketFollowUp[] $followUps */
        $followUps = $qb->getQuery()->getResult();
        foreach ($followUps as $followUp) {
            try {
                $em->beginTransaction();

                // apply follow up's macro actions
                $person = $followUp->getPerson();
                $ticket = $followUp->getTicket();
                $ticket->disableAutoTicketProcess();

                $followUp->getActionsCollection()->apply($ticket->getTicketLogger(), $ticket, $person);
                $context = $ticketManager->createSystemExecutorContext();
                $ticketManager->saveTicket($ticket, $context);

                // mark the follow up status as done
                $followUp->setStatus(TicketFollowUp::STATUS_DONE);

                $em->persist($followUp);
                $em->flush();
                $em->commit();
            } catch (\Exception $e) {
                $em->rollback();
                SystemErrorHandler::logException($e, true, 'follow_up_apply_'.$followUp->getId());
            }
        }
    }
}
