<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketNumUserReplies;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\AbstractDbalTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

/**
 * Class DbalTicketNumUserRepliesTermCompiler.
 */
class DbalTicketNumUserRepliesTermCompiler extends AbstractDbalTermCompiler
{
    /**
     * {@inheritdoc}
     */
    public function doCompile(TermInterface $term)
    {
        $qp = $this->getNumericHelper()->buildQueryPart(
            'ticket.count_user_replies',
            $term->getOp(),
            $term->getOption('num')
        );

        $this->logQueryPart($qp);

        return $qp;
    }
}
