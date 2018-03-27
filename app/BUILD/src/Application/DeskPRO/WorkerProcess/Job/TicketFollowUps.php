<?php

namespace Application\DeskPRO\WorkerProcess\Job;

use Application\DeskPRO\Tickets\ExecutorContext;
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

                $actionCollection = $followUp->getActionsCollection();
                $actionCollection->apply($ticket->getTicketLogger(), $ticket, $person);

                $eventType = ExecutorContext::EVENT_UPDATE;
                if ($actionCollection->getReplyActionsCollection()->countActions() > 0) {
                    $eventType = ExecutorContext::EVENT_REPLY;
                }

                $context = $ticketManager->createAgentExecutorContext($person, $eventType, ExecutorContext::METHOD_WEB);
                $context->getVars()->set('followup_id', $followUp->getId());

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
