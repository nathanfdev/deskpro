<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Filters\Terms;

use Application\DeskPRO\Criteria\CriteriaTermInterface;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Orb\Util\Arrays;
use Orb\Util\CheckedOptionsArray;
use Orb\Util\Util;

abstract class AbstractFilterTerm implements CriteriaTermInterface, FilterTermInterface
{
    const OP_NOOP        = null;
    const OP_IS          = 'is';
    const OP_NOT         = 'not';
    const OP_LT          = 'lt';
    const OP_GT          = 'gt';
    const OP_LTE         = 'lte';
    const OP_GTE         = 'gte';
    const OP_BETWEEN     = 'between';
    const OP_NOTBETWEEN  = 'notbetween';
    const OP_CONTAINS    = 'contains';
    const OP_NOTCONTAINS = 'notcontains';
    const OP_IS_REGEX    = 'is_regex';
    const OP_NOT_REGEX   = 'not_regex';

    /**
     * @var
     */
    private $op;

    /**
     * @var \Orb\Util\CheckedOptionsArray
     */
    private $options;

    /**
     * @param string $op
     * @param array  $options
     */
    public function __construct($op, array $options)
    {
        $this->op = $op;
        $this->_initOptions($options);
    }

    /**
     * @param array $options
     */
    private function _initOptions(array $options)
    {
        $this->options = $this->getOptionsDef();
        $this->options->setAll($options);
        $this->options->setArrayDefault($this->getDefaultOptions());
        $this->options->ensureRequired();
    }

    /**
     * @return array
     */
    protected function getDefaultOptions()
    {
        return [];
    }

    /**
     * @return CheckedOptionsArray
     */
    protected function getOptionsDef()
    {
        return new CheckedOptionsArray();
    }

    /**
     * Gets the type name of the criteria.
     *
     * @return string
     */
    public function getTermType()
    {
        return Util::getBaseClassname($this);
    }

    /**
     * Gets criteria operator (is, is not, etc).
     *
     * @return string
     */
    public function getTermOperator()
    {
        return $this->op;
    }

    /**
     * Get's an array of options.
     *
     * @return \Orb\Util\OptionsArray
     */
    public function getTermOptions()
    {
        return $this->options;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterQuery(ExecutorContextInterface $context = null)
    {
        return;
    }

    /**
     * Gets the matching trigger term for this filter term
     * (aka the term that checks a Ticket in PHP-land whereas these filters check in MySQL-lang).
     *
     * @return \Application\DeskPRO\Tickets\Triggers\Terms\AbstractTriggerTerm
     */
    public function getTriggerTerm()
    {
        return;
    }

    /**
     * @param string $field_name
     * @param array  $check_ids
     *
     * @throws \InvalidArgumentException
     *
     * @return FilterQuery
     */
    protected function getIdMatchQuery($field_name, array $check_ids)
    {
        $op = $this->getTermOperator();
        switch ($op) {
            case self::OP_IS:
            case self::OP_NOT:
                break;
            case self::OP_CONTAINS:
                $op = 'is';
                break;
            case self::OP_NOTCONTAINS:
                $op = 'not';
                break;
            default:
                throw new \InvalidArgumentException("Invalid operator: $op");
        }

        $check_ids = array_filter($check_ids, function ($x) {
            return (int) $x;
        });
        $check_ids = array_unique($check_ids);

        $has_null  = in_array(0, $check_ids, true);
        $check_ids = Arrays::removeFalsey($check_ids);

        $query = new FilterQuery();

        if ($has_null || $check_ids) {
            if ($has_null) {
                if ($op == 'is') {
                    $query->orWhere("$field_name IS NULL");
                } else {
                    $query->orWhere("$field_name IS NOT NULL");
                }
            }

            if ($check_ids) {
                $query->orWhereIn($field_name, $check_ids, $op == 'not');
            }
        } else {
            if ($op == 'is') {
                $query->andWhere('0');
            } else {
                $query->andWhere('1');
            }
        }

        return $query;
    }

    /**
     * @param string $field_name
     * @param int    $int
     *
     * @throws \InvalidArgumentException
     *
     * @return FilterQuery
     */
    protected function getIntMatchQuery($field_name, $int)
    {
        $query = new FilterQuery();
        $query->setParameter('int', (int) $int);

        switch ($this->getTermOperator()) {
            case self::OP_IS:
                $query->andWhere("$field_name = {param.int}");
                break;
            case self::OP_NOT:
                $query->andWhere("$field_name != {param.int}");
                break;
            case self::OP_LT:
                $query->andWhere("$field_name < {param.int}");
                break;
            case self::OP_LTE:
                $query->andWhere("$field_name <= {param.int}");
                break;
            case self::OP_GT:
                $query->andWhere("$field_name > {param.int}");
                break;
            case self::OP_GTE:
                $query->andWhere("$field_name >= {param.int}");
                break;
            default:
                throw new \InvalidArgumentException('Invalid operator: '.$this->getTermOperator());
        }

        return $query;
    }

    /**
     * @param string $field_name
     * @param int    $int1
     * @param int    $int2
     *
     * @throws \InvalidArgumentException
     *
     * @return FilterQuery
     */
    protected function getIntRangeMatch($field_name, $int1, $int2)
    {
        $int1 = (int) $int1;
        $int2 = (int) $int2;

        if ($int1 > $int2) {
            $x    = $int1;
            $int1 = $int2;
            $int2 = $x;
            unset($x);
        }

        $query = new FilterQuery();
        $query->setParameter('int1', $int1);
        $query->setParameter('int2', $int2);

        switch ($this->getTermOperator()) {
            case self::OP_BETWEEN:
                $query->andWhere("$field_name BETWEEN {param.int1} AND {param.int2}");
                break;
            case self::OP_NOTBETWEEN:
                $query->andWhere("$field_name NOT BETWEEN {param.int1} AND {param.int2}");
                break;
            default:
                throw new \InvalidArgumentException('Invalid operator: '.$this->getTermOperator());
        }

        return $query;
    }

    /**
     * @param string    $field_name
     * @param \DateTime $date
     *
     * @throws \InvalidArgumentException
     *
     * @return FilterQuery
     */
    protected function getDateMatchQuery($field_name, \DateTime $date)
    {
        $query = new FilterQuery();

        $date = clone $date;
        $date->setTimezone(new \DateTimeZone('UTC'));
        $query->setParameter('date', $date->format('Y-m-d H:i:s'));

        switch ($this->getTermOperator()) {
            case self::OP_IS:
                $query->andWhere("$field_name = {param.date}");
                break;
            case self::OP_NOT:
                $query->andWhere("$field_name != {param.date}");
                break;
            case self::OP_LT:
                $query->andWhere("$field_name < {param.date}");
                break;
            case self::OP_LTE:
                $query->andWhere("$field_name <= {param.date}");
                break;
            case self::OP_GT:
                $query->andWhere("$field_name > {param.date}");
                break;
            case self::OP_GTE:
                $query->andWhere("$field_name >= {param.date}");
                break;
            default:
                throw new \InvalidArgumentException('Invalid operator: '.$this->getTermOperator());
        }

        return $query;
    }

    /**
     * @param string    $field_name
     * @param \DateTime $date1
     * @param \DateTime $date2
     *
     * @throws \InvalidArgumentException
     *
     * @return FilterQuery
     */
    protected function getDateRangeMatch($field_name, \DateTime $date1, \DateTime $date2)
    {
        if ($date1 > $date2) {
            $x     = $date1;
            $date1 = $date2;
            $date2 = $x;
            unset($x);
        }

        $date1 = clone $date1;
        $date1->setTimezone(new \DateTimeZone('UTC'));
        $date2 = clone $date2;
        $date2->setTimezone(new \DateTimeZone('UTC'));

        $query = new FilterQuery();
        $query->setParameter('date1', $date1->format('Y-m-d H:i:s'));
        $query->setParameter('date2', $date2->format('Y-m-d H:i:s'));

        switch ($this->getTermOperator()) {
            case self::OP_BETWEEN:
                $query->andWhere("$field_name BETWEEN {param.date1} AND {param.date2}");
                break;
            case self::OP_NOTBETWEEN:
                $query->andWhere("$field_name NOT BETWEEN {param.date1} AND {param.date2}");
                break;
            default:
                throw new \InvalidArgumentException('Invalid operator: '.$this->getTermOperator());
        }

        return $query;
    }

    /**
     * @param string          $field_name
     * @param string|string[] $check_value
     *
     * @throws \InvalidArgumentException
     *
     * @return FilterQuery
     */
    protected function getStringMatchQuery($field_name, $check_value)
    {
        $query = new FilterQuery();

        if (!is_array($check_value)) {
            $check_value = [$check_value];
        }

        foreach ($check_value as $k => $str) {
            switch ($this->getTermOperator()) {
                case self::OP_IS:
                case self::OP_NOT:
                    $use_op = $this->getTermOptions() == self::OP_NOT ? '!=' : '=';
                    $query->orWhere("$field_name $use_op {param.str$k}");
                    $query->setParameter('str'.$k, $str);
                    break;
                case self::OP_NOT:
                case self::OP_CONTAINS:
                    $use_op = $this->getTermOptions() == self::OP_NOT ? 'NOT LIKE' : 'LIKE';
                    $query->orWhere("$field_name $use_op {param.str$k}");

                    $like_value = $str;
                    $like_value = str_replace('%', '%%', $like_value);
                    $like_value = str_replace('_', '__', $like_value);
                    $like_value = '%'.$like_value.'%';
                    $query->setParameter('str.$k', $like_value);
                    break;
                default:
                    throw new \InvalidArgumentException("Invalid operator: {$this->getTermOperator()}");
            }
        }

        return $query;
    }
}
