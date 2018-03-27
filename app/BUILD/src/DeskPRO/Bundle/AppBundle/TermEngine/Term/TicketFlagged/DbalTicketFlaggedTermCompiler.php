<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketFlagged;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQueryPart;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\AbstractDbalTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Expression\TermEngineExpression;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

/**
 * Class DbalTicketFlaggedTermCompiler.
 */
class DbalTicketFlaggedTermCompiler extends AbstractDbalTermCompiler
{
    /**
     * {@inheritdoc}
     */
    public function doCompile(TermInterface $term)
    {
        $qp = new DbalQueryPart();
        $qp
            ->addJoin(
                'tickets_flagged',
                'ticket.id = tickets_flagged.ticket_id AND tickets_flagged.person_id = :agent_id'
            )
            ->setParameter('agent_id', new TermEngineExpression('agent.getId()'))
        ;

        $isNot = $this->isOp($term->getOp(), TermInterface::OP_NOT);
        $value = $term->getOption('flag');

        if (!$value) {
            if ($isNot) {
                $qp->setWhereString('tickets_flagged.person_id = :agent_id AND tickets_flagged.color IS NULL');
            } else {
                $qp->setWhereString('tickets_flagged.person_id = :agent_id AND tickets_flagged.color IS NOT NULL');
            }
        } else {
            $qp->setParameter('color', $value);

            if ($isNot) {
                $qp->setWhereString('tickets_flagged.color NOT IN(:color) OR tickets_flagged.color IS NULL');
            } else {
                $qp->setWhereString('tickets_flagged.color IN(:color)');
            }
        }

        $this->logQueryPart($qp);

        return $qp;
    }
}
