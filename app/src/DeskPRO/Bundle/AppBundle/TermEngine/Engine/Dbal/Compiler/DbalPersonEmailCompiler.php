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

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Compiler;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\DbalCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use Doctrine\DBAL\Query\QueryBuilder;

class DbalPersonEmailCompiler extends AbstractDbalCompiler
{
    public function doCompile(TermInterface $term, DbalCompiler $compiler)
    {
        $op = $term->getOp();

        switch ($op) {
            case TermInterface::OP_IS:
                $compiler->addJoin(
                    'people_emails',
                    '{from}.person_id = people_emails.person_id'
                );
                $param = $compiler->addParameter('email', $term->getOption('email'));

                return sprintf(
                    'people_emails.email = :%s',
                    $param
                );
            case TermInterface::OP_NOT:
                $alias = $compiler->addUniqueJoin(
                    'people_emails',
                    '{from}.person_id = people_emails.person_id'
                );
                $param = $compiler->addParameter('email', $term->getOption('email'));

                return sprintf(
                    'NOT EXISTS ( SELECT 1 FROM people_emails pe WHERE pe.email = :%s AND pe.person_id = {from}.person_id )',
                    $param
                );
        }

        return '';
    }
}
