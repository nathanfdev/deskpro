<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
    const ORGANIZATION     = 'organization_id';
    const PERSON           = 'person';
    const LANGUAGE         = 'language_id';
    const URGENCY          = 'urgency';
    const AGENT            = 'agent';
    const AGENT_TEAM       = 'agent_team_id';
    const WAITING_TIME     = 'date_user_waiting';
    const ALL_WAITING_TIME = 'total_user_waiting';
    const OPEN_TIME        = 'open_time';
    const DATE_CREATED     = 'date_created';

    /** @var Contains this grouping's value. */
    protected $column = null;
    /** @var Additional select column, that will probably be used for grouping. */
    protected $select = null;
    /** @var Ordering clauses. Useful for some groupings. */
    protected $order_by = null;

    /**
     * This class must either be instantiated with ::fromString() or ::fromConst().
     */
    protected function __construct($column)
    {
        $this->column = $column;

        switch ($column) {
            case self::ALL_WAITING_TIME:
                $this->select = [
                    'alias' => 'all_waiting_time',
                    'sql'   => "case when ticket.total_user_waiting between 0 and 30 then '30s' when ticket.total_user_waiting between 30 and 300 then '5min' when ticket.total_user_waiting between 300 and 3600 then '1h' when ticket.total_user_waiting between 3600 and 10800 then '3h' when ticket.total_user_waiting between 10800 and 86400 then '24h' when ticket.total_user_waiting between 86400 and 259200 then '3d' when ticket.total_user_waiting between 259200 and 604800 then '1w' when ticket.total_user_waiting between 604800 and 2419200 then '1m' else '>1m' end",
                ];
                $this->order_by = [
                    'ticket.total_user_waiting' => 'ASC',
                ];
                break;
            case self::WAITING_TIME:
                $this->select = [
                    'alias' => 'waiting_time',
                    'sql'   => "case when TIMESTAMPDIFF(SECOND, ticket.date_user_waiting, NOW()) between 0 and 30 then '30s' when TIMESTAMPDIFF(SECOND, ticket.date_user_waiting, NOW()) between 30 and 300 then '5min' when TIMESTAMPDIFF(SECOND, ticket.date_user_waiting, NOW()) between 300 and 3600 then '1h' when TIMESTAMPDIFF(SECOND, ticket.date_user_waiting, NOW()) between 3600 and 10800 then '3h' when TIMESTAMPDIFF(SECOND, ticket.date_user_waiting, NOW()) between 10800 and 86400 then '24h' when TIMESTAMPDIFF(SECOND, ticket.date_user_waiting, NOW()) between 86400 and 259200 then '3d' when TIMESTAMPDIFF(SECOND, ticket.date_user_waiting, NOW()) between 259200 and 604800 then '1w' when TIMESTAMPDIFF(SECOND, ticket.date_user_waiting, NOW()) between 604800 and 2419200 then '1m' else '>1m' end",
                ];
                $this->order_by = [
                    'ticket.date_user_waiting' => 'DESC',
                ];
                break;
            case self::OPEN_TIME:
                $this->select = [
                    'alias' => 'open_time',
                    'sql'   => "case when TIMESTAMPDIFF(SECOND, ticket.date_created, IF(ticket.date_archived IS NOT NULL, ticket.date_archived, NOW())) between 0 and 30 then '30s' when TIMESTAMPDIFF(SECOND, ticket.date_created, IF(ticket.date_archived IS NOT NULL, ticket.date_archived, NOW())) between 30 and 300 then '5min' when TIMESTAMPDIFF(SECOND, ticket.date_created, IF(ticket.date_archived IS NOT NULL, ticket.date_archived, NOW())) between 300 and 3600 then '1h' when TIMESTAMPDIFF(SECOND, ticket.date_created, IF(ticket.date_archived IS NOT NULL, ticket.date_archived, NOW())) between 3600 and 10800 then '3h' when TIMESTAMPDIFF(SECOND, ticket.date_created, IF(ticket.date_archived IS NOT NULL, ticket.date_archived, NOW())) between 10800 and 86400 then '24h' when TIMESTAMPDIFF(SECOND, ticket.date_created, IF(ticket.date_archived IS NOT NULL, ticket.date_archived, NOW())) between 86400 and 259200 then '3d' when TIMESTAMPDIFF(SECOND, ticket.date_created, IF(ticket.date_archived IS NOT NULL, ticket.date_archived, NOW())) between 259200 and 604800 then '1w' when TIMESTAMPDIFF(SECOND, ticket.date_created, IF(ticket.date_archived IS NOT NULL, ticket.date_archived, NOW())) between 604800 and 2419200 then '1m' else '>1m' end",
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

    public static function fromString($col_string)
    {
        if (self::isCustom($col_string)) {
            return new self($col_string);
        }

        // A tiny bit of magic. Need PHP 5.3+
        $constant = constant(sprintf('%s::%s', __CLASS__, strtoupper($col_string)));

        return self::fromConst($constant);
    }

    public static function fromConst($value)
    {
        $refl      = new \ReflectionClass(__CLASS__);
        $constants = $refl->getConstants();

        if (false === array_search($value, $constants)) {
            throw new UnknownTicketGroupingColumnException();
        }

        return new self($value);
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
        self::getCustomFieldIdFromName($this->column);
    }

    public function getSelect()
    {
        return $this->select;
    }

    public function getColumn()
    {
        return $this->column;
    }

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
        if (strpos($column, self::CUSTOM_FIELD_COLUMN_PREFIX) !== 0) {
            return false;
        }
        $prefix = self::CUSTOM_FIELD_COLUMN_PREFIX;
        $id     = preg_replace("/{$prefix}{$separator}/", '', $column, 1);

        return preg_match('/^\d+$/', $id);
    }

    /**
     * @param string $name
     * @param string $separator
     *
     * @throws \Exception
     * @return int
     *
     */
    public static function getCustomFieldIdFromName($name, $separator = '.')
    {
        if (!self::isCustom($name, $separator)) {
            throw new \Exception('Field is not custom');
        }
        $prefix = self::CUSTOM_FIELD_COLUMN_PREFIX;
        $id     = preg_replace("/{$prefix}{$separator}/", '', $name, 1);

        return intval($id);
    }
}
