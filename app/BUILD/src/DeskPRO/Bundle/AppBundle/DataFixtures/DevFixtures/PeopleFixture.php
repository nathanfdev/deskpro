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

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\AppBundle\DataFixtures\DevFixtures;

use Application\DeskPRO\Entity\LabelDef;
use DeskPRO\Bundle\AppBundle\DataFixtures\DeskProAbstractFixture;
use DeskPRO\Bundle\AppBundle\DataFixtures\Tools\RandomFileFromDir;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Orb\Data\ContentTypes;
use Orb\Util\Strings;

class PeopleFixture extends DeskProAbstractFixture implements OrderedFixtureInterface
{
    private $num_people = 500;
    private $num_agents = 10;
    private $num_orgs   = 75;
    private $num_labels = 100;
    private $max_notes  = 3;

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
     * @var RandomFileFromDir
     */
    private $ava_files;

    /**
     * @var RandomFileFromDir
     */
    private $ava_people_files;

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 50;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager   = $manager;
        $this->ava_files = new RandomFileFromDir(
            DP_ROOT.'/src/DeskPRO/Bundle/AppBundle/DataFixtures/res/avatars'
        );
        $this->ava_people_files = new RandomFileFromDir(
            DP_ROOT.'/src/DeskPRO/Bundle/AppBundle/DataFixtures/res/avatars_people'
        );

        $this->loadLabels();
        $this->loadOrgs();
        $this->loadOrgProps();

        $this->loadPeople($this->num_agents, true);
        $this->agent_ids = $this->fetchIds(self::TABLE_PEOPLE);

        $this->loadPeople($this->num_people, false);
        $this->people_ids = $this->fetchIds(self::TABLE_PEOPLE);

        $this->loadPeopleProps();

        $this->createOrgExample();
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
                $batch[] = [
                    'label_type' => $label_type,
                    'label'      => $l,
                    'color'      => $this->faker->hexColor,
                    'total'      => 0,
                ];
            }
        }

        $this->db->batchInsert('label_defs', $batch, true);

        $this->labels = $this->db->fetchAllCol('SELECT label FROM label_defs WHERE label_type = ?', [$label_type]);
    }

    private function loadPeople($num, $is_agent)
    {
        $batch = [];

        $creation_string = 'api.dev.'.time();

        for ($i = 0; $i < $num; ++$i) {
            $fname = $this->faker->firstName;
            $lname = $this->faker->lastName;

            $ava_file = null;
            if ($is_agent) {
                $ava_file = $this->ava_people_files->next();
            } elseif ($this->faker->boolean(30)) {
                $ava_file = $this->ava_files->next();
            }

            $ava_id = null;
            if ($ava_file) {
                $ava = $this->container->get('deskpro.blob_storage')->createBlobRowFromFile(
                    $ava_file->getRealPath(),
                    $ava_file->getFilename(),
                    ContentTypes::getContentTypeFromFilename($ava_file->getFilename())
                );
                $ava_id = $ava['id'];
            }

            $batch[] = [
                'organization_id'   => $this->faker->randomElement($this->org_ids),
                'picture_blob_id'   => $ava_id,
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

        $people_ids = $this->db->fetchAllCol('SELECT id FROM people WHERE creation_system = ?', [$creation_string]);

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
        $this->db->executeUpdate(
            '
            UPDATE people
            JOIN people_emails ON (people_emails.person_id = people.id)
            SET people.primary_email_id = people_emails.id
            WHERE people.primary_email_id IS NULL
        '
        );
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
                $labels_batch[] = ['person_id' => $people_id, 'label' => $l];
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

    private function createOrgExample()
    {
        // the content publisher agent guy
        $publisher            = new \Application\DeskPRO\Entity\Person();
        $publisher->name      = 'Corporate Content';
        $publisher->can_agent = true;
        $publisher->is_agent  = true;
        $publisher->addEmailAddressString('content.publisher@deskprodemo.com');
        $publisher->setPassword('publisher');

        $this->manager->persist($publisher);
        $this->manager->flush();

        $this->setReference('person.publisher', $publisher);

        // an org
        $organization = new \Application\DeskPRO\Entity\Organization();
        $organization->setName('Mana Publishing');
        $organization->setImportance(5);

        $this->setReference('org.mana', $organization);

        // a regular dude
        $person       = new \Application\DeskPRO\Entity\Person();
        $person->name = 'Joe Kool';
        $person->addEmailAddressString('joe@deskprodemo.com');
        $person->setPassword('joe');
        $person->setOrganization($organization);

        $this->setReference('person.joe', $person);

        // an organization
        $mana       = new \Application\DeskPRO\Entity\Person();
        $mana->name = 'Mana Ger';
        $mana->addEmailAddressString('manager@deskprodemo.com');
        $mana->setPassword('manager');
        $mana->setOrganization($organization);
        $mana->organization_manager = true;
        $mana->setOrganizationPosition('MANAGER');

        $this->setReference('person.joes_manager', $person);

        $this->manager->persist($mana);
        $this->manager->persist($organization);
        $this->manager->flush();

        $this->manager->persist($person);
        $this->manager->flush();
    }
}
