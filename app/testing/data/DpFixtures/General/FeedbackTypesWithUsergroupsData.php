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

use Application\DeskPRO\Entity\FeedbackCategory;
use Application\DeskPRO\Entity\Usergroup;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class FeedbackTypesWithUsergroupsData extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        ###################################################################################################################
        # Feedback Types
        ####################################################################################################################

        $type1                = FeedbackCategory::createFeedbackCategory();
        $type1->title         = 'Suggestion';
        $type1->display_order = 10;

        $manager->persist($type1);

        $type2                = FeedbackCategory::createFeedbackCategory();
        $type2->title         = 'Feature Request';
        $type2->display_order = 20;

        $manager->persist($type2);

        $type3                = FeedbackCategory::createFeedbackCategory();
        $type3->title         = 'Bug Report';
        $type3->display_order = 30;

        $manager->persist($type3);

        $type4                = FeedbackCategory::createFeedbackCategory();
        $type4->title         = 'Feature Requested';
        $type4->display_order = 40;

        $manager->persist($type4);

        ###################################################################################################################
        # Usergroups
        ####################################################################################################################

        $usergroup1                 = new Usergroup();
        $usergroup1->title          = 'Everyone';
        $usergroup1->note           = 'description 1';
        $usergroup1->is_agent_group = false;
        $usergroup1->sys_name       = 'everyone';
        $usergroup1->is_enabled     = true;

        $manager->persist($usergroup1);

        $usergroup2                 = new Usergroup();
        $usergroup2->title          = 'Registered';
        $usergroup2->note           = 'description 2';
        $usergroup2->is_agent_group = false;
        $usergroup2->sys_name       = 'registered';
        $usergroup2->is_enabled     = true;

        $manager->persist($usergroup2);

        $usergroup3                 = new Usergroup();
        $usergroup3->title          = 'All Permissions';
        $usergroup3->note           = 'description 3';
        $usergroup3->is_agent_group = true;
        $usergroup3->sys_name       = null;
        $usergroup3->is_enabled     = true;

        $manager->persist($usergroup3);

        ###################################################################################################################
        # Linking feedback types with usergroups
        ####################################################################################################################

        $type1->addUsergroup($usergroup1);
        $type1->addUsergroup($usergroup2);
        $type1->addUsergroup($usergroup3);

        $manager->flush();
    }
}
