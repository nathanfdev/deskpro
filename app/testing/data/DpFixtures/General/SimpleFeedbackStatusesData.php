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

namespace DpFixtures\General;

use Application\DeskPRO\Entity\FeedbackStatusCategory;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

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
