<?php
namespace DpFixtures\General;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Application\DeskPRO\Entity\Department;

class SimpleChatDepartmentsData extends AbstractFixture
{
	public function load(ObjectManager $manager)
	{
		$dep1                = Department::createChatDepartment();
		$dep1->title         = "Chat Department 1";
		$dep1->display_order = 10;
		$manager->persist($dep1);

		$dep1a                = Department::createChatDepartment();
		$dep1a->title         = "Chat Department 1a";
		$dep1a->parent        = $dep1;
		$dep1a->display_order = 20;
		$manager->persist($dep1a);

		$dep1b                = Department::createChatDepartment();
		$dep1b->title         = "Chat Department 1b";
		$dep1b->parent        = $dep1;
		$dep1b->display_order = 30;
		$manager->persist($dep1b);

		$dep2                = Department::createChatDepartment();
		$dep2->title         = "Chat Department 2";
		$dep2->display_order = 40;
		$manager->persist($dep2);

		$manager->flush();
	}
}