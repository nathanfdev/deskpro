<?php
namespace DpFixtures\General;

use Application\DeskPRO\Entity\Usergroup;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Application\DeskPRO\Entity\FeedbackCategory;

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