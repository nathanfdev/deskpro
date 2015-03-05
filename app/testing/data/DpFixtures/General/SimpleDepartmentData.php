<?php
namespace DpFixtures\General;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Application\DeskPRO\Entity\Department;

class SimpleDepartmentData extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        $dep1 = Department::createTicketDepartment();
        $dep1->title = "Department 1";
        $dep1->display_order = 10;
        $manager->persist($dep1);

        $dep1a = Department::createTicketDepartment();
        $dep1a->title = "Department 1a";
        $dep1a->parent = $dep1;
        $dep1a->display_order = 20;
        $manager->persist($dep1a);

        $dep1b = Department::createTicketDepartment();
        $dep1b->title = "Department 1b";
        $dep1b->parent = $dep1;
        $dep1b->display_order = 30;
        $manager->persist($dep1b);

        $dep2 = Department::createTicketDepartment();
        $dep2->title = "Department 2";
        $dep2->display_order = 40;
        $manager->persist($dep2);

        $manager->flush();
    }
}
