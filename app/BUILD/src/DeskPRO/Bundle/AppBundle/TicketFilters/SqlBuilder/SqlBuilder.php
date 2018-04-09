<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters\SqlBuilder;

use DeskPRO\Component\Util\MapUtils;

class SqlBuilder extends \Doctrine\DBAL\Query\QueryBuilder
{
    /**
     * Static counter used for identifiers.
     *
     * @var int
     */
    private $queryPartCount = 0;

    /**
     * A main table alias that is usually the root static table
     * that sub parts can refer to.
     *
     * @var string
     */
    private $mainTableAlias = null;

    /**
     * If 'WITH ROLLUP' sql is added to the query.
     *
     * @var bool
     */
    private $withRollup = false;

    /**
     * @return bool
     */
    public function isWithRollup()
    {
        return $this->withRollup;
    }

    /**
     * @return $this
     */
    public function enableWithRollup()
    {
        $this->withRollup = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function disableWithRollup()
    {
        $this->withRollup = false;

        return $this;
    }

    /**
     * @return string
     */
    public function getMainTableAlias()
    {
        return $this->mainTableAlias;
    }

    /**
     * @param string $mainTableAlias
     *
     * @return $this
     */
    public function setMainTableAlias($mainTableAlias)
    {
        $this->mainTableAlias = $mainTableAlias;

        return $this;
    }

    /**
     * @param SqlConditionGroup $group
     *
     * @return $this
     */
    public function addQueryConditionGroup(SqlConditionGroup $group)
    {
        $where = $this->initQueryCondGroup($group);
        $this->andWhere($where);

        return $this;
    }

    /**
     * @param SqlCondition $cond
     *
     * @return $this
     */
    public function addQueryCondition(SqlCondition $cond)
    {
        $where = $this->initQueryCond($cond);
        $this->andWhere($where);

        return $this;
    }

    /**
     * @param SqlConditionGroup $group
     * @param int               $level Level of nesting
     *
     * @return string
     */
    private function initQueryCondGroup(SqlConditionGroup $group, $level = 0)
    {
        // this happens if someone is needlessly wrapping conditions
        // it prevents extra parenthesis in the generated sql, just clenas stuff up a bit
        if (!$group->countConditions() && $group->countSubGroups() === 1) {
            $subGroups = $group->getSubGroups();

            return $this->initQueryCondGroup($subGroups[0]);
        }

        $wheres = [];
        foreach ($group->getConditions() as $cond) {
            $wheres[] = $this->initQueryCond($cond);
        }

        foreach ($group->getSubGroups() as $subGroup) {
            $wheres[] = $this->initQueryCondGroup($subGroup, $level + 1);
        }

        $op = $group->getOperator();

        if (count($wheres) === 1 || $level === 0) {
            // we dont need to wrap with parens if
            // we're on the first level deep (doctrine builder already wraps it for us)
            // or if theres only one expression
            return implode(" $op ", $wheres);
        } else {
            return '('.implode(" $op ", $wheres).')';
        }
    }

    /**
     * Inits a query condition and returns the WHERE clause.
     *
     * @param SqlCondition $cond
     *
     * @return string
     */
    private function initQueryCond(SqlCondition $cond)
    {
        $queryParts = $this->getQueryParts();

        if (empty($queryParts['from'][0])) {
            throw new \RuntimeException('A FROM clause must be added before query conditions can be added');
        }

        //------------------------------
        // Figure out local names that need to be made global
        //------------------------------

        $id = $this->queryPartCount++;

        $joinRenames = [];
        $varRenames  = [];

        // Automatically add {from} alias
        $joinRenames['from'] = $queryParts['from'][0]['alias'];
        if ($this->mainTableAlias) {
            $joinRenames[$this->mainTableAlias] = $queryParts['from'][0]['alias'];
        }

        foreach ($cond->getSharedJoins() as $j) {
            $joinRenames[$j['localAlias']] = $j['table'];
        }
        foreach ($cond->getUniqueJoins() as $j) {
            $uniqueName = "c{$id}_{$j['localAlias']}";
            ++$id;
            $joinRenames[$j['localAlias']] = $uniqueName;
        }
        foreach ($cond->getParams() as $localName => $valueInfo) {
            $uniqueName = "c{$id}_{$localName}";
            ++$id;
            $varRenames[$localName] = $uniqueName;
            $this->setParameter($uniqueName, $valueInfo[0], $valueInfo[1]);
        }

        // Sort vars from longest name to shortest
        // so our strreplace always replaces longer names and not partials
        $varRenames = MapUtils::sortByFnValue($varRenames, function ($k, $v) {
            return strlen($k);
        }, true);

        //------------------------------
        // Add joins
        //------------------------------

        $joinNames = !empty($queryParts['joins']) ? array_keys($queryParts['joins']) : [];

        foreach ($cond->getSharedJoins() as $j) {
            if (!in_array($j['table'], $joinNames)) {
                $fromAlias = $this->replaceLocalNames('{'.$j['fromAlias'].'}', null, $joinRenames);
                $on        = $this->replaceLocalNames($j['on'], $varRenames, $joinRenames);
                switch ($j['type']) {
                    case 'LEFT':  $this->leftJoin($fromAlias, $j['table'], $joinRenames[$j['localAlias']], $on); break;
                    case 'RIGHT': $this->rightJoin($fromAlias, $j['table'], $joinRenames[$j['localAlias']], $on); break;
                    case 'INNER': $this->innerJoin($fromAlias, $j['table'], $joinRenames[$j['localAlias']], $on); break;
                    default: throw new \InvalidArgumentException('Unknown join type');
                }
            }
        }
        foreach ($cond->getUniqueJoins() as $j) {
            $fromAlias = $this->replaceLocalNames('{'.$j['fromAlias'].'}', null, $joinRenames);
            $on        = $this->replaceLocalNames($j['on'], $varRenames, $joinRenames);
            switch ($j['type']) {
                case 'LEFT':  $this->leftJoin($fromAlias, $j['table'], $joinRenames[$j['localAlias']], $on); break;
                case 'RIGHT': $this->rightJoin($fromAlias, $j['table'], $joinRenames[$j['localAlias']], $on); break;
                case 'INNER': $this->innerJoin($fromAlias, $j['table'], $joinRenames[$j['localAlias']], $on); break;
                default: throw new \InvalidArgumentException('Unknown join type');
            }
        }

        //------------------------------
        // Add where
        //------------------------------

        return $this->replaceLocalNames($cond->getWhere(), $varRenames, $joinRenames);
    }

    private function replaceLocalNames($string, $varRenames, $joinRenames)
    {
        if ($varRenames) {
            foreach ($varRenames as $local => $namespaced) {
                $string = str_replace(":$local", ":$namespaced", $string);
            }
        }
        if ($joinRenames) {
            foreach ($joinRenames as $local => $namespaced) {
                $string = str_replace('{'.$local.'}', $namespaced, $string);
            }
        }

        return $string;
    }

    /**
     * {@inheritdoc}
     */
    public function getSQL()
    {
        if ($this->withRollup && !empty($this->getQueryPart('groupBy'))) {
            // DBAL doesnt support WITH ROLLUP
            // so this is a hack to get 'WITH ROLLUP' added
            // to the right place in the SQL without
            // needing to actually re-parse the query or
            // have nasty regex

            // add a fake placeholder to represent
            // where we need the 'WITH ROLLUP' keyword to go
            $tok      = '__BOGUS_GROUP_BY_'.uniqid().'__';
            $oldParts = $this->getQueryPart('groupBy');
            $this->add('groupBy', $tok, true);
            $sql = parent::getSQL();

            // restore the 'real' group bar params the user added
            $this->resetQueryPart('groupBy');
            foreach ($oldParts as $p) {
                $this->add('groupBy', $p, true);
            }

            // replace our token with the real keyword
            $sql = str_replace(", $tok", ' WITH ROLLUP', $sql);
        } else {
            $sql = parent::getSQL();
        }

        return $sql;
    }
}
