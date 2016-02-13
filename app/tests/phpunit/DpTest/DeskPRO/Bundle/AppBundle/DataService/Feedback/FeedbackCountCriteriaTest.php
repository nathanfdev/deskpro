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

namespace DpTest\Bundle\AppBundle\DataService\Feedback;

use DeskPRO\Bundle\AppBundle\DataService\Feedback\FeedbackCountCriteria;
use DeskPRO\Bundle\AppBundle\DataService\Feedback\FeedbackSelectCriteria;
use DpTest\DeskProTestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class FeedbackCountCriteriaTest.
 */
class FeedbackCountCriteriaTest extends DeskProTestCase
{
    private static $dummyProperParams = [
        'status'   => 'active',
        'group_by' => 'status_category',
    ];

    /**
     * @test
     */
    public function it_should_be_instantiable_with_factory_method_from_empty_parameter()
    {
        static::assertInstanceOf(FeedbackCountCriteria::class, $this->instance());
    }

    /**
     * @test
     */
    public function it_should_be_instantiable_with_proper_parameters()
    {
        static::assertInstanceOf(FeedbackCountCriteria::class, $this->instance(self::$dummyProperParams));
    }

    /**
     * @test
     */
    public function it_should_extend_FeedbackSelectCriteria_with_group_by_functionality()
    {
        static::assertInstanceOf(FeedbackSelectCriteria::class, $this->instance(['group_by' => 'custom_category']));
    }

    /**
     * @test
     * @expectedException        \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @expectedExceptionMessage The option "group_by" with value "color" is invalid. Accepted values are:
     *                           'status_category', 'hidden_status', 'category', 'custom_category'.
     */
    public function it_should_throw_an_exception_with_list_of_allowed_group_by_values_when_passing_a_wrong_value()
    {
        $this->instance(['group_by' => 'color']);
    }

    /**
     * @param array $parameters
     *
     * @return FeedbackCountCriteria
     */
    private function instance(array $parameters = [])
    {
        $resolver = new OptionsResolver();

        return FeedbackCountCriteria::fromParameters($parameters, $resolver);
    }
}
