<?php

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
