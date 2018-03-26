<?php

/**
 * DeskPRO.
 *
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
use JMS\Serializer\Annotation as JMS;
use Orb\Util\Arrays;
use Orb\Util\OptionsArray;
use Orb\Util\TimeUnit;
use Orb\Util\WorkHoursSet;
use Orb\Util\WorkHoursSetAll;

/**
 * Entity for an SLA record.
 *
 * @property int            $id
 * @property string         $title
 * @property string         $sla_type
 * @property string         $active_time
 * @property int            $work_start
 * @property int            $work_end
 * @property int[]          $work_days
 * @property string         $work_timezone
 * @property array          $work_holidays
 * @property string         $apply_type
 * @property TriggerTerms   $apply_terms
 * @property int            $warn_time
 * @property string         $warn_time_unit
 * @property TriggerActions $warn_actions
 * @property int            $fail_time
 * @property string         $fail_time_unit
 * @property TriggerActions $fail_actions
 *
 * @JMS\ExclusionPolicy("all")
 */
class Sla extends DomainObject
{
    const TYPE_FIRST_RESPONSE = 'first_response';
    const TYPE_RESOLUTION     = 'resolution';
    const TYPE_WAITING_TIME   = 'waiting_time';

    /**
     * The unique ID.
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $id = null;

    /**
     * SLA title.
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $title = '';

    /**
     * Type of SLA - first_response, resolution, waiting_time.
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $sla_type;

    /**
     * Whether active all the time (all) or during work hours only (work_hours)
     * or use the default ticket-wide settings (default).
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $active_time = 'default';

    /**
     * When the work day starts. This is stored as the number of seconds after 00:00:00.
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $work_start;

    /**
     * When the work day ends. This is stored as the number of seconds after 00:00:00.
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $work_end;

    /**
     * Array of work days, stored with keys corresponding to day numbers. Values are true.
     * 0 = Sunday, 6 = Saturday (same as PHP, easy to convert to MySQL which is 1 = Sunday, 7 = Saturday).
     *
     * @JMS\Expose()
     * @JMS\Type("array")
     *
     * @var array
     */
    protected $work_days = [];

    /**
     * Timezone for work hours/days to be considered in.
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $work_timezone;

    /**
     * List of work holidays.
     *
     * @JMS\Expose()
     * @JMS\Type("array")
     *
     * @var array
     */
    protected $work_holidays = [];

    /**
     * Controls how the SLA is applied to tickets: all, auto, manual.
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $apply_type = 'all';

    /**
     * Conditions to apply.
     *
     * @JMS\Expose()
     * @JMS\Groups({"details"})
     * @JMS\Type("Application\DeskPRO\Tickets\Triggers\TriggerTerms")
     *
     * @var \Application\DeskPRO\Tickets\Triggers\TriggerTerms
     */
    protected $apply_terms = null;

    /**
     * Warn time integer.
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $warn_time = 1;

    /**
     * And it's unit (see warn time).
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $warn_time_unit = 'days';

    /**
     * How to warn about SLA will fail.
     *
     * @JMS\Expose()
     * @JMS\Groups({"details"})
     * @JMS\Type("Application\DeskPRO\Tickets\Triggers\TriggerActions")
     *
     * @var \Application\DeskPRO\Tickets\Triggers\TriggerActions
     */
    protected $warn_actions = null;

    /**
     * Fail time integer.
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $fail_time = 1;

    /**
     * And it's measuring unit (see fail_time).
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $fail_time_unit = 'days';

    /**
     * What to do if SLA failed.
     *
     * @JMS\Expose()
     * @JMS\Groups({"details"})
     * @JMS\Type("Application\DeskPRO\Tickets\Triggers\TriggerActions")
     *
     * @var \Application\DeskPRO\Tickets\Triggers\TriggerActions
     */
    protected $fail_actions = null;

    /**
     * @var \Orb\Util\WorkHoursSet
     */
    protected $_work_hours_set;

    /**
     * @var SlaCalculator
     */
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

    public function setTitle($title)
    {
        $this->setModelField('title', $title);

        return $this;
    }

    public function setSlaType($type)
    {
        $this->setModelField('sla_type', $type);

        return $this;
    }

    public function setApplyType($type)
    {
        $this->setModelField('apply_type', $type);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function propertyChangedCallback($prop, $old, $new)
    {
        $this->_calc = null;
    }

    /**
     * Set work days (ISO-8601, 1=monday, 7=sunday).
     *
     * @param array $days
     * @param bool  $raw
     */
    public function setWorkDays(array $days, $raw = false)
    {
        if (!$raw) {
            $days = array_unique($days);
            sort($days);
        }
        $this->setModelField('work_days', $days);
    }

    /**
     * Resets holidays.
     */
    public function resetHolidays()
    {
        $this->setModelField('work_holidays', []);
    }

    /**
     * Removes a single holiday by index.
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
     * Adds a holiday.
     *
     * @param      $name
     * @param      $day
     * @param      $month
     * @param null $year
     *
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

        if (!$this->work_holidays) {
            $this->work_holidays = [];
        }
        foreach ($this->work_holidays as $k => $existing) {
            if ($existing['day'] == $day && $existing['month'] == $month && $existing['year'] === $year) {
                return $k;
            }
        }

        $this->work_holidays[] = [
            'name'  => $name,
            'day'   => intval($day),
            'month' => intval($month),
            'year'  => $year,
        ];

        $this->_onPropertyChanged('work_holidays', $old, $this->work_holidays);

        return count($this->work_holidays) - 1;
    }

    /**
     * Gets an array of holidays, sorted by date.
     *
     * @return array
     */
    public function getHolidaysSorted()
    {
        $holidays = $this->work_holidays;
        uasort(
            $holidays,
            function ($a, $b) {
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
            }
        );

        return $holidays;
    }

    /**
     * @return \Orb\Util\WorkHoursSet
     */
    public function getWorkHoursSet()
    {
        if ($this->active_time == 'all') {
            return new WorkHoursSetAll();
        } elseif ($this->active_time == 'work_hours' || $this->active_time == 'custom') {
            return new WorkHoursSet(
                $this->work_start ?: 32400,
                $this->work_end ?: 64860,
                $this->work_days ?: [1, 2, 3, 4, 5],
                $this->work_timezone ?: 'UTC',
                $this->work_holidays ?: []
            );
        } else {
            $work_hours = App::getSetting('core_tickets.work_hours');
            if ($work_hours && !is_array($work_hours)) {
                $work_hours = @unserialize($work_hours);
            }
            if ($work_hours) {
                $work_hours = Arrays::removeEmptyArray($work_hours);
                $work_hours = Arrays::removeNull($work_hours);
                $work_hours = Arrays::removeEmptyString($work_hours);

                $work_hours = new OptionsArray($work_hours);

                return new WorkHoursSet(
                    $work_hours->get('start_hour', 9) * 3600 + $work_hours->get('start_min', 0) * 60,
                    $work_hours->get('end_hour', 18) * 3600 + $work_hours->get('end_min', 0) * 60,
                    $work_hours->get('work_days', [1, 2, 3, 4, 5]),
                    $work_hours->get('timezone', 'UTC'),
                    $work_hours->get('holidays', [])
                );
            } else {
                return new WorkHoursSetAll();
            }
        }
    }

    /**
     * @return SlaCalculator
     */
    public function getCalculator()
    {
        if ($this->_calc !== null) {
            return $this->_calc;
        }

        $this->_calc = new SlaCalculator(
            $this->sla_type,
            $this->getWorkHoursSet(),
            new TimeUnit($this->warn_time, $this->warn_time_unit),
            new TimeUnit($this->fail_time, $this->fail_time_unit)
        );

        return $this->_calc;
    }

    /**
     * {@inheritdoc}
     */
    public function toApiData($primary = true, $deep = true, array $visited = [])
    {
        if ($this->apply_terms === null) {
            $this->apply_terms = new TriggerTerms();
        }
        if ($this->warn_actions === null) {
            $this->warn_actions = new TriggerActions();
        }
        if ($this->fail_actions === null) {
            $this->fail_actions = new TriggerActions();
        }

        $data                 = parent::toApiData($primary, $deep, $visited);
        $data['apply_terms']  = $this->apply_terms->exportToArray();
        $data['warn_actions'] = $this->warn_actions->exportToArray();
        $data['fail_actions'] = $this->fail_actions->exportToArray();

        return $data;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->inheritanceType           = ClassMetadataInfo::INHERITANCE_TYPE_NONE;
        $metadata->changeTrackingPolicy      = ClassMetadataInfo::CHANGETRACKING_NOTIFY;
        $metadata->generatorType             = ClassMetadataInfo::GENERATOR_TYPE_IDENTITY;
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\Sla';

        $metadata->setPrimaryTable(
            [
                'name' => 'slas',
            ]
        );
        $metadata->mapField(
            [
                'columnName' => 'id',
                'fieldName'  => 'id',
                'type'       => 'integer',
                'id'         => true,
                'nullable'   => false,
            ]
        );
        $metadata->mapField(
            [
                'columnName' => 'title',
                'fieldName'  => 'title',
                'type'       => 'string',
                'length'     => 100,
                'nullable'   => false,
            ]
        );
        $metadata->mapField(
            [
                'columnName' => 'sla_type',
                'fieldName'  => 'sla_type',
                'type'       => 'string',
                'length'     => 50,
                'nullable'   => false,
            ]
        );
        $metadata->mapField(
            [
                'columnName' => 'active_time',
                'fieldName'  => 'active_time',
                'type'       => 'string',
                'length'     => 50,
                'nullable'   => false,
            ]
        );

        $metadata->mapField(
            [
                'columnName' => 'work_start',
                'fieldName'  => 'work_start',
                'type'       => 'integer',
                'nullable'   => true,
            ]
        );
        $metadata->mapField(
            [
                'columnName' => 'work_end',
                'fieldName'  => 'work_end',
                'type'       => 'integer',
                'nullable'   => true,
            ]
        );
        $metadata->mapField(
            [
                'columnName' => 'work_days',
                'fieldName'  => 'work_days',
                'type'       => 'simple_array',
                'nullable'   => true,
            ]
        );
        $metadata->mapField(
            [
                'columnName' => 'work_timezone',
                'fieldName'  => 'work_timezone',
                'type'       => 'string',
                'length'     => 50,
                'nullable'   => true,
            ]
        );
        $metadata->mapField(
            [
                'columnName' => 'work_holidays',
                'fieldName'  => 'work_holidays',
                'type'       => 'json_array',
                'nullable'   => true,
            ]
        );
        $metadata->mapField(
            [
                'columnName' => 'apply_type',
                'fieldName'  => 'apply_type',
                'type'       => 'string',
                'length'     => 25,
                'nullable'   => false,
            ]
        );
        $metadata->mapField(
            [
                'columnName' => 'apply_terms',
                'fieldName'  => 'apply_terms',
                'type'       => 'dp_json_obj',
                'nullable'   => false,
            ]
        );
        $metadata->mapField(
            [
                'columnName' => 'warn_time',
                'fieldName'  => 'warn_time',
                'type'       => 'integer',
                'nullable'   => false,
            ]
        );
        $metadata->mapField(
            [
                'columnName' => 'warn_time_unit',
                'fieldName'  => 'warn_time_unit',
                'type'       => 'string',
                'length'     => 50,
                'nullable'   => false,
            ]
        );
        $metadata->mapField(
            [
                'columnName' => 'warn_actions',
                'fieldName'  => 'warn_actions',
                'type'       => 'dp_json_obj',
                'nullable'   => false,
            ]
        );
        $metadata->mapField(
            [
                'columnName' => 'fail_time',
                'fieldName'  => 'fail_time',
                'type'       => 'integer',
                'nullable'   => false,
            ]
        );
        $metadata->mapField(
            [
                'columnName' => 'fail_time_unit',
                'fieldName'  => 'fail_time_unit',
                'type'       => 'string',
                'length'     => 50,
                'nullable'   => false,
            ]
        );
        $metadata->mapField(
            [
                'columnName' => 'fail_actions',
                'fieldName'  => 'fail_actions',
                'type'       => 'dp_json_obj',
                'nullable'   => false,
            ]
        );
    }
}
