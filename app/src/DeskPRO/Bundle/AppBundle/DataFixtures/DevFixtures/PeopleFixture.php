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

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Application\DeskPRO\DBAL\Connection;
use Orb\Util\Strings;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PeopleFixtures extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
{
    private $num_people = 500;
    private $num_agents = 10;

    /**
     * @var \Faker\Generator
     */
    private $faker;

    /**
     * @var ObjectManager
     */
    private $manager;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var Connection
     */
    private $db;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * DpFixture constructor.
     */
    public function __construct()
    {
        $this->faker = \Faker\Factory::create();
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 40;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->db = $this->container->get('database_connection');

        $this->loadPeople($this->num_agents, true);
        $this->loadPeople($this->num_people, false);
    }

    private function loadPeople($num, $is_agent)
    {
        $batch = [];

        $creation_string = 'api.dev.' . time();

        for ($i = 0; $i < $num; $i++) {
            $fname = $this->faker->firstName;
            $lname = $this->faker->lastName;

            $batch[] = [
                'is_contact' => 1,
                'is_user' => 1,
                'is_agent' => (int)$is_agent,
                'can_agent' => (int)1,
                'is_confirmed' => 1,
                'is_agent_confirmed' => 1,
                'creation_system' => $creation_string,
                'name' => "$fname $lname",
                'first_name' => $fname,
                'last_name' => $lname,
                'secret_string' => Strings::random(40),
                'timezone' => $this->faker->timezone,
                'password' => '$2a$11$dsjhQYwUUT/W7tqbp4D2iuqksIV4gpNxFmEvbUDh7Li96R6WO5u02',
                'password_scheme' => 'bcrypt',
                'salt' => Strings::random(40),
                'date_created' => $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'date_password_set' => $this->faker->dateTimeThisMonth->format('Y-m-d H:i:s'),
            ];
        }

        $this->db->batchInsert('people', $batch);

        $people_ids = $this->db->fetchAllCol("SELECT id FROM people WHERE creation_system = ?", array($creation_string));

        $batch = [];
        foreach ($people_ids as $pid) {
            $email = $this->faker->safeEmail;
            list (, $domain) = explode('@', $email);

            $batch[] = [
                'person_id'      => $pid,
                'email'          => $email,
                'email_domain'   => $domain,
                'is_validated'   => 1,
                'date_created'   => $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'date_validated' => $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
            ];
        }

        $this->db->batchInsert('people_emails', $batch, true);
        $this->db->executeUpdate("
            UPDATE people
            JOIN people_emails ON (people_emails.person_id = people.id)
            SET people.primary_email_id = people_emails.id
            WHERE people.primary_email_id IS NULL
        ");
    }
}
