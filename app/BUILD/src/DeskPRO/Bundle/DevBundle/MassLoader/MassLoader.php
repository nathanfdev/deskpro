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

namespace DeskPRO\Bundle\DevBundle\MassLoader;

use Application\DeskPRO\DBAL\Connection;
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
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em         = $em;
        $this->connection = $em->getConnection();
        $this->faker      = Factory::create();
    }

    public function clearDb()
    {
        $this->connection->executeUpdate('DELETE FROM task_links');
        $this->connection->executeUpdate('DELETE FROM task_attachments');
        $this->connection->executeUpdate('DELETE FROM tickets');
        $this->connection->executeUpdate('DELETE FROM ticket_filters');
        $this->connection->executeUpdate('DELETE FROM people_emails');
        $this->connection->executeUpdate('DELETE FROM agent_team_members');
        $this->connection->executeUpdate('DELETE FROM people');
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
                    'is_global' => 0,
                    'title'     => $this->faker->title,
                    'sys_name'  => $personId.'_'.$num.'_'.$this->faker->word,
                    'terms'     => $this->transformTicketFilterTerms($filterOptions),
                    'person_id' => $personId,
                ]);
            }
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
    }

    /**
     * @param array $options
     */
    public function loadGlobalTicketFilter(array $options = [])
    {
        $this->connection->insert('ticket_filters', [
            'is_global' => 1,
            'title'     => $this->faker->title,
            'sys_name'  => $this->faker->unique()->word,
            'terms'     => $this->transformTicketFilterTerms($options),
        ]);
    }

    /**
     * @param array $options
     */
    public function loadTicket(array $options = [])
    {
        $this->connection->insert('tickets', [
            'subject'      => $this->faker->title,
            'ref'          => DpStrings::random(10, Strings::CHARS_ALPHA_IU).'-'.date('YzB'),
            'date_created' => $this->faker->dateTime->format('c'),
            'person_id'    => $this->faker->randomElement($this->fetchAllIds('people')),
            'agent_id'     => $this->faker->randomElement($this->fetchAllIds('people')),
        ]);

        $ticketId = $this->connection->lastInsertId();

        if (!isset($options['messageCount'])) {
            $options['messageCount'] = $this->faker->randomDigitNotNull;
        }

        for ($i = 0; $i < $options['messageCount']; ++$i) {
            $this->connection->insert('tickets_messages', [
                'ticket_id' => $ticketId,
                'message'   => $this->faker->text,
            ]);
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
                    $this->connection->insertIgnore('person2usergroups', [
                        'person_id'    => $personId,
                        'usergroup_id' => $agentGroup,
                    ]);
                }
            }
        }

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
