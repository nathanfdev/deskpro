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

namespace DpIntegrationTests\DeskPRO\Feedback\Categories;

class FeedbackCategoriesDataTest extends \DpIntegrationTestCase
{
    /**
     * @var \Application\DeskPRO\FeedbackCategories\FeedbackCategories
     */
    private $feedback_categories;

    public function runBefore()
    {
        $this->helper->enableDatabaseSet('EmptyDb');
        $this->helper->loadFixtures('General/SimpleFeedbackCategoriesData');

        $this->feedback_categories = $this->helper->getSymfonyContainer()->getSystemService('feedback_categories');
    }

    public function testGetFeedbackCategoryEntities()
    {
        $this->assertNull($this->feedback_categories->getById(100));
        $this->assertNotNull($this->feedback_categories->getById(2));

        $this->assertEquals('Category 1', $this->feedback_categories->getById(2)->getTitle());

        $this->assertEquals(4, $this->feedback_categories->count());
    }

    public function testGettingOfParentFeedbackCategory()
    {
        $this->assertInstanceOf(
            'Application\DeskPRO\FeedbackCategories\FeedbackCategories',
            $this->feedback_categories
        );

        $this->assertInstanceOf(
            'Application\DeskPRO\Entity\CustomDefFeedback',
            $this->feedback_categories->getParentCategory()
        );

        $this->assertEquals(1, $this->feedback_categories->getParentCategory()->getId());
    }

    public function testUpdatingOfFeedbackCategoryDisplayOrders()
    {
        $this->feedback_categories->updateDisplayOrders(
            array(4, 5, 2, 3)
        );

        $this->assertEquals(30, $this->feedback_categories->getById(2)->display_order);
        $this->assertEquals(40, $this->feedback_categories->getById(3)->display_order);
        $this->assertEquals(10, $this->feedback_categories->getById(4)->display_order);
        $this->assertEquals(20, $this->feedback_categories->getById(5)->display_order);
    }
}
