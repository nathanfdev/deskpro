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

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalCompiledQueryWriter;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\AbstractDbalTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

class DbalDepartmentTermCompiler extends AbstractDbalTermCompiler
{
    public function doCompile(TermInterface $term, DbalCompiledQueryWriter $query_writer)
    {
        return $query_writer->getHelper()->writeEntityCheck(
            'ticket.department_ids',
            $term->getOp(),
            $term->getOption('department_ids')
        );

        $op = $term->getOp();

        $isser = $this->isOp($op, TermInterface::OP_NOT) ? 'NOT IN' : 'IN';

        $param_name = $query_writer->addParameter('department_ids', $term->getOption('department_ids'));

        return sprintf('ticket.department_id %s (:%s)', $isser, $param_name);
    }
}
