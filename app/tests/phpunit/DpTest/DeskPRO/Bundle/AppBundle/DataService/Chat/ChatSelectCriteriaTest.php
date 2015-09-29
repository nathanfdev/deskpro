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

use DeskPRO\Bundle\AppBundle\DataService\Chat\ChatSelectCriteria;
use Doctrine\ORM\QueryBuilder;
use DpTest\DeskProTestCase;

/**
 * Class ChatSelectCriteriaTest.
 */
class ChatSelectCriteriaTest extends DeskProTestCase
{
    public static $dummyProperParams = [
        'agent'        => 1,
        'department'   => 1,
        'date_created' => '2013-01-01:2015-01-01',
        'date_period'  => 'this_month',
    ];

    /**
     * @test
     */
    public function it_should_be_instantiable_with_factory_method_from_empty_parameter()
    {
        $this->assertInstanceOf(ChatSelectCriteria::class, $this->instance([]));
    }

    /**
     * @test
     */
    public function it_should_be_instantiable_with_proper_parameters()
    {
        $this->assertInstanceOf(ChatSelectCriteria::class, $this->instance(self::$dummyProperParams));
    }

    /**
     * @test
     * @expectedException \Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException
     */
    public function it_should_throw_an_exception_when_passing_an_unknown_parameter()
    {
        $this->instance(['color' => 'purple']);
    }

    /**
     * @test
     * @expectedException        \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @expectedExceptionMessage The option "date_period" with value "hanukkah" is invalid. Accepted values are:
     *                           "today", "yesterday", "this_week", "this_month", "last_month", "this_year", "ever".
     */
    public function it_should_throw_an_exception_with_list_of_allowed_date_period_values_when_passing_a_wrong_value()
    {
        $this->instance(['date_period' => 'hanukkah']);
    }

    /**
     * @test
     * @expectedException \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    public function it_should_throw_an_exception_when_date_created_param_is_not_two_YYYY_MM_DD_strings_separated_by_column()
    {
        $this->instance(['date_created' => '2013-1-1:2013-12-31']);
    }

    /**
     * @test
     */
    public function it_should_parse_and_apply_given_parameters_to_the_passed_QueryBuilder()
    {
        $qb = $this->mockQueryBuilder();

        // expectations when applying self::$dummyProperParams
        $qb->setParameter('from',        '2013-01-01')->shouldBeCalled();
        $qb->setParameter('to',          '2015-01-01')->shouldBeCalled();
        $qb->setParameter('agent',       1)->shouldBeCalled();
        $qb->setParameter('department',  1)->shouldBeCalled();
        $qb->setParameter('date_period', 'this_month')->shouldBeCalled();

        /** @var QueryBuilder $qb */
        $qb = $qb->reveal();
        $this->instance(self::$dummyProperParams)->applyFilters($qb);
    }

    /**
     * @param array $parameters
     *
     * @return ChatSelectCriteria
     */
    private function instance($parameters = [])
    {
        $resolver = new \Symfony\Component\OptionsResolver\OptionsResolver();
        $me       = new \Application\DeskPRO\Entity\Person();

        return ChatSelectCriteria::fromParameters($parameters, $resolver, [$me]);
    }
}
