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

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\PersonEmail;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQueryPart;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\AbstractDbalTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

/**
 * Class DbalPersonEmailTermCompiler.
 */
class DbalPersonEmailTermCompiler extends AbstractDbalTermCompiler
{
    /**
     * {@inheritdoc}
     */
    public function doCompile(TermInterface $term)
    {
        $subQuery  = 'SELECT pe.person_id FROM people_emails pe WHERE pe.email IN(:email)';
        $isNot     = $this->isOp($term->getOp(), TermInterface::OP_NOT);
        $notPrefix = $isNot ? 'NOT' : '';
        $composite = $isNot ? 'AND' : 'OR';

        $qp = new DbalQueryPart();
        $qp
            ->setParameter('email', $term->getOption('email'))
            ->setWhereString("
                ticket.person_id $notPrefix IN($subQuery) $composite $notPrefix EXISTS(
                  SELECT * FROM
                    tickets_participants tp
                        JOIN
                    people p ON tp.person_id = p.id
                  WHERE
                    p.is_agent = 0 AND p.id $notPrefix IN($subQuery) AND ticket.id = tp.ticket_id
                )
            ")
        ;

        $this->logQueryPart($qp);

        return $qp;
    }
}
