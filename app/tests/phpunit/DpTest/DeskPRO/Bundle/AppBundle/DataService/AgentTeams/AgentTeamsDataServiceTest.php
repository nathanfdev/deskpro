<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace DpTest\Bundle\AppBundle\DataService\AgentTeams;

use Prophecy\Argument;
use DpTest\DeskProTestCase;
use Application\DeskPRO\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\AbstractQuery as Query;
use DeskPRO\Bundle\AppBundle\DataService\AgentTeams\AgentTeamsDataService;
use DeskPRO\Bundle\AppBundle\CountBadge\Count;
use DeskPRO\Bundle\AppBundle\CountBadge\CountsGroup;

/**
 * Class AgentTeamsDataServiceTest
 */
class AgentTeamsDataServiceTest extends DeskProTestCase
{
    /**
     * @test
     */
    function it_should_be_instantiable()
    {
        $this->assertInstanceOf(AgentTeamsDataService::class, $this->instance());
    }

    /**
     * @test
     */
    function it_should_return_Count_instance_with_nested_CountsGroup_when_counting_agents_in_teams()
    {
        $result = $this->instance()->countAgentsInTeams();
        $this->assertInstanceOf(Count::class, $result);
        $this->assertInstanceOf(CountsGroup::class, $result->getNested());
    }

    /**
     * @return AgentTeamsDataService
     */
    private function instance()
    {
        $query = $this->prophesize(Query::class);
        $qb = $this->prophesize(QueryBuilder::class);
        $em = $this->prophesize(EntityManager::class);

        // describe Query double
        $query->getArrayResult()->willReturn([]);

        // describe QueryBuilder double
        $qb->getQuery()->willReturn($query);
        $qb->getRootAliases()->willReturn(['alias']);
        $qb->select(Argument::any())->willReturn($qb);
        $qb->addSelect(Argument::any())->willReturn($qb);
        $qb->from(Argument::any(), Argument::any())->willReturn($qb);
        $qb->join(Argument::any(), Argument::any())->willReturn($qb);
        $qb->andWhere(Argument::any())->willReturn($qb);
        $qb->groupBy(Argument::any())->willReturn($qb);

        // describe EntityManager double
        $em->createQueryBuilder()->willReturn($qb);

        /** @var EntityManager $em */
        $em = $em->reveal();

        return new AgentTeamsDataService($em);
    }
}
