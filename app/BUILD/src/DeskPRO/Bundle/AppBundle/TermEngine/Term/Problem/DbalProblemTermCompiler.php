<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\Problem;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQueryPart;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\AbstractDbalTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

/**
 * Class DbalProblemTermCompiler.
 */
class DbalProblemTermCompiler extends AbstractDbalTermCompiler
{
    /**
     * {@inheritdoc}
     */
    public function doCompile(TermInterface $term)
    {
        $value = $term->getOption('problem');

        $qp = new DbalQueryPart();
        $qp
            ->addUniqueJoin(
                'problem2tickets',
                'problem2tickets',
                '{problem2tickets}.ticket_id = ticket.id'
            )
            ->setParameter('problem', $value)
        ;

        if ($this->isOp($term->getOp(), TermInterface::OP_NOT)) {
            if ($value) {
                $qp->setWhereString('{problem2tickets}.problem_id NOT IN(:problem) OR {problem2tickets}.problem_id IS NULL');
            } else {
                $qp->setWhereString('{problem2tickets}.problem_id IS NOT NULL');
            }
        } else {
            if ($value) {
                $qp->setWhereString('{problem2tickets}.problem_id IN(:problem)');
            } else {
                $qp->setWhereString('{problem2tickets}.id IS NULL');
            }
        }

        $this->logQueryPart($qp);

        return $qp;
    }
}
