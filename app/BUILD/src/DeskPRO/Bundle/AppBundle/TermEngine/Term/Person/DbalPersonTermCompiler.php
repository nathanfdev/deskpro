<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\Person;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQueryPart;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\AbstractDbalTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

/**
 * Class DbalPersonTermCompiler.
 */
class DbalPersonTermCompiler extends AbstractDbalTermCompiler
{
    /**
     * {@inheritdoc}
     */
    public function doCompile(TermInterface $term)
    {
        $isNot = $this->isOp($term->getOp(), TermInterface::OP_NOT);

        $notPrefix = $isNot ? 'NOT' : '';
        $composite = $isNot ? 'AND' : 'OR';

        $qp = new DbalQueryPart();
        $qp
            ->setParameter(
                'ids',
                array_map(
                    function ($id) {
                        return (int) $id;
                    },
                    $term->getOption('person_ids')
                )
            )
            ->setWhereString(
                "
                ticket.person_id $notPrefix IN(:ids) 
                $composite ticket.organization_id $notPrefix IN (
                  SELECT p.organization_id from people p
                  INNER JOIN tickets t ON t.organization_id = p.organization_id
                  WHERE p.organization_manager = 1 AND p.id IN(:ids)
                )
                $composite $notPrefix EXISTS(
                  SELECT * FROM
                    tickets_participants tp
                        JOIN
                    people p ON tp.person_id = p.id
                  WHERE
                    p.is_agent = 0 AND p.id $notPrefix IN(:ids) AND ticket.id = tp.ticket_id
                )
            "
            );

        $this->logQueryPart($qp);

        return $qp;
    }
}
