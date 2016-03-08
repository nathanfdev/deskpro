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

use Application\DeskPRO\Entity\CustomDefTicket;
use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\TicketLayout\Layout;
use Application\DeskPRO\TicketLayout\LayoutField;
use DeskPRO\Bundle\AppBundle\DataFixtures\DeskProAbstractFixture;
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
class TicketProfileFixture extends DeskProAbstractFixture implements OrderedFixtureInterface
{
    private static $ref_cnt = 1;

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
    private $agent_teams;

    /**
     * @var RandomFileFromDir
     */
    private $ava_files;

    /**
     * @var array
     */
    private $known_users = [
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
        return 60;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->em      = $this->container->get('doctrine.orm.entity_manager');

        $this->ava_files = new RandomFileFromDir(DP_ROOT.'/src/DeskPRO/Bundle/AppBundle/DataFixtures/res/avatars');

        $this->initRecords();
        $this->initLayouts();
        $this->initContent();
    }

    private function initRecords()
    {
        $this->agents = $this->em->createQuery('SELECT p FROM DeskPRO:Person p WHERE p.is_agent = TRUE')
            ->execute();
        $this->agent_teams = $this->em->createQuery('SELECT t FROM DeskPRO:AgentTeam t')->execute();
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

    ####################################################################################################################
    # Users, Orgs and Tickets
    ####################################################################################################################

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

    private function setupPerson(Organization $org, array $person_info = [])
    {
        $person_info                 = array_merge(array_shift($this->known_users), $person_info);
        $person_info['organization'] = $org;

        $person = Person::newContactPerson($person_info);
        $person->setPassword('password');

        $ava_file = $this->ava_files->next();
        $ava      = $this->container->get('deskpro.blob_storage')->createBlobRecordFromFile(
            $ava_file->getRealPath(),
            $ava_file->getFilename(),
            ContentTypes::getContentTypeFromFilename($ava_file->getFilename())
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

    private function makeTicket(Person $person, $department_ref, $status)
    {
        $department = $this->getReference('department.'.$department_ref);

        #------------------------------
        # Ticket
        #------------------------------

        $subj = $this->faker->realText($this->faker->numberBetween(40, 60));

        $ticket = new Ticket();
        $ticket->disableAutoTicketProcess();
        $ticket->person               = $person;
        $ticket->department           = $department;
        $ticket->ref                  = 'DEMO-'.self::$ref_cnt++;
        $ticket->agent                = $this->faker->randomElement($this->agents);
        $ticket->agent_team           = $this->faker->randomElement($this->agent_teams);
        $ticket->urgency              = $this->faker->numberBetween(1, 10);
        $ticket->status               = $status;
        $ticket->subject              = $subj;
        $ticket->original_subject     = $subj;
        $ticket->date_created         = $this->faker->dateTimeThisYear;
        $ticket->date_last_user_reply = $ticket->date_created;

        $this->em->persist($ticket);
        $this->em->flush();

        #------------------------------
        # Messages
        #------------------------------

        $num   = $this->faker->numberBetween(1, 8);
        $batch = [];
        for ($i = 0; $i < $num; ++$i) {
            $as_agent = $this->faker->boolean(50);

            $text   = [];
            $text[] = $this->faker->realText($this->faker->numberBetween(100, 300));

            if ($this->faker->boolean(50)) {
                $text[] = $this->faker->realText($this->faker->numberBetween(100, 300));
            }
            if ($this->faker->boolean(20)) {
                $text[] = '<img src="'.$this->faker->imageUrl(200, 100, 'cats').'" />';
            }
            if ($this->faker->boolean(50)) {
                $text[] = '<strong>'.$this->faker->realText($this->faker->numberBetween(10, 150)).'</strong>';
            }
            if ($this->faker->boolean(10)) {
                $text[] = $this->faker->realText($this->faker->numberBetween(150, 800));
            }

            $text = implode('<br/><br/>', $text);

            if ($as_agent) {
                $author = $this->faker->randomElement($this->agents);
            } else {
                $author = $person;
            }

            $is_note = ($as_agent && $this->faker->boolean(10));

            $batch[] = [
                'ticket_id'    => $ticket->getId(),
                'person_id'    => $author->getId(),
                'date_created' => date(
                    'Y-m-d H:i:s',
                    $ticket->date_created->getTimestamp() + $this->faker->numberBetween(900, 14400)
                ),
                'creation_system' => 'web',
                'is_agent_note'   => (int) $is_note,
                'ip_address'      => $this->faker->ipv4,
                'hostname'        => $this->faker->domainName,
                'geo_country'     => $this->faker->countryCode,
                'message_hash'    => sha1(uniqid('', true)),
                'message'         => $text,
            ];
        }

        $this->db->batchInsert('tickets_messages', $batch, true);

        #------------------------------
        # Field Data
        #------------------------------

        /** @var CustomDefTicket[] $fields */
        $fields = TicketFieldsFixture::$fields[$department_ref];

        $batch = [];
        foreach ($fields as $f) {
            $num = 1;
            if ($f->getOption('multiple')) {
                $num = $this->faker->numberBetween(1, count($f->getChildren()));
            }

            for ($x = 0; $x < $num; ++$x) {
                $row_data = [
                    'ticket_id'     => $ticket->getId(),
                    'field_id'      => $f->getId(),
                    'root_field_id' => $f->getId(),
                    'value'         => 0,
                    'input'         => '',
                ];
                switch ($f->getTypeName()) {
                    case 'text':
                        $row_data['input'] = $this->faker->realText($this->faker->numberBetween(10, 80));
                        break;
                    case 'textarea':
                        $row_data['input'] = $this->faker->realText($this->faker->numberBetween(20, 500));
                        break;
                    case 'date':
                    case 'datetime':
                        $row_data['value'] = time();
                        break;
                    case 'choice':
                        $opt                  = $this->faker->randomElement($f->getChildren()->toArray());
                        $row_data['field_id'] = $opt->getId();
                        $row_data['value']    = 1;
                        break;
                    default:
                        throw new \InvalidArgumentException();
                }

                if ($row_data) {
                    $batch[] = $row_data;
                }
            }
        }

        if ($batch) {
            $this->db->batchInsert(self::TABLE_CUSTOM_DATA_TICKET, $batch, true);
        }
    }
}
