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

use Application\DeskPRO\Entity\Person;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

class AgentData extends AbstractFixture
{
    protected $agentToDepartment = array(
        0 => 1,
        1 => 1,
        2 => 2,
        3 => 2,
        4 => 5,
    );

    public function load(ObjectManager $manager)
    {
        $faker      = Factory::create();
        $connection = $manager->getConnection();

        for ($i = 0; $i < 5; ++$i) {
            $agent       = new Person();
            $agent->name = $faker->name;
            $agent->setEmail($faker->email, true);
            $agent->date_created = new \DateTime();
            $agent->is_user      = true;
            $agent->is_confirmed = true;
            $agent->is_agent     = true;

            $manager->persist($agent);
            $manager->flush();

            // Default to non-destructive perm group, or if thats deleted, the default all perms group
            $has_ug = $connection->fetchColumn('SELECT id FROM usergroups WHERE id IN (4,3) ORDER BY id DESC');

            if ($has_ug) {
                $connection->insert('person2usergroups', array(
                    'person_id'    => $agent->getId(),
                    'usergroup_id' => $has_ug,
                ));
            }

            $dep = array(
                'department_id' => $this->agentToDepartment[$i],
                'person_id'     => $agent->getId(),
                'app'           => 'tickets',
                'name'          => 'full',
                'value'         => 1,
            );

            $connection->insert('department_permissions', $dep);
        }
    }
}
