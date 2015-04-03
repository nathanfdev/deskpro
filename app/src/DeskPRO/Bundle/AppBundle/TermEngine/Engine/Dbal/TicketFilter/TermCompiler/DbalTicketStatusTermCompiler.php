<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at https://www.deskpro.com/eula/                            |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TicketFilter\TermCompiler;

use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQueryBuilder;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\AbstractDbalTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

class DbalTicketStatusTermCompiler extends AbstractDbalTermCompiler
{
    public function doCompile(TermInterface $term, DbalQueryBuilder $query_writer)
    {
        $op = $term->getOp();
        $isser = $this->isOp($op, TermInterface::OP_NOT) ? 'NOT IN' : 'IN';

        $statuses = $term->getOption('status');

        $non_hidden = array();
        $hidden = array();
        foreach ($statuses as $status) {
            if ($this->isHiddenStatus($status)) {
                $hidden[] = $status;
            } else {
                $non_hidden[] = $status;
            }
        }

        // only non hidden
        if (count($non_hidden) && !count($hidden)) {
            $status_non_hidden = $query_writer->addParameter('status', $non_hidden);

            return sprintf(
                'ticket.status %s (:%s)',
                $isser,
                $status_non_hidden
            );
        }

        // both
        if (count($non_hidden) && count($hidden)) {
            $status_non_hidden = $query_writer->addParameter('status', $non_hidden);
            $hidden_param = $query_writer->addParameter('status', Ticket::STATUS_HIDDEN);
            $status_hidden = $query_writer->addParameter('status', $hidden);

            return sprintf(
                'ticket.status %s (:%s) OR (ticket.status = :%s AND ticket.hidden_status %s (:%s))',
                $isser,
                $status_non_hidden,
                $hidden_param,
                $isser,
                $status_hidden
            );
        }

        // only hidden
        if (!count($non_hidden) && count($hidden)) {
            $hidden_param = $query_writer->addParameter('status', Ticket::STATUS_HIDDEN);
            $status_hidden = $query_writer->addParameter('status', $hidden);

            if ($this->isOp($op, TermInterface::OP_NOT)) {

                return sprintf(
                    'ticket.status != :%s OR (ticket.status = :%s AND ticket.hidden_status NOT IN (:%s))',
                    $hidden_param,
                    $hidden_param,
                    $status_hidden
                );

            } else {

                return sprintf(
                    'ticket.status = :%s AND ticket.hidden_status IN (:%s)',
                    $hidden_param,
                    $status_hidden
                );

            }
        }
    }

    /**
     * @param $status
     * @return bool
     */
    protected function isHiddenStatus($status)
    {
        return in_array(
            $status,
            array(
                Ticket::HIDDEN_STATUS_VALIDATING,
                Ticket::HIDDEN_STATUS_SPAM,
                Ticket::HIDDEN_STATUS_DELETED,
                Ticket::HIDDEN_STATUS_TEMP
            )
        );
    }
}