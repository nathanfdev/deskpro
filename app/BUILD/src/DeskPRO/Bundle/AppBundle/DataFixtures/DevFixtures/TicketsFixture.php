<?php

namespace DeskPRO\Bundle\AppBundle\DataFixtures\DevFixtures;

use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\LabelDef;
use Application\DeskPRO\Entity\Sla;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketSla;
use DeskPRO\Bundle\AppBundle\DataFixtures\AbstractDpFixture;
use DeskPRO\Bundle\AppBundle\DataFixtures\DevFixtures\CustomFields\CustomDataGenerator;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Orb\Util\DpStrings;
use Orb\Util\Strings;

class TicketsFixture extends AbstractDpFixture implements OrderedFixtureInterface
{
    private $numCategories     = 5;
    private $numWorkflows      = 5;
    private $numProducts       = 5;
    private $numLabels         = 100;
    private $ticketMaxMessages = 10;

    private $ticketChannels = [
        'web.person',
        'web.person.portal',
        'web.person.widget',
        'web.person.embed',
        'gateway.person',
        'gateway.agent',
        'web.api',
        'web.api.person',
        'web.api.agent',
    ];

    private $numTickets = 300;

    /**
     * @var int[]
     */
    private $departmentIds;

    /**
     * @var int[]
     */
    private $problemIds;

    /**
     * @var int[]
     */
    private $agentIds;

    /**
     * @var int[]
     */
    private $agentTeamIds;

    /**
     * @var int[]
     */
    private $categoryIds;

    /**
     * @var int[]
     */
    private $productIds;

    /**
     * @var int[]
     */
    private $languageIds;

    /**
     * @var int[]
     */
    private $workflowIds;

    /**
     * @var int[]
     */
    private $peopleIds;

    /**
     * @var int - our test user for portal
     */
    private $joeId;

    /**
     * @var int - our test org manager on the portal
     */
    private $joeManagerId;

    /**
     * @var int - our test org for portal
     */
    private $joeManagerOrgId;

    /**
     * @var string[]
     */
    private $labels;

    /**
     * @var int[]
     */
    private $ticketIds = [];

    /**
     * @var \Application\DeskPRO\Entity\CustomDefTicket[]
     */
    private $fields;

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 80;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->initIds();
        $this->loadCategories();
        $this->loadWorkflows();
        $this->loadProducts();
        $this->loadLabels();
        $this->loadTickets();
        $this->loadTicketsForJoe();
        $this->loadTicketsForManager();
        $this->loadTicketMessages();
        $this->loadTicketProps();
        $this->loadTicketSlas();
        $this->setParentTicket();
        $this->setTicketOrganizations();
    }

    private function initIds()
    {
        $this->languageIds     = $this->fetchIds(self::TABLE_LANGUAGES);
        $this->agentTeamIds    = $this->fetchIds(self::TABLE_AGENT_TEAMS);
        $this->agentIds        = $this->fetchIds(self::TABLE_PEOPLE, [['field' => 'is_agent', 'value' => 1]]);
        $this->peopleIds       = $this->fetchIds(self::TABLE_PEOPLE, [['field' => 'is_agent', 'value' => 0]]);
        $this->problemIds      = $this->fetchIds(self::TABLE_PROBLEMS);
        $this->departmentIds   = $this->getLeafDepartentIds();
        $this->joeId           = $this->getReference('person.joe')->getId();
        $this->joeManagerId    = $this->getReference('person.joes_manager')->getId();
        $this->joeManagerOrgId = $this->getReference('org.mana')->getId();

        $this->fields = $this->container->get('doctrine.orm.entity_manager')->createQuery(
            '
            SELECT f
            FROM DeskPRO:CustomDefTicket f
            WHERE f.parent IS NULL ORDER BY f.display_order ASC
        '
        )->execute();
    }

    private function getLeafDepartentIds()
    {
        $leafDepartmentIds = [];
        $allDepartments    = $this->manager->getRepository(Department::class)->findBy(['is_tickets_enabled' => true]);
        /** @var Department $department */
        foreach ($allDepartments as $department) {
            if ($department->getChildren()->count() === 0) {
                $leafDepartmentIds[] = $department->getId();
            }
        }

        return $leafDepartmentIds;
    }

    private function loadCategories()
    {
        $batch = [];

        for ($i = 1; $i <= $this->numCategories; ++$i) {
            $batch[] = [
                'title'         => 'Ticket Category '.$i,
                'display_order' => 1,
            ];
        }

        $this->db->batchInsert(self::TABLE_TICKET_CATEGORIES, $batch);
        $this->categoryIds = $this->fetchIds(self::TABLE_TICKET_CATEGORIES);
    }

    private function loadWorkflows()
    {
        $batch = [];

        for ($i = 1; $i <= $this->numWorkflows; ++$i) {
            $batch[] = [
                'title'         => 'Workflow '.$i,
                'display_order' => 1,
            ];
        }

        $this->db->batchInsert(self::TABLE_TICKET_WORKFLOWS, $batch);
        $this->workflowIds = $this->fetchIds(self::TABLE_TICKET_WORKFLOWS);
    }

    private function loadProducts()
    {
        $batch = [];

        for ($i = 1; $i <= $this->numProducts; ++$i) {
            $batch[] = [
                'title'         => 'Product '.$i,
                'display_order' => 1,
                'depth'         => 1,
            ];
        }

        $this->db->batchInsert(self::TABLE_PRODUCTS, $batch);
        $this->productIds = $this->fetchIds(self::TABLE_PRODUCTS);
    }

    private function loadLabels()
    {
        $labelType = LabelDef::TYPE_TICKETS;
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

    private function loadTickets()
    {
        $batch = [];

        for ($i = 0; $i < $this->numTickets; ++$i) {
            if ($this->faker->boolean(60)) {
                $status = 'awaiting_agent';
            } else {
                $status = $this->faker->randomElement(['awaiting_user', 'resolved']);
            }

            $ticketRating = null;
            if ($status == 'resolved') {
                if ($this->faker->boolean(60)) {
                    $ticketRating = 1;
                } elseif ($this->faker->boolean(50)) {
                    $ticketRating = -1;
                } else {
                    $ticketRating = null;
                }
            }

            $subj = $this->faker->sentence(4);

            $batch[] = [
                'brand_id'      => $this->getReference('brand')->getId(),
                'department_id' => $this->faker->randomElement($this->departmentIds),
                'language_id'   => $this->faker->randomElement($this->languageIds),
                'category_id'   => $this->faker->randomElement($this->categoryIds),
                'workflow_id'   => $this->faker->randomElement($this->workflowIds),
                'product_id'    => $this->faker->randomElement($this->productIds),
                'agent_id'      => $this->faker->boolean(90) ?
                    $this->faker->randomElement($this->agentIds) : null,
                'person_id'     => $this->faker->randomElement($this->peopleIds),
                'agent_team_id' => $this->agentTeamIds && $this->faker->boolean(40)
                    ? $this->faker->randomElement($this->agentTeamIds) : null,
                'ref'                     => Strings::random(15, Strings::CHARS_ALPHANUM_IU),
                'auth'                    => DpStrings::random(Ticket::TAC_AUTHCODE_LEN, Strings::CHARS_KEY),
                'status'                  => $status,
                'urgency'                 => $this->faker->numberBetween(1, 10),
                'subject'                 => $subj,
                'original_subject'        => $subj,
                'feedback_rating'         => $ticketRating,
                'creation_system'         => $this->faker->randomElement($this->ticketChannels),
                'date_created'            => $this->faker->boolean(15) ? $this->faker->dateTimeBetween('-2 days')->format('Y-m-d H:i:s') : $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'date_resolved'           => $this->faker->boolean(15) ? $this->faker->dateTimeBetween('-2 days')->format('Y-m-d H:i:s') : $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'date_archived'           => $this->faker->boolean(15) ? $this->faker->dateTimeBetween('-2 days')->format('Y-m-d H:i:s') : $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'date_first_agent_assign' => $this->faker->boolean(15) ? $this->faker->dateTimeBetween('-2 days')->format('Y-m-d H:i:s') : $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'date_first_agent_reply'  => $this->faker->boolean(15) ? $this->faker->dateTimeBetween('-2 days')->format('Y-m-d H:i:s') : $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'date_last_agent_reply'   => $this->faker->boolean(15) ? $this->faker->dateTimeBetween('-2 days')->format('Y-m-d H:i:s') : $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'date_last_user_reply'    => $this->faker->boolean(15) ? $this->faker->dateTimeBetween('-2 days')->format('Y-m-d H:i:s') : $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'date_agent_waiting'      => $this->faker->boolean(15) ? $this->faker->dateTimeBetween('-2 days')->format('Y-m-d H:i:s') : $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'date_user_waiting'       => $this->faker->boolean(15) ? $this->faker->dateTimeBetween('-2 days')->format('Y-m-d H:i:s') : $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'date_status'             => $this->faker->boolean(15) ? $this->faker->dateTimeBetween('-2 days')->format('Y-m-d H:i:s') : $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
            ];
        }

        $this->db->batchInsert(self::TABLE_TICKETS, $batch);
        $this->ticketIds = $this->fetchIds(self::TABLE_TICKETS);
    }

    private function loadTicketsForJoe()
    {
        $batch = [];

        for ($i = 0; $i < $this->numTickets; ++$i) {
            if ($this->faker->boolean(33)) {
                $status = 'awaiting_agent';
            } else {
                $status = $this->faker->randomElement(['awaiting_user', 'resolved']);
            }

            $subj    = $this->faker->realText($this->faker->numberBetween(40, 60));
            $batch[] = [
                'brand_id'      => $this->getReference('brand')->getId(),
                'department_id' => $this->faker->randomElement($this->departmentIds),
                'language_id'   => $this->faker->randomElement($this->languageIds),
                'category_id'   => $this->faker->randomElement($this->categoryIds),
                'workflow_id'   => $this->faker->randomElement($this->workflowIds),
                'product_id'    => $this->faker->randomElement($this->productIds),
                'agent_id'      => $this->faker->boolean(90)
                    ? $this->faker->randomElement($this->agentIds) : null,
                'person_id'     => $this->joeId,
                'agent_team_id' => $this->agentTeamIds && $this->faker->boolean(40)
                    ? $this->faker->randomElement($this->agentTeamIds) : null,
                'ref'                     => Strings::random(15, Strings::CHARS_ALPHANUM_IU),
                'auth'                    => DpStrings::random(Ticket::TAC_AUTHCODE_LEN, Strings::CHARS_KEY),
                'status'                  => $status,
                'urgency'                 => $this->faker->numberBetween(1, 10),
                'subject'                 => $subj,
                'original_subject'        => $subj,
                'creation_system'         => $this->faker->randomElement($this->ticketChannels),
                'date_created'            => $this->faker->boolean(15) ? $this->faker->dateTimeBetween('-2 days')->format('Y-m-d H:i:s') : $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'date_resolved'           => $this->faker->boolean(15) ? $this->faker->dateTimeBetween('-2 days')->format('Y-m-d H:i:s') : $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'date_archived'           => $this->faker->boolean(15) ? $this->faker->dateTimeBetween('-2 days')->format('Y-m-d H:i:s') : $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'date_first_agent_assign' => $this->faker->boolean(15) ? $this->faker->dateTimeBetween('-2 days')->format('Y-m-d H:i:s') : $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'date_first_agent_reply'  => $this->faker->boolean(15) ? $this->faker->dateTimeBetween('-2 days')->format('Y-m-d H:i:s') : $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'date_last_agent_reply'   => $this->faker->boolean(15) ? $this->faker->dateTimeBetween('-2 days')->format('Y-m-d H:i:s') : $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'date_last_user_reply'    => $this->faker->boolean(15) ? $this->faker->dateTimeBetween('-2 days')->format('Y-m-d H:i:s') : $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'date_agent_waiting'      => $this->faker->boolean(15) ? $this->faker->dateTimeBetween('-2 days')->format('Y-m-d H:i:s') : $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'date_user_waiting'       => $this->faker->boolean(15) ? $this->faker->dateTimeBetween('-2 days')->format('Y-m-d H:i:s') : $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'date_status'             => $this->faker->boolean(15) ? $this->faker->dateTimeBetween('-2 days')->format('Y-m-d H:i:s') : $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'organization_id'         => $this->joeManagerOrgId,
            ];
        }

        $this->db->batchInsert(self::TABLE_TICKETS, $batch);

        $this->ticketIds = $this->fetchIds(self::TABLE_TICKETS);
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
                'brand_id'      => $this->getReference('brand')->getId(),
                'department_id' => $this->faker->randomElement($this->departmentIds),
                'language_id'   => $this->faker->randomElement($this->languageIds),
                'category_id'   => $this->faker->randomElement($this->categoryIds),
                'workflow_id'   => $this->faker->randomElement($this->workflowIds),
                'product_id'    => $this->faker->randomElement($this->productIds),
                'agent_id'      => $this->faker->boolean(90)
                    ? $this->faker->randomElement($this->agentIds) : null,
                'person_id'     => $this->joeManagerId,
                'agent_team_id' => $this->agentTeamIds && $this->faker->boolean(40)
                    ? $this->faker->randomElement($this->agentTeamIds) : null,
                'ref'                     => Strings::random(15, Strings::CHARS_ALPHANUM_IU),
                'auth'                    => DpStrings::random(Ticket::TAC_AUTHCODE_LEN, Strings::CHARS_KEY),
                'status'                  => $status,
                'urgency'                 => $this->faker->numberBetween(1, 10),
                'subject'                 => $subj,
                'original_subject'        => $subj,
                'creation_system'         => $this->faker->randomElement($this->ticketChannels),
                'date_created'            => $this->faker->boolean(15) ? $this->faker->dateTimeBetween('-2 days')->format('Y-m-d H:i:s') : $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'date_resolved'           => $this->faker->boolean(15) ? $this->faker->dateTimeBetween('-2 days')->format('Y-m-d H:i:s') : $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'date_archived'           => $this->faker->boolean(15) ? $this->faker->dateTimeBetween('-2 days')->format('Y-m-d H:i:s') : $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'date_first_agent_assign' => $this->faker->boolean(15) ? $this->faker->dateTimeBetween('-2 days')->format('Y-m-d H:i:s') : $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'date_first_agent_reply'  => $this->faker->boolean(15) ? $this->faker->dateTimeBetween('-2 days')->format('Y-m-d H:i:s') : $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'date_last_agent_reply'   => $this->faker->boolean(15) ? $this->faker->dateTimeBetween('-2 days')->format('Y-m-d H:i:s') : $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'date_last_user_reply'    => $this->faker->boolean(15) ? $this->faker->dateTimeBetween('-2 days')->format('Y-m-d H:i:s') : $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'date_agent_waiting'      => $this->faker->boolean(15) ? $this->faker->dateTimeBetween('-2 days')->format('Y-m-d H:i:s') : $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'date_user_waiting'       => $this->faker->boolean(15) ? $this->faker->dateTimeBetween('-2 days')->format('Y-m-d H:i:s') : $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'date_status'             => $this->faker->boolean(15) ? $this->faker->dateTimeBetween('-2 days')->format('Y-m-d H:i:s') : $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
            ];
        }

        $this->db->batchInsert(self::TABLE_TICKETS, $batch);

        $this->ticketIds = $this->fetchIds(self::TABLE_TICKETS);
    }

    private function loadTicketMessages()
    {
        $batch = [];

        foreach ($this->ticketIds as $ticketId) {
            $num  = (int) $ticketId === 100 ? 1500 : $this->faker->numberBetween(1, $this->ticketMaxMessages);
            $date = $this->faker->dateTimeBetween('-2 months');
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
                    'ticket_id' => $ticketId,
                    'person_id' => $as_agent
                        ? $this->faker->randomElement($this->agentIds)
                        : $this->faker->randomElement($this->peopleIds),
                    'date_created'    => $date->add(new \DateInterval('PT1H'))->format('Y-m-d H:i:s'),
                    'creation_system' => 'web',
                    'is_agent_note'   => (int) ($as_agent && $this->faker->boolean(10)),
                    'ip_address'      => $this->faker->ipv4,
                    'hostname'        => $this->faker->domainName,
                    'geo_country'     => $this->faker->countryCode,
                    'message_hash'    => sha1(uniqid('', true)),
                    'message'         => $text,
                ];

                if (count($batch) > 1000) {
                    $this->db->batchInsert('tickets_messages', $batch);
                    $batch = [];
                }
            }
        }

        if ($batch) {
            $this->db->batchInsert('tickets_messages', $batch);
        }
    }

    private function loadTicketProps()
    {
        $labels_batch    = [];
        $probs_batch     = [];
        $parts_batch     = [];
        $fielddata_batch = [];
        $logsBatch       = [];

        $customDefGenerator = new CustomDataGenerator($this->faker);

        foreach ($this->ticketIds as $ticket_id) {
            foreach ($this->faker->randomElements($this->labels, $this->faker->numberBetween(1, 5)) as $l) {
                $labels_batch[] = ['ticket_id' => $ticket_id, 'label' => $l];
            }
            $probs_batch[] = [
                'ticket_id'  => $ticket_id,
                'problem_id' => $this->faker->randomElement($this->problemIds),
            ];
            $people_ids = $this->faker->randomElements($this->peopleIds, $this->faker->numberBetween(1, 4));
            foreach ($people_ids as $pid) {
                $parts_batch[] = [
                    'ticket_id' => $ticket_id,
                    'person_id' => $pid,
                ];
            }
            $people_ids = $this->faker->randomElements($this->agentIds, $this->faker->numberBetween(1, 2));
            foreach ($people_ids as $pid) {
                $parts_batch[] = [
                    'ticket_id' => $ticket_id,
                    'person_id' => $pid,
                ];
            }

            foreach ($this->fields as $f) {
                $customDefGenerator->addCustomDefData($fielddata_batch, $f, 'ticket_id', $ticket_id);
            }

            $logsBatch[] = [
                'ticket_id'    => $ticket_id,
                'action_type'  => 'free',
                'date_created' => $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'details'      => serialize([
                    'message' => 'Test ticket created by development fixture.',
                ]),
            ];
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
        if ($logsBatch) {
            $this->db->batchInsert('tickets_logs', $logsBatch, true);
        }
    }

    private function loadTicketSlas()
    {
        $batch = [];
        $sla   = $this->manager->getRepository(Sla::class)->findOneBy(['sla_type' => 'first_response']);
        foreach ($this->ticketIds as $ticketId) {
            $status = $this->faker->randomElement(
                [TicketSla::STATUS_OK, TicketSla::STATUS_WARNING, TicketSla::STATUS_FAIL]
            );
            $batch[] = [
                'ticket_id'  => $ticketId,
                'sla_id'     => $sla->getId(),
                'sla_status' => $status,
                'warn_date'  => $status === TicketSla::STATUS_WARNING ?
                    $this->faker->dateTimeBetween('-14 days', '-10 days')->format('Y-m-d H:i:s') : null,
                'fail_date' => $status === TicketSla::STATUS_FAIL ?
                    $this->faker->dateTimeBetween('-14 days', '-10 days')->format('Y-m-d H:i:s') : null,
                'is_completed'         => (int) $this->faker->boolean(70),
                'completed_time_taken' => $this->faker->numberBetween(60 * 60 * 24, 60 * 60 * 24 * 10),
            ];
        }

        if ($batch) {
            $this->db->batchInsert('ticket_slas', $batch, true);
        }
    }

    private function setParentTicket()
    {
        foreach ($this->ticketIds as $id) {
            if ($id > 2 && $this->faker->boolean(33)) {
                $previousTicketKeys = array_keys(
                    array_slice($this->ticketIds, 0, array_search($id, $this->ticketIds) - 1, true)
                );

                if (!empty($previousTicketKeys)) {
                    $parentKey = $this->faker->randomElement($previousTicketKeys);
                    $this->db->executeUpdate(
                        'UPDATE tickets
                            SET tickets.parent_ticket_id = '.$this->ticketIds[$parentKey].'
                            WHERE tickets.id = '.$id
                    );
                }
            }
        }
    }

    private function setTicketOrganizations()
    {
        $this->db->executeUpdate('
            UPDATE tickets
            JOIN people ON people.id = tickets.person_id
            SET tickets.organization_id = people.organization_id
        ');
    }
}
