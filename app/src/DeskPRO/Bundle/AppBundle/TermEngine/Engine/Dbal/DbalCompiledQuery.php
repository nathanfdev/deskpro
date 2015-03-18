<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at http://www.deskpro.com/license                           |
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

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal;


class DbalCompiledQuery
{
    /**
     * @var string
     */
    private $select;

    /**
     * @var string
     */
    private $from_table;

    /**
     * @var string
     */
    private $from_alias;

    /**
     * @var string
     */
    private $where;

    /**
     * @var string
     */
    private $modifiers;

    /**
     * @var array
     */
    private $joins;

    /**
     * @var array
     */
    private $parameters;

    public function __construct()
    {
    }

    public function __toString()
    {
        return sprintf(
            'SELECT %s%s FROM %s WHERE %s %s',
            $this->select,
            $this->from_table,
            $this->from_alias ? ' ' . $this->from_alias : '',
            $this->where,
            $this->modifiers
        );
    }

    /**
     * @return mixed
     */
    public function getWhere()
    {
        return $this->where;
    }

    /**
     * @param mixed $where
     */
    public function setWhere($where)
    {
        $this->where = trim($where);
    }

    /**
     * @return mixed
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param mixed $parameters
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * @return mixed
     */
    public function getModifiers()
    {
        return $this->modifiers;
    }

    /**
     * @param mixed $modifiers
     */
    public function setModifiers($modifiers)
    {
        $this->modifiers = trim($modifiers);
    }

    /**
     * @return string
     */
    public function getSelect()
    {
        return $this->select;
    }

    /**
     * @param string $select
     */
    public function setSelect($select)
    {
        $this->select = trim($select);
    }

    /**
     * @return array
     */
    public function getJoins()
    {
        return $this->joins;
    }

    public function addJoin($table, $alias)
    {
        $this->joins[trim($table)] = trim($alias);
    }

    /**
     * @param array $joins
     */
    public function setJoins(array $joins)
    {
        $this->joins = $joins;
    }

    /**
     * @return string
     */
    public function getFromTable()
    {
        return $this->from_table;
    }

    /**
     * @param string $from_table
     */
    public function setFromTable($from_table)
    {
        $this->from_table = trim($from_table);
    }

    /**
     * @return string
     */
    public function getFromAlias()
    {
        return $this->from_alias;
    }

    /**
     * @param string $from_alias
     */
    public function setFromAlias($from_alias)
    {
        $this->from_alias = trim($from_alias);
    }
}
