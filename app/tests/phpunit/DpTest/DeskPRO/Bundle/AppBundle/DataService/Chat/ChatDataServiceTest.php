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

namespace DpTest\Bundle\AppBundle\DataService\Chat;

use DeskPRO\Bundle\AppBundle\CountBadge\Count;
use DeskPRO\Bundle\AppBundle\DataService\Chat\ChatCountCriteria;
use DeskPRO\Bundle\AppBundle\DataService\Chat\ChatDataService;
use DeskPRO\Bundle\AppBundle\DataService\Chat\ChatSelectCriteria;
use DpTest\DeskProTestCase;
use Pagerfanta\Pagerfanta;

/**
 * Class ChatDataServiceTest.
 */
class ChatDataServiceTest extends DeskProTestCase
{
    /**
     * @test
     */
    public function it_should_be_instantiable()
    {
        $this->assertInstanceOf(ChatDataService::class, $this->instance());
    }

    /**
     * @test
     */
    public function it_should_return_Pagerfanta_instance_when_selecting_chats()
    {
        /** @var ChatSelectCriteria $criteria */
        $criteria = $this->prophesize(ChatSelectCriteria::class)->reveal();
        $result   = $this->instance()->selectChats($criteria, 1, 10);
        $this->assertInstanceOf(Pagerfanta::class, $result);
    }

    /**
     * @test
     */
    public function it_should_return_Count_instance_when_counting_chats()
    {
        /** @var ChatCountCriteria $criteria */
        $criteria = $this->prophesize(ChatCountCriteria::class)->reveal();
        $result   = $this->instance()->countChats($criteria);
        $this->assertInstanceOf(Count::class, $result);
    }

    /**
     * @test
     */
    public function it_should_return_Count_instance_with_group_by_indication_when_counting_criteria_has_group_by_option()
    {
        $resolver = new \Symfony\Component\OptionsResolver\OptionsResolver();
        $me       = new \Application\DeskPRO\Entity\Person();
        $criteria = ChatCountCriteria::fromParameters(['group_by' => 'date_created'], $resolver, [$me]);

        $result = $this->instance()->countChats($criteria);

        $this->assertEquals($result->getGroupedBy(), 'date_created');
    }

    /**
     * @test
     */
    public function it_should_return_Count_instance_without_group_by_indication_when_counting_criteria_has_no_group_by_option()
    {
        $resolver = new \Symfony\Component\OptionsResolver\OptionsResolver();
        $me       = new \Application\DeskPRO\Entity\Person();
        $criteria = ChatCountCriteria::fromParameters([], $resolver, [$me]);

        $result = $this->instance()->countChats($criteria);

        $this->assertNull($result->getType());
    }

    /**
     * @return ChatDataService
     */
    private function instance()
    {
        /** @var \Doctrine\ORM\EntityManagerInterface $em */
        $em = $this->mockQueryBuildingEntityManager()->reveal();

        return new ChatDataService($em);
    }
}
