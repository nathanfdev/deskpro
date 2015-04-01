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
use DeskPRO\Bundle\AppBundle\TermEngine\Expression\TermEngineExpression;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\AgentTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

class DbalAgentTermCompiler extends AbstractDbalTermCompiler
{
    public function doCompile(TermInterface $term, DbalCompiledQueryWriter $query_writer)
    {
        $op = $term->getOp();

        $agent_ids = array();
        $me_expression = null;
        $unassigned = false;
        $and_or = $this->isOp($op, TermInterface::OP_IS) ? 'OR' : 'AND';

        foreach ($term->getOption('agent_ids') as $id) {
            if ($id === AgentTerm::ID_ME) {
                $agent_ids[] = new TermEngineExpression('agent.getId()');
            } elseif ($id === AgentTerm::ID_UNASSIGNED) {
                $unassigned = true;
            } else {
                $agent_ids[] = $id;
            }
        }

        $where = '';
        if (count($agent_ids) > 0) {
            $ids_isser = $this->isOp($op, TermInterface::OP_NOT) ? 'NOT IN' : 'IN';
            $ids_param = $query_writer->addParameter('agent_ids', $agent_ids);

            $where .= sprintf('ticket.agent_id %s (:%s)', $ids_isser, $ids_param);
        }

        if ($unassigned) {
            $unassigned_isser = $this->isOp($op, TermInterface::OP_NOT) ? 'IS NOT NULL' : 'IS NULL';
            $where .= sprintf(
                '%sticket.agent_id %s',
                strlen($where) > 0 ? ' ' . $and_or . ' ' : '',
                $unassigned_isser
            );
        }

        return $where;
    }
}