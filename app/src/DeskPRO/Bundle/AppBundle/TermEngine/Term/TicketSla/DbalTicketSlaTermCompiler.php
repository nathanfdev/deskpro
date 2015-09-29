<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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
namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketSla;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\AbstractDbalTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

class DbalTicketSlaTermCompiler extends AbstractDbalTermCompiler
{
    public function doCompile(TermInterface $term)
    {
        $slas     = $term->hasOption('sla') ? $term->getOption('sla') : false;
        $statuses = $term->hasOption('status') ? $term->getOption('status') : false;

        $fields = array();

        if ($slas) {
            $fields['ticket_slas.sla_id'] = $slas;
        }
        if ($statuses) {
            $fields['ticket_slas.sla_status'] = $statuses;
        }

        $query_part = $this->getJoinedHelper()->buildQueryPart(
            $fields,
            'ticket_slas',
            'tickets.id = ticket_slas.ticket_id',
            $term->getOp()
        );

        $this->logQueryPart($query_part);

        return $query_part;
    }
}
