<?php

namespace DeskPRO\Bundle\AppBundle\DataFixtures\DevFixtures;

use Application\DeskPRO\Entity\LabelDef;
use Application\DeskPRO\Entity\LabelOrganization;
use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\OrganizationNote;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Usergroup;
use DeskPRO\Bundle\AppBundle\DataFixtures\AbstractDpFixture;
use DeskPRO\Bundle\AppBundle\DataFixtures\Tools\RandomFileFromDir;
use DeskPRO\Bundle\AppBundle\Entity\PersonOnboarding;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Orb\Data\ContentTypes;
use Orb\Util\Strings;

class PeopleFixture extends AbstractDpFixture implements OrderedFixtureInterface
{
    private $numPeople = 500;
    private $numAgents = 10;
    private $numOrgs   = 75;
    private $numLabels = 100;
    private $maxNotes  = 3;

    /**
     * @var string[]
     */
    private $labels;

    /**
     * @var array
     */
    private $agentIds = [];

    /**
     * @var array
     */
    private $peopleIds = [];

    /**
     * @var array
     */
    private $orgIds = [];

    /**
     * @var RandomFileFromDir
     */
    private $avaFiles;

    /**
     * @var RandomFileFromDir
     */
    private $avaPeopleFiles;

    /** @var Usergroup[] */
    private $userGroups;

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
        $this->manager  = $manager;
        $this->avaFiles = new RandomFileFromDir(
            DP_ROOT.'/src/DeskPRO/Bundle/AppBundle/DataFixtures/res/avatars'
        );
        $this->avaPeopleFiles = new RandomFileFromDir(
            DP_ROOT.'/src/DeskPRO/Bundle/AppBundle/DataFixtures/res/avatars_people'
        );
        $this->userGroups = $this->getExtraUserGroups();

        $this->loadLabels();
        $this->loadOrgs($manager);

        $this->loadPeople($this->numAgents, true);
        $this->agentIds = $this->fetchIds(self::TABLE_PEOPLE);

        $this->loadPeople($this->numPeople, false);
        $this->peopleIds = $this->fetchIds(self::TABLE_PEOPLE);

        $this->loadOrgProps($manager);
        $this->addExtraGroups();
        $this->loadPeopleProps($manager);

        $this->createOrgExample();
    }

    private function loadLabels()
    {
        $labelType = LabelDef::TYPE_PEOPLE;
        $this->faker->unique(true);

        $batch = [];

        for ($i = 0; $i < $this->numLabels; ++$i) {
            $l = str_replace(',', '', $this->faker->unique()->company);
            if ($l) {
                $l       = strtolower($l);
                $batch[] = [
                    'label_type' => $labelType,
                    'label'      => $l,
                    'color'      => $this->faker->hexColor,
                    'total'      => 0,
                ];
            }
        }

        $this->db->batchInsert('label_defs', $batch, true);

        $this->labels = $this->db->fetchAllCol('SELECT label FROM label_defs WHERE label_type = ?', [$labelType]);
    }

    private function loadPeople($num, $is_agent)
    {
        $batch = [];

        $creationString = 'api.dev.'.time();

        for ($i = 0; $i < $num; ++$i) {
            $fname = $this->faker->firstName;
            $lname = $this->faker->lastName;

            $avaFile = null;
            if ($is_agent) {
                $avaFile = $this->avaPeopleFiles->next();
            } elseif ($this->faker->boolean(30)) {
                $avaFile = $this->avaFiles->next();
            }

            $avaId = null;
            if ($avaFile) {
                $ava = $this->container->get('deskpro.blob_storage')->createBlobRowFromFile(
                    $avaFile->getRealPath(),
                    $avaFile->getFilename(),
                    ContentTypes::getContentTypeFromFilename($avaFile->getFilename())
                );
                $avaId = $ava['id'];
            }

            $batch[] = [
                'organization_id'   => $this->faker->randomElement($this->orgIds),
                'picture_blob_id'   => $avaId,
                'is_contact'        => 1,
                'is_user'           => 1,
                'is_agent'          => (int) $is_agent,
                'can_agent'         => (int) 1,
                'is_confirmed'      => 1,
                'creation_system'   => $creationString,
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

        $peopleIds = $this->db->fetchAllCol('SELECT id FROM people WHERE creation_system = ?', [$creationString]);

        $batch = [];
        foreach ($peopleIds as $pid) {
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

    private function addExtraGroups()
    {
        $people = $this->manager->getRepository(Person::class)->findAll();

        /** @var Person $person */
        foreach ($people as $person) {
            if ($this->faker->boolean(33)) {
                foreach ($this->userGroups as $userGroup) {
                    if ($this->faker->boolean(50)) {
                        $person->addUsergroup($userGroup);
                    }
                }
                $this->manager->persist($person);
            }
        }
        $this->manager->flush();
    }

    private function loadOrgs(ObjectManager $manager)
    {
        for ($i = 0; $i < $this->numOrgs; ++$i) {
            $org = new Organization();
            $org
                ->setName($this->faker->company)
                ->setSummary($this->faker->realText($this->faker->numberBetween(10, 500)))
                ->setImportance(1)
                ->setDateCreated($this->faker->dateTimeThisYear)
            ;

            if ($this->faker->boolean(33)) {
                foreach ($this->userGroups as $userGroup) {
                    if ($this->faker->boolean(50)) {
                        $org->addUserGroup($userGroup);
                    }
                }
            }

            $manager->persist($org);
        }

        $manager->flush();
        $this->orgIds = $this->fetchIds(self::TABLE_ORGANIZATIONS);
    }

    private function loadOrgProps(ObjectManager $manager)
    {
        $organizations = $manager->getRepository(Organization::class)->findAll();
        $agents        = $manager->getRepository(Person::class)->findBy(['is_agent' => true]);

        foreach ($organizations as $organization) {
            foreach ($this->faker->randomElements($this->labels, $this->faker->numberBetween(1, 5)) as $label) {
                $labelEntity = new LabelOrganization();
                $labelEntity
                    ->setLabel($label)
                    ->setOrganization($organization)
                ;

                $manager->persist($labelEntity);
            }

            for ($i = 0; $i < $this->faker->numberBetween(1, $this->maxNotes); ++$i) {
                $orgNote = new OrganizationNote();
                $orgNote
                    ->setAgent($this->faker->randomElement($agents))
                    ->setOrganization($organization)
                    ->setDateCreated($this->faker->dateTimeThisYear)
                    ->setNote($this->faker->realText($this->faker->numberBetween(10, 500)))
                ;

                $manager->persist($orgNote);
            }
        }

        $manager->flush();
    }

    private function loadPeopleProps(ObjectManager $manager)
    {
        $labelsBatch = [];
        $notesBatch  = [];
        $onboardings = [];

        /** @var PersonOnboarding[] $existingOnboardings */
        $existingOnboardings = $manager->getRepository(PersonOnboarding::class)
            ->findBy(['onboardingClass' => 'topbar']);

        foreach ($this->peopleIds as $peopleId) {
            foreach ($this->faker->randomElements($this->labels, $this->faker->numberBetween(1, 5)) as $l) {
                $labelsBatch[] = ['person_id' => $peopleId, 'label' => $l];
            }

            $num = $this->faker->numberBetween(1, $this->maxNotes);
            for ($i = 0; $i < $num; ++$i) {
                $notesBatch[] = [
                    'person_id'    => $peopleId,
                    'agent_id'     => $this->faker->randomElement($this->agentIds),
                    'date_created' => $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                    'note'         => $this->faker->realText($this->faker->numberBetween(10, 500)),
                ];
            }
        }

        foreach ($this->agentIds as $agentId) {
            foreach ($existingOnboardings as $onboarding) {
                if ($onboarding->getPerson()->getId() == $agentId) {
                    continue 2;
                }
            }
            $onboardings[$agentId] = [
                'person_id'        => $agentId,
                'onboarding_class' => 'topbar',
                'application'      => 'Agent',
            ];
        }

        if ($labelsBatch) {
            $this->db->batchInsert('labels_people', $labelsBatch, true);
        }
        if ($notesBatch) {
            $this->db->batchInsert('people_notes', $notesBatch);
        }
        if ($onboardings) {
            $this->db->batchInsert('person_onboarding', $onboardings);
        }
    }

    private function createOrgExample()
    {
        // the content publisher agent guy
        $publisher            = new Person();
        $publisher->name      = 'Corporate Content';
        $publisher->can_agent = true;
        $publisher->is_agent  = true;
        $publisher->addEmailAddressString('content.publisher@deskprodemo.com');
        $publisher->setPassword('publisher');

        $this->manager->persist($publisher);
        $this->manager->flush();

        $this->setReference('person.publisher', $publisher);

        // an org
        $organization = new Organization();
        $organization->setName('Mana Publishing');
        $organization->setImportance(5);

        $this->setReference('org.mana', $organization);

        // a regular dude
        $person       = new Person();
        $person->name = 'Joe Kool';
        $person->addEmailAddressString('joe@deskprodemo.com');
        $person->setPassword('joe');
        $person->setOrganization($organization);

        $this->setReference('person.joe', $person);

        // an organization
        $mana       = new Person();
        $mana->name = 'Mana Ger';
        $mana->addEmailAddressString('manager@deskprodemo.com');
        $mana->setPassword('manager');
        $mana->setOrganization($organization);
        $mana->organization_manager = true;
        $mana->setOrganizationPosition('MANAGER');

        $this->setReference('person.joes_manager', $mana);

        $this->manager->persist($mana);
        $this->manager->persist($organization);
        $this->manager->flush();

        $this->manager->persist($person);
        $this->manager->flush();
    }

    /**
     * @return mixed
     */
    private function getExtraUserGroups()
    {
        $criteria = new Criteria();
        $criteria->where($criteria->expr()->gt('id', 4));
        $userGroups = $this->manager->getRepository(Usergroup::class)->matching($criteria);

        return $userGroups;
    }
}
