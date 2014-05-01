<?php

namespace DpFixtures\General;

use Application\DeskPRO\Entity\Person;
use Faker\Factory;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class AgentData extends AbstractFixture
{
    protected $agentToDepartment = array(
        0 => 1,
        1 => 1,
        2 => 2,
        3 => 2,
        4 => 5
    );

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();
        $connection = $manager->getConnection();

        for ($i = 0; $i < 5; $i++) {

            $agent = new Person();
            $agent->name = $faker->name;
            $agent->setEmail($faker->email, true);
            $agent->date_created = new \DateTime();
            $agent->is_user = true;
            $agent->is_confirmed = true;
            $agent->is_agent = true;

            $manager->persist($agent);
            $manager->flush();

            // Default to non-destructive perm group, or if thats deleted, the default all perms group
            $has_ug = $connection->fetchColumn("SELECT id FROM usergroups WHERE id IN (4,3) ORDER BY id DESC");

            if ($has_ug) {
                $connection->insert('person2usergroups', array(
                    'person_id' => $agent->getId(),
                    'usergroup_id' => $has_ug
                ));
            }

            $dep = array(
                'department_id' => $this->agentToDepartment[$i],
                'person_id'     => $agent->getId(),
                'app'           => 'tickets',
                'name'          => 'full',
                'value'         => 1
            );

            $connection->insert('department_permissions', $dep);
        }


    }
} 