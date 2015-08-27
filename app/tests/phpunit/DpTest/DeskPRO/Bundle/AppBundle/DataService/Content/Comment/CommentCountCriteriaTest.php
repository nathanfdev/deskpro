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

namespace DpTest\Bundle\AppBundle\DataService\Content;

use Prophecy\Argument;
use DpTest\DeskProTestCase;
use DeskPRO\Bundle\AppBundle\DataService\Content\Comment\CommentsCountCriteria;

/**
 * Class CommentCountCriteriaTest
 */
class CommentCountCriteriaTest extends DeskProTestCase
{
    static $dummyProperParams = [
        'status'         => 'validating',
        'article'        => '1',
        'is_reviewed'    => '0',
        'period_created' => 'ever',
        'group_by'       => 'status'
    ];

    /**
     * @test
     * @expectedException        \Symfony\Component\OptionsResolver\Exception\MissingOptionsException
     * @expectedExceptionMessage The required option "group_by" is missing.
     */
    function it_should_not_be_constructable_without_group_by()
    {
        $this->instance([]);
    }

    /**
     * @test
     */
    function it_should_be_constructable_with_only_group_by()
    {
        $this->assertInstanceOf(CommentsCountCriteria::class, $this->instance(['group_by' => 'status']));
    }

    /**
     * @test
     */
    function it_should_be_constructable_with_proper_parameters()
    {
        $this->assertInstanceOf(CommentsCountCriteria::class, $this->instance(self::$dummyProperParams));
    }

    /**
     * @test
     */
    function it_should_apply_given_group_by_to_the_passed_QueryBuilder()
    {
        $qb = $this->mockQueryBuilder();
        $qb->groupBy(Argument::any())->shouldBeCalled();
        $this->instance(self::$dummyProperParams)->applyGroupBy($qb->reveal());
    }

    /**
     * @test
     */
    function it_should_apply_given_parameters_to_the_passed_QueryBuilder()
    {
        $qb = $this->mockQueryBuilder();

        // expectations when applying self::$dummyProperParams
        $qb->setParameter('status',         'validating')->shouldBeCalled();
        $qb->setParameter('article',        '1')->shouldBeCalled();
        $qb->setParameter('is_reviewed',    '0')->shouldBeCalled();
        $qb->setParameter('period_created', 'ever')->shouldBeCalled();

        /** @var \Doctrine\ORM\QueryBuilder $qb */
        $qb = $qb->reveal();
        $this->instance(self::$dummyProperParams)->applyFilters($qb);
    }

    /**
     * @param array $parameters
     * @return CommentsCountCriteria
     */
    private function instance(array $parameters)
    {
        return CommentsCountCriteria::fromParameters(
                   $parameters, new \Symfony\Component\OptionsResolver\OptionsResolver());
    }
}
