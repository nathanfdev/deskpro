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
namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketCustomData;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQueryPart;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\AbstractDbalTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

class DbalTicketCustomDataTermCompiler extends AbstractDbalTermCompiler
{
    public function doCompile(TermInterface $term)
    {
        $query_part = new DbalQueryPart();

        $op    = $term->getOp();
        $isser = $this->isOp($op, TermInterface::OP_NOT) ? 'NOT IN' : 'IN';

        $query_part->setParameter('field_id', $term->getOption('field_id'));

        $query_part->addUniqueJoin(
            'custom_data',
            'custom_data_ticket',
            '{custom_data}.ticket_id = ticket.id AND {custom_data}.field_id = :field_id'
        );

        if ($input = $term->getOption('input')) {
            $query_part->setParameter('input', $input);
            $query_part->setWhereString('{custom_data}.input = :input');

            return $query_part;
        }

        $query_part->setParameter('values', $term->getOption('values'));
        $query_part->setWhereString('{custom_data}.value IN (:values)');

        $this->logQueryPart($query_part);

        return $query_part;
    }
}
