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

use DeskPRO\Bundle\AppBundle\DataService\Chat\ChatCountCriteria;
use DeskPRO\Bundle\AppBundle\DataService\Chat\ChatSelectCriteria;
use Doctrine\ORM\QueryBuilder;
use DpTest\DeskProTestCase;
use Prophecy\Argument;

/**
 * Class ChatCountCriteriaTest.
 */
class ChatCountCriteriaTest extends DeskProTestCase
{
    public static $dummyProperParams = [
        'agent'        => 1,
        'department'   => 1,
        'date_created' => '2013-01-01:2015-01-01',
        'date_period'  => 'this_month',
        'group_by'     => 'agent',
    ];

    /**
     * @test
     */
    public function it_should_be_instantiable_with_factory_method_from_empty_parameter()
    {
        $this->assertInstanceOf(ChatCountCriteria::class, $this->instance([]));
    }

    /**
     * @test
     */
    public function it_should_be_instantiable_with_proper_parameters()
    {
        $this->assertInstanceOf(ChatCountCriteria::class, $this->instance(self::$dummyProperParams));
    }

    /**
     * @test
     */
    public function it_should_extend_ChatSelectCriteria_with_group_by_functionality()
    {
        $this->assertInstanceOf(ChatSelectCriteria::class, $this->instance(['group_by' => 'date_period']));
    }

    /**
     * @test
     * @expectedException        \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @expectedExceptionMessage The option "group_by" with value "color" is invalid. Accepted values are: "agent",
     *                           "department", "date_created", "date_period".
     */
    public function it_should_throw_an_exception_with_list_of_allowed_group_by_values_when_passing_a_wrong_value()
    {
        $this->instance(['group_by' => 'color']);
    }

    /**
     * @test
     */
    public function it_should_apply_given_group_by_to_the_passed_QueryBuilder()
    {
        $qb = $this->mockQueryBuilder();

        // expectations when applying self::$dummyProperParams
        $qb->groupBy(Argument::any())->shouldBeCalled();

        /** @var QueryBuilder $qb */
        $qb = $qb->reveal();
        $this->instance(self::$dummyProperParams)->applyGroupBy($qb);
    }

    /**
     * @param array $parameters
     *
     * @return ChatCountCriteria
     */
    private function instance($parameters = [])
    {
        $resolver = new \Symfony\Component\OptionsResolver\OptionsResolver();
        $me       = new \Application\DeskPRO\Entity\Person();

        return ChatCountCriteria::fromParameters($parameters, $resolver, [$me]);
    }
}
