<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Filters\Terms;

use Application\DeskPRO\Tickets\ExecutorContextInterface;

class FilterTermComposite implements FilterTermInterface
{
    const OP_AND = 'AND';
    const OP_OR  = 'OR';

    /**
     * @var FilterTermInterface[]
     */
    private $terms = [];

    /**
     * @var string
     */
    private $op = 'AND';

    /**
     * @param FilterTermInterface[] $terms
     * @param string                $op
     */
    public function __construct(array $terms = [], $op = self::OP_AND)
    {
        $this->setAll($terms);
        $this->setOperator($op);
    }

    /**
     * Change the logic operator between AND/OR ('all must match' versus 'any match').
     *
     * @param string $op
     */
    public function setOperator($op)
    {
        $this->op = (strtoupper($op) == self::OP_AND ? self::OP_AND : self::OP_OR);
    }

    /**
     * @return string
     */
    public function getOperator()
    {
        return $this->op;
    }

    /**
     * @param FilterTermInterface $term
     */
    public function add(FilterTermInterface $term)
    {
        $this->terms[] = $term;
    }

    /**
     * @param FilterTermInterface[] $terms
     */
    public function setAll(array $terms)
    {
        $this->terms = [];
        foreach ($terms as $t) {
            $this->add($t);
        }
    }

    /**
     * @return FilterTermInterface[]
     */
    public function getAll()
    {
        return $this->terms;
    }

    /**
     * @param ExecutorContextInterface $context
     *
     * @return FilterQuery|null
     */
    public function getFilterQuery(ExecutorContextInterface $context = null)
    {
        $filter_query = new FilterQuery();

        foreach ($this->terms as $t) {
            $query = $t->getFilterQuery();
            if (!$query) {
                continue;
            }

            $parts = $query->getQueryParts();
            foreach ($parts['joins'] as $join) {
                $filter_query->addJoin(
                    $join['fromAlias'],
                    $join['join'],
                    $join['alias'] ?: $join['input_alias'],
                    $join['condition']
                );
            }
            foreach ($parts['params'] as $param) {
                $filter_query->setParameter(
                    $param['name'],
                    $param['value'],
                    $param['type'],
                    false
                );
            }

            if ($this->op == self::OP_AND) {
                $filter_query->andWhere($parts['where']);
            } else {
                $filter_query->orWhere($parts['where']);
            }
        }

        return $filter_query;
    }
}
