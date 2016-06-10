<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DpBehat\Data\Factory;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Usergroup;
use Doctrine\ORM\EntityManager;
use DpBehat\Data\DataContext;

/**
 * Class PersonFactories.
 */
class PersonFactories
{
    /**
     * @param string $role
     * @param array  $data
     *
     * @throws \Exception
     *
     * @return Person
     */
    public static function create($role, array $data)
    {
        $email = array_key_exists('email', $data) ? $data['email'] : uniqid().'@deskpro.com';

        switch ($role) {
            case 'admin':
                $person = self::createAdmin($email);
                break;
            case 'agent':
                $person = self::createAgent($email);
                break;
            case 'user':
                $person = self::createuser($email);
                break;
            default:
                throw new \Exception("Unknown role $role");
        }

        return $person;
    }

    /**
     * Init user groups (DB and references).
     */
    public static function initUsergroups(EntityManager $em)
    {
        $groupsData = [
            ['sys_name' => Usergroup::EVERYONE, 'is_agent_group' => 0],
            ['sys_name' => Usergroup::REGISTERED, 'is_agent_group' => 0],
            ['sys_name' => Usergroup::AGENT_ALL_SAFE_PERM, 'is_agent_group' => 1],
            ['sys_name' => Usergroup::AGENT_ALL_PERM, 'is_agent_group' => 1],
        ];
        foreach ($groupsData as $data) {
            $group = SimpleFactory::create(Usergroup::class, [
                'title'          => $data['sys_name'],
                'note'           => $data['sys_name'],
                'sys_name'       => $data['sys_name'],
                'is_agent_group' => $data['is_agent_group'],
                'is_enabled'     => 1,
            ]);

            $em->persist($group);
            $em->flush($group);

            DataContext::setReference("{$data['sys_name']}_group", $group);
        }
    }

    /**
     * @return Person
     */
    private static function createAdmin($email)
    {
        /** @var Person $admin */
        $admin = SimpleFactory::create(Person::class, [
            'first_name'   => 'Admin',
            'last_name'    => 'Admin',
            'is_user'      => true,
            'is_confirmed' => true,
            'is_agent'     => true,
            'can_agent'    => true,
            'can_admin'    => true,
            'password'     => 'password',
        ]);

        $admin->setEmail($email, true);
        $admin->addUsergroup(self::getUsergroup(Usergroup::EVERYONE));
        $admin->addUsergroup(self::getUsergroup(Usergroup::REGISTERED));
        $admin->addUsergroup(self::getUsergroup(Usergroup::AGENT_ALL_SAFE_PERM));
        $admin->addUsergroup(self::getUsergroup(Usergroup::AGENT_ALL_PERM));

        return $admin;
    }

    /**
     * @return Person
     */
    private static function createAgent($email)
    {
        /** @var Person $agent */
        $agent = SimpleFactory::create(Person::class, [
            'first_name'   => 'Agent',
            'last_name'    => 'Agent',
            'is_user'      => true,
            'is_confirmed' => true,
            'is_agent'     => true,
            'can_agent'    => false,
            'can_admin'    => false,
            'password'     => 'password',
        ]);
        $agent->setEmail($email, true);
        $agent->addUsergroup(self::getUsergroup(Usergroup::EVERYONE));
        $agent->addUsergroup(self::getUsergroup(Usergroup::REGISTERED));
        $agent->addUsergroup(self::getUsergroup(Usergroup::AGENT_ALL_SAFE_PERM));

        return $agent;
    }

    /**
     * @return Person
     */
    private static function createUser($email)
    {
        /* @var Person $agent */
        $user = SimpleFactory::create(Person::class, [
            'first_name'   => 'User',
            'last_name'    => 'User',
            'is_user'      => true,
            'is_confirmed' => true,
            'is_agent'     => false,
            'can_agent'    => false,
            'can_admin'    => false,
            'password'     => 'password',
        ]);
        $user->setEmail($email, true);
        $user->addUsergroup(self::getUsergroup(Usergroup::EVERYONE));
        $user->addUsergroup(self::getUsergroup(Usergroup::REGISTERED));

        return $user;
    }

    /**
     * @param string $name
     *
     * @return Usergroup
     */
    private static function getUsergroup($name)
    {
        return DataContext::getReference("{$name}_group");
    }
}
