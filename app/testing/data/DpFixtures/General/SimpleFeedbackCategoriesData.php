<?php
namespace DpFixtures\General;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Application\DeskPRO\Entity\CustomDefFeedback;

class SimpleFeedbackCategoriesData extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        $catRoot                = CustomDefFeedback::createFeedbackCategory();
        $catRoot->handler_class = 'Application\\DeskPRO\\CustomFields\\Handler\\Choice';
        $catRoot->title         = 'Category';
        $catRoot->sys_name      = 'cat';
        $catRoot->description   = 'Category';

        $manager->persist($catRoot);

        $cat1                = CustomDefFeedback::createFeedbackCategory();
        $cat1->title         = 'Category 1';
        $cat1->display_order = 10;
        $cat1->parent        = $catRoot;
        $cat1->sys_name      = null;
        $cat1->description   = 'description';

        $manager->persist($cat1);

        $cat2                = CustomDefFeedback::createFeedbackCategory();
        $cat2->title         = "Category 1a";
        $cat2->display_order = 20;
        $cat2->parent        = $catRoot;
        $cat2->sys_name      = null;
        $cat2->description   = 'description';

        $cat2->setOption('parent_id', 2);

        $manager->persist($cat2);

        $cat3                = CustomDefFeedback::createFeedbackCategory();
        $cat3->title         = "Category 1b";
        $cat3->display_order = 30;
        $cat3->parent        = $catRoot;
        $cat3->sys_name      = null;
        $cat3->description   = 'description';

        $cat2->setOption('parent_id', 2);

        $manager->persist($cat3);

        $cat4                = CustomDefFeedback::createFeedbackCategory();
        $cat4->title         = "Category 2";
        $cat4->display_order = 40;
        $cat4->parent        = $catRoot;
        $cat4->sys_name      = null;
        $cat4->description   = 'description';

        $manager->persist($cat4);

        $manager->flush();
    }
}
