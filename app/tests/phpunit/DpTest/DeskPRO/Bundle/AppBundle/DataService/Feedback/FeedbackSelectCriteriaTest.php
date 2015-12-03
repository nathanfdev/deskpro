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

/*
 * DeskPRO
 *
 * @package DeskPRO
 */

use DeskPRO\Bundle\AppBundle\DataService\Feedback\FeedbackSelectCriteria;
use DpTest\DeskProTestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class FeedbackSelectCriteriaTest.
 */
class FeedbackSelectCriteriaTest extends DeskProTestCase
{
    private static $dummyProperParams = [
        'status' => 'new',
    ];

    /**
     * @test
     */
    public function it_should_be_instantiable_with_factory_method_from_empty_parameter()
    {
        static::assertInstanceOf(FeedbackSelectCriteria::class, $this->instance());
    }

    /**
     * @test
     */
    public function it_should_be_instantiable_with_proper_parameters()
    {
        static::assertInstanceOf(FeedbackSelectCriteria::class, $this->instance(self::$dummyProperParams));
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
     * @expectedExceptionMessage The option "status" with value "unknown" is invalid.
     */
    public function it_should_throw_an_exception_with_list_of_allowed_statuses_values_when_passing_a_wrong_value()
    {
        $this->instance(['status' => 'unknown']);
    }

    /**
     * @param array $parameters
     *
     * @return FeedbackSelectCriteria
     */
    private function instance(array $parameters = [])
    {
        $resolver = new OptionsResolver();

        return FeedbackSelectCriteria::fromParameters($parameters, $resolver);
    }
}
