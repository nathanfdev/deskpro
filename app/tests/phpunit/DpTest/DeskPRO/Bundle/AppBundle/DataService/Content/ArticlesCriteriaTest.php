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
namespace DpTest\Bundle\AppBundle\DataService\Content;

use DeskPRO\Bundle\AppBundle\DataService\Content\ArticlesCriteria;
use DpTest\DeskProTestCase;
use Prophecy\Argument;

/**
 * Class ArticlesCountCriteriaTest.
 */
class ArticlesCriteriaTest extends DeskProTestCase
{
    public static $dummyProperParams = [
        'status'         => 'published',
        'author'         => 1,
        'category'       => 1,
        'period_created' => 'this_month',
        'group_by'       => 'category',
    ];

    /**
     * @test
     */
    public function it_should_be_constructable_with_empty_params()
    {
        $this->assertInstanceOf(ArticlesCriteria::class, $this->instance([]));
    }

    /**
     * @test
     */
    public function it_should_apply_given_group_by_to_the_passed_QueryBuilder()
    {
        $qb = $this->mockQueryBuilder();
        $qb->groupBy(Argument::any())->shouldBeCalled();
        $this->instance(self::$dummyProperParams)->applyGroupBy($qb->reveal());
    }

    /**
     * @test
     */
    public function it_should_apply_given_parameters_to_the_passed_QueryBuilder()
    {
        $qb = $this->mockQueryBuilder();

        // expectations when applying self::$dummyProperParams
        $qb->setParameter('status', 'published')->shouldBeCalled();
        $qb->setParameter('person', 1)->shouldBeCalled();
        $qb->setParameter('category', 1)->shouldBeCalled();
        $qb->setParameter('period_created', 'this_month')->shouldBeCalled();

        /** @var \Doctrine\ORM\QueryBuilder $qb */
        $qb = $qb->reveal();
        $this->instance(self::$dummyProperParams)->applyFilters($qb);
    }

    /**
     * @param array $parameters
     *
     * @return ArticlesCriteria
     */
    private function instance(array $parameters)
    {
        return ArticlesCriteria::fromParameters(
            $parameters,
            new \Symfony\Component\OptionsResolver\OptionsResolver(),
            [new \Application\DeskPRO\Entity\Person()]
        );
    }
}
