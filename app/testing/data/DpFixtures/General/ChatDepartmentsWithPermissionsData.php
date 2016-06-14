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

use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\DepartmentPermission;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Usergroup;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class ChatDepartmentsWithPermissionsData extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        ###################################################################################################################
        # Chat Departments
        ####################################################################################################################

        $dep1                = Department::createChatDepartment();
        $dep1->title         = 'Chat Department 1';
        $dep1->display_order = 10;
        $manager->persist($dep1);

        $dep1a                = Department::createChatDepartment();
        $dep1a->title         = 'Chat Department 1a';
        $dep1a->parent        = $dep1;
        $dep1a->display_order = 20;
        $manager->persist($dep1a);

        $dep1b                = Department::createChatDepartment();
        $dep1b->title         = 'Chat Department 1b';
        $dep1b->parent        = $dep1;
        $dep1b->display_order = 30;
        $manager->persist($dep1b);

        $dep2                = Department::createChatDepartment();
        $dep2->title         = 'Chat Department 2';
        $dep2->display_order = 40;
        $manager->persist($dep2);

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
        # Persons
        ####################################################################################################################

        $person1       = new Person();
        $person1->name = 'Ivan Ivanov';

        $manager->persist($person1);

        $person2       = new Person();
        $person2->name = 'Petr Sidorov';

        $manager->persist($person2);

        ###################################################################################################################
        # Department Permissions
        ####################################################################################################################

        $permission1             = new DepartmentPermission();
        $permission1->department = $dep1;
        $permission1->person     = null;
        $permission1->usergroup  = $usergroup1;
        $permission1->app        = 'chat';
        $permission1->name       = 'full';
        $permission1->value      = 1;

        $manager->persist($permission1);

        $permission2             = new DepartmentPermission();
        $permission2->department = $dep1;
        $permission2->person     = null;
        $permission2->usergroup  = $usergroup2;
        $permission2->app        = 'chat';
        $permission2->name       = 'full';
        $permission2->value      = 1;

        $manager->persist($permission2);

        $permission3             = new DepartmentPermission();
        $permission3->department = $dep1;
        $permission3->person     = $person1;
        $permission3->usergroup  = null;
        $permission3->app        = 'chat';
        $permission3->name       = 'full';
        $permission3->value      = 1;

        $manager->persist($permission3);

        $permission4             = new DepartmentPermission();
        $permission4->department = $dep1;
        $permission4->person     = $person2;
        $permission4->usergroup  = null;
        $permission4->app        = 'chat';
        $permission4->name       = 'full';
        $permission4->value      = 1;

        $manager->persist($permission4);

        $manager->flush();
    }
}
