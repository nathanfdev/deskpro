<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketStatus;

use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQueryPart;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\AbstractDbalTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

/**
 * Class DbalTicketStatusTermCompiler.
 */
class DbalTicketStatusTermCompiler extends AbstractDbalTermCompiler
{
    /**
     * {@inheritdoc}
     */
    public function doCompile(TermInterface $term)
    {
        $qp    = new DbalQueryPart();
        $op    = $term->getOp();
        $isser = $this->isOp($op, TermInterface::OP_NOT) ? 'NOT IN' : 'IN';

        $statuses = $term->getOption('status');

        $non_hidden = [];
        $hidden     = [];
        foreach ($statuses as $status) {
            $status = str_replace('hidden.', '', $status); // internally we use the shorter hidden status
            if ($this->isHiddenStatus($status)) {
                $hidden[] = $status;
            } else {
                $non_hidden[] = $status;
            }
        }

        $this->logDebug('Processed options', [
            'non-hidden' => $non_hidden,
            'hidden'     => $hidden,
        ]);

        // only non hidden
        if (count($non_hidden) && !count($hidden)) {
            $qp->setParameter('status', $non_hidden);

            $where = sprintf(
                'ticket.status %s (:status)',
                $isser
            );

            $qp->setWhereString(
                $where
            );

            $this->logQueryPart($qp);

            return $qp;
        }

        // both
        if (count($non_hidden) && count($hidden)) {
            $qp->setParameter('status', $non_hidden);
            $qp->setParameter('status_hidden', Ticket::STATUS_HIDDEN);
            $qp->setParameter('hidden_status', $hidden);

            $qp->setWhereString(
                sprintf(
                    'ticket.status %s (:status) OR (ticket.status = :status_hidden AND ticket.hidden_status %s (:hidden_status))',
                    $isser,
                    $isser
                )
            );

            $this->logQueryPart($qp);

            return $qp;
        }

        // only hidden
        if (!count($non_hidden) && count($hidden)) {
            $qp->setParameter('status_hidden', Ticket::STATUS_HIDDEN);
            $qp->setParameter('hidden_status', $hidden);

            if ($this->isOp($op, TermInterface::OP_NOT)) {
                $qp->setWhereString(
                    'ticket.status != :status_hidden OR (ticket.status = :status_hidden AND ticket.hidden_status NOT IN (:hidden_status))'
                );

                $this->logQueryPart($qp);

                return $qp;
            } else {
                $qp->setWhereString(
                    'ticket.status = :status_hidden AND ticket.hidden_status IN (:hidden_status)'
                );

                $this->logQueryPart($qp);

                return $qp;
            }
        }
    }

    /**
     * @param $status
     *
     * @return bool
     */
    protected function isHiddenStatus($status)
    {
        return in_array(
            $status,
            [
                Ticket::HIDDEN_STATUS_SPAM,
                Ticket::HIDDEN_STATUS_DELETED,
            ]
        );
    }
}
