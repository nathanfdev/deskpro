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

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

use Application\DeskPRO\App;

/**
 * Entity for an SLA record
 *
 */
class Sla extends \Application\DeskPRO\Domain\DomainObject
{
	const TYPE_FIRST_RESPONSE = 'first_response';
	const TYPE_RESOLUTION = 'resolution';
	const TYPE_WAITING_TIME = 'waiting_time';

	const ACTIVE_24X7 = 'all';
	const ACTIVE_WORK_HOURS = 'work_hours';

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
	 *
	 * @var string
	 */
	protected $active_time;

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
	 * If true, apply to all tickets
	 *
	 * @var bool
	 */
	protected $apply_all = false;

	/**
	 * If true, allows agents to apply this SLA manually
	 *
	 * @var bool
	 */
	protected $allow_agent_manual = true;

	/**
	 * @var TicketTrigger
	 */
	protected $warning_trigger;

	/**
	 * @var TicketTrigger
	 */
	protected $fail_trigger;

	/**
	 * @var TicketPriority
	 */
	protected $apply_priority;

	/**
	 * @var TicketTrigger
	 */
	protected $apply_trigger;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 */
	protected $people;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 */
	protected $organizations;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 */
	protected $ticket_slas;

	/**
	 * Creates a new team.
	 */
	public function __construct()
	{
		$this->people = new \Doctrine\Common\Collections\ArrayCollection();
		$this->organizations = new \Doctrine\Common\Collections\ArrayCollection();
		$this->ticket_slas = new \Doctrine\Common\Collections\ArrayCollection();
	}

	public function getWorkStartHour()
	{
		return floor($this->work_start / 3600);
	}

	public function getWorkStartMinute()
	{
		return floor(($this->work_start % 3600) / 60);
	}

	public function getWorkEndHour()
	{
		return floor($this->work_end / 3600);
	}

	public function getWorkEndMinute()
	{
		return floor(($this->work_end % 3600) / 60);
	}

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

	public function resetHolidays()
	{
		$this->setModelField('work_holidays', array());
	}

	public function removeHolidayKey($key)
	{
		$old = $this->work_holidays;
		unset($this->work_holidays[$key]);
		$this->_onPropertyChanged('work_holidays', $old, $this->work_holidays);
	}

	public function addHoliday($name, $day, $month, $year = null)
	{
		$old = $this->work_holidays;

		if (!$year) {
			$year = null;
		} else {
			$year = intval($year);
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

	public function getWarningTimeText()
	{
		return $this->_getTriggerTimeText($this->warning_trigger);
	}

	public function getFailTimeText()
	{
		return $this->_getTriggerTimeText($this->fail_trigger);
	}

	protected function _getTriggerTimeText($trigger) {
		if (!$trigger) {
			return '';
		}

		$length = $trigger->getOptionTime();
		$scale = $trigger->getOptionScale();

		$translator = App::getTranslator();

		switch ($scale) {
			case 'minutes': return $translator->phrase('admin.general.time_x_minute', array('count' => $length));
			case 'hours': return $translator->phrase('admin.general.time_x_hour', array('count' => $length));
			case 'days': return $translator->phrase('admin.general.time_x_day', array('count' => $length));
			case 'weeks': return $translator->phrase('admin.general.time_x_week', array('count' => $length));
			case 'months': return $translator->phrase('admin.general.time_x_month', array('count' => $length));
			default: return '';
		}
	}

	public function setPeople($people)
	{
		$have_ids = array();
		foreach ($people AS $person) {
			$this->addPerson($person);
			$have_ids[] = $person->id;
		}

		foreach ($this->people AS $k => $person) {
			if (!in_array($person->id, $have_ids)) {
				$this->people->remove($k);
			}
		}
	}

	public function removePerson(Person $person)
	{
		$this->people->removeElement($person);
	}

	public function addPerson(Person $person)
	{
		if (!$this->people->contains($person)) {
			$this->people->add($person);
		}
	}

	public function setOrganizations($organizations)
	{
		$have_ids = array();
		foreach ($organizations AS $organization) {
			$this->addOrganization($organization);
			$have_ids[] = $organization->id;
		}

		foreach ($this->organizations AS $k => $organization) {
			if (!in_array($organization->id, $have_ids)) {
				$this->organizations->remove($k);
			}
		}
	}

	public function removeOrganization(Organization $organization)
	{
		$this->organizations->removeElement($organization);
	}

	public function addOrganization(Organization $organization)
	{
		if (!$this->organizations->contains($organization)) {
			$this->organizations->add($organization);
		}
	}

	public function appliesToPerson(Person $person)
	{
		return App::getEntityRepository('DeskPRO:Sla')->doesSlaApplyToPerson($this, $person);
	}

	public function appliesToOrganization(Organization $organization)
	{
		return App::getEntityRepository('DeskPRO:Sla')->doesSlaApplyToOrganization($this, $organization);
	}

	public function calculateWarnDate(Ticket $ticket)
	{
		if (!$this->warning_trigger) {
			return null;
		}

		return $this->_calculateTriggerDate($this->warning_trigger->getOptionSeconds(), $ticket);
	}

	public function calculateFailDate(Ticket $ticket)
	{
		if (!$this->warning_trigger) {
			return null;
		}

		return $this->_calculateTriggerDate($this->fail_trigger->getOptionSeconds(), $ticket);
	}

	protected function _calculateTriggerDate($delay, Ticket $ticket)
	{
		if ($this->sla_type == self::TYPE_FIRST_RESPONSE) {
			if ($this->active_time == self::ACTIVE_24X7) {
				$date = $ticket->date_created->getTimestamp() + $delay;
				return new \DateTime("@$date");
			} else {
				return $this->_calculateWorkHoursDelay($ticket->date_created, $delay);
			}
		}

		if ($this->sla_type == self::TYPE_RESOLUTION) {
			if ($this->active_time == self::ACTIVE_24X7) {
				$date = $ticket->date_created->getTimestamp() + $delay;
				return new \DateTime("@$date");
			} else {
				return $this->_calculateWorkHoursDelay($ticket->date_created, $delay);
			}
		}

		if ($this->sla_type == self::TYPE_WAITING_TIME) {
			if ($ticket->status != 'awaiting_agent') {
				// can't know when it will expire
				return null;
			}

			if ($this->active_time == self::ACTIVE_24X7) {
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
							$wait_time += $this->_getWaitTimeWorkingLength($waiting['start'], $waiting['end']);
						}
					}
				}

				if ($ticket->date_user_waiting) {
					// ticket is waiting but we don't have an end so add that
					$wait_time += $this->_getWaitTimeWorkingLength($ticket->date_user_waiting->getTimestamp());
				}

				return $this->_calculateWorkHoursDelay(new \DateTime(), $delay - $wait_time);
			}
		}

		return null;
	}

	protected function _calculateWorkHoursDelay(\DateTime $date_start, $delay)
	{
		$work_day_length = $this->work_end - $this->work_start;
		if ($work_day_length <= 0) {
			return null;
		}

		if ($delay < 0) {
			return $this->_calculateWorkHoursDelayPast($date_start, $delay);
		}

		$date_end = new \DateTime('@' . $date_start->getTimestamp());
		if ($this->work_timezone) {
			$date_end->setTimezone(new \DateTimeZone($this->work_timezone));
		}

		$time_remaining = null;
		if ($this->_isInWorkDay($date_end, $time_remaining)) {
			if ($delay > $time_remaining) {
				$date_end->modify('+' . ($time_remaining + 1) . ' seconds');
				$delay -= $time_remaining;
			} else {
				$date_end->modify('+' . $delay . ' seconds');
				$delay = 0;
			}
		}

		while ($delay > 0) {
			$date_end = $this->_getNextWorkDayStart($date_end);
			if ($delay > $work_day_length) {
				$date_end->modify('+' . ($work_day_length + 1) . ' seconds');
				$delay -= $work_day_length;
			} else {
				$date_end->modify('+' . $delay . ' seconds');
				$delay = 0;
			}
		}

		return new \DateTime('@' . $date_end->getTimestamp());
	}

	protected function _calculateWorkHoursDelayPast(\DateTime $date_start, $delay)
	{
		$work_day_length = $this->work_end - $this->work_start;
		if ($work_day_length <= 0) {
			return null;
		}

		$date_end = new \DateTime('@' . $date_start->getTimestamp());
		if ($this->work_timezone) {
			$date_end->setTimezone(new \DateTimeZone($this->work_timezone));
		}

		$time_remaining = null;
		if ($this->_isInWorkDay($date_end, $time_remaining)) {
			$time_past = $work_day_length - $time_remaining;
			$date_end->modify('-' . ($time_past + 1) . ' seconds');
			$delay += $time_past;
		}

		while ($delay < 0) {
			$date_end = $this->_getNextWorkDayStart($date_end, true);
			$delay += $work_day_length;
		}

		return $this->_calculateWorkHoursDelay($date_end, $delay);
	}

	protected function _isInWorkDay(\DateTime $date, &$time_remaining = null)
	{
		$time_remaining = null;

		list($dow, $year, $month, $day, $hours, $minutes, $seconds) = explode('|', $date->format('w|Y|n|j|G|i|s'));
		$dow = intval($dow);
		$year = intval($year);
		$month = intval($month);
		$day = intval($day);
		$hours = intval($hours);
		$minutes = intval($minutes);
		$seconds = intval($seconds);

		if (!isset($this->work_days[$dow])) {
			return false;
		}

		$day_offset = $hours * 3600 + $minutes * 60 + $seconds;
		if ($day_offset < $this->work_start || $day_offset > $this->work_end) {
			return false;
		}

		foreach ($this->work_holidays AS $holiday) {
			if ($holiday['year'] && $year != $holiday['year']) {
				continue;
			}

			if ($holiday['day'] == $day && $holiday['month'] == $month) {
				return false;
			}
		}

		$time_remaining = $this->work_end - $day_offset;
		return true;
	}

	public function _getNextWorkDayStart(\DateTime $date, $backwards = false)
	{
		$work_date = clone $date;

		$adjust = ($backwards ? '-1 day' : '+1 day');

		do {
			list($dow, $year, $month, $day, $hours, $minutes, $seconds) = explode('|', $work_date->format('w|Y|n|j|G|i|s'));
			$dow = intval($dow);
			$year = intval($year);
			$month = intval($month);
			$day = intval($day);
			$hours = intval($hours);
			$minutes = intval($minutes);
			$seconds = intval($seconds);

			if (!isset($this->work_days[$dow])) {
				$work_date->modify($adjust);
				$work_date->setTime(0, 0, 0);
				continue;
			}

			foreach ($this->work_holidays AS $holiday) {
				// is today a holiday?
				if ($holiday['year'] && $year != $holiday['year']) {
					continue;
				}

				if ($holiday['day'] == $day && $holiday['month'] == $month) {
					$work_date->modify($adjust);
					$work_date->setTime(0, 0, 0);
					continue 2;
				}
			}

			$day_offset = $hours * 3600 + $minutes * 60 + $seconds;
			if ($day_offset > $this->work_start) {
				$work_date->modify($adjust);
				$work_date->setTime(0, 0, 0);
				continue;
			}

			// today is a work day and we haven't passed the start, so shift to that
			$work_date->setTime($this->getWorkStartHour(), $this->getWorkStartMinute());
			break;
		} while (true);

		return $work_date;
	}

	protected function _getWaitTimeWorkingLength($start, $end = null)
	{
		$start = ($start instanceof \DateTime ? $start->getTimestamp() : intval($start));
		$end = ($end instanceof \DateTime ? $end->getTimestamp() : intval($end));

		if (!$end) {
			$end = time();
		}

		$length = $end - $start;
		$work_day_length = $this->work_end - $this->work_start;

		$date = new \DateTime("@$start");
		if ($this->work_timezone) {
			$date->setTimezone(new \DateTimeZone($this->work_timezone));
		}

		$wait_time = 0;

		$time_remaining = null;
		if ($this->_isInWorkDay($date, $time_remaining)) {
			if ($length <= $time_remaining) {
				// waiting happened entirely in this work day
				$wait_time += $length;
				return $wait_time;
			} else {
				$wait_time += $time_remaining;
				$date->modify('+' . ($time_remaining + 1) . ' seconds');
			}
		}

		while ($date->getTimestamp() < $end) {
			$date = $this->_getNextWorkDayStart($date);
			if ($date->getTimestamp() >= $end) {
				break;
			}

			$work_end = $date->getTimestamp() + $work_day_length;
			if ($work_end >= $end) {
				// waiting ended within a work day
				$wait_time += $end - $date->getTimestamp();
				break;
			} else {
				// work day ended, still waiting from beginning
				$wait_time += $work_day_length;
			}
		}

		return $wait_time;
	}

	public function calculateCompleted(Ticket $ticket)
	{
		if ($ticket->status == 'resolved' || $ticket->status == 'hidden') {
			return true;
		}

		if ($this->sla_type == self::TYPE_FIRST_RESPONSE && $ticket->date_first_agent_reply) {
			return true;
		}

		return false;
	}

	public function getSlaTestTime(Ticket $ticket)
	{
		$times = array(time());

		if ($this->sla_type == self::TYPE_FIRST_RESPONSE && $ticket->date_first_agent_reply) {
			$times[] = $ticket->date_first_agent_reply->getTimestamp();
		}

		if ($ticket->date_resolved) {
			$times[] = $ticket->date_resolved->getTimestamp();
		}

		return min($times);
	}



	############################################################################
	# Doctrine Metadata
	############################################################################

	public static function loadMetadata(ClassMetadata $metadata)
	{
		$metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
		$metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\Sla';
		$metadata->setPrimaryTable(array(
			'name' => 'slas'
		));
		$metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
		$metadata->mapField(array( 'fieldName' => 'id', 'type' => 'integer', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'id', 'id' => true, ));
		$metadata->mapField(array( 'fieldName' => 'title', 'type' => 'string', 'length' => 100, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'title', ));
		$metadata->mapField(array( 'fieldName' => 'sla_type', 'type' => 'string', 'length' => 50, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'sla_type', ));
		$metadata->mapField(array( 'fieldName' => 'active_time', 'type' => 'string', 'length' => 50, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'active_time', ));
		$metadata->mapField(array( 'fieldName' => 'work_start', 'type' => 'integer', 'precision' => 0, 'scale' => 0, 'nullable' => true, 'columnName' => 'work_start', ));
		$metadata->mapField(array( 'fieldName' => 'work_end', 'type' => 'integer', 'precision' => 0, 'scale' => 0, 'nullable' => true, 'columnName' => 'work_end', ));
		$metadata->mapField(array( 'fieldName' => 'work_days', 'type' => 'array', 'precision' => 0, 'scale' => 0, 'nullable' => true, 'columnName' => 'work_days', ));
		$metadata->mapField(array( 'fieldName' => 'work_timezone', 'type' => 'string', 'length' => 50, 'precision' => 0, 'scale' => 0, 'nullable' => true, 'columnName' => 'work_timezone', ));
		$metadata->mapField(array( 'fieldName' => 'work_holidays', 'type' => 'array', 'precision' => 0, 'scale' => 0, 'nullable' => true, 'columnName' => 'work_holidays', ));
		$metadata->mapField(array( 'fieldName' => 'apply_all', 'type' => 'boolean', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'apply_all', ));
		$metadata->mapField(array( 'fieldName' => 'allow_agent_manual', 'type' => 'boolean', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'allow_agent_manual', ));

		$metadata->mapManyToOne(array( 'fieldName' => 'warning_trigger', 'targetEntity' => 'Application\\DeskPRO\\Entity\\TicketTrigger', 'cascade' => array('remove'), 'mappedBy' => NULL, 'inversedBy' => NULL, 'joinColumns' => array( 0 => array( 'name' => 'warning_trigger_id', 'referencedColumnName' => 'id', 'nullable' => true, 'onDelete' => 'set null', 'columnDefinition' => NULL, ), ),  ));
		$metadata->mapManyToOne(array( 'fieldName' => 'fail_trigger', 'targetEntity' => 'Application\\DeskPRO\\Entity\\TicketTrigger', 'cascade' => array('remove'), 'mappedBy' => NULL, 'inversedBy' => NULL, 'joinColumns' => array( 0 => array( 'name' => 'fail_trigger_id', 'referencedColumnName' => 'id', 'nullable' => true, 'onDelete' => 'set null', 'columnDefinition' => NULL, ), ),  ));
		$metadata->mapManyToOne(array( 'fieldName' => 'apply_priority', 'targetEntity' => 'Application\\DeskPRO\\Entity\\TicketPriority', 'mappedBy' => NULL, 'inversedBy' => NULL, 'joinColumns' => array( 0 => array( 'name' => 'apply_priority_id', 'referencedColumnName' => 'id', 'nullable' => true, 'onDelete' => 'set null', 'columnDefinition' => NULL, ), ),  ));
		$metadata->mapManyToOne(array( 'fieldName' => 'apply_trigger', 'targetEntity' => 'Application\\DeskPRO\\Entity\\TicketTrigger', 'cascade' => array('remove'), 'mappedBy' => NULL, 'inversedBy' => NULL, 'joinColumns' => array( 0 => array( 'name' => 'apply_trigger_id', 'referencedColumnName' => 'id', 'nullable' => true, 'onDelete' => 'set null', 'columnDefinition' => NULL, ), ),  ));

		$metadata->mapOneToMany(array( 'fieldName' => 'ticket_slas', 'targetEntity' => 'Application\\DeskPRO\\Entity\\TicketSla', 'cascade' => array( 0 => 'remove', 1 => 'persist', 3 => 'merge', ), 'mappedBy' => 'sla', 'orphanRemoval' => true ));

		$metadata->mapManyToMany(array( 'fieldName' => 'people', 'targetEntity' => 'Application\\DeskPRO\\Entity\\Person', 'joinTable' => array( 'name' => 'sla_people', 'schema' => NULL, 'joinColumns' => array( 0 => array( 'name' => 'sla_id', 'referencedColumnName' => 'id', 'nullable' => true, 'onDelete' => 'cascade', 'columnDefinition' => NULL, ), ), 'inverseJoinColumns' => array( 0 => array( 'name' => 'person_id', 'referencedColumnName' => 'id', 'nullable' => true, 'onDelete' => 'cascade', 'columnDefinition' => NULL, ), ), ), 'orderBy' => array( 'name' => 'ASC', ), ));
		$metadata->mapManyToMany(array( 'fieldName' => 'organizations', 'targetEntity' => 'Application\\DeskPRO\\Entity\\Organization', 'joinTable' => array( 'name' => 'sla_organizations', 'schema' => NULL, 'joinColumns' => array( 0 => array( 'name' => 'sla_id', 'referencedColumnName' => 'id', 'nullable' => true, 'onDelete' => 'cascade', 'columnDefinition' => NULL, ), ), 'inverseJoinColumns' => array( 0 => array( 'name' => 'organization_id', 'referencedColumnName' => 'id', 'nullable' => true, 'onDelete' => 'cascade', 'columnDefinition' => NULL, ), ), ), 'orderBy' => array( 'name' => 'ASC', ), ));

		$metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
	}
}
