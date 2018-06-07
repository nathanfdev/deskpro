<?php

namespace DeskPRO\Bundle\DevBundle\MassLoader;

use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\Usergroup;
use Application\DeskPRO\People\PasswordScheme\Bcrypt;
use Doctrine\ORM\EntityManager;
use Faker\Factory;
use Faker\Generator;
use Orb\Util\DpStrings;
use Orb\Util\Strings;

/**
 * Class MassLoader.
 */
class MassLoader
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var Generator
     */
    private $faker;

    /**
     * @var array
     */
    private $cache = [];

    /**
     * @var array
     */
    private static $subscriptionProps = [
        'email_created'         => 1,
        'email_new'             => 1,
        'email_leave'           => 1,
        'email_user_activity'   => 1,
        'email_agent_activity'  => 1,
        'email_agent_note'      => 1,
        'email_property_change' => 1,
        'alert_created'         => 1,
        'alert_new'             => 1,
        'alert_leave'           => 1,
        'alert_user_activity'   => 1,
        'alert_agent_activity'  => 1,
        'alert_agent_note'      => 1,
        'alert_property_change' => 1,
    ];

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em         = $em;
        $this->faker      = Factory::create();
        $this->connection = $em->getConnection();
        $this->connection->getConfiguration()->setSQLLogger(null);
    }

    public function clearTickets()
    {
        $this->connection->executeUpdate('DELETE FROM tickets');
    }

    public function clearDb()
    {
        $this->clearTickets();

        $this->connection->executeUpdate('TRUNCATE TABLE ticket_filter_subscriptions');
        $this->connection->executeUpdate('TRUNCATE TABLE permissions');
        $this->connection->executeUpdate('DELETE FROM ticket_filters');
        $this->connection->executeUpdate('DELETE FROM people_emails WHERE person_id != 1');
        $this->connection->executeUpdate('TRUNCATE TABLE agent_team_members');
        $this->connection->executeUpdate('DELETE FROM people WHERE id != 1');
        $this->connection->executeUpdate('DELETE FROM organizations');
        $this->connection->executeUpdate('DELETE FROM agent_teams');
        $this->connection->executeUpdate('DELETE FROM departments');
        $this->connection->executeUpdate('DELETE FROM usergroups WHERE sys_name NOT IN (?)',
            [
                [
                    Usergroup::EVERYONE,
                    Usergroup::REGISTERED,
                    Usergroup::AGENT_ALL_PERM,
                    Usergroup::AGENT_ALL_SAFE_PERM,
                ],
            ],
            [
                Connection::PARAM_INT_ARRAY,
            ]
        );

        // grant group permissions to use tickets
        $usergroupIds = $this->connection->fetchAllCol('SELECT id FROM usergroups WHERE sys_name IN(?, ?)', [
            Usergroup::EVERYONE,
            Usergroup::REGISTERED,
        ]);

        $permissions = [];
        $permNames   = [
            'tickets.use',
            'tickets.create',
        ];

        foreach ($usergroupIds as $usergroupId) {
            foreach ($permNames as $permissionName) {
                $permissions[] = [
                    'usergroup_id' => $usergroupId,
                    'name'         => $permissionName,
                    'value'        => 1,
                    'is_active'    => true,
                ];
            }
        }

        $this->connection->batchInsert('permissions', $permissions);
    }

    public function loadOrganization()
    {
        $this->connection->insert('organizations', [
            'name' => $this->faker->title,
        ]);
    }

    /**
     * @param array $options
     */
    public function loadAgent(array $options = [])
    {
        $extendedData = [
            'is_agent'  => true,
            'can_agent' => true,
            'can_admin' => true,
        ];

        $personId = $this->loadPerson($extendedData, $options);

        // assign to agent team
        if (isset($options['agent_team'])) {
            // select random agent team
            if ($options['agent_team'] === 'random') {
                $teamIds = $this->fetchAllIds('agent_teams');
                if (count($teamIds)) {
                    $options['agent_team'] = $this->faker->randomElement($teamIds);
                } else {
                    $options['agent_team'] = null;
                }
            }

            if ($options['agent_team']) {
                $this->connection->insert('agent_team_members', [
                    'person_id' => $personId,
                    'team_id'   => $options['agent_team'],
                ]);
            }
        }

        // grant department permissions
        if (isset($options['departments'])) {
            foreach ($options['departments'] as $departmentId) {
                if ($departmentId === 'random') {
                    $departmentIds = $this->fetchAllIds('departments');
                    if (count($departmentIds)) {
                        $departmentId = $this->faker->randomElement($departmentIds);
                    } else {
                        $departmentId = null;
                    }
                }

                if ($departmentId) {
                    $this->connection->insertIgnore('department_permissions', [
                        'department_id' => $departmentId,
                        'person_id'     => $personId,
                        'name'          => 'full',
                        'value'         => 1,
                        'is_active'     => true,
                        'app'           => 'tickets',
                    ]);
                }
            }
        }

        // create own ticket filters
        if (isset($options['filters'])) {
            foreach ($options['filters'] as $num => $filterOptions) {
                $this->connection->insertIgnore('ticket_filters', [
                    'is_global'  => 0,
                    'is_enabled' => true,
                    'title'      => $this->faker->title,
                    'sys_name'   => null, // user filters has no sys name
                    'terms'      => $this->transformTicketFilterTerms($filterOptions),
                    'person_id'  => $personId,
                ]);
            }
        }

        // grant individual permissions to use tickets
        $permissions = [];
        $permNames   = [
            'agent_tickets.use',
            'agent_tickets.view_others',
            'agent_tickets.view_unassigned',
            'agent_tickets.create',
            'agent_tickets.modify_own',
            'agent_tickets.delete',
            'agent_tickets.reply_mass',
            'agent_tickets.reply_own',
            'agent_tickets.reply_others',
        ];

        foreach ($permNames as $permissionName) {
            $permissions[] = [
                'person_id' => $personId,
                'name'      => $permissionName,
                'value'     => 1,
                'is_active' => true,
            ];
        }

        $this->connection->batchInsert('permissions', $permissions);

        // load agent ticket subscriptions
        $filterIds = $this->connection->fetchAllCol('SELECT id FROM ticket_filters WHERE person_id = :person_id OR person_id IS NULL', [
            'person_id' => $personId,
        ]);

        $subscriptions = [];
        foreach ($filterIds as $filterId) {
            $subscriptions[] = array_merge(self::$subscriptionProps, [
                'filter_id' => $filterId,
                'person_id' => $personId,
            ]);
        }

        if ($subscriptions) {
            $this->connection->batchInsert('ticket_filter_subscriptions', $subscriptions);
        }
    }

    /**
     * @param array $options
     */
    public function loadUser(array $options = [])
    {
        $this->loadPerson([], $options);
    }

    public function loadAgentTeam()
    {
        $this->cache['agent_teams'] = null;
        $this->connection->insert('agent_teams', [
            'name' => $this->faker->title,
        ]);
    }

    /**
     * @param array $options
     */
    public function loadAgentGroup(array $options = [])
    {
        $this->connection->insert('usergroups', [
            'title'          => $this->faker->title,
            'note'           => $this->faker->text,
            'sys_name'       => $this->faker->unique()->word,
            'is_agent_group' => true,
            'is_enabled'     => 1,
        ]);

        $usergroupId = $this->connection->lastInsertId();

        if (isset($options['departments'])) {
            foreach ($options['departments'] as $departmentId) {
                if ($departmentId === 'random') {
                    $departmentIds = $this->fetchAllIds('departments');
                    if (count($departmentIds)) {
                        $departmentId = $this->faker->randomElement($departmentIds);
                    } else {
                        $departmentId = null;
                    }
                }

                if ($departmentId) {
                    $this->connection->insertIgnore('department_permissions', [
                        'department_id' => $departmentId,
                        'usergroup_id'  => $usergroupId,
                        'name'          => 'full',
                        'value'         => 1,
                        'is_active'     => true,
                        'app'           => 'tickets',
                    ]);
                }
            }
        }
    }

    public function loadTicketDepartment()
    {
        $this->cache['departments'] = null;
        $this->connection->insert('departments', [
            'title'              => $this->faker->title,
            'is_tickets_enabled' => true,
            'is_chat_enabled'    => false,
        ]);

        $departmentId = $this->connection->lastInsertId();

        // tie with each existing brand
        $brandIds = $this->fetchAllIds('brands');

        $department2brands = [];
        foreach ($brandIds as $brandId) {
            $department2brands[] = [
                'department_id' => $departmentId,
                'brand_id'      => $brandId,
            ];
        }

        $this->connection->batchInsert('department_to_brand', $department2brands);

        // usergroups
        $usergroupsIds = $this->connection->fetchAllCol('SELECT id FROM usergroups WHERE sys_name IN(?, ?)', [
            Usergroup::EVERYONE,
            Usergroup::REGISTERED,
        ]);

        $permissions = [];
        foreach ($usergroupsIds as $usergroupId) {
            $permissions[] = [
                'department_id' => $departmentId,
                'usergroup_id'  => $usergroupId,
                'name'          => 'full',
                'value'         => 1,
                'is_active'     => true,
                'app'           => 'tickets',
            ];
        }

        $this->connection->batchInsert('department_permissions', $permissions);
    }

    /**
     * @param array $options
     */
    public function loadGlobalTicketFilter(array $options = [])
    {
        $this->connection->insert('ticket_filters', [
            'is_global'  => 1,
            'is_enabled' => true,
            'title'      => $this->faker->title,
            'terms'      => $this->transformTicketFilterTerms($options),
        ]);

        $filterId = $this->connection->lastInsertId();
        $agentIds = $this->connection->fetchAllCol('SELECT id FROM people WHERE is_agent = 1');

        $subscriptions = [];
        foreach ($agentIds as $agentId) {
            $subscriptions[] = array_merge(self::$subscriptionProps, [
                'filter_id' => $filterId,
                'person_id' => $agentId,
            ]);
        }

        if ($subscriptions) {
            $this->connection->batchInsert('ticket_filter_subscriptions', $subscriptions);
        }
    }

    /**
     * @param array $options
     */
    public function loadTicketBatch(array $options = [])
    {
        $batchSize = isset($options['ticketsBatchCount']) ? $options['ticketsBatchCount'] : 1000;

        // create tickets
        $refs     = [];
        $tickets  = [];
        $statuses = [
            Ticket::STATUS_AWAITING_AGENT,
            Ticket::STATUS_AWAITING_USER,
            Ticket::STATUS_RESOLVED,
        ];

        $allDepartmentIds = $this->connection->fetchAllCol('SELECT id FROM departments WHERE is_tickets_enabled = 1');

        for ($i = 0; $i < $batchSize; ++$i) {
            $ref         = DpStrings::random(10, Strings::CHARS_ALPHA_IU).'-'.date('YzB');
            $dateCreated = $this->faker->dateTime->format('c');

            $refs[]    = $ref;
            $tickets[] = [
                'subject'               => $this->faker->title,
                'ref'                   => $ref,
                'date_created'          => $dateCreated,
                'department_id'         => $this->faker->randomElement($allDepartmentIds),
                'person_id'             => $this->faker->randomElement($this->fetchAllIds('people')),
                'agent_id'              => $this->faker->randomElement($this->fetchAllIds('people')),
                'status'                => $this->faker->randomElement($statuses),
                'ticket_hash'           => md5($ref.$dateCreated),
                'brand_id'              => $this->faker->randomElement($this->fetchAllIds('brands')),
                'date_last_user_reply'  => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d H:i:s'),
                'date_last_agent_reply' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d H:i:s'),
            ];
        }

        $this->connection->batchInsert('tickets', $tickets);
        $newTicketIds = $this->connection->fetchAllCol('SELECT id FROM tickets WHERE ref IN (?)', [$refs], [Connection::PARAM_INT_ARRAY]);

        // create ticket messages
        $messages = [];
        foreach ($newTicketIds as $newTicketId) {
            if (!isset($options['messagesBatchCount'])) {
                $options['messagesBatchCount'] = $this->faker->randomDigitNotNull;
            }

            for ($i = 0; $i < $options['messagesBatchCount']; ++$i) {
                $messages[] = [
                    'ticket_id'     => $newTicketId,
                    'message'       => $this->faker->text(5),
                    'date_created'  => $this->faker->dateTimeBetween('-2 years', 'now')->format('Y-m-d H:i:s'),
                    'person_id'     => $this->faker->randomElement($this->fetchAllIds('people')),
                    'is_agent_note' => $this->faker->boolean(10),

                ];
            }
        }

        foreach (array_chunk($messages, $batchSize) as $messagesChunk) {
            $this->connection->batchInsert('tickets_messages', $messagesChunk);
        }

        if (!empty($options['postBatchCallback'])) {
            call_user_func(
                $options['postBatchCallback'],
                $newTicketIds,
                $this->connection,
                $this->faker
            );
        }

        if ($options['isLastBatch']) {
            /** @var \Application\DeskPRO\EntityRepository\Ticket $ticketRepository */
            $ticketRepository = $this->em->getRepository(Ticket::class);
            $ticketRepository->fillSearchTable();
        }
    }

    /**
     * @param array $extendedData
     * @param array $options
     *
     * @return $int
     */
    private function loadPerson(array $extendedData = [], array $options = [])
    {
        static $password;
        if (!$password) {
            $password = password_hash('pass', PASSWORD_BCRYPT, ['cost' => Bcrypt::ITERATIONS]);
        }

        // person info
        $this->connection->insert('people', array_merge(
            [
                'name'            => $this->faker->name,
                'is_user'         => true,
                'is_confirmed'    => true,
                'password'        => $password,
                'password_scheme' => 'bcrypt',
                'date_created'    => $this->faker->dateTime->format('c'),
            ],
            $extendedData
        ));

        $personId = $this->connection->lastInsertId();

        // primary email
        $this->connection->insert('people_emails', [
            'person_id' => $personId,
            'email'     => "dev-loaded_$personId@example.com",
        ]);

        $emailId = $this->connection->lastInsertId();
        $this->connection->update('people', ['primary_email_id' => $emailId], ['id' => $personId]);

        // usergroups
        $usergroupIds = $this->connection->fetchAllCol('SELECT id FROM usergroups WHERE sys_name IN(?, ?)', [
            Usergroup::EVERYONE,
            Usergroup::REGISTERED,
        ]);

        if (isset($options['usergroups'])) {
            foreach ($options['usergroups'] as $agentGroup) {
                if ($agentGroup === 'random') {
                    $agentGroupIds = $this->fetchAllIds('usergroups');
                    if (count($agentGroupIds)) {
                        $agentGroup = $this->faker->randomElement($agentGroupIds);
                    } else {
                        $agentGroup = null;
                    }
                }

                if ($agentGroup) {
                    $usergroupIds[] = $agentGroup;
                }
            }
        }

        $usergroups = [];
        foreach (array_unique($usergroupIds) as $usergroupsId) {
            $usergroups[] = [
                'person_id'    => $personId,
                'usergroup_id' => $usergroupsId,
            ];
        }

        $this->connection->batchInsert('person2usergroups', $usergroups);

        return $personId;
    }

    /**
     * @param string $table
     *
     * @return int[]
     */
    private function fetchAllIds($table)
    {
        if (!isset($this->cache[$table])) {
            $this->cache[$table] = $this->connection->fetchAllCol("SELECT id FROM $table");
        }

        return $this->cache[$table];
    }

    /**
     * @param array $options
     *
     * @return string
     */
    private function transformTicketFilterTerms(array $options = [])
    {
        $terms = [];
        if (isset($options['terms'])) {
            foreach ($options['terms'] as $termType => $term) {
                if (is_array($term)) {
                    $op    = $term['op'];
                    $value = $term['value'];
                } else {
                    $op    = 'is';
                    $value = $term;
                }

                $terms[] = [
                    'type'    => $termType,
                    'op'      => $op,
                    'options' => [
                        $termType => $value,
                    ],
                ];
            }
        }

        return json_encode($terms);
    }
}
