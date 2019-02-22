<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Filters\Terms;

use Application\DeskPRO\Tickets\ExecutorContextInterface;
use DeskPRO\Bundle\AppBundle\Entity\TicketStatus;
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
        $query  = new FilterQuery();
        $isHold = $this->getTermOptions()->get('is_hold') ? 1 : 0;

        switch ($this->getTermOperator()) {
            case self::OP_IS:
                $op = $isHold ? '=' : '!=';
                $query->andWhere(sprintf('tickets.is_hold %s "%s"', $op, TicketStatus::STATUS_TYPE_PENDING));
                break;
            case self::OP_NOT:
                $op = $isHold ? '!=' : '=';
                $query->andWhere(sprintf('tickets.is_hold %s "%s"', $op, TicketStatus::STATUS_TYPE_PENDING));
                break;
            default:
                throw new \InvalidArgumentException("Invalid operator: {$this->getTermOperator()}");
        }

        return $query;
    }
}
