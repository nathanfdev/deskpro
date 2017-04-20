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

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\AgentTeam;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\AbstractDbalTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Expression\TermEngineExpression;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

/**
 * Class DbalAgentTeamTermCompiler.
 */
class DbalAgentTeamTermCompiler extends AbstractDbalTermCompiler
{
    /**
     * {@inheritdoc}
     */
    public function doCompile(TermInterface $term)
    {
        $ids = [];

        foreach ($term->getOption('agent_team_ids') as $agent_team_id) {
            if ($agent_team_id === AgentTeamTerm::TEAM_ID_ME) {
                $ids[] = new TermEngineExpression('agent.getTeamIds()');
            } else {
                $ids[] = $agent_team_id;
            }
        }

        $query_part = $this->getEntityHelper()->buildQueryPart(
            'ticket.agent_team_id',
            $term->getOp(),
            $ids
        );

        $this->logQueryPart($query_part);

        return $query_part;
    }
}
