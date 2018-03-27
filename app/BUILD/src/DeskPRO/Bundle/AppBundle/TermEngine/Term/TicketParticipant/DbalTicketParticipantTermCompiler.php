<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketParticipant;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQueryPart;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\AbstractDbalTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Expression\TermEngineExpression;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

/**
 * Class DbalTicketParticipantTermCompiler.
 */
class DbalTicketParticipantTermCompiler extends AbstractDbalTermCompiler
{
    /**
     * {@inheritdoc}
     */
    public function doCompile(TermInterface $term)
    {
        $op    = $term->getOp();
        $isser = $this->isOp($op, TermInterface::OP_NOT) ? 'NOT IN' : 'IN';

        $qp = new DbalQueryPart();
        $qp->addUniqueJoin(
            'participants',
            'tickets_participants',
            '{participants}.ticket_id = ticket.id'
        );

        $person_ids = [];
        foreach ($term->getOption('person_ids') as $id) {
            if ($id === TicketParticipantTerm::ID_ME) {
                $person_ids[] = new TermEngineExpression('agent.getId()');
            } else {
                $person_ids[] = $id;
            }
        }

        $qp->setParameter('person_ids', $person_ids);
        $qp->setWhereString(sprintf('{participants}.person_id %s (:person_ids)', $isser));

        $this->logQueryPart($qp);

        return $qp;
    }
}
