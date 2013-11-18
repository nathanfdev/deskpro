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
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\Tickets\Triggers\TriggerActions;
use Application\DeskPRO\Tickets\Triggers\TriggerTerms;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

use Application\DeskPRO\App;

/**
 * Entity for an SLA record
 *
 * @property int $id
 * @property string $title
 * @property string $sla_type
 * @property string $active_time
 * @property int $work_start
 * @property int $work_end
 * @property int[] $work_days
 * @property string $work_timezone
 * @property array $work_holidays
 * @property string $apply_type
 * @property TriggerTerms $apply_terms
 * @property int $warn_time
 * @property string $warn_time_unit
 * @property TriggerActions $warn_actions
 * @property int $fail_time
 * @property string $fail_time_unit
 * @property TriggerActions $fail_actions
 */
class Sla extends \Application\DeskPRO\Domain\DomainObject
{
	const TYPE_FIRST_RESPONSE = 'first_response';
	const TYPE_RESOLUTION = 'resolution';
	const TYPE_WAITING_TIME = 'waiting_time';

	/**
	 * The unique ID.
	 *
	 * @var int
	 *
	 */
	protected $id = null;

	/**
	 * @var string
	 */
	protected $title;

	/**
	 * Type of SLA - first_response, resolution, waiting_time
	 *
	 * @var string
	 */
	protected $sla_type;

	/**
	 * Whether active all the time (all) or during work hours only (work_hours)
	 * or use the default ticket-wide settings (default)
	 *
	 * @var string
	 */
	protected $active_time = 'default';

	/**
	 * When the work day starts. This is stored as the number of seconds after 00:00:00.
	 *
	 * @var integer
	 */
	protected $work_start;

	/**
	 * When the work day ends. This is stored as the number of seconds after 00:00:00.
	 *
	 * @var integer
	 */
	protected $work_end;

	/**
	 * Array of work days, stored with keys corresponding to day numbers. Values are true.
	 * 0 = Sunday, 6 = Saturday (same as PHP, easy to convert to MySQL which is 1 = Sunday, 7 = Saturday)
	 *
	 * @var array
	 */
	protected $work_days = array();

	/**
	 * Timezone for work hours/days to be considered in
	 *
	 * @var string
	 */
	protected $work_timezone;

	/**
	 * List of work holidays
	 *
	 * @var array
	 */
	protected $work_holidays = array();

	/**
	 * Controls how the SLA is applied to tickets
	 *
	 * @var string
	 */
	protected $apply_type = 'all';

	/**
	 * @var \Application\DeskPRO\Tickets\Triggers\TriggerTerms
	 */
	protected $apply_terms = null;

	/**
	 * @var int
	 */
	protected $warn_time = 1;

	/**
	 * @var string
	 */
	protected $warn_time_unit = 'days';

	/**
	 * @var \Application\DeskPRO\Tickets\Triggers\TriggerActions
	 */
	protected $warn_actions = null;

	/**
	 * @var int
	 */
	protected $fail_time = 1;

	/**
	 * @var string
	 */
	protected $fail_time_unit = 'days';

	/**
	 * @var \Application\DeskPRO\Tickets\Triggers\TriggerActions
	 */
	protected $fail_actions = null;

	/**
	 * @var \Orb\Util\WorkHoursSet
	 */
	protected $_work_hours_set;

	/**
	 * Creates a new team.
	 */
	public function __construct()
	{
		$this->apply_terms  = new TriggerTerms();
		$this->warn_actions = new TriggerActions();
		$this->fail_actions = new TriggerActions();
	}


	/**
	 * @return float
	 */
	public function getWorkStartHour()
	{
		return floor($this->work_start / 3600);
	}


	/**
	 * @return float
	 */
	public function getWorkStartMinute()
	{
		return floor(($this->work_start % 3600) / 60);
	}


	/**
	 * @return float
	 */
	public function getWorkEndHour()
	{
		return floor($this->work_end / 3600);
	}


	/**
	 * @return float
	 */
	public function getWorkEndMinute()
	{
		return floor(($this->work_end % 3600) / 60);
	}


	/**
	 * @param array $days
	 * @param bool $raw
	 */
	public function setWorkDays(array $days, $raw = false)
	{
		$old = $this->work_days;

		if ($raw) {
			$this->work_days = $days;
		} else {
			$days = array_unique($days);
			sort($days);

			$this->work_days = array_fill_keys($days, true);
		}
		$this->_onPropertyChanged('work_days', $old, $this->work_days);
	}

	/**
	 * Resets holidays
	 */
	public function resetHolidays()
	{
		$this->setModelField('work_holidays', array());
	}


	/**
	 * Removes a single holiday by index
	 *
	 * @param $key
	 */
	public function removeHolidayKey($key)
	{
		$old = $this->work_holidays;
		unset($this->work_holidays[$key]);
		$this->_onPropertyChanged('work_holidays', $old, $this->work_holidays);
	}

	/**
	 * Adds a holiday
	 *
	 * @param $name
	 * @param $day
	 * @param $month
	 * @param null $year
	 * @return int|string
	 */
	public function addHoliday($name, $day, $month, $year = null)
	{
		$old = $this->work_holidays;

		if (!$year) {
			$year = null;
		} else {
			$year = intval($year);
		}

		foreach ($this->work_holidays AS $k => $existing) {
			if ($existing['day'] == $day && $existing['month'] == $month && $existing['year'] === $year) {
				return $k;
			}
		}

		$this->work_holidays[] = array(
			'name' => $name,
			'day' => intval($day),
			'month' => intval($month),
			'year' => $year
		);

		$this->_onPropertyChanged('work_holidays', $old, $this->work_holidays);

		return count($this->work_holidays) - 1;
	}


	/**
	 * Gets an array of holidays, sorted by date
	 *
	 * @return array
	 */
	public function getHolidaysSorted()
	{
		$holidays = $this->work_holidays;
		uasort($holidays, function($a, $b) {
			if ($a['month'] < $b['month']) {
				return -1;
			}
			if ($a['month'] > $b['month']) {
				return 1;
			}
			if ($a['day'] < $b['day']) {
				return -1;
			}
			if ($a['day'] > $b['day']) {
				return 1;
			}

			return 0; // same month and day
		});

		return $holidays;
	}

	//TODO
	public function calculateWarnDate(Ticket $ticket)
	{
		if (!$this->warning_trigger) {
			return null;
		}

		return $this->_calculateTriggerDate($this->warning_trigger->getOptionSeconds(), $ticket);
	}

	//TODO
	public function calculateFailDate(Ticket $ticket)
	{
		if (!$this->warning_trigger) {
			return null;
		}

		if (!$this->fail_trigger) {
			return null;
		}

		return $this->_calculateTriggerDate($this->fail_trigger->getOptionSeconds(), $ticket);
	}

	/**
	 * @return \Orb\Util\WorkHoursSet
	 */
	public function getWorkHoursSet()
	{
		if (!$this->_work_hours_set) {
			if ($this->active_time == 'default') {
				$work_hours = unserialize(App::getSetting('core_tickets.work_hours'));
				$this->_work_hours_set = new \Orb\Util\WorkHoursSet(
					$work_hours['active_time'], $work_hours['start_hour'] * 3600 + $work_hours['start_minute'] * 60,
					$work_hours['end_hour'] * 3600 + $work_hours['end_minute'] * 60,
					$work_hours['days'], $work_hours['timezone'], $work_hours['holidays']
				);
			} else {
				$this->_work_hours_set = new \Orb\Util\WorkHoursSet(
					$this->active_time, $this->work_start, $this->work_end,
					$this->work_days, $this->work_timezone, $this->work_holidays
				);
			}
		}

		return $this->_work_hours_set;
	}


	/**
	 * @param $end_ts
	 * @param Ticket $ticket
	 * @return int
	 */
	public function calculateSlaTimeUntil($end_ts, Ticket $ticket)
	{
		if ($this->sla_type == self::TYPE_WAITING_TIME) {
			$time = 0;
			$work_hours_set = $this->getWorkHoursSet();
			foreach ($ticket->waiting_times AS $waiting) {
				if ($waiting['type'] == 'user' && $waiting['start'] < $end_ts) {
					$time += $work_hours_set->getWorkTimeBetween($waiting['start'], min($end_ts, $waiting['end']));
				}
			}

			return $time;
		} else {
			return $this->getWorkHoursSet()->getWorkTimeBetween($ticket->date_created, $end_ts);
		}
	}


	/**
	 * @param $delay
	 * @param Ticket $ticket
	 * @return \DateTime|null
	 */
	protected function _calculateTriggerDate($delay, Ticket $ticket)
	{
		if ($this->sla_type == self::TYPE_FIRST_RESPONSE || $this->sla_type == self::TYPE_RESOLUTION) {
			return $this->getWorkHoursSet()->calculateWorkHoursDelay($ticket->date_created, $delay);
		}

		if ($this->sla_type == self::TYPE_WAITING_TIME) {
			if ($ticket->status != 'awaiting_agent') {
				// can't know when it will expire
				return null;
			}

			$work_hours_set = $this->getWorkHoursSet();

			if ($work_hours_set->getActiveTime() == \Orb\Util\WorkHoursSet::ACTIVE_24X7) {
				$wait_time = $ticket->total_user_waiting;
				if ($ticket->date_user_waiting) {
					$wait_time += time() - $ticket->date_user_waiting->getTimestamp();
				}

				return new \DateTime('+' . ($delay - $wait_time) . ' seconds', new \DateTimeZone('UTC'));
			} else {
				$work_day_length = $this->work_end - $this->work_start;
				if ($work_day_length <= 0) {
					return null;
				}

				$wait_time = 0;
				if ($ticket->waiting_times) {
					foreach ($ticket->waiting_times AS $waiting) {
						if ($waiting['type'] == 'user') {
							$wait_time += $work_hours_set->getWorkTimeBetween($waiting['start'], $waiting['end']);
						}
					}
				}

				if ($ticket->date_user_waiting && $ticket->status == 'awaiting_agent') {
					// ticket is waiting but we don't have an end so add that
					$wait_time += $work_hours_set->getWorkTimeBetween($ticket->date_user_waiting);
				}

				return $work_hours_set->calculateWorkHoursDelay(new \DateTime(), $delay - $wait_time);
			}
		}

		return null;
	}


	/**
	 * @param Ticket $ticket
	 * @return mixed|null
	 */
	public function calculateCompleted(Ticket $ticket)
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

		if ($this->sla_type == self::TYPE_FIRST_RESPONSE && $ticket->date_last_agent_reply) {
			if ($ticket->date_last_agent_reply->getTimestamp() > $ticket->date_created->getTimestamp()) {
				// don't auto resolve sla on ticket creation, even if created by an agent
				$dates[] = $ticket->date_first_agent_reply->getTimestamp();
			}
		}

		if ($this->sla_type == self::TYPE_FIRST_RESPONSE && $ticket->date_status && $ticket->date_status->getTimestamp() > $ticket->date_created->getTimestamp() && $ticket->status != 'awaiting_agent') {
			$dates[] = $ticket->date_status->getTimestamp();
		}

		if ($dates) {
			return min($dates);
		}

		return null;
	}


	/**
	 * @param Ticket $ticket
	 * @return mixed
	 */
	public function getSlaTestTime(Ticket $ticket)
	{
		$times = array(time());

		if ($this->sla_type == self::TYPE_FIRST_RESPONSE && $ticket->date_last_agent_reply) {
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

		return min($times);
	}

	/**
	 * {@inheritDoc}
	 */
	public function toApiData($primary = true, $deep = true, array $visited = array())
	{
		$data = parent::toApiData($primary, $deep, $visited);
		$data['apply_terms']   = $this->apply_terms->exportToArray();
		$data['warn_actions']  = $this->warn_actions->exportToArray();
		$data['fail_actions']  = $this->fail_actions->exportToArray();
		return $data;
	}

	############################################################################
	# Doctrine Metadata
	############################################################################

	public static function loadMetadata(ClassMetadata $metadata)
	{
		$metadata->inheritanceType           = ClassMetadataInfo::INHERITANCE_TYPE_NONE;
		$metadata->changeTrackingPolicy      = ClassMetadataInfo::CHANGETRACKING_NOTIFY;
		$metadata->generatorType             = ClassMetadataInfo::GENERATOR_TYPE_IDENTITY;
		$metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\Sla';

		$metadata->setPrimaryTable(array(
			'name' => 'slas'
		));
		$metadata->mapField(array(
			'columnName' => 'id',
			'fieldName'  => 'id',
			'type'       => 'integer',
			'id'         => true,
			'nullable'   => false,
		));
		$metadata->mapField(array(
			'columnName' => 'title',
			'fieldName'  => 'title',
			'type'       => 'string',
			'length'     => 100,
			'nullable'   => false,
		));
		$metadata->mapField(array(
			'columnName' => 'sla_type',
			'fieldName'  => 'sla_type',
			'type'       => 'string',
			'length'     => 50,
			'nullable'   => false,
		));
		$metadata->mapField(array(
			'columnName' => 'active_time',
			'fieldName'  => 'active_time',
			'type'       => 'string',
			'length'     => 50,
			'nullable'   => false,
		));
		$metadata->mapField(array(
			'columnName' => 'work_start',
			'fieldName'  => 'work_start',
			'type'       => 'integer',
			'nullable'   => true,
		));
		$metadata->mapField(array(
			'columnName' => 'work_end',
			'fieldName'  => 'work_end',
			'type'       => 'integer',
			'nullable'   => true,
		));
		$metadata->mapField(array(
			'columnName' => 'work_days',
			'fieldName'  => 'work_days',
			'type'       => 'array',
			'nullable'   => true,
		));
		$metadata->mapField(array(
			'columnName' => 'work_timezone',
			'fieldName'  => 'work_timezone',
			'type'       => 'string',
			'length'     => 50,
			'nullable'   => true,
		));
		$metadata->mapField(array(
			'columnName' => 'work_holidays',
			'fieldName'  => 'work_holidays',
			'type'       => 'array',
			'nullable'   => true,
		));
		$metadata->mapField(array(
			'columnName' => 'apply_type',
			'fieldName'  => 'apply_type',
			'type'       => 'string',
			'length'     => 25,
			'nullable'   => false,
		));
		$metadata->mapField(array(
			'columnName' => 'apply_terms',
			'fieldName'  => 'apply_terms',
			'type'       => 'object',
			'nullable'   => false,
		));
		$metadata->mapField(array(
			'columnName' => 'warn_time',
			'fieldName'  => 'warn_time',
			'type'       => 'integer',
			'nullable'   => false,
		));
		$metadata->mapField(array(
			'columnName' => 'warn_time_unit',
			'fieldName'  => 'warn_time_unit',
			'type'       => 'string',
			'length'     => 50,
			'nullable'   => false,
		));
		$metadata->mapField(array(
			'columnName' => 'warn_actions',
			'fieldName'  => 'warn_actions',
			'type'       => 'object',
			'nullable'   => false,
		));
		$metadata->mapField(array(
			'columnName' => 'fail_time',
			'fieldName'  => 'fail_time',
			'type'       => 'integer',
			'nullable'   => false,
		));
		$metadata->mapField(array(
			'columnName' => 'fail_time_unit',
			'fieldName'  => 'fail_time_unit',
			'type'       => 'string',
			'length'     => 50,
			'nullable'   => false,
		));
		$metadata->mapField(array(
			'columnName' => 'fail_actions',
			'fieldName'  => 'fail_actions',
			'type'       => 'object',
			'nullable'   => false,
		));
	}
}
