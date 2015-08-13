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
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\Query\Expr;
use DpTest\DeskProTestCase;
use DeskPRO\Bundle\AppBundle\DataService\People\PeopleCountCriteria;

/**
 * Class PeopleCountCriteriaTest
 */
class PeopleCountCriteriaTest extends DeskProTestCase
{
    static $dummyProperParams = [
        'is_agent'   => '0',
        'is_deleted' => '1',
    ];

    /**
     * @test
     */
    function it_should_be_instantiable_with_factory_method_from_empty_parameter()
    {
        $this->assertInstanceOf(PeopleCountCriteria::class, $this->instance([]));
    }

    /**
     * @test
     */
    function it_should_be_instantiable_with_proper_parameters()
    {
        $this->assertInstanceOf(PeopleCountCriteria::class, $this->instance(self::$dummyProperParams));
    }

    /**
     * @test
     * @expectedException \Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException
     */
    function it_should_throw_an_exception_when_passing_an_unknown_parameter()
    {
        $this->instance(['color' => 'purple']);
    }

    /**
     * @param array $parameters
     * @return PeopleCountCriteria
     */
    private function instance($parameters = [])
    {
        return PeopleCountCriteria::fromParameters($parameters, new OptionsResolver());
    }
}
