<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Triggers\Terms;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Orb\Util\Util;

class TriggerTermComposite implements TriggerTermInterface, \Countable
{
    const OP_AND = 'AND';
    const OP_OR  = 'OR';

    /**
     * @var TriggerTermInterface[]
     */
    private $terms = [];

    /**
     * @var string
     */
    private $op = 'AND';

    /**
     * @param TriggerTermInterface[] $terms
     * @param string                 $op
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
     * @param TriggerTermInterface $term
     */
    public function add(TriggerTermInterface $term)
    {
        $this->terms[] = $term;
    }

    /**
     * @param TriggerTermInterface[] $terms
     */
    public function setAll(array $terms)
    {
        $this->terms = [];
        foreach ($terms as $t) {
            $this->add($t);
        }
    }

    /**
     * @return TriggerTermInterface[]
     */
    public function getAll()
    {
        return $this->terms;
    }

    /**
     * {@inheritdoc}
     */
    public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $context)
    {
        $logger = $context->getLogger();

        if (!$this->terms) {
            if ($logger) {
                $logger->debug('[Term:Composite] Empty term set => true');
            }

            return true;
        }

        if ($this->op == self::OP_AND) {
            if ($logger) {
                $logger->debug('[Term:Composite] AND operator');
            }
            foreach ($this->terms as $k => $t) {
                if (!$t->isTriggerMatch($ticket, $context)) {
                    if ($logger) {
                        $logger->debug(sprintf('[Term:%s:%d] => false', Util::getBaseClassname($t), $k));
                    }

                    return false;
                } else {
                    if ($logger) {
                        $logger->debug(sprintf('[Term:%s:%d] => true', Util::getBaseClassname($t), $k));
                    }
                }
            }

            return true;
        } else {
            if ($logger) {
                $logger->debug('[Term:Composite] OR operator');
            }

            foreach ($this->terms as $k => $t) {
                if ($t->isTriggerMatch($ticket, $context)) {
                    if ($logger) {
                        $logger->debug(sprintf('[Term:%s:%d] => true', Util::getBaseClassname($t), $k));
                    }

                    return true;
                } else {
                    if ($logger) {
                        $logger->debug(sprintf('[Term:%s:%d] => false', Util::getBaseClassname($t), $k));
                    }
                }
            }

            return false;
        }
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->terms);
    }
}
