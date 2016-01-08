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
use Application\DeskPRO\Entity\CustomDefTicket;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\TicketLayout\Layout;
use Application\DeskPRO\TicketLayout\LayoutField;
use DeskPRO\Bundle\AppBundle\DataFixtures\Tools\RandomFileFromDir;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Orb\Data\ContentTypes;
use Orb\Types\JsonObjectSerializer;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
class TicketProfileFixture extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
{
    private static $cnt     = 1;
    private static $ref_cnt = 1;

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
     * @var \Application\DeskPRO\ORM\EntityManager
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
     * @var \Application\DeskPRO\Entity\Department[]
     */
    private $all_departments;

    /**#@+
     * @var \Application\DeskPRO\Entity\Department
     */
    private $dep1;
    private $dep2;
    private $dep2_a;
    private $dep2_b;
    private $dep3;
    /**#@-*/

    /**
     * @var \Application\DeskPRO\Entity\CustomDefTicket[]
     */
    private $fields;

    /**
     * Array of depId => field.
     *
     * @var \Application\DeskPRO\Entity\CustomDefTicket[][]
     */
    private $dep_to_fields;

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
        return 60;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->db      = $this->container->get('database_connection');
        $this->em      = $this->container->get('doctrine.orm.entity_manager');

        $this->ava_files = new RandomFileFromDir(DP_ROOT.'/src/DeskPRO/Bundle/AppBundle/DataFixtures/res/avatars');

        $this->initRecords();
        $this->initDeps();
        $this->initFields();
        $this->initLayouts();
        $this->initContent();
    }

    private function initRecords()
    {
        $this->agents      = $this->em->createQuery('SELECT p FROM DeskPRO:Person p WHERE p.is_agent = true')->execute();
        $this->agent_teams = $this->em->createQuery('SELECT t FROM DeskPRO:AgentTeam t')->execute();
    }

    ####################################################################################################################
    # Setup deps and fields
    ####################################################################################################################

    protected function initDeps()
    {
        $this->dep1                     = new Department();
        $this->dep1->is_tickets_enabled = true;
        $this->dep1->title              = 'Widgets';
        $this->dep1->display_order      = self::$cnt++;
        $this->all_departments[]        = $this->dep1;

        $this->dep2                     = new Department();
        $this->dep2->is_tickets_enabled = true;
        $this->dep2->title              = 'Regulation and Control of Magical Creatures';
        $this->dep2->display_order      = self::$cnt++;
        $this->all_departments[]        = $this->dep2;

        $this->dep2_a                     = new Department();
        $this->dep2_a->is_tickets_enabled = true;
        $this->dep2_a->title              = 'Regulation';
        $this->dep2_a->parent             = $this->dep2;
        $this->dep2_a->display_order      = self::$cnt++;
        $this->all_departments[]          = $this->dep2_a;

        $this->dep2_b                     = new Department();
        $this->dep2_b->is_tickets_enabled = true;
        $this->dep2_b->title              = 'Control';
        $this->dep2_b->parent             = $this->dep2;
        $this->dep2_b->display_order      = self::$cnt++;
        $this->all_departments[]          = $this->dep2_b;

        $this->dep3                     = new Department();
        $this->dep3->is_tickets_enabled = true;
        $this->dep3->title              = 'Hotdogs';
        $this->dep3->display_order      = self::$cnt++;
        $this->all_departments[]        = $this->dep3;

        foreach ($this->all_departments as $d) {
            $this->em->persist($d);
        }
        $this->em->flush();

        // Perms
        $perms = [];
        foreach ($this->all_departments as $d) {
            $perms[] = [
                'department_id' => $d->getId(),
                'usergroup_id'  => 1, // everyone
                'app'           => 'tickets',
                'name'          => 'full',
                'value'         => 1,
            ];
        }

        $this->db->batchInsert('department_permissions', $perms, true);
    }

    private function initFields()
    {
        $this->fields        = [];
        $this->dep_to_fields = [];

        #------------------------------
        # Default
        #------------------------------

        $depId                       = 0;
        $this->dep_to_fields[$depId] = [];

        $f                             = $this->createField('select', 'Flumdiggler', ['Agree', 'Disagree', 'I\'d rather not say']);
        $this->fields[]                = $f;
        $this->dep_to_fields[$depId][] = $f;

        #------------------------------
        # Widgets
        #------------------------------

        $depId                       = $this->dep1->getId();
        $this->dep_to_fields[$depId] = [];

        $f                             = $this->createField('text', 'Widget Type');
        $this->fields[]                = $f;
        $this->dep_to_fields[$depId][] = $f;

        $f                             = $this->createField('textarea', 'Widget Description');
        $this->fields[]                = $f;
        $this->dep_to_fields[$depId][] = $f;

        $f                             = $this->createField('checkbox', 'Desired Sizes', ['Small', 'Medium', 'Large']);
        $this->fields[]                = $f;
        $this->dep_to_fields[$depId][] = $f;

        $f                             = $this->createField('date', 'Manufacture Date');
        $this->fields[]                = $f;
        $this->dep_to_fields[$depId][] = $f;

        #------------------------------
        # Regulation and Control of Magical Creatures [both]
        #------------------------------

        $depIda                       = $this->dep2_a->getId();
        $depIdb                       = $this->dep2_b->getId();
        $this->dep_to_fields[$depIda] = [];
        $this->dep_to_fields[$depIdb] = [];

        $f                              = $this->createField('radio', 'Reason for Complaint', ['Nuisance', 'Dangerous', 'Smelly', 'Ugly', 'Mean', 'Other']);
        $this->fields[]                 = $f;
        $this->dep_to_fields[$depIda][] = $f;
        $this->dep_to_fields[$depIdb][] = $f;

        $f                              = $this->createField('multiselect', 'Suggested Actions', ['Eviction', 'Shun', 'Fire them off to the moon', 'Strongly worded letter']);
        $this->fields[]                 = $f;
        $this->dep_to_fields[$depIda][] = $f;
        $this->dep_to_fields[$depIdb][] = $f;

        #------------------------------
        # Hotdogs
        #------------------------------

        $depId                       = $this->dep3->getId();
        $this->dep_to_fields[$depId] = [];

        $f = $this->createField('select', 'Hotdog Kind', [
            'Normal',
            ['German', ['Bratwurst', 'Extrawurst', ['Frankfurter', ['Rindswurst', 'Würstchen']]]],
            'Large',
        ]);
        $this->fields[]                = $f;
        $this->dep_to_fields[$depId][] = $f;

        $f                             = $this->createField('datetime', 'Delivery Time');
        $this->fields[]                = $f;
        $this->dep_to_fields[$depId][] = $f;
    }

    protected function initLayouts()
    {
        foreach ($this->dep_to_fields as $depId => $fields) {
            $layout = new Layout();
            $layout->add(new LayoutField('user_name_and_email'));
            $layout->add(new LayoutField('department'));
            $layout->add(new LayoutField('department'));
            $layout->add(new LayoutField('subject'));

            foreach ($fields as $f) {
                $layout->add(new LayoutField('ticket_field', $f->getId()));
            }

            $layout->add(new LayoutField('message'));
            $layout->add(new LayoutField('attach'));

            $enc = JsonObjectSerializer::serialize($layout);

            $insert_layout = [
                'department_id' => $depId ?: null,
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
        $ava      = $this->container->get('deskpro.blob_storage')->createBlobRecordFromString(
            $ava_file->getRealPath(),
            $ava_file->getFilename(),
            ContentTypes::getContentTypeFromFilename($ava_file->getFilename())
        );
        $person->picture_blob = $ava;

        $this->em->persist($person);
        $this->em->flush();

        $this->makeTicket($person, $this->dep1, 'resolved');
        $this->makeTicket($person, $this->dep1, 'awaiting_agent');

        $this->makeTicket($person, $this->dep2_a, 'awaiting_user');
        $this->makeTicket($person, $this->dep2_b, 'resolved');

        $this->makeTicket($person, $this->dep3, 'awaiting_agent');
        $this->makeTicket($person, $this->dep3, 'awaiting_user');
    }

    private function makeTicket(Person $person, Department $dep, $status)
    {
        #------------------------------
        # Ticket
        #------------------------------

        $subj = $this->faker->realText($this->faker->numberBetween(40, 60));

        $ticket = new Ticket();
        $ticket->disableAutoTicketProcess();
        $ticket->person               = $person;
        $ticket->department           = $dep;
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

            $batch[] = array(
                'ticket_id'       => $ticket->getId(),
                'person_id'       => $author->getId(),
                'date_created'    => date('Y-m-d H:i:s', $ticket->date_created->getTimestamp() + $this->faker->numberBetween(900, 14400)),
                'creation_system' => 'web',
                'is_agent_note'   => (int) $is_note,
                'ip_address'      => $this->faker->ipv4,
                'hostname'        => $this->faker->domainName,
                'geo_country'     => $this->faker->countryCode,
                'message_hash'    => sha1(uniqid('', true)),
                'message'         => $text,
            );
        }

        $this->db->batchInsert('tickets_messages', $batch, true);

        #------------------------------
        # Field Data
        #------------------------------

        /** @var CustomDefTicket[] $fields */
        $fields = array_merge($this->dep_to_fields[0], $this->dep_to_fields[$dep->getId()]);

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
            $this->db->batchInsert('custom_data_ticket', $batch, true);
        }
    }

    ####################################################################################################################

    /**
     * @param string     $type
     * @param string     $title
     * @param array|null $choices
     *
     * @return CustomDefTicket
     */
    private function createField($type, $title, array $choices = null)
    {
        $options = [];
        switch ($type) {
            case 'text':
                $handler_class = 'Application\DeskPRO\CustomFields\Handler\Text';
                break;
            case 'textarea':
                $handler_class = 'Application\DeskPRO\CustomFields\Handler\Textarea';
                break;
            case 'date':
                $handler_class = 'Application\DeskPRO\CustomFields\Handler\Date';
                break;
            case 'datetime':
                $handler_class = 'Application\DeskPRO\CustomFields\Handler\DateTime';
                break;
            case 'select':
                $handler_class = 'Application\DeskPRO\CustomFields\Handler\Choice';
                break;
            case 'multiselect':
                $handler_class       = 'Application\DeskPRO\CustomFields\Handler\Choice';
                $options['multiple'] = true;
                break;
            case 'checkbox':
                $handler_class       = 'Application\DeskPRO\CustomFields\Handler\Choice';
                $options['multiple'] = true;
                $options['expanded'] = true;
                break;
            case 'radio':
                $handler_class       = 'Application\DeskPRO\CustomFields\Handler\Choice';
                $options['multiple'] = false;
                $options['expanded'] = true;
                break;
            default:
                throw new \InvalidArgumentException();
        }

        $f                  = new CustomDefTicket();
        $f->title           = $title;
        $f->description     = 'A custom '.$f->getWidgetType().' field';
        $f->handler_class   = $handler_class;
        $f->options         = $options;
        $f->is_user_enabled = true;
        $f->is_enabled      = true;
        $f->display_order   = self::$cnt++;

        $this->em->persist($f);
        $this->em->flush();

        if ($handler_class === 'Application\DeskPRO\CustomFields\Handler\Choice' && $choices) {
            foreach ($choices as $c) {
                $this->_createSubOptions($f, null, $c);
            }
        }

        return $f;
    }

    /**
     * @param CustomDefTicket      $parent
     * @param CustomDefTicket|null $parent_opt
     * @param array|string         $desc
     *
     * @return CustomDefTicket
     */
    private function _createSubOptions(CustomDefTicket $parent, CustomDefTicket $parent_opt = null, $desc)
    {
        if (is_array($desc)) {
            $title  = $desc[0];
            $others = $desc[1];
        } else {
            $title  = $desc;
            $others = array();
        }

        $opt_f                  = new CustomDefTicket();
        $opt_f->parent          = $parent;
        $opt_f->title           = $title;
        $opt_f->description     = '';
        $opt_f->is_user_enabled = true;
        $opt_f->is_enabled      = true;
        $opt_f->display_order   = self::$cnt++;

        if ($parent_opt) {
            $opt_f->setOption('parent_id', $parent_opt->getId());
        }

        $parent->addChild($opt_f);

        $this->em->persist($opt_f);
        $this->em->flush();

        if ($others) {
            foreach ($others as $sub_title) {
                $this->_createSubOptions($parent, $opt_f, $sub_title);
            }
        }

        return $opt_f;
    }
}
