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
namespace DeskPRO\Bundle\AppBundle\DataService\AgentTeams;

use Application\DeskPRO\Entity\AgentTeam;
use DeskPRO\Bundle\AppBundle\CountBadge\Count;
use DeskPRO\Bundle\AppBundle\DataService\AbstractDataService;

/**
 * Class AgentTeamsDataService.
 */
class AgentTeamsDataService extends AbstractDataService
{
    /**
     * @return Count
     */
    public function countAgentsInTeams()
    {
        $qb = $this->em->createQueryBuilder();

        $qb->select('count(m) as value, at.id as group_name')
            ->from('DeskPRO:AgentTeam', 'at')
            ->join('at.members', 'm')
            ->andWhere('m.is_deleted = false')
            ->groupBy('group_name')
        ;

        $result = $qb->getQuery()->getArrayResult();

        $count = Count::fromGroupedBy('agent_team');
        foreach ($result as $group) {
            $count->add($group['value']);
            $count->addNested($group['value'], $group['group_name'], 'agent_team');
        }

        return $count;
    }

    public function getAgentsFromTeam($teamId)
    {
        $repo = $this->em->getRepository('DeskPRO:AgentTeam');
        /** @var AgentTeam $team */
        $team = $repo->find($teamId);

        return $team->getPersonList();
    }
}
