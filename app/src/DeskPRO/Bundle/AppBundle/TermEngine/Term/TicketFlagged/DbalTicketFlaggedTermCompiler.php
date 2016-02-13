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
namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketFlagged;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQueryPart;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\AbstractDbalTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Expression\TermEngineExpression;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

class DbalTicketFlaggedTermCompiler extends AbstractDbalTermCompiler
{
    public function doCompile(TermInterface $term)
    {
        $query_part = new DbalQueryPart();

        $op    = $term->getOp();
        $isser = $this->isOp($op, TermInterface::OP_NOT) ? '!=' : '=';

        $query_part->addJoin(
            'tickets_flagged',
            'ticket.id = tickets_flagged.ticket_id AND tickets_flagged.person_id = :agent_id'
        );

        $query_part->setParameter('agent_id', new TermEngineExpression('agent.getId()'));

        $color = $term->getOption('flag');
        if ($color == '') {
            if (TermInterface::OP_NOT === $op) {
                $query_part->setWhereString('tickets_flagged.person_id = :agent_id tickets_flagged.color IS NULL');
            } else {
                $query_part->setWhereString('tickets_flagged.person_id = :agent_id tickets_flagged.color IS NOT NULL');
            }
        } else {
            $operator = TermInterface::OP_NOT === $op ? '!=' : '=';
            $query_part->setWhereString(sprintf('tickets_flagged.color %s :color', $operator));
            $query_part->setParameter('color', $color);
        }

        $this->logQueryPart($query_part);

        return $query_part;
    }
}
