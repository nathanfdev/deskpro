<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketStatus;

use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQueryPart;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\AbstractDbalTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

class DbalTicketStatusTermCompiler extends AbstractDbalTermCompiler
{
    public function doCompile(TermInterface $term)
    {
        $query_part = new DbalQueryPart();

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
            $query_part->setParameter('status', $non_hidden);

            $where = sprintf(
                'ticket.status %s (:status)',
                $isser
            );

            $query_part->setWhereString(
                $where
            );

            $this->logQueryPart($query_part);

            return $query_part;
        }

        // both
        if (count($non_hidden) && count($hidden)) {
            $query_part->setParameter('status', $non_hidden);
            $query_part->setParameter('status_hidden', Ticket::STATUS_HIDDEN);
            $query_part->setParameter('hidden_status', $hidden);

            $query_part->setWhereString(
                sprintf(
                    'ticket.status %s (:status) OR (ticket.status = :status_hidden AND ticket.hidden_status %s (:hidden_status))',
                    $isser,
                    $isser
                )
            );

            $this->logQueryPart($query_part);

            return $query_part;
        }

        // only hidden
        if (!count($non_hidden) && count($hidden)) {
            $query_part->setParameter('status_hidden', Ticket::STATUS_HIDDEN);
            $query_part->setParameter('hidden_status', $hidden);

            if ($this->isOp($op, TermInterface::OP_NOT)) {
                $query_part->setWhereString(
                    'ticket.status != :status_hidden OR (ticket.status = :status_hidden AND ticket.hidden_status NOT IN (:hidden_status))'
                );

                $this->logQueryPart($query_part);

                return $query_part;
            } else {
                $query_part->setWhereString(
                    'ticket.status = :status_hidden AND ticket.hidden_status IN (:hidden_status)'
                );

                $this->logQueryPart($query_part);

                return $query_part;
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
