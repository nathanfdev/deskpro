<?php
namespace DpFixtures\General;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use  \Application\DeskPRO\Entity\FeedbackStatusCategory;

class SimpleFeedbackStatusesData extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        $status1                = FeedbackStatusCategory::createFeedbackStatusCategory();
        $status1->status_type   = 'active';
        $status1->title         = 'Planning';
        $status1->display_order = 10;

        $manager->persist($status1);

        $status2                = FeedbackStatusCategory::createFeedbackStatusCategory();
        $status2->status_type   = 'active';
        $status2->title         = 'Started';
        $status2->display_order = 20;

        $manager->persist($status2);

        $status3                = FeedbackStatusCategory::createFeedbackStatusCategory();
        $status3->status_type   = 'closed';
        $status3->title         = 'Under Review';
        $status3->display_order = 30;

        $manager->persist($status3);

        $status4                = FeedbackStatusCategory::createFeedbackStatusCategory();
        $status4->status_type   = 'closed';
        $status4->title         = 'Completed';
        $status4->display_order = 40;

        $manager->persist($status4);

        $manager->flush();
    }
}
