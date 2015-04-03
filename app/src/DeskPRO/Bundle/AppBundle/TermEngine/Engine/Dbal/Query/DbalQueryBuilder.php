<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at https://www.deskpro.com/eula/                            |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQuery;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\Helper\DbalEntityHelper;

class DbalQueryBuilder
{
    /**
     * @var DbalQuery
     */
    private $query;

    public function __construct(DbalQuery $query)
    {
        $this->query = $query;
    }

    /**
     * @return DbalQuery
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * ONLY the main compiler should use this. It will replace the existing WHERE string
     * in the query with a new one.
     *
     * @param $new_where_string
     */
    public function setWhereString($new_where_string)
    {
        $this->query->setWherePart($new_where_string);
    }

    /**
     * Set a parameter, but the $name_prefix is just a prefix. The actual parameter
     * name will be returned to you.
     *
     * @param $name_prefix
     * @param $value
     * @return string the parameter name
     */
    public function addParameter($name_prefix, $value)
    {
        return $this->query->addParameter($name_prefix, $value);
    }

    /**
     * This will do nothing if the table is already joined, else it will join the
     * table with the given ON
     *
     * @param $table_name
     * @param string $on
     */
    public function addJoin($table_name, $on)
    {
        $this->query->addJoin($table_name, $on);
    }

    /**
     * You can use {alias} in the $on param as a placeholder for the real join alias.
     *
     * It returns the real join alias so you can reference it.
     *
     * @param $table_name
     * @param string $on
     * @param string $type
     * @return string the join alias
     */
    public function addUniqueJoin($table_name, $on, $type = 'LEFT')
    {
        return $this->query->addUniqueJoin($table_name, $on, $type);
    }

    /**
     * What table are we selecting from?
     *
     * @param $table
     * @param $alias
     */
    public function setFrom($table, $alias)
    {
        $this->query->setFrom($table, $alias);
    }

    /**
     * @param $field_name
     * @param $op
     * @param array $ids
     * @return string
     */
    public function writeEntityCheck($field_name, $op, array $ids)
    {
        return DbalEntityHelper::write($this, $field_name, $op, $ids);
    }
}
