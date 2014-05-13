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

use Application\DeskPRO\App;
use Application\DeskPRO\Domain\DomainObject;
use Application\DeskPRO\Tickets\Slas\SlaCalculator;
use Application\DeskPRO\Tickets\Triggers\TriggerActions;
use Application\DeskPRO\Tickets\Triggers\TriggerTerms;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Orb\Util\TimeUnit;
use Orb\Util\WorkHoursSet;
use Orb\Util\WorkHoursSetAll;

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
class Sla extends DomainObject
{
	const TYPE_FIRST_RESPONSE = 'first_response';
	const TYPE_RESOLUTION     = 'resolution';
	const TYPE_WAITING_TIME   = 'waiting_time';

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
	 * Controls how the SLA is applied to tickets: all, auto, manual
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


	protected $_calc;

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
	 * {@inheritDoc}
	 */
	protected function propertyChangedCallback($prop, $old, $new)
	{
		$this->_calc = null;
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


	/**
	 * @return \Orb\Util\WorkHoursSet
	 */
	public function getWorkHoursSet()
	{
		if ($this->active_time == 'all') {
			return new WorkHoursSetAll();
		} else if ($this->active_time == 'work_hours') {
			return new WorkHoursSet(
				$this->work_start,
				$this->work_end,
				$this->work_days,
				$this->work_timezone,
				$this->work_holidays
			);
		}

		return null;
	}


	/**
	 * @return SlaCalculator
	 */
	public function getCalculator()
	{
		if ($this->_calc !== null) return $this->_calc;

		$this->_calc = new SlaCalculator(
			$this->sla_type,
			$this->getWorkHoursSet(),
			new TimeUnit($this->warn_time, $this->warn_time_unit),
			new TimeUnit($this->fail_time, $this->fail_time_unit)
		);

		return $this->_calc;
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
			'type'       => 'simple_array',
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
			'type'       => 'json_array',
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
			'type'       => 'dp_json_obj',
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
			'type'       => 'dp_json_obj',
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
			'type'       => 'dp_json_obj',
			'nullable'   => false,
		));
	}
}
