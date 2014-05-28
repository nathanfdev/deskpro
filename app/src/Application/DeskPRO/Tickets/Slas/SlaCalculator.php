<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Tickets
 */

namespace Application\DeskPRO\Tickets\Slas;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketSla;
use Orb\Util\WorkHoursInterface;
use Orb\Util\TimeUnit;
use Orb\Util\WorkHoursSetAll;

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
	 * @param                    $type
	 * @param WorkHoursInterface $work_hours
	 * @param TimeUnit           $warn_time
	 * @param TimeUnit           $fail_time
	 */
	public function __construct($type, WorkHoursInterface $work_hours, TimeUnit $warn_time, TimeUnit $fail_time)
	{
		$this->type       = $type;
		$this->work_hours = $work_hours;
		$this->warn_time  = $warn_time;
		$this->fail_time  = $fail_time;
	}


	/**
	 * Calculates a date in the future where a SLA fail/warn status is breached.
	 *
	 * @param Ticket $ticket
	 * @param int    $delay
	 * @return \DateTime|null
	 */
	private function _calculateDate(Ticket $ticket, $delay)
	{
		switch ($this->type) {
			case self::TYPE_FIRST_RESPONSE:
			case self::TYPE_RESOLUTION:
				return $this->work_hours->calculateWorkHoursDelay($ticket->date_created, $delay);

			case self::TYPE_WAITING_TIME:
				if ($ticket->status != 'awaiting_agent') {
					// can't know when it will expire
					return null;
				}

				if ($this->work_hours instanceof WorkHoursSetAll) {
					$wait_time = $ticket->total_user_waiting;
					if ($ticket->date_user_waiting) {
						$wait_time += time() - $ticket->date_user_waiting->getTimestamp();
					}

					return new \DateTime('+' . ($delay - $wait_time) . ' seconds', new \DateTimeZone('UTC'));
				} else {
					$wait_time = 0;
					if ($ticket->waiting_times) {
						foreach ($ticket->waiting_times AS $waiting) {
							if ($waiting['type'] == 'user') {
								$wait_time += $this->work_hours->getWorkTimeBetween($waiting['start'], $waiting['end']);
							}
						}
					}

					if ($ticket->date_user_waiting && $ticket->status == 'awaiting_agent') {
						// ticket is waiting but we don't have an end so add that
						$wait_time += $this->work_hours->getWorkTimeBetween($ticket->date_user_waiting);
					}

					return $this->work_hours->calculateWorkHoursDelay(new \DateTime(), $delay - $wait_time);
				}
				break;
		}

		return null;
	}


	/**
	 * Calculate the date the ticket will reach warning status.
	 *
	 * @param Ticket $ticket
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
	 * @return \DateTime|null
	 */
	public function calculateCompletedDate(Ticket $ticket)
	{
		$dates = array();

		if ($ticket->status == 'resolved') {
			if ($ticket->date_resolved) {
				$dates[] = $ticket->date_resolved->getTimestamp();
			} else {
				$dates[] = time();
			}
		}

		if ($ticket->status == 'hidden' && ($ticket->hidden_status == 'spam' || $ticket->hidden_status == 'deleted')) {
			$dates[] = time();
		}

		if ($ticket->date_closed) {
			$dates[] = $ticket->date_closed->getTimestamp();
		}

		if ($this->type == self::TYPE_FIRST_RESPONSE && $ticket->date_last_agent_reply) {
			if ($ticket->date_last_agent_reply->getTimestamp() > $ticket->date_created->getTimestamp()) {
				// don't auto resolve sla on ticket creation, even if created by an agent
				$dates[] = $ticket->date_first_agent_reply->getTimestamp();
				$dates[] = $ticket->date_last_agent_reply->getTimestamp();
			}
		}

		if ($this->type == self::TYPE_FIRST_RESPONSE && $ticket->date_status && $ticket->status != 'awaiting_agent') {
			$dates[] = $ticket->date_status->getTimestamp();
		}

		if ($dates) {
			return new \DateTime('@' . min($dates));
		}

		return null;
	}


	/**
	 * Calculate SLA countable time (in seconds) that happened in ticket between start and $ate.
	 *
	 * @param Ticket    $ticket
	 * @param \DateTime $date
	 * @return int
	 */
	public function calculateTimeUntil(Ticket $ticket, \DateTime $date)
	{
		$end_ts = $date->getTimestamp();

		if ($this->type == self::TYPE_WAITING_TIME) {
			$time = 0;
			foreach ($ticket->waiting_times AS $waiting) {
				if ($waiting['type'] == 'user' && $waiting['start'] < $end_ts) {
					$time += $this->work_hours->getWorkTimeBetween($waiting['start'], min($end_ts, $waiting['end']));
				}
			}

			return $time;
		} else {
			return $this->work_hours->getWorkTimeBetween($ticket->date_created, $end_ts);
		}
	}


	/**
	 * Gets the appropriate Date to compare against warn/fail dates.
	 *
	 * @param Ticket $ticket
	 * @return \DateTime
	 */
	public function getTestTime(Ticket $ticket)
	{
		$times = array(time());

		if ($this->type == self::TYPE_FIRST_RESPONSE && $ticket->date_last_agent_reply) {
			if ($ticket->date_last_agent_reply->getTimestamp() > $ticket->date_created->getTimestamp()) {
				// don't auto resolve sla on ticket creation, even if created by an agent
				$times[] = $ticket->date_first_agent_reply->getTimestamp();
			}
		}

		if ($ticket->date_closed) {
			$times[] = $ticket->date_closed->getTimestamp();
		}

		if ($ticket->status == 'resolved' && $ticket->date_resolved) {
			$times[] = $ticket->date_resolved->getTimestamp();
		}

		return new \DateTime('@' . min($times));
	}


	/**
	 * @param Ticket    $ticket
	 * @param TicketSla $ticket_sla
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
}