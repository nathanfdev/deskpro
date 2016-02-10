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
use Application\DeskPRO\Entity\Ticket;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Orb\Util\DpStrings;
use Orb\Util\Strings;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TicketsFixture extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
{
    private $num_problems        = 100;
    private $num_labels          = 100;
    private $ticket_max_messages = 10;

    private $num_tickets = 250;

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
     * @var int[]
     */
    private $department_ids;

    /**
     * @var int[]
     */
    private $problem_ids;

    /**
     * @var int[]
     */
    private $agent_ids;

    /**
     * @var int[]
     */
    private $agent_team_ids;

    /**
     * @var int[]
     */
    private $people_ids;

    /**
     * @var int - our test user for portal
     */
    private $joe_id;

    /**
     * @var int - our test org manager on the portal
     */
    private $joe_manager_id;

    /**
     * @var int - our test org for portal
     */
    private $joe_manager_org_id;

    /**
     * @var string[]
     */
    private $labels;

    /**
     * @var int[]
     */
    private $ticket_ids;

    /**
     * @var \Application\DeskPRO\Entity\CustomDefTicket[]
     */
    private $fields;

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
        return 70;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->db      = $this->container->get('database_connection');
        $this->initIds();
        $this->loadProblems();
        $this->loadLabels();
        $this->loadTickets();
        $this->loadTicketsForJoe();
        $this->loadTicketsForManager();
        $this->loadTicketMessages();
        $this->loadTicketProps();
    }

    private function initIds()
    {
        $this->agent_ids          = $this->db->fetchAllCol('SELECT id FROM people WHERE is_agent = 1');
        $this->agent_team_ids     = $this->db->fetchAllCol('SELECT id FROM agent_teams');
        $this->people_ids         = $this->db->fetchAllCol('SELECT id FROM people WHERE is_agent = 0');
        $this->joe_id             = $this->getReference('person.joe')->getId();
        $this->joe_manager_id     = $this->getReference('person.joes_manager')->getId();
        $this->joe_manager_org_id = $this->getReference('org.mana')->getId();
        $this->department_ids     = $this->db->fetchAllCol('SELECT id FROM departments WHERE is_tickets_enabled = 1');

        $this->fields = $this->container->get('doctrine.orm.entity_manager')->createQuery('
            SELECT f
            FROM DeskPRO:CustomDefTicket f
            WHERE f.parent IS NULL ORDER BY f.display_order ASC
        ')->execute();
    }

    private function loadProblems()
    {
        $batch = [];

        for ($i = 0; $i < $this->num_problems; ++$i) {
            $batch[] = array(
                'person_id' => $this->faker->randomElement($this->agent_ids),
                'title'     => $this->faker->sentence(4),
                'created'   => $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'is_open'   => (int) $this->faker->boolean(25),
            );
        }

        $this->db->batchInsert('problems', $batch);

        $this->problem_ids = $this->db->fetchAllCol('SELECT id FROM problems');
    }

    private function loadLabels()
    {
        $label_type = LabelDef::TYPE_TICKETS;
        $this->faker->unique(true);

        $batch = [];

        for ($i = 0; $i < $this->num_labels; ++$i) {
            $l = $this->faker->unique()->company;
            if ($l) {
                $l       = strtolower($l);
                $batch[] = ['label_type' => $label_type, 'label' => $l, 'color' => $this->faker->hexColor, 'total' => 0];
            }
        }

        $this->db->batchInsert('label_defs', $batch, true);

        $this->labels = $this->db->fetchAllCol('SELECT label FROM label_defs WHERE label_type = ?', array($label_type));
    }

    private function loadTickets()
    {
        $batch = [];

        for ($i = 0; $i < $this->num_tickets; ++$i) {
            if ($this->faker->boolean(60)) {
                $status = 'awaiting_agent';
            } else {
                $status = $this->faker->randomElement(['awaiting_user', 'resolved']);
            }

            $ticket_rating = null;
            if ($status == 'resolved') {
                if ($this->faker->boolean(60)) {
                    $ticket_rating = 1;
                } elseif ($this->faker->boolean(50)) {
                    $ticket_rating = -1;
                } else {
                    $ticket_rating = null;
                }
            }

            $subj    = $this->faker->realText($this->faker->numberBetween(10, 20));
            $batch[] = [
                'department_id'           => $this->faker->randomElement($this->department_ids),
                'agent_id'                => $this->faker->boolean(90) ? $this->faker->randomElement($this->agent_ids) : null,
                'person_id'               => $this->faker->randomElement($this->people_ids),
                'agent_team_id'           => $this->agent_team_ids && $this->faker->boolean(40) ? $this->faker->randomElement($this->agent_team_ids) : null,
                'ref'                     => Strings::random(15, Strings::CHARS_ALPHANUM_IU),
                'auth'                    => DpStrings::random(Ticket::TAC_AUTHCODE_LEN, Strings::CHARS_KEY),
                'status'                  => $status,
                'urgency'                 => $this->faker->numberBetween(1, 10),
                'subject'                 => $subj,
                'original_subject'        => $subj,
                'feedback_rating'         => $ticket_rating,
                'date_created'            => $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'date_resolved'           => $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'date_archived'           => $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'date_first_agent_assign' => $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'date_first_agent_reply'  => $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'date_last_agent_reply'   => $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'date_last_user_reply'    => $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'date_agent_waiting'      => $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'date_user_waiting'       => $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'date_status'             => $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
            ];
        }

        $this->db->batchInsert('tickets', $batch);

        $this->ticket_ids = $this->db->fetchAllCol('SELECT id FROM tickets');
    }

    private function loadTicketsForJoe()
    {
        $batch = [];

        for ($i = 0; $i < $this->num_tickets; ++$i) {
            if ($this->faker->boolean(33)) {
                $status = 'awaiting_agent';
            } else {
                $status = $this->faker->randomElement(['awaiting_user', 'resolved']);
            }

            $subj    = $this->faker->realText($this->faker->numberBetween(40, 60));
            $batch[] = [
                'department_id'           => $this->faker->randomElement($this->department_ids),
                'agent_id'                => $this->faker->boolean(90) ? $this->faker->randomElement($this->agent_ids) : null,
                'person_id'               => $this->joe_id,
                'agent_team_id'           => $this->agent_team_ids && $this->faker->boolean(40) ? $this->faker->randomElement($this->agent_team_ids) : null,
                'ref'                     => Strings::random(15, Strings::CHARS_ALPHANUM_IU),
                'auth'                    => DpStrings::random(Ticket::TAC_AUTHCODE_LEN, Strings::CHARS_KEY),
                'status'                  => $status,
                'urgency'                 => $this->faker->numberBetween(1, 10),
                'subject'                 => $subj,
                'original_subject'        => $subj,
                'date_created'            => $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'date_resolved'           => $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'date_archived'           => $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'date_first_agent_assign' => $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'date_first_agent_reply'  => $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'date_last_agent_reply'   => $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'date_last_user_reply'    => $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'date_agent_waiting'      => $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'date_user_waiting'       => $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'date_status'             => $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'organization_id'         => $this->joe_manager_org_id,
            ];
        }

        $this->db->batchInsert('tickets', $batch);

        $this->ticket_ids = $this->db->fetchAllCol('SELECT id FROM tickets');
    }

    private function loadTicketsForManager()
    {
        $batch = [];

        for ($i = 0; $i < 12; ++$i) {
            if ($this->faker->boolean(50)) {
                $status = 'awaiting_agent';
            } else {
                $status = $this->faker->randomElement(['awaiting_user', 'resolved']);
            }

            $subj    = $this->faker->realText($this->faker->numberBetween(40, 60));
            $batch[] = [
                'department_id'           => $this->faker->randomElement($this->department_ids),
                'agent_id'                => $this->faker->boolean(90) ? $this->faker->randomElement($this->agent_ids) : null,
                'person_id'               => $this->joe_manager_id,
                'agent_team_id'           => $this->agent_team_ids && $this->faker->boolean(40) ? $this->faker->randomElement($this->agent_team_ids) : null,
                'ref'                     => Strings::random(15, Strings::CHARS_ALPHANUM_IU),
                'auth'                    => DpStrings::random(Ticket::TAC_AUTHCODE_LEN, Strings::CHARS_KEY),
                'status'                  => $status,
                'urgency'                 => $this->faker->numberBetween(1, 10),
                'subject'                 => $subj,
                'original_subject'        => $subj,
                'date_created'            => $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'date_resolved'           => $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'date_archived'           => $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'date_first_agent_assign' => $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'date_first_agent_reply'  => $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'date_last_agent_reply'   => $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'date_last_user_reply'    => $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'date_agent_waiting'      => $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'date_user_waiting'       => $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'date_status'             => $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
            ];
        }

        $this->db->batchInsert('tickets', $batch);

        $this->ticket_ids = $this->db->fetchAllCol('SELECT id FROM tickets');
    }

    private function loadTicketMessages()
    {
        $batch = [];

        foreach ($this->ticket_ids as $ticket_id) {
            $num = $this->faker->numberBetween(1, $this->ticket_max_messages);
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

                $batch[] = [
                    'ticket_id'       => $ticket_id,
                    'person_id'       => $as_agent ? $this->faker->randomElement($this->agent_ids) : $this->faker->randomElement($this->people_ids),
                    'date_created'    => $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                    'creation_system' => 'web',
                    'is_agent_note'   => (int) ($as_agent && $this->faker->boolean(10)),
                    'ip_address'      => $this->faker->ipv4,
                    'hostname'        => $this->faker->domainName,
                    'geo_country'     => $this->faker->countryCode,
                    'message_hash'    => sha1(uniqid('', true)),
                    'message'         => $text,
                ];
            }
        }

        $this->db->batchInsert('tickets_messages', $batch);
    }

    private function loadTicketProps()
    {
        $labels_batch    = [];
        $probs_batch     = [];
        $parts_batch     = [];
        $fielddata_batch = [];

        foreach ($this->ticket_ids as $ticket_id) {
            foreach ($this->faker->randomElements($this->labels, $this->faker->numberBetween(1, 5)) as $l) {
                $labels_batch[] = ['ticket_id' => $ticket_id, 'label' => $l];
            }
            $probs_batch[] = ['ticket_id' => $ticket_id, 'problem_id' => $this->faker->randomElement($this->problem_ids)];
            $people_ids    = $this->faker->randomElements($this->people_ids, $this->faker->numberBetween(1, 4));
            foreach ($people_ids as $pid) {
                $parts_batch[] = [
                    'ticket_id' => $ticket_id,
                    'person_id' => $pid,
                ];
            }
            $people_ids = $this->faker->randomElements($this->agent_ids, $this->faker->numberBetween(1, 2));
            foreach ($people_ids as $pid) {
                $parts_batch[] = [
                    'ticket_id' => $ticket_id,
                    'person_id' => $pid,
                ];
            }

            #------------------------------
            # Field Data
            #------------------------------

            foreach ($this->fields as $f) {
                $num = 1;
                if ($f->getOption('multiple')) {
                    $num = $this->faker->numberBetween(1, count($f->getChildren()));
                }

                for ($x = 0; $x < $num; ++$x) {
                    $row_data = [
                        'ticket_id'     => $ticket_id,
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
                        $fielddata_batch[] = $row_data;
                    }
                }
            }
        }

        if ($labels_batch) {
            $this->db->batchInsert('labels_tickets', $labels_batch, true);
        }
        if ($probs_batch) {
            $this->db->batchInsert('problem2tickets', $probs_batch, true);
        }
        if ($parts_batch) {
            $this->db->batchInsert('tickets_participants', $parts_batch, true);
        }
        if ($fielddata_batch) {
            $this->db->batchInsert('custom_data_ticket', $fielddata_batch, true);
        }
    }
}
