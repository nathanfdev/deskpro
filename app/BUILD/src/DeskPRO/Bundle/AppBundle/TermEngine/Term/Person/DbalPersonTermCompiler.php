<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\Person;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQueryPart;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\AbstractDbalTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

/**
 * Class DbalPersonTermCompiler.
 */
class DbalPersonTermCompiler extends AbstractDbalTermCompiler
{
    /**
     * {@inheritdoc}
     */
    public function doCompile(TermInterface $term)
    {
        $isNot = $this->isOp($term->getOp(), TermInterface::OP_NOT);

        $notPrefix = $isNot ? 'NOT' : '';
        $composite = $isNot ? 'AND' : 'OR';

        $qp = new DbalQueryPart();
        $qp
            ->setParameter('ids', $term->getOption('person_ids'))
            ->setWhereString(
                "
                ticket.person_id $notPrefix IN(:ids) 
                $composite ticket.organization_id $notPrefix IN (
                  SELECT p.organization_id from people p
                  INNER JOIN tickets t ON t.organization_id = p.organization_id
                  WHERE p.organization_manager = 1 AND p.id IN(:ids)
                )
                $composite $notPrefix EXISTS(
                  SELECT * FROM
                    tickets_participants tp
                        JOIN
                    people p ON tp.person_id = p.id
                  WHERE
                    p.is_agent = 0 AND p.id $notPrefix IN(:ids) AND ticket.id = tp.ticket_id
                )
            "
            );

        $this->logQueryPart($qp);

        return $qp;
    }
}
