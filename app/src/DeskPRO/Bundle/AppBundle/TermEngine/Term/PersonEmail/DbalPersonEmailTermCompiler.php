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
namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\PersonEmail;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQueryPart;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\AbstractDbalTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

class DbalPersonEmailTermCompiler extends AbstractDbalTermCompiler
{
    public function doCompile(TermInterface $term)
    {
        $query_part = new DbalQueryPart();

        $op = $term->getOp();

        switch ($op) {
            case TermInterface::OP_IS:
                $query_part->setParameter('email', $term->getOption('email'));
                $query_part->addJoin('people_emails', 'ticket.person_id = people_emails.person_id');
                $query_part->setWhereString('people_emails.email = :email');

                $this->logQueryPart($query_part);

                return $query_part;
            case TermInterface::OP_NOT:
                $query_part->setParameter('email', $term->getOption('email'));
                $query_part->addUniqueJoin(
                    'email_join',
                    'people_emails',
                    'ticket.person_id = {email_join}.person_id AND {email_join}.email = :email'
                );
                $query_part->setWhereString('{email_join}.id IS NULL');

                $this->logQueryPart($query_part);

                return $query_part;
        }

        return '';
    }
}
