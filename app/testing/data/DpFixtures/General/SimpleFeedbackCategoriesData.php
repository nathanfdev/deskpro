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

use Application\DeskPRO\Entity\CustomDefFeedback;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

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
        $cat2->title         = 'Category 1a';
        $cat2->display_order = 20;
        $cat2->parent        = $catRoot;
        $cat2->sys_name      = null;
        $cat2->description   = 'description';

        $cat2->setOption('parent_id', 2);

        $manager->persist($cat2);

        $cat3                = CustomDefFeedback::createFeedbackCategory();
        $cat3->title         = 'Category 1b';
        $cat3->display_order = 30;
        $cat3->parent        = $catRoot;
        $cat3->sys_name      = null;
        $cat3->description   = 'description';

        $cat2->setOption('parent_id', 2);

        $manager->persist($cat3);

        $cat4                = CustomDefFeedback::createFeedbackCategory();
        $cat4->title         = 'Category 2';
        $cat4->display_order = 40;
        $cat4->parent        = $catRoot;
        $cat4->sys_name      = null;
        $cat4->description   = 'description';

        $manager->persist($cat4);

        $manager->flush();
    }
}
