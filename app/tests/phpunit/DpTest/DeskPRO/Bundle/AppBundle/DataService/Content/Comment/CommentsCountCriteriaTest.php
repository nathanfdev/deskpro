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

use DeskPRO\Bundle\AppBundle\DataService\Content\Comment\CommentsCountCriteria;
use DeskPRO\Bundle\AppBundle\DataService\Content\Comment\CommentsSelectCriteria;
use DpTest\DeskProTestCase;
use Prophecy\Argument;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CommentsCountCriteriaTest.
 */
class CommentsCountCriteriaTest extends DeskProTestCase
{
    public static $dummyProperParams = [
        'status'         => 'visible',
        'article'        => '1',
        'is_reviewed'    => '0',
        'period_created' => 'ever',
        'group_by'       => 'status',
    ];

    /**
     * @test
     */
    public function it_should_be_constructable_with_empty_params()
    {
        $this->assertInstanceOf(CommentsCountCriteria::class, $this->instance([]));
    }

    /**
     * @test
     */
    public function it_should_be_constructable_with_only_group_by()
    {
        $this->assertInstanceOf(CommentsCountCriteria::class, $this->instance(['group_by' => 'status']));
    }

    /**
     * @test
     */
    public function it_should_be_constructable_with_proper_parameters()
    {
        $this->assertInstanceOf(CommentsCountCriteria::class, $this->instance(self::$dummyProperParams));
    }

    /**
     * @test
     */
    public function it_should_extend_CommentsSelectCriteria()
    {
        $this->assertInstanceOf(CommentsSelectCriteria::class, $this->instance([]));
    }

    /**
     * @test
     */
    public function it_should_inherit_all_OptionsResolver_data_from_CommentsSelectCriteria_and_add_own_group_by_option()
    {
        $data = [new \Application\DeskPRO\Entity\Person()];

        CommentsCountCriteria::fromParameters([], $countOptionsResolver = new OptionsResolver(), $data);
        CommentsSelectCriteria::fromParameters([], $selectOptionsResolver = new OptionsResolver(), $data);
        $countOptions  = $countOptionsResolver->getDefinedOptions();
        $selectOptions = $selectOptionsResolver->getDefinedOptions();

        $this->assertEquals(
            array_values($selectOptions),
            array_values($this->removeFromArray('group_by', $countOptions))
        );
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
        $qb->setParameter('status',         'visible')->shouldBeCalled();
        $qb->setParameter('article',        '1')->shouldBeCalled();
        $qb->setParameter('is_reviewed',    '0')->shouldBeCalled();
        $qb->setParameter('period_created', 'ever')->shouldBeCalled();

        /** @var \Doctrine\ORM\QueryBuilder $qb */
        $qb = $qb->reveal();
        $this->instance(self::$dummyProperParams)->applyFilters($qb);
    }

    /**
     * @param array $parameters
     *
     * @return CommentsCountCriteria
     */
    private function instance(array $parameters)
    {
        return CommentsCountCriteria::fromParameters(
                   $parameters, new \Symfony\Component\OptionsResolver\OptionsResolver());
    }
}
