<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Model;

use Application\DeskPRO\Entity\CustomDataAbstract;
use DeskPRO\Bundle\AppBundle\Exception\UnknownTicketGroupingColumnException;

/**
 * Defines grouping columns for the Ticket table.
 *
 * @deprecated as of new filters
 */
class TicketGrouping
{
    /**
     * Custom field column prefix.
     *
     * Full name of a custom column is "{$prefix}.{$column_id}"
     */
    const CUSTOM_FIELD_COLUMN_PREFIX = 'ticket_field';

    const DEPARTMENT = 'department';
    /** @deprecated not in use */
    const ORGANIZATION = 'organization';
    /** @deprecated not in use */
    const PERSON     = 'person';
    const LANGUAGE   = 'language';
    const URGENCY    = 'urgency';
    const AGENT      = 'agent';
    const AGENT_TEAM = 'agent_team';
    /** @deprecated not in use */
    const WAITING_TIME = 'waiting_time';
    /** @deprecated not in use */
    const ALL_WAITING_TIME = 'all_waiting_time';
    /** @deprecated not in use */
    const OPEN_TIME    = 'open_time';
    const DATE_CREATED = 'date_created';

    /**
     * Contains this grouping's value.
     *
     * @var string
     */
    protected $column = null;

    /**
     * Additional select column, that will probably be used for grouping.
     *
     * @var array
     */
    protected $select = null;

    /**
     * Ordering clauses. Useful for some groupings.
     *
     * @var array
     */
    protected $order_by = null;

    /**
     * @var array
     */
    protected static $fieldsMapping = [
        self::AGENT_TEAM => 'agent_team_id',
        self::LANGUAGE   => 'language_id',
    ];

    /**
     * Constructor.
     *
     * This class must either be instantiated with ::fromString() or ::fromConst().
     *
     * @param string $column
     */
    protected function __construct($column)
    {
        $this->column = $column;
        if (isset(self::$fieldsMapping[$column])) {
            $this->column = self::$fieldsMapping[$column];
        }

        switch ($column) {
            case self::DATE_CREATED:
                //todo
                break;
        }

        if (self::isCustom($column)) {
            $this->select = [
                'alias' => self::CUSTOM_FIELD_COLUMN_PREFIX,
                'sql'   => CustomDataAbstract::getDataSql(),
            ];
        }
    }

    /**
     * @param string $value
     *
     * @return TicketGrouping
     */
    public static function fromString($value)
    {
        if (self::isCustom($value)) {
            return new self($value);
        }

        return self::fromConst($value);
    }

    /**
     * @param string $value
     *
     * @throws UnknownTicketGroupingColumnException
     *
     * @return TicketGrouping
     */
    public static function fromConst($value)
    {
        $refl      = new \ReflectionClass(__CLASS__);
        $constants = $refl->getConstants();
        $name      = strtoupper($value);

        if (empty($constants[$name])) {
            throw new UnknownTicketGroupingColumnException();
        }

        return new self($constants[$name]);
    }

    /**
     * @return bool
     */
    public function isCustomField()
    {
        return self::isCustom($this->column);
    }

    /**
     * @throws \Exception
     *
     * @return int
     */
    public function getCustomFieldId()
    {
        return self::getCustomFieldIdFromName($this->column);
    }

    /**
     * @return array
     */
    public function getSelect()
    {
        return $this->select;
    }

    /**
     * @return string
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * @return array|null
     */
    public function getOrderBy()
    {
        return $this->order_by;
    }

    /**
     * @param string $column
     * @param string $separator
     *
     * @return bool
     */
    public static function isCustom($column, $separator = '.')
    {
        if (strpos($column, self::CUSTOM_FIELD_COLUMN_PREFIX.$separator) !== 0) {
            return false;
        }

        $id = substr($column, strlen(self::CUSTOM_FIELD_COLUMN_PREFIX) + strlen($separator));

        return preg_match('/^\d+$/', $id);
    }

    /**
     * @param string $name
     * @param string $separator
     *
     * @throws \Exception
     *
     * @return int
     */
    public static function getCustomFieldIdFromName($name, $separator = '.')
    {
        if (!self::isCustom($name, $separator)) {
            throw new \Exception('Field is not custom');
        }

        $id = substr($name, strlen(self::CUSTOM_FIELD_COLUMN_PREFIX) + strlen($separator));

        return intval($id);
    }
}
