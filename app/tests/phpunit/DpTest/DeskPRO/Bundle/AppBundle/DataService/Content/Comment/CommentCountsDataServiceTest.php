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
namespace DpTest\Bundle\AppBundle\DataService\Comment;

use Application\DeskPRO\Entity\ArticleComment;
use DeskPRO\Bundle\AppBundle\CountBadge\Count;
use DeskPRO\Bundle\AppBundle\DataService\Content\Comment\CommentsCountCriteria;
use DeskPRO\Bundle\AppBundle\DataService\Content\Comment\CommentsDataService;
use DeskPRO\Bundle\AppBundle\DataService\Content\Comment\CommentsSelectCriteria;
use DpTest\DeskProTestCase;
use Pagerfanta\Pagerfanta;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CommentCountsDataServiceTest.
 */
class CommentCountsDataServiceTest extends DeskProTestCase
{
    /**
     * @test
     */
    public function it_should_be_instantiable()
    {
        $this->assertInstanceOf(CommentsDataService::class, $this->instance());
    }

    /**
     * @test
     */
    public function it_should_return_Count_instance_with_group_by_indication()
    {
        $criteria = CommentsCountCriteria::fromParameters(['group_by' => 'status'], new OptionsResolver());

        $result = $this->instance()->countComments(ArticleComment::class, $criteria);

        $this->assertInstanceOf(Count::class, $result);
        $this->assertEquals($result->getGroupedBy(), 'status');
    }

    /**
     * @test
     */
    public function it_should_return_Pagerfanta_instance_when_selecting_comments()
    {
        /** @var CommentsSelectCriteria $criteria */
        $criteria = $this->prophesize(CommentsSelectCriteria::class)->reveal();
        $result   = $this->instance()->selectComments(ArticleComment::class, $criteria, 1, 10);
        $this->assertInstanceOf(Pagerfanta::class, $result);
    }

    /**
     * @return CommentsDataService
     */
    private function instance($em = null)
    {
        /* @var \Doctrine\ORM\EntityManagerInterface $em */
        $em or $em = $this->mockQueryBuildingEntityManager()->reveal();

        return new CommentsDataService($em);
    }
}
