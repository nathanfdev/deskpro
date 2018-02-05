<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\DataFixtures\DevFixtures;

use Application\DeskPRO\Entity\CustomDefTicket;
use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\TicketLayout\Layout;
use Application\DeskPRO\TicketLayout\LayoutField;
use DeskPRO\Bundle\AppBundle\DataFixtures\AbstractDpFixture;
use DeskPRO\Bundle\AppBundle\DataFixtures\DevFixtures\CustomFields\CustomDataGenerator;
use DeskPRO\Bundle\AppBundle\DataFixtures\DevFixtures\CustomFields\TicketFieldsFixture;
use DeskPRO\Bundle\AppBundle\DataFixtures\Tools\RandomFileFromDir;
use DeskPRO\Bundle\AppBundle\Form\FormFields;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Orb\Data\ContentTypes;
use Orb\Types\JsonObjectSerializer;

/**
 * This inserts some ticket fields, a test profile, and some test tickets. This is useful
 * for testing a somewhat 'real life' scenario.
 *
 * Setup details:
 * - Four new departments
 * - Various fields
 * - Department layouts
 *
 * Profiles:
 * - 15 users
 * - 3 orgs (5 users per org, 1 of them is a manager)
 * - 6 tickets per user
 */
class TicketProfileFixture extends AbstractDpFixture implements OrderedFixtureInterface
{
    private static $refCnt = 1;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var int[]
     */
    private $agents;

    /**
     * @var int[]
     */
    private $agentTeams;

    /**
     * @var RandomFileFromDir
     */
    private $avaFiles;

    /**
     * @var array
     */
    private $knownUsers = [
        ['first_name' => 'Scott', 'last_name' => 'Jordan', 'email' => 'demo-user1@example.com'],
        ['first_name' => 'Berk', 'last_name' => 'Clarke', 'email' => 'demo-user2@example.com'],
        ['first_name' => 'Denton', 'last_name' => 'Pace', 'email' => 'demo-user3@example.com'],
        ['first_name' => 'Francis', 'last_name' => 'Shields', 'email' => 'demo-user4@example.com'],
        ['first_name' => 'Mara', 'last_name' => 'Reilly', 'email' => 'demo-user5@example.com'],
        ['first_name' => 'Brady', 'last_name' => 'Bullock', 'email' => 'demo-user6@example.com'],
        ['first_name' => 'Abigail', 'last_name' => 'Bean', 'email' => 'demo-user7@example.com'],
        ['first_name' => 'Rosalyn', 'last_name' => 'Zimmerman', 'email' => 'demo-user8@example.com'],
        ['first_name' => 'Melinda', 'last_name' => 'Stanley', 'email' => 'demo-user9@example.com'],
        ['first_name' => 'Quail', 'last_name' => 'Scott', 'email' => 'demo-user10@example.com'],
        ['first_name' => 'Rhiannon', 'last_name' => 'Bartlett', 'email' => 'demo-user11@example.com'],
        ['first_name' => 'Martina', 'last_name' => 'Delaney', 'email' => 'demo-user12@example.com'],
        ['first_name' => 'Ila', 'last_name' => 'Knox', 'email' => 'demo-user13@example.com'],
        ['first_name' => 'Pascale', 'last_name' => 'Caldwell', 'email' => 'demo-user14@example.com'],
        ['first_name' => 'Paki', 'last_name' => 'Andrews', 'email' => 'demo-user15@example.com'],
        ['first_name' => 'Kuame', 'last_name' => 'Mcclain', 'email' => 'demo-user16@example.com'],
        ['first_name' => 'Steven', 'last_name' => 'Obrien', 'email' => 'demo-user17@example.com'],
        ['first_name' => 'Brandon', 'last_name' => 'Bush', 'email' => 'demo-user18@example.com'],
        ['first_name' => 'Serena', 'last_name' => 'Meadows', 'email' => 'demo-user19@example.com'],
        ['first_name' => 'Buffy', 'last_name' => 'Hebert', 'email' => 'demo-user20@example.com'],
    ];

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 90;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->em      = $this->container->get('doctrine.orm.entity_manager');

        $this->avaFiles = new RandomFileFromDir(DP_ROOT.'/src/DeskPRO/Bundle/AppBundle/DataFixtures/res/avatars');

        $this->initRecords();
        $this->initLayouts();
        $this->initContent();
    }

    private function initRecords()
    {
        $this->agents = $this->em->createQuery('SELECT p FROM DeskPRO:Person p WHERE p.is_agent = TRUE')
            ->execute();
        $this->agentTeams = $this->em->createQuery('SELECT t FROM DeskPRO:AgentTeam t')->execute();
    }

    protected function initLayouts()
    {
        $refs = [
            'widgets',
            'regulation',
            'control',
            'hotdogs',
        ];

        foreach ($refs as $ref) {
            $department = $this->getReference('department.'.$ref);
            $fields     = TicketFieldsFixture::$fields[$ref];

            $layout = new Layout();
            $layout->add(new LayoutField('person'));
            $layout->add(new LayoutField('department'));
            $layout->add(new LayoutField('department'));
            $layout->add(new LayoutField('subject'));

            foreach ($fields as $f) {
                $layout->add(new LayoutField('ticket_field', $f->getId()));
            }

            $layout->add(new LayoutField('message'));
            $layout->add(new LayoutField(FormFields::ATTACHMENTS));

            $enc = JsonObjectSerializer::serialize($layout);

            $insert_layout = [
                'department_id' => $department->getId() ?: null,
                'is_enabled'    => 1,
                'user_layout'   => $enc,
                'agent_layout'  => $enc,
                'date_updated'  => date('Y-m-d H:i:s'),
            ];

            $this->db->replace('ticket_layouts', $insert_layout);
        }
    }

    //###################################################################################################################
    // Users, Orgs and Tickets
    //###################################################################################################################

    protected function initContent()
    {
        $this->setupOrg('Iced Caps');
        $this->setupOrg('Gargantuan Space Travel');
        $this->setupOrg('Suz\'s Confectionery');
    }

    private function setupOrg($name)
    {
        $org       = new Organization();
        $org->name = $name;

        $this->em->persist($org);
        $this->em->flush();

        $this->setupPerson($org, ['organization_manager' => true]);
        $this->setupPerson($org);
        $this->setupPerson($org);
    }

    private function setupPerson(Organization $org, array $personInfo = [])
    {
        $personInfo                 = array_merge(array_shift($this->knownUsers), $personInfo);
        $personInfo['organization'] = $org;

        $person = Person::newContactPerson($personInfo);
        $person->setPassword('password');

        $avaFile = $this->avaFiles->next();
        $ava     = $this->container->get('deskpro.blob_storage')
            ->createBlobRecordFromFile(
                $avaFile->getRealPath(),
                $avaFile->getFilename(),
                ContentTypes::getContentTypeFromFilename($avaFile->getFilename())
            );
        $person->picture_blob = $ava;

        $this->em->persist($person);
        $this->em->flush();

        $this->makeTicket($person, 'widgets', 'resolved');
        $this->makeTicket($person, 'widgets', 'awaiting_agent');

        $this->makeTicket($person, 'regulation', 'awaiting_user');
        $this->makeTicket($person, 'control', 'resolved');

        $this->makeTicket($person, 'hotdogs', 'awaiting_agent');
        $this->makeTicket($person, 'hotdogs', 'awaiting_user');
    }

    private function makeTicket(Person $person, $departmentRef, $status)
    {
        $department = $this->getReference('department.'.$departmentRef);

        //------------------------------
        // Ticket
        //------------------------------

        $subj = $this->faker->sentence(4);
        $date = $this->faker->dateTimeBetween('-2 months', '-2days');

        $ticket = new Ticket();
        $ticket->disableAutoTicketProcess();
        $ticket->person               = $person;
        $ticket->department           = $department;
        $ticket->ref                  = 'DEMO-'.self::$refCnt++;
        $ticket->brand                = $this->getReference('brand');
        $ticket->agent                = $this->faker->randomElement($this->agents);
        $ticket->agent_team           = $this->faker->randomElement($this->agentTeams);
        $ticket->urgency              = $this->faker->numberBetween(1, 10);
        $ticket->status               = $status;
        $ticket->subject              = $subj;
        $ticket->original_subject     = $subj;
        $ticket->date_created         = $date;
        $ticket->date_last_user_reply = $ticket->date_created;

        $this->em->persist($ticket);
        $this->em->flush();

        //------------------------------
        // Field Data
        //------------------------------

        /** @var CustomDefTicket[] $fields */
        $fields             = TicketFieldsFixture::$fields[$departmentRef];
        $customDefGenerator = new CustomDataGenerator($this->faker);

        $batch = [];
        foreach ($fields as $f) {
            $customDefGenerator->addCustomDefData($batch, $f, 'ticket_id', $ticket->getId());
        }

        if ($batch) {
            $this->db->batchInsert(self::TABLE_CUSTOM_DATA_TICKET, $batch, true);
        }
    }
}
