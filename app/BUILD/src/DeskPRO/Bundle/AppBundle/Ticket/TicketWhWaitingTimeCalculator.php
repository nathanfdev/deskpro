<?php

namespace DeskPRO\Bundle\AppBundle\Ticket;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketLog;
use Application\DeskPRO\Monolog\NullLogger;
use DeskPRO\Bundle\AppBundle\Entity\TicketStatus;
use Orb\Util\WorkHoursInterface;
use Orb\Util\Testable\DateTime;
use Orb\Util\Dates;
use Doctrine\ORM\EntityManager;
use Monolog\Logger;


/**
 * Re-Calculate ticket working hours waiting times by Ticket logs entry
 */
class TicketWhWaitingTimeCalculator
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     *
     * @var Logger
     */
    private $logger;

    /**
     *
     * @var \Application\DeskPRO\EntityRepository\TicketLog
     */
    private $repo;

    /**
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em, Logger $logger = null)
    {
        $this->em = $em;
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * $res:
     *  [
     *      total_user_waiting_wh_start => DateTime | null - in UTC timezone
     *      total_user_waiting_wh => int
     *      total_to_first_reply_wh => int
     *  ]
     *
     * @param array &$res
     * @param Ticket $ticket
     * @param WorkHoursInterface $wh
     * @return bool
     */
    public function calculate(Ticket $ticket, WorkHoursInterface $wh, array &$res)
    {
        $logs = $this->getticketLogs($ticket);

        if (!$logs) {
            $this->logger->debug("Can't find any ticket logs, skip calculations ...");
            return false;
        }

        $res = array_merge([
            'total_user_waiting_wh_start' => null,
            'total_user_waiting_wh' => 0,
            'total_to_first_reply_wh' => null
        ], $res);

        $now = new DateTime();
        $dateCreated = null;
        $statusChangedAt = null; // significant change (i.e. from (awaiting_agent || pending) to something !(awaiting_agent || pending)
        $status = null;
        $firstAgentReplyCreatedAt = null;

        foreach ($logs as $log) {
            switch ($log->getActionType()) {

                case 'ticket_created':
                    $dateCreated = $log->getDateCreated();
                    $statusChangedAt = $log->getDateCreated();
                    $status = TicketStatus::STATUS_TYPE_AWAITING_AGENT;

                    $this->logger->debug(sprintf('%s [%s]: %s',
                        $dateCreated->format('Y-m-d H:i:s'),
                        $dateCreated->getTimestamp(),
                        'Ticket created'
                    ));

                    break;

                case 'message_created':
                    $details = $log->getDetails();

                    // save first agent reply
                    if (
                        $details
                        && $dateCreated
                        && !$firstAgentReplyCreatedAt
                        && isset($details['is_agent_message'])
                        && $details['is_agent_message']
                        && (!isset($details['is_agent_note']) || !$details['is_agent_note'])
                    ) {
                        $firstAgentReplyCreatedAt = $log->getDateCreated();
                        $res['total_to_first_reply_wh'] = $wh->getWorkTimeBetween($dateCreated, $firstAgentReplyCreatedAt);

                        $this->logger->debug(sprintf('%s [%s]: %s',
                            $firstAgentReplyCreatedAt->format('Y-m-d H:i:s'),
                            $firstAgentReplyCreatedAt->getTimestamp(),
                            'First agent reply created'
                        ));

                        $this->logger->debug(sprintf('%s [%s]: First reply waiting time: %s (%s)',
                            $firstAgentReplyCreatedAt->format('Y-m-d H:i:s'),
                            $firstAgentReplyCreatedAt->getTimestamp(),
                            Dates::secsToReadable($res['total_to_first_reply_wh'], 4),
                            $res['total_to_first_reply_wh']
                        ));
                    }

                    break;

                case 'changed_status':
                    $details = $log->getDetails();

                    if (!isset($details['old_status']) || !isset($details['new_status'])) {
                        $this->logger->debug(sprintf('%s [%s]: %s',
                            $log->getDateCreated()->format('Y-m-d H:i:s'),
                            $log->getDateCreated()->getTimestamp(),
                            'Status changed. But no info about old/new status in log. Skip this log entiy ...'
                        ));

                        break;
                    }

                    $oldStatus = $details['old_status'];
                    $newStatus = $details['new_status'];

                    $this->logger->debug(sprintf('%s [%s]: Status changed `%s` => `%s`',
                        $log->getDateCreated()->format('Y-m-d H:i:s'),
                        $log->getDateCreated()->getTimestamp(),
                        $oldStatus,
                        $newStatus
                    ));


                    // user START waiting
                    if (
                        in_array($newStatus, [TicketStatus::STATUS_TYPE_AWAITING_AGENT, TicketStatus::STATUS_TYPE_PENDING])
                        && !in_array($oldStatus, [TicketStatus::STATUS_TYPE_AWAITING_AGENT, TicketStatus::STATUS_TYPE_PENDING])
                    ) {
                        $status = $newStatus;
                        $statusChangedAt = $log->getDateCreated();

                        $this->logger->debug(sprintf('%s [%s]: %s',
                            $statusChangedAt->format('Y-m-d H:i:s'),
                            $statusChangedAt->getTimestamp(),
                            'User START waiting'
                        ));
                    }

                    // user STOP waiting
                    if (
                        !in_array($newStatus, [TicketStatus::STATUS_TYPE_AWAITING_AGENT, TicketStatus::STATUS_TYPE_PENDING])
                        && in_array($oldStatus, [TicketStatus::STATUS_TYPE_AWAITING_AGENT, TicketStatus::STATUS_TYPE_PENDING])
                    ) {
                        $status = $newStatus;
                        $res['total_user_waiting_wh_start'] = null;
                        $whTime = $wh->getWorkTimeBetween($statusChangedAt, $log->getDateCreated());
                        $res['total_user_waiting_wh'] = $res['total_user_waiting_wh'] + $whTime;

                        $statusChangedAt = $log->getDateCreated();

                        $this->logger->debug(sprintf('%s [%s]: %s',
                            $statusChangedAt->format('Y-m-d H:i:s'),
                            $statusChangedAt->getTimestamp(),
                            'User STOP waiting'
                        ));

                        $this->logger->debug(sprintf('%s [%s]: Waiting time: + %s (%s sec) = %s (%s sec)',
                            $statusChangedAt->format('Y-m-d H:i:s'),
                            $statusChangedAt->getTimestamp(),
                            Dates::secsToReadable($whTime, 4),
                            $whTime,
                            Dates::secsToReadable($res['total_user_waiting_wh'], 4),
                            $res['total_user_waiting_wh']
                        ));
                    }

                    break;
            }
        }

        $this->logger->debug('End of ticket logs');

        if (!$dateCreated) {
            $this->logger->debug('No ticket log with ticket creation date, skip calculation ...');
            return false;
        }

        if ($res['total_to_first_reply_wh'] === null) {
            $res['total_to_first_reply_wh'] = $wh->getWorkTimeBetween($dateCreated, $now);

            $this->logger->debug(sprintf('%s [%s]: First reply waiting time till now: %s (%s)',
                $now->format('Y-m-d H:i:s'),
                $now->getTimestamp(),
                Dates::secsToReadable($res['total_to_first_reply_wh'], 4),
                $res['total_to_first_reply_wh']
            ));
        }

        if (in_array($status, [TicketStatus::STATUS_TYPE_AWAITING_AGENT, TicketStatus::STATUS_TYPE_PENDING])) {
            $res['total_user_waiting_wh_start'] = $wh->getNextWorkTimeStart($now);

            $whTime = $wh->getWorkTimeBetween($statusChangedAt, $now);
            $res['total_user_waiting_wh'] = $res['total_user_waiting_wh'] + $whTime;

            $this->logger->debug(sprintf('%s [%s]: %s',
                $now->format('Y-m-d H:i:s'),
                $now->getTimestamp(),
                'User still waiting'
            ));

            $this->logger->debug(sprintf('%s [%s]: Waiting time: + %s (%s sec) = %s (%s sec)',
                $now->format('Y-m-d H:i:s'),
                $now->getTimestamp(),
                Dates::secsToReadable($whTime, 4),
                $whTime,
                Dates::secsToReadable($res['total_user_waiting_wh'], 4),
                $res['total_user_waiting_wh']
            ));
        }

        return true;
    }

    /**
     *
     * @param Ticket $ticket
     * @return TicketLog[]
     */
    protected function getTicketLogs(Ticket $ticket)
    {
        if (!$this->repo) {
            $this->repo = $this->em->getRepository(TicketLog::class);
        }
        return $this->repo->getLogsForTicket(
            $ticket,
            [
                'order_dir' => 'ASC',
                'types' => ['ticket_created', 'message_created', 'changed_status']
            ]
        );
    }
}
