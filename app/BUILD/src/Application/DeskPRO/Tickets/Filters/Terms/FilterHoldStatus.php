<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Filters\Terms;

use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Orb\Util\CheckedOptionsArray;

/**
 * Filters based on hold status.
 */
class FilterHoldStatus extends AbstractFilterTerm
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('is_hold');

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterQuery(ExecutorContextInterface $context = null)
    {
        $query = new FilterQuery();
        $query->setParameter('hold', $this->getTermOptions()->get('is_hold') ? 1 : 0);

        switch ($this->getTermOperator()) {
            case self::OP_IS:
                $query->andWhere('tickets.is_hold = {param.hold}');
            case self::OP_NOT:
                $query->andWhere('tickets.is_hold != {param.hold}');
                break;
            default:
                throw new \InvalidArgumentException("Invalid operator: {$this->getTermOperator()}");
        }

        return $query;
    }
}
