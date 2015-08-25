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
use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\News;
use DeskPRO\Bundle\AppBundle\DataService\Content\Category\CategoriesDataService;
use DeskPRO\Bundle\AppBundle\DataService\Content\ArticlesCountCriteria;
use DeskPRO\Bundle\AppBundle\DataService\Content\ContentCountCriteria;
use DeskPRO\Bundle\AppBundle\DataService\Content\ContentCountsDataService;
use DeskPRO\Bundle\AppBundle\CountBadge\Count;

/**
 * Class ContentCountsDataServiceTest
 */
class ContentCountsDataServiceTest extends DeskProTestCase
{
    /**
     * @test
     */
    function it_should_be_instantiable()
    {
        $this->assertInstanceOf(ContentCountsDataService::class, $this->instance());
    }

    /**
     * @test
     */
    function it_should_return_Count_instance_with_group_by_indication()
    {
        $result = $this->instance()->countContent(Article::class, $this->contentCriteria());

        $this->assertInstanceOf(Count::class, $result);
        $this->assertNotNull($result->getGroupedBy());
    }

    /**
     * @test
     */
    function it_should_perform_a_single_query_to_select_counts()
    {
        $em = $this->mockQueryBuildingEntityManager();
        $em->createQueryBuilder()->shouldBeCalledTimes(1);
        $this->instance($em->reveal())->countContent(Article::class, $this->contentCriteria());
    }

    /**
     * @test
     */
    function it_should_perform_additional_query_to_select_articles_distinct_count_when_grouped_by_category()
    {
        $em = $this->mockQueryBuildingEntityManager();
        $em->createQueryBuilder()->shouldBeCalledTimes(2);
        $this->instance($em->reveal())
             ->countContent(Article::class, $this->articlesCriteria(['group_by' => 'category']));
    }

    /**
     * @test
     */
    function it_should_still_perform_a_single_query_to_select_news_counts_when_grouped_by_category()
    {
        $em = $this->mockQueryBuildingEntityManager();
        $em->createQueryBuilder()->shouldBeCalledTimes(1);
        $this->instance($em->reveal())->countContent(News::class, $this->contentCriteria(['group_by' => 'category']));
    }

    /**
     * @return ContentCountsDataService
     */
    private function instance($em = null)
    {
        /** @var \Doctrine\ORM\EntityManagerInterface $em */
        $em or $em = $this->mockQueryBuildingEntityManager()->reveal();


        return new ContentCountsDataService($em, new CategoriesDataService($em));
    }

    /**
     * @param array $params
     * @return ContentCountCriteria
     */
    private function contentCriteria($params = ['group_by' => 'author'])
    {
        $resolver = new \Symfony\Component\OptionsResolver\OptionsResolver();
        $criteria = ContentCountCriteria::fromParameters($params, $resolver);

        return $criteria;
    }
    /**
     * @param array $params
     * @return ArticlesCountCriteria
     */
    private function articlesCriteria($params = ['group_by' => 'author'])
    {
        $resolver = new \Symfony\Component\OptionsResolver\OptionsResolver();
        $criteria = ArticlesCountCriteria::fromParameters($params, $resolver);

        return $criteria;
    }
}
