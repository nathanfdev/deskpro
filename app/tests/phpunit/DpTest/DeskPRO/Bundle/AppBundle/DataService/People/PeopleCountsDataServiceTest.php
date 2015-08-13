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

namespace DpTest\Bundle\AppBundle\DataService\People;

use Prophecy\Argument;
use DpTest\DeskProTestCase;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\AbstractQuery as Query;
use DeskPRO\Bundle\AppBundle\DataService\People\PeopleCountsDataService;
use DeskPRO\Bundle\AppBundle\DataService\People\PeopleCountCriteria;
use DeskPRO\Bundle\AppBundle\CountBadge\Count;

/**
 * Class PeopleCountsDataServiceTest
 */
class PeopleCountsDataServiceTest extends DeskProTestCase
{
    /**
     * @test
     */
    function it_should_be_instantiable()
    {
        $this->assertInstanceOf(PeopleCountsDataService::class, $this->instance());
    }

    /**
     * @test
     */
    function it_should_return_Count_instance_when_counting_people()
    {
        /** @var PeopleCountCriteria $criteria */
        $criteria = $this->prophesize(PeopleCountCriteria::class)->reveal();
        $result = $this->instance()->countPeople($criteria);
        $this->assertInstanceOf(Count::class, $result);
    }

    /**
     * @return PeopleCountsDataService
     */
    private function instance()
    {
        $qb_prophecy = $this->prophesize(QueryBuilder::class);
        $em_prophecy = $this->prophesize(EntityManager::class);

        // describe QueryBuilder double
        $qb_prophecy->getQuery()->willReturn($this->prophesize(Query::class)->reveal());
        $qb_prophecy->from(Argument::any(), Argument::any())->willReturn($qb_prophecy->reveal());
        $qb_prophecy->select(Argument::any())->willReturn($qb_prophecy->reveal());
        $qb_prophecy->getRootAliases()->willReturn(['alias']);

        // describe EntityManager double
        $em_prophecy->createQueryBuilder()->willReturn($qb_prophecy->reveal());

        /** @var EntityManager $em */
        $em = $em_prophecy->reveal();

        return new PeopleCountsDataService($em);
    }
}
