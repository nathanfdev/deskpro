<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\PersonEmail;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQueryPart;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\AbstractDbalTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

/**
 * Class DbalPersonEmailTermCompiler.
 */
class DbalPersonEmailTermCompiler extends AbstractDbalTermCompiler
{
    /**
     * {@inheritdoc}
     */
    public function doCompile(TermInterface $term)
    {
        $subQuery  = 'SELECT pe.person_id FROM people_emails pe WHERE pe.email IN(:email)';
        $isNot     = $this->isOp($term->getOp(), TermInterface::OP_NOT);
        $notPrefix = $isNot ? 'NOT' : '';
        $composite = $isNot ? 'AND' : 'OR';

        $qp = new DbalQueryPart();
        $qp
            ->setParameter('email', $term->getOption('email'))
            ->setWhereString("
                ticket.person_id $notPrefix IN($subQuery) $composite $notPrefix EXISTS(
                  SELECT * FROM
                    tickets_participants tp
                        JOIN
                    people p ON tp.person_id = p.id
                  WHERE
                    p.is_agent = 0 AND p.id $notPrefix IN($subQuery) AND ticket.id = tp.ticket_id
                )
            ")
        ;

        $this->logQueryPart($qp);

        return $qp;
    }
}
