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

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\AbstractDbalTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Compiler\DbalCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\AgentTeamTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Expression\TermEngineExpression;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use Doctrine\DBAL\Query\QueryBuilder;

class DbalAgentTeamTermCompiler extends AbstractDbalTermCompiler
{
    public function doCompile(TermInterface $term, DbalCompiler $compiler)
    {
        $op = $term->getOp();

        $agent_team_ids = $term->getOption('agent_team_ids');

        $real_agent_team_ids = array();
        $me_expression = null;
        $unassigned = false;
        $and_or = $this->isOp($op, TermInterface::OP_IS) ? 'OR' : 'AND';

        foreach ($agent_team_ids as $agent_team_id) {
            if ($agent_team_id === AgentTeamTerm::TEAM_ID_ME) {
                $me_expression = new TermEngineExpression('agent.getTeamIds()');
            } elseif ($agent_team_id === AgentTeamTerm::TEAM_ID_UNASSIGNED) {
                $unassigned = true;
            } else {
                $real_agent_team_ids[] = $agent_team_id;
            }
        }


        $where = '';
        if (count($real_agent_team_ids)) {
            $in_isser = $this->isOp($op, TermInterface::OP_NOT) ? 'NOT IN' : 'IN';
            $ids_param = $compiler->addParameter('agent_team_ids', $real_agent_team_ids);

            $where = sprintf('ticket.agent_team_id %s (:%s)', $in_isser, $ids_param);
        }

        if ($me_expression) {
            $me_isser = $this->isOp($op, TermInterface::OP_NOT) ? 'NOT IN' : 'IN';
            $me_param = $compiler->addParameter('me', $me_expression);

            $where .= sprintf(
                '%sticket.agent_team_id %s (:%s)',
                strlen($where) > 0 ? ' ' . $and_or . ' ' : '',
                $me_isser,
                $me_param
            );
        }

        if ($unassigned) {
            $unassigned_isser = $this->isOp($op, TermInterface::OP_NOT) ? 'IS NOT NULL' : 'IS NULL';

            $where .= sprintf(
                '%sticket.agent_team_id %s',
                strlen($where) > 0 ? ' ' . $and_or . ' ' : '',
                $unassigned_isser
            );
        }

        return $where;
    }
}