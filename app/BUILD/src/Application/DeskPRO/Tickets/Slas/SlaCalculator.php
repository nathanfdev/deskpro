<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Tickets\Slas;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketSla;
use DeskPRO\Bundle\AppBundle\Entity\TicketStatus;
use Orb\Util\TimeUnit;
use Orb\Util\WorkHoursInterface;
use Orb\Util\Testable\DateTime;

class SlaCalculator
{
    const TYPE_FIRST_RESPONSE = 'first_response';
    const TYPE_RESOLUTION     = 'resolution';
    const TYPE_WAITING_TIME   = 'waiting_time';

    /**
     * @var string
     */
    private $type;

    /**
     * @var \Orb\Util\WorkHoursInterface
     */
    private $work_hours;

    /**
     * @var TimeUnit
     */
    private $warn_time;

    /**
     * @var TimeUnit
     */
    private $fail_time;

    /**
     *
     * @var array
     */
    private $excludeTicketStatuses = [];

    /**
     * @param                    $type
     * @param WorkHoursInterface $work_hours
     * @param TimeUnit           $warn_time
     * @param TimeUnit           $fail_time
     */
    public function __construct(
        $type,
        WorkHoursInterface $work_hours,
        TimeUnit $warn_time,
        TimeUnit $fail_time,
        $excludeTicketStatuses
    ) {
        $this->type       = $type;
        $this->work_hours = $work_hours;
        $this->warn_time  = $warn_time;
        $this->fail_time  = $fail_time;
        $this->excludeTicketStatuses = $excludeTicketStatuses;
    }

    /**
     * Calculates a date in the future where a SLA fail/warn status is breached.
     *
     * @param Ticket $ticket
     * @param int    $delay
     *
     * @return \DateTime|null
     */
    private function _calculateDate(Ticket $ticket, $delay)
    {
        switch ($this->type) {
            case self::TYPE_FIRST_RESPONSE:
            case self::TYPE_RESOLUTION:

                if ($this->isExcludedTicketStatus($ticket->getTicketStatus()->getStatusCode())) {
                    // can't know when it will expire
                    return;
                }

                // Check for old waiting_times format
                // In this case return old way calculations
                $isOldFormat = $ticket->waiting_times && !array_key_exists('ticket_status', $ticket->waiting_times[0]);

                // Calculation is simple if no excluded statuses or old format
                if (!$this->excludeTicketStatuses || $isOldFormat || !$ticket->waiting_times) {
                    return $this->work_hours->calculateWorkHoursDelay($ticket->date_created, $delay);
                }

                foreach ($ticket->waiting_times as $waiting) {
                    if ($delay <= 0) {
                        break;
                    }
                    if (
                        array_key_exists('ticket_status', $waiting)
                        && $this->isExcludedTicketStatus($waiting['ticket_status'])
                    ) {
                        continue;
                    }
                    $waitTime = $this->work_hours->getWorkTimeBetween($waiting['start'], $waiting['end']);

                    if ($delay <= $waitTime) {
                        return $this->work_hours->calculateWorkHoursDelay(
                            new \DateTime('@'.$waiting['start']),
                            $delay
                        );
                    } else {
                        $delay -= $waitTime;
                    }
                }
                
                return $this->work_hours->calculateWorkHoursDelay($ticket->date_status, $delay);

            case self::TYPE_WAITING_TIME:
                if (!$this->isWaitingTimeTicketStatus($ticket->getTicketStatus()->getStatusCode())) {
                    // can't know when it will expire
                    return;
                }

                $wait_time = 0;
                if ($ticket->waiting_times) {
                    foreach ($ticket->waiting_times as $waiting) {
                        if (
                            (
                                // old `waiting_times`  format
                                array_key_exists('type', $waiting)
                                && $waiting['type'] == 'user'
                            )
                            || (
                                // new `waiting_times`  format
                                array_key_exists('ticket_status', $waiting)
                                && $this->isWaitingTimeTicketStatus($waiting['ticket_status'])
                            )
                        ) {
                            $wait_time += $this->work_hours->getWorkTimeBetween($waiting['start'], $waiting['end']);
                        }
                    }
                }

                /** @var \Orb\Util\Testable\DateTime $now */
                $now = new DateTime();
                
                if ($ticket->date_status) {
                    // ticket is waiting but we don't have an end so add that
                    $wait_time += $this->work_hours->getWorkTimeBetween($ticket->date_status, $now);
                }

                return $this->work_hours->calculateWorkHoursDelay($now, $delay - $wait_time);
                break;
        }

        return;
    }

    /**
     * Calculate the date the ticket will reach warning status.
     *
     * @param Ticket $ticket
     *
     * @return \DateTime|null
     */
    public function calculateWarnDate(Ticket $ticket)
    {
        return $this->_calculateDate($ticket, $this->warn_time->getSecs());
    }

    /**
     * Calculate the date the ticket will reach failing status.
     *
     * @param Ticket $ticket
     *
     * @return \DateTime|null
     */
    public function calculateFailDate(Ticket $ticket)
    {
        return $this->_calculateDate($ticket, $this->fail_time->getSecs());
    }

    /**
     * Calculate the date that the SLA completed, or null if it is not completed.
     *
     * @param Ticket $ticket
     *
     * @return \DateTime|null
     */
    public function calculateCompletedDate(Ticket $ticket)
    {
        $dates = [];

        if ($ticket->status == 'hidden' && ($ticket->hidden_status == 'spam' || $ticket->hidden_status == 'deleted')) {
            $dates[] = time();
        }

        if ($ticket->date_archived) {
            $dates[] = $ticket->date_archived->getTimestamp();
        }

        if ($this->type == self::TYPE_FIRST_RESPONSE) {
            if ($ticket->date_last_agent_reply) {
                if ($this->isTicketHasNotInitialAgentReply($ticket)) {
                    // don't auto resolve sla on ticket creation, even if created by an agent
                    if ($ticket->date_first_agent_reply) {
                        $dates[] = $ticket->date_first_agent_reply->getTimestamp();
                    }
                    $dates[] = $ticket->date_last_agent_reply->getTimestamp();
                }
            }
        } else {
            if ($ticket->status == 'resolved') {
                if ($ticket->date_resolved) {
                    $dates[] = $ticket->date_resolved->getTimestamp();
                } else {
                    $dates[] = time();
                }
            }
        }

        if ($dates) {
            return new \DateTime('@'.min($dates));
        }

        return;
    }

    /**
     * Calculate SLA countable time (in seconds) that happened in ticket between start and $ate.
     *
     * @param Ticket    $ticket
     * @param \DateTime $date
     *
     * @return int
     */
    public function calculateTimeUntil(Ticket $ticket, \DateTime $date)
    {
        $end_ts = $date->getTimestamp();

        if ($this->type == self::TYPE_WAITING_TIME) {
            $time = 0;
            foreach ($ticket->waiting_times as $waiting) {
                if (
                    $waiting['start'] < $end_ts
                    && (
                    (
                        // old `waiting_times`  format
                        array_key_exists('type', $waiting)
                        && $waiting['type'] == 'user'
                    )
                    || (
                        // new `waiting_times`  format
                        array_key_exists('ticket_status', $waiting)
                        && $this->isWaitingTimeTicketStatus($waiting['ticket_status'])
                    ))
                ) {
                    $time += $this->work_hours->getWorkTimeBetween($waiting['start'], min($end_ts, $waiting['end']));
                }
            }

            return $time;
        } else {
            return $this->work_hours->getWorkTimeBetween($ticket->date_created, $end_ts)
                    - $this->getTicketWorkingTimeInExcludedStatuses($ticket, $end_ts);
        }
    }

    /**
     * Gets the appropriate Date to compare against warn/fail dates.
     *
     * @param Ticket $ticket
     *
     * @return \DateTime
     */
    public function getTestTime(Ticket $ticket)
    {
        $times = [time()];

        if ($this->type == self::TYPE_FIRST_RESPONSE && $ticket->date_last_agent_reply) {
            if ($this->isTicketHasNotInitialAgentReply($ticket)) {
                // don't auto resolve sla on ticket creation, even if created by an agent
                if ($ticket->date_first_agent_reply) {
                    $times[] = $ticket->date_first_agent_reply->getTimestamp();
                }
            }
        }

        if ($ticket->date_archived) {
            $times[] = $ticket->date_archived->getTimestamp();
        }

        if (
            // First reply SLA should not be completed when the ticket status is changed and no agent reply exists
            $this->type !== self::TYPE_FIRST_RESPONSE
            && ($ticket->status == 'resolved' || $ticket->status == 'archived')
            && $ticket->date_resolved
        ) {
            $times[] = $ticket->date_resolved->getTimestamp();
        }

        return new \DateTime('@'.min($times));
    }

    /**
     * @param Ticket    $ticket
     * @param TicketSla $ticket_sla
     *
     * @return bool
     */
    public function isTicketSlaWarning(Ticket $ticket, TicketSla $ticket_sla)
    {
        $time = $this->getTestTime($ticket)->getTimestamp();

        if ($ticket_sla->warn_date && $ticket_sla->warn_date->getTimestamp() < $time) {
            return true;
        }

        return false;
    }

    /**
     * @param Ticket    $ticket
     * @param TicketSla $ticket_sla
     *
     * @return bool
     */
    public function isTicketSlaFailed(Ticket $ticket, TicketSla $ticket_sla)
    {
        $time = $this->getTestTime($ticket)->getTimestamp();

        if ($ticket_sla->fail_date && $ticket_sla->fail_date->getTimestamp() < $time) {
            return true;
        }

        return false;
    }

    /**
     * Check if ticket has real Agent reply and not initial Agent message that created ticket.
     * It is necessary to prevent:
     * > don't auto resolve sla on ticket creation, even if created by an agent.
     *
     * @param Ticket $ticket
     */
    protected function isTicketHasNotInitialAgentReply(Ticket $ticket)
    {
        // check if there is agent reply created after ticket
        $hasReply = $ticket->date_last_agent_reply
                    && $ticket->date_last_agent_reply->getTimestamp() > $ticket->date_created->getTimestamp();

        // if agent reply added through the 'new ticket' trigger then it is possible that
        // agent reply creation date = ticket creation date
        // in this case check if there is user reply exists before agent reply
        // for simplicity we just check that first message is user reply
        if (!$hasReply && $ticket->date_last_agent_reply && $ticket->date_last_user_reply) {
            // ->getFirstMessage() executes separate query with limit 1 - not a big overhead
            $firstMessage = $ticket->getFirstMessage();
            $hasReply     = $firstMessage
                        && $firstMessage->getPerson()
                        && !$firstMessage->getPerson()->isAgent();
        }

        return $hasReply;
    }

    /**
     *
     * @param Ticket $ticket
     * @return int - seconds
     */
    private function getTicketWorkingTimeInExcludedStatuses(Ticket $ticket, int $endTs = null)
    {
        if (!$this->excludeTicketStatuses) {
            return 0;
        }

        $sec = 0;
        foreach ($ticket->getWaitingTimes() as $waiting) {
            if (!$endTs) {
                $endTs = (int)$waiting['end'];
            }
            
            // `ticket_status` key exists only in new `waiting_times` format
            if (
                array_key_exists('ticket_status', $waiting)
                && $this->isExcludedTicketStatus($waiting['ticket_status'])
                && $waiting['start'] <= $endTs
            ) {
                $sec += $this->work_hours->getWorkTimeBetween($waiting['start'], min($waiting['end'], $endTs));
            }
        }

        return $sec;
    }

    /**
     * Is ticket status should be counted in WAITING_TIME SLA
     *
     * @param string $ticketStatus
     * @return bool
     */
    private function isWaitingTimeTicketStatus($ticketStatus)
    {
        return in_array($ticketStatus, [TicketStatus::STATUS_TYPE_AWAITING_AGENT, TicketStatus::STATUS_TYPE_PENDING])
                && !$this->isExcludedTicketStatus($ticketStatus);
    }

    /**
     *
     * @param string $ticketStatus
     * @return bool
     */
    private function isExcludedTicketStatus($ticketStatus)
    {
        return in_array($ticketStatus, $this->excludeTicketStatuses);
    }
}
