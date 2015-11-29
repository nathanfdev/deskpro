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
namespace DeskPRO\Bundle\AppBundle\DataFixtures\DevFixtures;

use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity\LabelDef;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Orb\Util\Strings;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PeopleFixture extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
{
    private $num_people = 500;
    private $num_agents = 10;
    private $num_orgs   = 75;
    private $num_labels = 100;
    private $max_notes  = 3;

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
     * @var string[]
     */
    private $labels;

    /**
     * @var array
     */
    private $agent_ids = [];

    /**
     * @var array
     */
    private $people_ids = [];

    /**
     * @var array
     */
    private $org_ids = [];

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
        $this->db      = $this->container->get('database_connection');

        $this->loadLabels();
        $this->loadOrgs();
        $this->loadOrgProps();

        $this->loadPeople($this->num_agents, true);
        $this->agent_ids = $this->db->fetchAllCol('SELECT id FROM people');

        $this->loadPeople($this->num_people, false);
        $this->people_ids = $this->db->fetchAllCol('SELECT id FROM people');

        $this->loadPeopleProps();
    }

    private function loadLabels()
    {
        $label_type = LabelDef::TYPE_PEOPLE;
        $this->faker->unique(true);

        $batch = [];

        for ($i = 0; $i < $this->num_labels; ++$i) {
            $l = $this->faker->unique()->company;
            if ($l) {
                $l       = strtolower($l);
                $batch[] = array('label_type' => $label_type, 'label' => $l, 'color' => $this->faker->hexColor, 'total' => 0);
            }
        }

        $this->db->batchInsert('label_defs', $batch, true);

        $this->labels = $this->db->fetchAllCol('SELECT label FROM label_defs WHERE label_type = ?', array($label_type));
    }

    private function loadPeople($num, $is_agent)
    {
        $batch = [];

        $creation_string = 'api.dev.'.time();

        for ($i = 0; $i < $num; ++$i) {
            $fname = $this->faker->firstName;
            $lname = $this->faker->lastName;

            $batch[] = [
                'organization_id'   => $this->faker->randomElement($this->org_ids),
                'is_contact'        => 1,
                'is_user'           => 1,
                'is_agent'          => (int) $is_agent,
                'can_agent'         => (int) 1,
                'is_confirmed'      => 1,
                'creation_system'   => $creation_string,
                'name'              => "$fname $lname",
                'first_name'        => $fname,
                'last_name'         => $lname,
                'secret_string'     => Strings::random(40),
                'timezone'          => $this->faker->timezone,
                'password'          => '$2a$11$dsjhQYwUUT/W7tqbp4D2iuqksIV4gpNxFmEvbUDh7Li96R6WO5u02',
                'password_scheme'   => 'bcrypt',
                'salt'              => Strings::random(40),
                'date_created'      => $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'date_password_set' => $this->faker->dateTimeThisMonth->format('Y-m-d H:i:s'),
            ];
        }

        $this->db->batchInsert('people', $batch);

        $people_ids = $this->db->fetchAllCol('SELECT id FROM people WHERE creation_system = ?', array($creation_string));

        $batch = [];
        foreach ($people_ids as $pid) {
            $email          = $this->faker->safeEmail;
            list(, $domain) = explode('@', $email);

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
        $this->db->executeUpdate('
            UPDATE people
            JOIN people_emails ON (people_emails.person_id = people.id)
            SET people.primary_email_id = people_emails.id
            WHERE people.primary_email_id IS NULL
        ');
    }

    private function loadOrgs()
    {
        $batch = [];

        for ($i = 0; $i < $this->num_orgs; ++$i) {
            $batch[] = [
                'name'         => $this->faker->company,
                'summary'      => $this->faker->realText($this->faker->numberBetween(10, 500)),
                'importance'   => 1,
                'date_created' => $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
            ];
        }

        $this->db->batchInsert('organizations', $batch);

        $this->org_ids = $this->db->fetchAllCol('SELECT id FROM organizations');
    }

    private function loadOrgProps()
    {
        $notes_batch = [];

        foreach ($this->org_ids as $org_id) {
            $num = $this->faker->numberBetween(1, $this->max_notes);
            for ($i = 0; $i < $num; ++$i) {
                $notes_batch[] = [
                    'organization_id' => $org_id,
                    'agent_id'        => $this->faker->randomElement($this->agent_ids),
                    'date_created'    => $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                    'note'            => $this->faker->realText($this->faker->numberBetween(10, 500)),
                ];
            }
        }

        if ($notes_batch) {
            $this->db->batchInsert('organization_notes', $notes_batch);
        }
    }

    private function loadPeopleProps()
    {
        $labels_batch = [];
        $notes_batch  = [];

        foreach ($this->people_ids as $people_id) {
            foreach ($this->faker->randomElements($this->labels, $this->faker->numberBetween(1, 5)) as $l) {
                $labels_batch[] = array('person_id' => $people_id, 'label' => $l);
            }

            $num = $this->faker->numberBetween(1, $this->max_notes);
            for ($i = 0; $i < $num; ++$i) {
                $notes_batch[] = [
                    'person_id'    => $people_id,
                    'agent_id'     => $this->faker->randomElement($this->agent_ids),
                    'date_created' => $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                    'note'         => $this->faker->realText($this->faker->numberBetween(10, 500)),
                ];
            }
        }

        if ($labels_batch) {
            $this->db->batchInsert('labels_people', $labels_batch, true);
        }
        if ($notes_batch) {
            $this->db->batchInsert('people_notes', $notes_batch);
        }
    }
}
