<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Model;

use Application\DeskPRO\Entity\CustomDataAbstract;
use DeskPRO\Bundle\AppBundle\Exception\UnknownTicketGroupingColumnException;

/**
 * Defines grouping columns for the Ticket table.
 */
class TicketGrouping
{
    /**
     * Custom field column prefix.
     *
     * Full name of a custom column is "{$prefix}.{$column_id}"
     */
    const CUSTOM_FIELD_COLUMN_PREFIX = 'ticket_field';

    const DEPARTMENT       = 'department';
    const ORGANIZATION     = 'organization';
    const PERSON           = 'person';
    const LANGUAGE         = 'language';
    const URGENCY          = 'urgency';
    const AGENT            = 'agent';
    const AGENT_TEAM       = 'agent_team';
    const WAITING_TIME     = 'waiting_time';
    const ALL_WAITING_TIME = 'all_waiting_time';
    const OPEN_TIME        = 'open_time';
    const DATE_CREATED     = 'date_created';

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
        self::AGENT_TEAM   => 'agent_team_id',
        self::ORGANIZATION => 'organization_id',
        self::LANGUAGE     => 'language_id',
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
            case self::ALL_WAITING_TIME:
                $this->select = [
                    'alias' => $column,
                    'sql'   => "
                        case
                            when ticket.total_user_waiting between 0 and 30 then '30s'
                            when ticket.total_user_waiting between 30 and 300 then '5min'
                            when ticket.total_user_waiting between 300 and 3600 then '1h'
                            when ticket.total_user_waiting between 3600 and 10800 then '3h'
                            when ticket.total_user_waiting between 10800 and 86400 then '24h'
                            when ticket.total_user_waiting between 86400 and 259200 then '3d'
                            when ticket.total_user_waiting between 259200 and 604800 then '1w'
                            when ticket.total_user_waiting between 604800 and 2419200 then '1m'
                            else '>1m'
                        end",
                ];
                $this->order_by = [
                    'ticket.total_user_waiting' => 'ASC',
                ];
                break;
            case self::WAITING_TIME:
                $this->select = [
                    'alias' => $column,
                    'sql'   => "
                        case
                            when TIMESTAMPDIFF(SECOND, ticket.date_user_waiting, NOW()) between 0 and 30 then '30s'
                            when TIMESTAMPDIFF(SECOND, ticket.date_user_waiting, NOW()) between 30 and 300 then '5min'
                            when TIMESTAMPDIFF(SECOND, ticket.date_user_waiting, NOW()) between 300 and 3600 then '1h'
                            when TIMESTAMPDIFF(SECOND, ticket.date_user_waiting, NOW()) between 3600 and 10800 then '3h'
                            when TIMESTAMPDIFF(SECOND, ticket.date_user_waiting, NOW()) between 10800 and 86400 then '24h'
                            when TIMESTAMPDIFF(SECOND, ticket.date_user_waiting, NOW()) between 86400 and 259200 then '3d'
                            when TIMESTAMPDIFF(SECOND, ticket.date_user_waiting, NOW()) between 259200 and 604800 then '1w'
                            when TIMESTAMPDIFF(SECOND, ticket.date_user_waiting, NOW()) between 604800 and 2419200 then '1m'
                            else '>1m'
                        end",
                ];
                $this->order_by = [
                    'ticket.date_user_waiting' => 'DESC',
                ];
                break;
            case self::OPEN_TIME:
                $this->select = [
                    'alias' => $column,
                    'sql'   => "
                        case
                            when TIMESTAMPDIFF(SECOND, ticket.date_created, IF(ticket.date_archived IS NOT NULL, ticket.date_archived, NOW())) between 0 and 30 then '30s'
                            when TIMESTAMPDIFF(SECOND, ticket.date_created, IF(ticket.date_archived IS NOT NULL, ticket.date_archived, NOW())) between 30 and 300 then '5min'
                            when TIMESTAMPDIFF(SECOND, ticket.date_created, IF(ticket.date_archived IS NOT NULL, ticket.date_archived, NOW())) between 300 and 3600 then '1h'
                            when TIMESTAMPDIFF(SECOND, ticket.date_created, IF(ticket.date_archived IS NOT NULL, ticket.date_archived, NOW())) between 3600 and 10800 then '3h'
                            when TIMESTAMPDIFF(SECOND, ticket.date_created, IF(ticket.date_archived IS NOT NULL, ticket.date_archived, NOW())) between 10800 and 86400 then '24h'
                            when TIMESTAMPDIFF(SECOND, ticket.date_created, IF(ticket.date_archived IS NOT NULL, ticket.date_archived, NOW())) between 86400 and 259200 then '3d'
                            when TIMESTAMPDIFF(SECOND, ticket.date_created, IF(ticket.date_archived IS NOT NULL, ticket.date_archived, NOW())) between 259200 and 604800 then '1w'
                            when TIMESTAMPDIFF(SECOND, ticket.date_created, IF(ticket.date_archived IS NOT NULL, ticket.date_archived, NOW())) between 604800 and 2419200 then '1m'
                            else '>1m'
                        end",
                ];
                $this->order_by = [
                    'ticket.date_archived' => 'DESC',
                    'ticket.date_created'  => 'DESC',
                ];
                break;
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
