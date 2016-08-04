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

namespace DeskPRO\Bundle\DevBundle\Command;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;
use Application\DeskPRO\Entity\CustomDefTicket;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Ticket;
use Doctrine\ORM\EntityManager;
use Faker\Factory;
use Orb\Util\Strings;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DevLoadDataCommand.
 */
class DevLoadDataCommand extends ContainerAwareCommand
{
    /**
     * @var \Application\DeskPRO\DBAL\Connection
     */
    private $db;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var int
     */
    private $num = 1;

    /**
     * @var array
     */
    protected $_data_cache = [];

    /**
     * @var array
     */
    protected $_batch_insert = [];

    /**
     * @var array
     */
    protected $_batch_insert_ignore = [];

    /**
     * @var array
     */
    protected $_batch_insert_label_def = [];

    /**
     * @var null|int
     */
    protected $_start_ts = null;

    /**
     * @var null|int
     */
    protected $_date_offset = null;

    /**
     * @var array
     */
    protected $_ticket_statuses = [
        0 => 'awaiting_user',
        1 => 'awaiting_user',
        2 => 'awaiting_user',
        3 => 'awaiting_user',
        4 => 'awaiting_user',
        5 => 'resolved',
        6 => 'resolved',
        7 => 'resolved',
        8 => 'archived',
        9 => 'archived',
    ];

    protected $_label_type_map = [
        'article'      => ['labels_articles', 'article_id'],
        'download'     => ['labels_downloads', 'download_id'],
        'feedback'     => ['labels_feedback', 'feedback_id'],
        'news'         => ['labels_news', 'news_id'],
        'organization' => ['labels_organizations', 'organization_id'],
        'person'       => ['labels_people', 'person_id'],
        'ticket'       => ['labels_tickets', 'ticket_id'],
    ];

    /**
     * @var int
     */
    protected $_twitter_hits = 0;

    /**
     * @var int
     */
    protected $_person_hits = 0;

    /**
     * @var \Faker\Generator
     */
    private $faker;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('dpdev:load-data');
        $this->addOption(
            'count',
            null,
            InputOption::VALUE_REQUIRED,
            'Amount of data for each type to create',
            0
        );
        $this->addOption(
            'types',
            null,
            InputOption::VALUE_REQUIRED,
            'Comma separated list of data types (* for all)',
            ''
        );
        $this->addOption(
            'types-not',
            null,
            InputOption::VALUE_REQUIRED,
            'Comma separated list of data types to skip (implies --types=*)',
            ''
        );
        $this->addOption(
            'range',
            null,
            InputOption::VALUE_REQUIRED,
            'Range of dates to cover data for (eg, "3 years")',
            ''
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->faker = Factory::create();
        $this->db    = $this->getContainer()->get('database_connection');
        $this->em    = $this->getContainer()->get('doctrine.orm.default_entity_manager');

        $GLOBALS['DP_NOSQL_LOG'] = true;
        ini_set('memory_limit', -1);
        set_time_limit(0);
        $this->db->getConfiguration()->setSQLLogger(null);

        // todo: triggers, escalations, banned emails, banned IPs, agent teams, perm groups
        $availableTypes = [
            'custom_defs',
            'agent',
            'organization',
            'usergroup',
            'person',
            'sla',
            'ticket_department',
            'ticket',
            'ticket_filter',
            'ticket_snippet_category',
            'ticket_snippet',
            'ticket_macro',
            'chat_snippet_category',
            'chat_snippet',
            'chat_department',
            'feedback_status',
            'feedback_type',
            'feedback',
            'article_category',
            'article',
            'news_category',
            'news',
            'download_category',
            'download',
            'glossary',
            'task',
            'twitter_user',
            'twitter_status',
        ];
        $types_manual = ['agent', 'twitter_user', 'twitter_status'];

        $amount = intval($input->getOption('count'));
        if ($amount <= 0) {
            $amount = 100;
        }

        $type_input = $input->getOption('types');
        $types_not  = $input->getOption('types-not');
        if ($types_not) {
            if (!$type_input) {
                $type_input = '*';
            } else {
                echo "Cannot specify --types and --types-not together.\n";

                return 1;
            }
        }

        if ($type_input === '*') {
            $types = $availableTypes;

            foreach ($types_manual as $type_manual) {
                $manual_type_key = array_search($type_manual, $types);
                if ($manual_type_key !== false) {
                    unset($types[$manual_type_key]);
                }
            }

            if ($types_not) {
                $type_not_list = preg_split('/,\s*/', $types_not, -1, PREG_SPLIT_NO_EMPTY);
                foreach ($type_not_list as $not) {
                    $type_key = array_search($not, $types);
                    if ($type_key !== false) {
                        unset($types[$type_key]);
                    }
                }
            }
        } else {
            $types = preg_split('/,\s*/', $type_input, -1, PREG_SPLIT_NO_EMPTY);
        }

        if (!$types) {
            sort($availableTypes);
            echo "No types given. Cannot continue. Available types:\n\t".implode(', ', $availableTypes)."\n";

            return 2;
        }

        if (!$input->getOption('range')) {
            $output->writeln('A date range (--range) must be specified');

            return 3;
        }

        $range = $input->getOption('range');

        try {
            $start_date = new \DateTime('-'.$range);
        } catch (\Exception $e) {
            $output->writeln("Failed to parse date range '$range'.");

            return 4;
        }
        $this->_start_ts    = $start_date->getTimestamp();
        $this->_date_offset = (time() - $start_date->getTimestamp()) / $amount;

        $total = count($types);
        $begin = microtime(true);

        $this->db->exec('SET unique_checks=0');
        $this->db->exec('SET foreign_key_checks=0');
        $this->db->beginTransaction();

        // loop through all to keep the order the same as we create some dependent stuff first
        $type_count = 0;
        foreach ($availableTypes as $type) {
            if (!in_array($type, $types)) {
                continue;
            }

            $start = microtime(true);
            $count = ++$type_count;

            $method = '_load'.str_replace('_', '', $type);
            if (!method_exists($this, $method)) {
                echo str_pad(
                    sprintf('[%02d/%02d] %s is unknown, skipping.', $count, $total, $type),
                    60
                )."\n";
                continue;
            }

            $memory = memory_get_usage() / 1024 / 1024;

            echo str_pad(
                sprintf('[%02d/%02d] %s... 0/%d (%.2f MB)', $count, $total, $type, $amount, $memory),
                60
            )."\r";

            for ($i = 0; $i < $amount; ++$i) {
                $this->$method($i);

                if ($i > 0 && $i % 10 == 0) {
                    $time   = microtime(true) - $start;
                    $memory = memory_get_usage() / 1024 / 1024;

                    if ($i % 500 == 0) {
                        $this->_flushAndClear();
                    }

                    echo str_pad(
                        sprintf('[%02d/%02d] %s... %d/%d (%.2f s, %.2f MB)', $count, $total, $type, $i, $amount, $time, $memory),
                        60
                    )."\r";
                }
            }

            $time   = microtime(true) - $start;
            $memory = memory_get_usage() / 1024 / 1024;
            echo str_pad(
                sprintf('[%02d/%02d] %s... completing (%.2f s, %.2f MB)', $count, $total, $type, $time, $memory),
                60
            )."\r";

            $this->_flushAndClear();

            $complete_method = '_complete'.str_replace('_', '', $type);
            if (method_exists($this, $complete_method)) {
                $this->$complete_method();
            }

            $time   = microtime(true) - $start;
            $memory = memory_get_usage() / 1024 / 1024;
            echo str_pad(
                sprintf('[%02d/%02d] %s... Done, inserted %d (%.2f s, %.2f MB)', $count, $total, $type, $amount, $time, $memory),
                60
            )."\n";
        }

        $this->db->commit();
        $this->db->exec('SET unique_checks=1');
        $this->db->exec('SET foreign_key_checks=1');

        $time   = microtime(true) - $begin;
        $memory = memory_get_usage() / 1024 / 1024;
        echo "\n".
            sprintf('Data load completed (%.2f s, %.2f MB)', $time, $memory)
            ."\n";
    }

    protected function _flushAndClear()
    {
        $this->em->flush();

        foreach ($this->_batch_insert as $table => $batches) {
            $this->db->batchInsert($table, $batches);
        }
        foreach ($this->_batch_insert_ignore as $table => $batches) {
            $this->db->batchInsert($table, $batches, true);
        }
        foreach ($this->_batch_insert_label_def as $type => $labels) {
            $batches = [];
            foreach ($labels as $label => $total) {
                $batches[] = "('$type', '$label', $total)";
            }
            $this->db->executeUpdate('
                INSERT INTO label_defs
                    (label_type, label, total)
                VALUES
                    '.implode(',', $batches).'
                ON DUPLICATE KEY UPDATE total = VALUES(total);
            ');
        }

        $this->em->commit();
        $this->em->clear();
        $this->_data_cache             = [];
        $this->_batch_insert           = [];
        $this->_batch_insert_ignore    = [];
        $this->_batch_insert_label_def = [];
        gc_collect_cycles();

        $this->db->beginTransaction();
    }

    protected function _loadAgent()
    {
        $agent       = new Entity\Person();
        $agent->name = $this->faker->name;
        $agent->setEmail($this->getEmail(), true);
        $agent->date_created = $this->_getRandomDate();
        $agent->is_user      = true;
        $agent->is_confirmed = true;
        $agent->is_agent     = true;

        $this->em->persist($agent);
        $this->em->flush();

        // Default to non-destructive perm group, or if thats deleted, the default all perms group
        $has_ug = $this->db->fetchColumn('SELECT id FROM usergroups WHERE id IN (4,3) ORDER BY id DESC');
        if ($has_ug) {
            $this->db->insert('person2usergroups', [
                'person_id'    => $agent->getId(),
                'usergroup_id' => $has_ug,
            ]);
        }

        // Default access to all departments
        if (!isset($this->_data_cache['all_departments'])) {
            $this->_data_cache['all_departments'] = App::getDataService('Department')->getAll();
        }

        $batch = [];
        foreach ($this->_data_cache['all_departments'] as $dep) {
            $batch[] = [
                'department_id' => $dep->getId(),
                'person_id'     => $agent->getId(),
                'app'           => $dep->is_tickets_enabled ? 'tickets' : 'chat',
                'name'          => 'full',
                'value'         => 1,
                'is_active'     => 1,
            ];
        }

        $this->db->batchInsert('department_permissions', $batch);

        // Default notifications
        $agent_id = $agent->getId();
        $this->db->executeUpdate("
            INSERT INTO `people_prefs` (`person_id`, `name`, `value_str`, `value_array`, `date_expire`)
            VALUES
                ($agent_id, 'agent_notif.chat_message.email', '1', X'4E3B', NULL),
                ($agent_id, 'agent_notif.login_attempt_fail.email', '1', X'4E3B', NULL),
                ($agent_id, 'agent_notif.task_assign_self.email', '1', X'4E3B', NULL),
                ($agent_id, 'agent_notif.task_assign_self.alert', '1', X'4E3B', NULL),
                ($agent_id, 'agent_notif.task_assign_team.email', '1', X'4E3B', NULL),
                ($agent_id, 'agent_notif.task_assign_team.alert', '1', X'4E3B', NULL),
                ($agent_id, 'agent_notif.task_complete.email', '1', X'4E3B', NULL),
                ($agent_id, 'agent_notif.task_complete.alert', '1', X'4E3B', NULL),
                ($agent_id, 'agent_notif.task_due.email', '1', X'4E3B', NULL),
                ($agent_id, 'agent_notif.task_due.alert', '1', X'4E3B', NULL),
                ($agent_id, 'agent_notif.new_comment.alert', '1', X'4E3B', NULL),
                ($agent_id, 'agent_notif.new_comment.email', '1', X'4E3B', NULL),
                ($agent_id, 'agent_notif.new_comment_validate.alert', '1', X'4E3B', NULL),
                ($agent_id, 'agent_notif.new_comment_validate.email', '1', X'4E3B', NULL),
                ($agent_id, 'agent_notif.new_feedback.alert', '1', X'4E3B', NULL),
                ($agent_id, 'agent_notif.new_feedback.email', '1', X'4E3B', NULL),
                ($agent_id, 'agent_notif.new_feedback_validate.alert', '1', X'4E3B', NULL),
                ($agent_id, 'agent_notif.new_feedback_validate.email', '1', X'4E3B', NULL),
                ($agent_id, 'agent_notif.new_user.alert', '1', X'4E3B', NULL)
        ");

        // Add pref for first login marker
        $this->db->insert('people_prefs', [
            'person_id'   => $agent_id,
            'name'        => 'agent.first_login',
            'value_str'   => 1,
            'value_array' => null,
            'date_expire' => null,
        ]);
        $this->db->insert('people_prefs', [
            'person_id'   => $agent_id,
            'name'        => 'agent.first_login_name',
            'value_str'   => 1,
            'value_array' => null,
            'date_expire' => null,
        ]);
    }

    protected function _loadCustomDefs()
    {
        $customDefClasses = [
            Entity\CustomDefOrganization::class,
            Entity\CustomDefPerson::class,
            Entity\CustomDefTicket::class,
            Entity\CustomDefChat::class,
            Entity\CustomDefFeedback::class,
            Entity\CustomDefArticle::class,
        ];

        foreach ($customDefClasses as $customDefClass) {
            $org_field                = new $customDefClass();
            $org_field->title         = $this->faker->title;
            $org_field->description   = $this->faker->realText();
            $org_field->handler_class = Entity\CustomDefAbstract::HANDLER_CLASS_TEXT;

            $this->em->persist($org_field);
        }
    }

    protected function _loadOrganization()
    {
        if (!isset($this->_data_cache['usergroups'])) {
            $this->_data_cache['usergroups'] = $this->em->getRepository('DeskPRO:Usergroup')->findAll();
        }
        if (!isset($this->_data_cache['org_fields'])) {
            $this->_data_cache['org_fields'] = $this->em->getRepository('DeskPRO:CustomDefOrganization')->findAll();
        }

        $this->db->insert('organizations', [
            'name'         => $this->faker->title,
            'date_created' => $this->_getRandomDate('string'),
        ]);

        $orgId = $this->db->lastInsertId();
        $this->_applyLabelsDb('organization', $orgId);

        if (mt_rand(1, 4) == 1) {
            $count = mt_rand(1, 3);
            for ($i = 0; $i < $count; ++$i) {
                $ug_id = $this->_getRandomFromCache('usergroups', 'id');
                $this->db->executeUpdate('
                    INSERT IGNORE INTO organization2usergroups
                        (organization_id, usergroup_id)
                    VALUES
                        (?, ?)
                ', [$orgId, $ug_id]);
            }
        }

        foreach ($this->_data_cache['org_fields'] as $field) {
            if ($field->getTypeName() == 'text') {
                $this->db->insert('custom_data_organizations', [
                    'organization_id' => $orgId,
                    'field_id'        => $field->id,
                    'root_field_id'   => $field->id,
                    'value'           => 0,
                    'input'           => $this->faker->word,
                ]);
            }
        }

        // todo: contact data
    }

    protected function _loadUsergroup()
    {
        $usergroup        = new Entity\Usergroup();
        $usergroup->title = $this->faker->title;
        $usergroup->note  = $this->faker->text;

        $this->em->persist($usergroup);
    }

    protected function _loadPerson()
    {
        if (!isset($this->_data_cache['usergroups'])) {
            $this->_data_cache['usergroups'] = $this->em->getRepository('DeskPRO:Usergroup')->findAll();
        }
        if (!isset($this->_data_cache['person_fields'])) {
            $this->_data_cache['person_fields'] = $this->em->getRepository('DeskPRO:CustomDefPerson')->findAll();
        }

        $first_name = $this->faker->firstName;
        $last_name  = $this->faker->lastName;

        $person = [
            'name'         => "$first_name $last_name",
            'first_name'   => $first_name,
            'last_name'    => $last_name,
            'date_created' => $this->_getRandomDate('string'),
            'gravatar_url' => '',
        ];
        if (mt_rand(1, 3) == 1) {
            $person['organization_id'] = $this->_getRandomOrgId();
        }

        $person_ent = new Entity\Person();
        $person     = array_merge($person_ent->getScalarData(), $person);

        $this->db->insert('people', $person);
        $person['id'] = $this->db->lastInsertId();

        $email = [
            'person_id'      => $person['id'],
            'email'          => $this->getEmail(),
            'email_domain'   => 'example.com',
            'is_validated'   => 1,
            'date_created'   => $person['date_created'],
            'date_validated' => $person['date_created'],
        ];
        $this->db->insert('people_emails', $email);
        $email['id'] = $this->db->lastInsertId();

        $person['primary_email_id'] = $email['id'];
        $this->db->update('people',
            ['primary_email_id' => $email['id']],
            ['id'               => $person['id']]
        );

        $this->_applyLabelsDb('person', $person['id']);

        if (mt_rand(1, 4) == 1) {
            $count = mt_rand(1, 3);
            for ($i = 0; $i < $count; ++$i) {
                $this->_addBatchInsert('person2usergroups', [
                    'person_id'    => $person['id'],
                    'usergroup_id' => $this->_getRandomFromCache('usergroups', 'id'),
                ], true);
            }
        }

        foreach ($this->_data_cache['person_fields'] as $field) {
            if ($field->getTypeName() == 'text') {
                $this->_addBatchInsert('custom_data_person', [
                    'person_id'     => $person['id'],
                    'field_id'      => $field->id,
                    'root_field_id' => $field->id,
                    'value'         => 0,
                    'input'         => $this->faker->word,
                ]);
            }
        }

        // todo: contact data, secondary emails
    }

    protected function _loadSla()
    {
        $sla        = new Entity\Sla();
        $sla->title = $this->faker->title;
        $types      = [
            0 => \Application\DeskPRO\Entity\Sla::TYPE_FIRST_RESPONSE,
            1 => \Application\DeskPRO\Entity\Sla::TYPE_RESOLUTION,
            2 => \Application\DeskPRO\Entity\Sla::TYPE_WAITING_TIME,
        ];
        $sla->sla_type    = $types[mt_rand(0, 2)];
        $sla->active_time = 'all';
        $sla->apply_type  = mt_rand(1, 6) == 1 ? 'all' : 'manual';

        $this->em->persist($sla);
        $this->em->flush();

        $warning_trigger                = new Entity\TicketTrigger();
        $warning_trigger->title         = $sla->title.' - SLA Warning';
        $warning_trigger->event_trigger = 'sla.warning';
        $warning_time                   = mt_rand(30, 500);
        $time                           = $warning_time.' minutes';
        $warning_trigger->setEventTriggerOption('time', $time);
        $warning_trigger->terms->addTermFromArray([
            'type'    => 'CheckSlaStatus',
            'op'      => 'is',
            'options' => ['sla_status' => 'warning', 'sla_ids' => [$sla->id]],
        ]);
        $warning_trigger->actions->addActionFromArray([
            'type'    => 'SetStatus',
            'options' => ['status' => 'awaiting_agent'],
        ]);

        $this->em->persist($warning_trigger);

        $fail_trigger                = new Entity\TicketTrigger();
        $fail_trigger->title         = $sla->title.' - SLA Failure';
        $fail_trigger->event_trigger = 'sla.fail';
        $time                        = mt_rand($warning_time, 600).' minutes';
        $fail_trigger->setEventTriggerOption('time', $time);
        $fail_trigger->terms->addTermFromArray([
            'type'    => 'CheckSlaStatus',
            'op'      => 'is',
            'options' => ['sla_status' => 'fail', 'sla_ids' => [$sla->id]],
        ]);
        $fail_trigger->actions->addActionFromArray([
            'type'    => 'SetStatus',
            'options' => ['status' => 'awaiting_user'],
        ]);

        $this->em->persist($fail_trigger);

        $sla->warning_trigger = $warning_trigger;
        $sla->fail_trigger    = $fail_trigger;
        $this->em->persist($sla);
    }

    protected function _loadTicketDepartment()
    {
        $department                     = new Entity\Department();
        $department->title              = $this->faker->title;
        $department->is_tickets_enabled = true;
        $department->is_chat_enabled    = false;
        $department->display_order      = mt_rand(1, 1000000);
        if (!empty($this->_data_cache['ticket_department_parent'])) {
            $department->parent = $this->_data_cache['ticket_department_parent'];
        }

        $this->em->persist($department);
        $this->em->flush($department);

        $dep_perms = [];

        $this->_getRandomAgent();

        foreach ($this->_data_cache['agents'] as $agent) {
            $dep_perms[] = [
                'department_id' => $department->getId(),
                'usergroup_id'  => null,
                'person_id'     => $agent->getId(),
                'app'           => 'tickets',
                'name'          => 'full',
                'value'         => 1,
                'is_active'     => 1,
            ];
            $dep_perms[] = [
                'department_id' => $department->getId(),
                'usergroup_id'  => null,
                'person_id'     => $agent->getId(),
                'app'           => 'tickets',
                'name'          => 'assign',
                'value'         => 1,
                'is_active'     => 1,
            ];
        }

        $dep_perms[] = [
            'department_id' => $department->getId(),
            'usergroup_id'  => 1,
            'person_id'     => null,
            'app'           => 'tickets',
            'name'          => 'full',
            'value'         => 1,
            'is_active'     => 1,
        ];

        $this->db->batchInsert('department_permissions', $dep_perms);

        if (empty($this->_data_cache['ticket_department_parent'])) {
            $this->_data_cache['ticket_department_parent'] = $department;
        }
    }

    protected function _loadTicket()
    {
        if (!isset($this->_data_cache['ticket_departments'])) {
            $this->_data_cache['ticket_departments'] = $this->em->getRepository(Department::class)->getChildDepartments('ticket');
        }
        if (!isset($this->_data_cache['ticket_fields'])) {
            $this->_data_cache['ticket_fields'] = $this->em->getRepository(CustomDefTicket::class)->findAll();
        }

        $date_created = $this->_getRandomDate();
        $ticket       = [
            'subject'         => $this->faker->title,
            'date_created'    => $date_created->format('Y-m-d H:i:s'),
            'person_id'       => $this->_getRandomPersonId(),
            'department_id'   => $this->_getRandomFromCache('ticket_departments', 'id'),
            'creation_system' => Entity\Ticket::CREATED_WEB_API,
            'ref'             => App::getRefGenerator()->generateReference(Ticket::class),
        ];

        if (mt_rand(0, 2) == 0) {
            $rand = $this->_getRandomAgent();
            if ($rand) {
                $ticket['agent_id'] = $rand->id;
            }
        }
        if (time() - $date_created->getTimestamp() > 90 * 86400) {
            $ticket['status'] = 'archived';
        } else {
            if (mt_rand(0, 100) == 0) {
                $ticket['status'] = 'awaiting_agent';
            } else {
                $ticket['status'] = $this->_ticket_statuses[mt_rand(0, 9)];
            }
        }

        $ticket_ent = new Entity\Ticket(false);
        $ticket     = array_merge($ticket_ent->getScalarData(), $ticket);

        $this->db->insert('tickets', $ticket);
        $ticket['id'] = $this->db->lastInsertId();

        $this->_applyLabelsDb('ticket', $ticket['id']);

        $message = [
            'ticket_id'       => $ticket['id'],
            'person_id'       => $ticket['person_id'],
            'is_agent_note'   => 0,
            'creation_system' => Entity\TicketMessage::CREATED_WEB_API,
            'message'         => $this->faker->realText(),
            'date_created'    => $ticket['date_created'],
        ];

        if (mt_rand(1, 50) == 1) {
            $this->db->insert('tickets_messages', $message);
            $message['id'] = $this->db->lastInsertId();
            $this->_addTicketMessageAttachments($ticket['id'], $message);
        } else {
            $this->_addBatchInsert('tickets_messages', $message);
        }

        $message_count = mt_rand(0, 10);
        if ($message_count > 0) {
            $range = $date_created->getTimestamp() + mt_rand(200, max(201, time() - $date_created->getTimestamp()));
            for ($j = 0; $j < $message_count; ++$j) {
                $is_agent = !empty($ticket['agent_id']) && mt_rand(0, 1);
                $message  = [
                    'ticket_id'       => $ticket['id'],
                    'person_id'       => $is_agent ? $ticket['agent_id'] : $ticket['person_id'],
                    'is_agent_note'   => ($is_agent && mt_rand(0, 1) ? 1 : 0),
                    'creation_system' => Entity\TicketMessage::CREATED_WEB_API,
                    'message'         => $this->faker->realText(),
                    'date_created'    => $this->_getRandomDate('string', $ticket['date_created'], $range),
                ];
                if (mt_rand(1, 50) == 1) {
                    $this->db->insert('tickets_messages', $message);
                    $message['id'] = $this->db->lastInsertId();
                    $this->_addTicketMessageAttachments($ticket['id'], $message);
                } else {
                    $this->_addBatchInsert('tickets_messages', $message);
                }
            }
        }

        foreach ($this->_data_cache['ticket_fields'] as $field) {
            if ($field->getTypeName() == 'text') {
                $this->_addBatchInsert('custom_data_ticket', [
                    'ticket_id'     => $ticket['id'],
                    'field_id'      => $field->id,
                    'root_field_id' => $field->id,
                    'value'         => 0,
                    'input'         => $this->faker->words(3, true),
                ]);
            }
        }
    }

    /**
     * @param int   $ticket_id
     * @param array $message
     */
    protected function _addTicketMessageAttachments($ticket_id, array $message)
    {
        $amount = mt_rand(1, 3);
        for ($i = 0; $i < $amount; ++$i) {
            $file_info = $this->faker->randomElement(
                [
                    ['name' => 'file.txt', 'ext' => 'txt', 'type' => 'text/plain', 'content' => 'example file'],
                    [
                        'name' => 'file.zip',
                        'ext'  => 'zip',
                        'type' => 'application/zip',
                        'file' => DP_APP_DIR.'/src/Application/AdminInterfaceBundle/Resources/assets/Bulk-Add-Agents-Spreadsheet-Template.zip',
                    ],
                    [
                        'name' => 'file.pdf',
                        'ext'  => 'pdf',
                        'type' => 'application/pdf',
                        'file' => DP_APP_DIR.'/src/Application/AgentBundle/Resources/assets/agent-quickstart/en_US.pdf',
                    ],
                    [
                        'name' => 'file.jpg',
                        'ext'  => 'jpg',
                        'type' => 'image/jpeg',
                        'file' => DP_APP_DIR.'/src/Application/DeskPRO/Resources/assets/avatar-man-face.png',
                    ],
                ]
            );

            $blob = $this->getContainer()->get('blob.storage')->createBlobRecordFromString(
                @$file_info['content'] ?: file_get_contents($file_info['file']),
                $file_info['name'],
                $file_info['type']
            );

            $this->_addBatchInsert('tickets_attachments', [
                'ticket_id'     => $ticket_id,
                'person_id'     => $message['person_id'],
                'message_id'    => $message['id'],
                'blob_id'       => $blob->id,
                'is_agent_note' => 0,
                'is_inline'     => 0,
            ]);
        }
    }

    protected function _loadTicketFilter()
    {
        $possible_terms = [
            0 => ['type' => 'subject', 'op' => 'contains', 'options' => ['subject' => 'test']],
            1 => ['type' => 'urgency', 'op' => 'gte', 'options' => ['num' => '5']],
            2 => ['type' => 'label', 'op' => 'is', 'options' => ['labels' => ['test']]],
            3 => ['type' => 'person_email_domain', 'op' => 'is', 'options' => ['email_domain' => 'example.com']],
            4 => ['type' => 'person_contact_phone', 'op' => 'contains', 'options' => ['phone' => '123']],
            5 => ['type' => 'org_label', 'op' => 'is', 'options' => ['label' => 'organization']],
            6 => ['type' => 'org_email_domain', 'op' => 'is', 'options' => ['email_domain' => 'example.com']],
            7 => ['type' => 'agent', 'op' => 'is', 'options' => ['agent' => '0']],
            8 => ['type' => 'organization', 'op' => 'is', 'options' => ['organization' => $this->_getRandomOrgId()]],
            9 => [
                'type'    => 'date_created',
                'op'      => 'lte',
                'options' => [
                    'date1'               => '',
                    'date2'               => '',
                    'date1_relative'      => '5',
                    'date1_relative_type' => 'days',
                    'date2_relative'      => '',
                    'date2_relative_type' => '',
                ],
            ],
        ];

        $filter            = new Entity\LegacyTicketFilter();
        $filter->title     = $this->faker->title;
        $filter->is_global = true;

        $terms = [];
        $count = mt_rand(1, 4);
        for ($i = 0; $i < $count; ++$i) {
            $k         = mt_rand(0, 9);
            $terms[$k] = $possible_terms[$k];
        }
        $filter->terms = array_values($terms);

        $this->em->persist($filter);
    }

    protected function _loadTicketMacro()
    {
        $macro             = new Entity\TicketMacro();
        $macro->title      = $this->faker->title;
        $macro->is_global  = (mt_rand(0, 1) == 1);
        $macro->is_enabled = true;
        $macro->actions    = [
            ['type' => 'agent', 'options' => ['agent' => '-1']],
        ];
        $macro->person = $this->_getRandomAgent();

        $this->em->persist($macro);
    }

    protected function _loadTicketSnippetCategory()
    {
        $category            = new Entity\TextSnippetCategory();
        $category->is_global = true;
        $category->person    = $this->_getRandomAgent();
        $category->typename  = 'tickets';

        $this->em->persist($category);
        $this->em->flush();

        $this->db->replace('object_lang', [
            'language_id' => 1,
            'ref'         => 'text_snippet_categories.'.$category->getId(),
            'prop_name'   => 'title',
            'value'       => $this->faker->word,
        ]);
    }

    protected function _loadTicketSnippet()
    {
        if (!isset($this->_data_cache['ticket_snippet_categories'])) {
            $this->_data_cache['ticket_snippet_categories'] = $this->em->getRepository('DeskPRO:TextSnippetCategory')->findAll();
        }

        $snippet           = new Entity\TextSnippet();
        $snippet->category = $this->_getRandomFromCache('text_snippet_categories');
        $snippet->person   = $this->_getRandomAgent();

        $title = $this->faker->title;
        $text  = $this->faker->text;

        $this->em->persist($snippet);
        $this->em->flush();

        $this->db->replace('object_lang', [
            'language_id' => 1,
            'ref'         => 'text_snippets.'.$snippet->getId(),
            'prop_name'   => 'title',
            'value'       => $title,
        ]);

        $this->db->replace('object_lang', [
            'language_id' => 1,
            'ref'         => 'text_snippets.'.$snippet->getId(),
            'prop_name'   => 'snippet',
            'value'       => $text,
        ]);
    }

    protected function _loadChatSnippetCategory()
    {
        $category            = new Entity\TextSnippetCategory();
        $category->typename  = 'chat';
        $category->is_global = true;
        $category->person    = $this->_getRandomAgent();

        $this->em->persist($category);
        $this->db->replace('object_lang', [
            'language_id' => 1,
            'ref'         => 'text_snippet_categories.'.$category->getId(),
            'prop_name'   => 'title',
            'value'       => $this->faker->title,
        ]);
    }

    protected function _loadChatSnippet()
    {
        if (!isset($this->_data_cache['chat_snippet_categories'])) {
            $this->_data_cache['chat_snippet_categories'] = $this->em->getRepository('DeskPRO:TextSnippetCategory')->getAllByType('chat');
        }

        $snippet           = new Entity\TextSnippet();
        $snippet->category = $this->_getRandomFromCache('chat_snippet_categories');
        $snippet->person   = $this->_getRandomAgent();

        $this->em->persist($snippet);
        $this->db->replace('object_lang', [
            'language_id' => 1,
            'ref'         => 'text_snippets.'.$snippet->getId(),
            'prop_name'   => 'title',
            'value'       => $this->faker->title,
        ]);
        $this->db->replace('object_lang', [
            'language_id' => 1,
            'ref'         => 'text_snippets.'.$snippet->getId(),
            'prop_name'   => 'snippet',
            'value'       => $this->faker->text,
        ]);
    }

    protected function _loadChatDepartment()
    {
        $department                     = new Entity\Department();
        $department->title              = $this->faker->title;
        $department->is_tickets_enabled = false;
        $department->is_chat_enabled    = true;
        $department->display_order      = mt_rand(1, 1000000);
        if (!empty($this->_data_cache['chat_department_parent'])) {
            $department->parent = $this->_data_cache['chat_department_parent'];
        }

        $this->em->persist($department);
        $this->em->flush($department);

        $dep_perms = [];

        $this->_getRandomAgent();
        foreach ($this->_data_cache['agents'] as $agent) {
            $dep_perms[] = [
                'department_id' => $department->getId(),
                'usergroup_id'  => null,
                'person_id'     => $agent->getId(),
                'app'           => 'chat',
                'name'          => 'full',
                'value'         => 1,
                'is_active'     => 1,
            ];
        }

        $dep_perms[] = [
            'department_id' => $department->getId(),
            'usergroup_id'  => 1,
            'person_id'     => null,
            'app'           => 'chat',
            'name'          => 'full',
            'value'         => 1,
            'is_active'     => 1,
        ];

        $this->db->batchInsert('department_permissions', $dep_perms);

        if (empty($this->_data_cache['chat_department_parent'])) {
            $this->_data_cache['chat_department_parent'] = $department;
        }
    }

    protected function _loadFeedbackType()
    {
        $category                = new Entity\FeedbackCategory();
        $category->title         = $this->faker->title;
        $category->display_order = mt_rand(1, 1000000);

        $this->em->persist($category);
        $this->em->flush();

        $this->db->insert('feedback_category2usergroup', [
            'category_id'  => $category->getId(),
            'usergroup_id' => 1,
        ]);
    }

    protected function _loadFeedbackStatus()
    {
        $category                = new Entity\FeedbackStatusCategory();
        $category->title         = $this->faker->title;
        $category->display_order = mt_rand(1, 1000000);
        $category->status_type   = mt_rand(1, 2) == 1 ? 'active' : 'closed';

        $this->em->persist($category);
    }

    protected function _loadFeedback()
    {
        if (!isset($this->_data_cache['feedback_types'])) {
            $this->_data_cache['feedback_types'] = $this->em->getRepository('DeskPRO:FeedbackCategory')->findAll();
        }
        if (!isset($this->_data_cache['feedback_statuses'])) {
            $this->_data_cache['feedback_statuses'] = $this->em->getRepository('DeskPRO:FeedbackStatusCategory')->findAll();
        }

        $title    = $this->faker->title;
        $feedback = [
            'title'        => $title,
            'slug'         => Strings::slugifyTitle($title).microtime().$this->num++,
            'content'      => $this->faker->realText(),
            'date_created' => $this->_getRandomDate('string'),
            'status'       => 'published',
            'person_id'    => $this->_getRandomAgent(true),
            'category_id'  => $this->_getRandomFromCache('feedback_types', 'id'),
        ];

        if (mt_rand(1, 3) == 1) {
            $feedback['status'] = 'new';
        } else {
            $status                         = $this->_getRandomFromCache('feedback_statuses');
            $feedback['status']             = $status->status_type;
            $feedback['status_category_id'] = $status->id;
        }

        $feedback_ent = new Entity\Feedback();
        $feedback     = array_merge($feedback_ent->getScalarData(), $feedback);

        $this->db->insert('feedback', $feedback);
        $feedback['id'] = $this->db->lastInsertId();

        $this->_applyLabelsDb('feedback', $feedback['id']);

        // todo: attachments, user categories, validation?, comments
    }

    protected function _loadArticleCategory()
    {
        $category                = new Entity\ArticleCategory();
        $category->title         = $this->faker->title;
        $category->display_order = mt_rand(1, 1000000);

        $this->em->persist($category);
        $this->em->flush();

        $this->db->insert('article_category2usergroup', [
            'category_id'  => $category->getId(),
            'usergroup_id' => 1,
        ]);
    }

    protected function _loadArticle()
    {
        if (!isset($this->_data_cache['article_categories'])) {
            $this->_data_cache['article_categories'] = $this->em->getRepository('DeskPRO:ArticleCategory')->findAll();
        }
        if (!isset($this->_data_cache['article_fields'])) {
            $this->_data_cache['article_fields'] = $this->em->getRepository('DeskPRO:CustomDefArticle')->findAll();
        }

        $title   = $this->faker->title;
        $article = [
            'title'        => $title,
            'slug'         => Strings::slugifyTitle($title).microtime().$this->num++,
            'content'      => htmlspecialchars($this->faker->text()),
            'date_created' => $this->_getRandomDate('string'),
            'status'       => 'published',
            'person_id'    => $this->_getRandomAgent(true),
        ];

        $article_ent = new Entity\Article();
        $article     = array_merge($article_ent->getScalarData(), $article);

        $this->db->insert('articles', $article);
        $article['id'] = $this->db->lastInsertId();

        $this->_applyLabelsDb('article', $article['id']);

        $this->db->insert('article_to_categories', [
            'article_id'  => $article['id'],
            'category_id' => $this->_getRandomFromCache('article_categories', 'id'),
        ]);

        foreach ($this->_data_cache['article_fields'] as $field) {
            if ($field->getTypeName() == 'text') {
                $this->db->insert('custom_data_article', [
                    'article_id'    => $article['id'],
                    'field_id'      => $field->id,
                    'root_field_id' => $field->id,
                    'value'         => 0,
                    'input'         => $this->faker->word,
                ]);
            }
        }

        // todo: products, attachments, comments (with validation), varied statuses
    }

    protected function _loadNewsCategory()
    {
        $category                = new Entity\NewsCategory();
        $category->title         = $this->faker->title;
        $category->display_order = mt_rand(1, 1000000);

        $this->em->persist($category);
        $this->em->flush();

        $this->db->insert('news_category2usergroup', [
            'category_id'  => $category->getId(),
            'usergroup_id' => 1,
        ]);
    }

    protected function _loadNews()
    {
        if (!isset($this->_data_cache['news_categories'])) {
            $this->_data_cache['news_categories'] = $this->em->getRepository('DeskPRO:NewsCategory')->findAll();
        }

        $title = $this->faker->title;
        $news  = [
            'title'        => $title,
            'slug'         => Strings::slugifyTitle($title).microtime().$this->num++,
            'content'      => $this->faker->realText(),
            'date_created' => $this->_getRandomDate('string'),
            'status'       => 'published',
            'person_id'    => $this->_getRandomAgent(true),
            'category_id'  => $this->_getRandomFromCache('news_categories', 'id'),
        ];

        $ent  = new Entity\News();
        $news = array_merge($ent->getScalarData(), $news);

        $this->db->insert('news', $news);
        $news['id'] = $this->db->lastInsertId();

        $this->_applyLabelsDb('news', $news['id']);

        // todo: attachments, comments (with validation)
    }

    protected function _loadDownloadCategory()
    {
        $category                = new Entity\DownloadCategory();
        $category->title         = $this->faker->title;
        $category->display_order = mt_rand(1, 1000000);

        $this->em->persist($category);
        $this->em->flush();

        $this->db->insert('download_category2usergroup', [
            'category_id'  => $category->getId(),
            'usergroup_id' => 1,
        ]);
    }

    protected function _loadDownload()
    {
        if (!isset($this->_data_cache['download_categories'])) {
            $this->_data_cache['download_categories'] = $this->em->getRepository('DeskPRO:DownloadCategory')->findAll();
        }

        $title    = $this->faker->title;
        $download = [
            'title'        => $title,
            'slug'         => Strings::slugifyTitle($title).microtime().$this->num++,
            'content'      => htmlspecialchars($this->faker->realText()),
            'date_created' => $this->_getRandomDate('string'),
            'status'       => 'published',
            'person_id'    => $this->_getRandomAgent(true),
            'category_id'  => $this->_getRandomFromCache('download_categories', 'id'),
        ];

        $ent      = new Entity\Download();
        $download = array_merge($ent->getScalarData(), $download);

        $this->db->insert('downloads', $download);
        $download['id'] = $this->db->lastInsertId();

        $this->_applyLabelsDb('download', $download['id']);

        // todo: attachments, comments (with validation)
    }

    protected function _loadGlossary()
    {
        $def             = new Entity\GlossaryWordDefinition();
        $def->definition = $this->faker->realText();
        $word_count      = mt_rand(1, 5);
        for ($i = 0; $i < $word_count; ++$i) {
            $word = new Entity\GlossaryWord();
            $word->setWord($this->faker->word);
            $def->addWord($word);
        }

        if (count($def->words)) {
            $this->em->persist($def);
            $this->em->flush(); // need to flush each as might get a dupe error
        }
    }

    protected function _loadTask()
    {
        $task         = new Entity\Task();
        $task->title  = $this->faker->title;
        $task->person = $this->_getRandomAgent();
        $task->setVisibility(mt_rand(1, 3) == 1 ? 0 : 1);
        $task->date_created = $this->_getRandomDate();
        if (mt_rand(0, 1)) {
            $task->due_date = new \DateTime('@'.($task->date_created->getTimestamp() + mt_rand(10000, 10000000)));
        }
        if (mt_rand(1, 3) == 1) {
            $task->assigned_agent = $this->_getRandomAgent();
        } elseif (mt_rand(1, 3) == 1) {
            $task->assigned_agent_team = $this->_getRandomAgentTeam();
        }

        $task->setCompleted(mt_rand(1, 3) == 1);

        // todo: comments, ticket linking

        $this->em->persist($task);
        $this->_applyLabels($task);
    }

    protected function _loadTwitterUser()
    {
        $this->_addBatchInsert('twitter_users', [
            'id'                   => mt_rand(1, mt_getrandmax()),
            'name'                 => $this->faker->name,
            'screen_name'          => $this->faker->name,
            'profile_image_url'    => '',
            'language'             => 'en',
            'is_protected'         => 0,
            'is_verified'          => 0,
            'location'             => '',
            'description'          => $this->faker->realText(),
            'is_geo_enabled'       => 0,
            'is_stub'              => 0,
            'url'                  => '',
            'last_timeline_update' => null,
            'last_profile_update'  => null,
            'followers_count'      => 0,
            'friends_count'        => 0,
            'last_follow_update'   => null,
        ], true);
    }

    protected function _loadTwitterStatus()
    {
        $data = [
            'id'           => mt_rand(1, mt_getrandmax()),
            'user_id'      => $this->_getRandomTwitterUserId(),
            'text'         => $this->faker->realText(),
            'date_created' => $this->_getRandomDate('string'),
        ];

        $modified = $this->db->executeUpdate('
            INSERT IGNORE INTO twitter_statuses
                (id, user_id, text, is_truncated, date_created)
            VALUES (?, ?, ?, 0, ?)
        ', [$data['id'], $data['user_id'], $data['text'], $data['date_created']]);

        if ($modified == 2) {
            return;
        }

        $status_types = [
            0 => 'direct',
            1 => 'reply',
            2 => 'mention',
            3 => 'retweet',
            4 => 'timeline',
            5 => 'timeline',
            6 => 'timeline',
            7 => 'timeline',
            8 => null,
        ];

        $this->_addBatchInsert('twitter_accounts_statuses', [
            'account_id'      => 1,
            'status_id'       => $data['id'],
            'agent_id'        => mt_rand(0, 1) ? $this->_getRandomAgent(true) : null,
            'agent_team_id'   => null,
            'retweeted_id'    => null,
            'in_reply_to_id'  => null,
            'date_created'    => $data['date_created'],
            'status_type'     => $status_types[mt_rand(0, 8)],
            'is_archived'     => mt_rand(1, 1000) == 1 ? 0 : 1,
            'is_favorited'    => mt_rand(1, 10000) == 1 ? 1 : 0,
            'action_agent_id' => null,
        ]);
    }

    /**
     * @param null $format
     * @param null $start
     * @param null $end
     *
     * @return \DateTime|int|string
     */
    private function _getRandomDate($format = null, $start = null, $end = null)
    {
        if ($start === null) {
            $start = $this->_start_ts;
        }
        if ($start instanceof \DateTime) {
            $start = $start->getTimestamp();
        }
        $start = intval($start);

        if ($end === null) {
            $end = time();
        }
        if ($end instanceof \DateTime) {
            $end = $end->getTimestamp();
        }
        $end = intval($end);
        if ($end <= $start) {
            $end = $start + 1000;
        }

        $rand = mt_rand($start, $end);
        switch ($format) {
            case 'ts':
                return $rand;

            case 'string':
                return gmdate('Y-m-d H:i:s', $rand);

            default:
                return new \DateTime('@'.$rand);
        }
    }

    private function _getRandomPersonId()
    {
        if ($this->_person_hits <= 0 || !isset($this->_data_cache['random_people_ids'])) {
            $this->_data_cache['random_people_ids'] = $this->db->fetchAllCol('
                SELECT id
                FROM people
                WHERE is_agent = 0
                ORDER BY RAND()
                LIMIT 1000
            ');
            if (!$this->_data_cache['random_people_ids']) {
                if (empty($this->_data_cache['agents'])) {
                    $this->_getRandomAgent();
                }
                $this->_data_cache['random_people_ids'] = array_keys($this->_data_cache['agents']);
            }

            $this->_person_hits = count($this->_data_cache['random_people_ids']);
        }
        --$this->_person_hits;

        return $this->_getRandomFromCache('random_people_ids');
    }

    private function _getRandomAgent($id = false)
    {
        if (!isset($this->_data_cache['agents'])) {
            $this->_data_cache['agents'] = $this->em->createQuery('
                SELECT p
                FROM DeskPRO:Person p INDEX BY p.id
                WHERE p.is_agent = true AND p.is_deleted = false
                ORDER BY p.first_name ASC, p.last_name ASC
            ')->execute();
        }

        return $this->_getRandomFromCache('agents', $id ? 'id' : null);
    }

    private function _getRandomAgentTeam()
    {
        if (!isset($this->_data_cache['agent_teams'])) {
            $this->_data_cache['agent_teams'] = $this->em->getRepository('DeskPRO:AgentTeam')->getTeams();
        }

        return $this->_getRandomFromCache('agent_teams');
    }

    private function _getRandomOrgId()
    {
        if (!isset($this->_data_cache['random_org_ids'])) {
            $this->_data_cache['random_org_ids'] = $this->db->fetchAllCol('
                SELECT id
                FROM organizations
                ORDER BY RAND()
                LIMIT 1000
            ');
        }

        return $this->_getRandomFromCache('random_org_ids');
    }

    private function _getRandomTwitterUserId()
    {
        if ($this->_twitter_hits <= 0 || !isset($this->_data_cache['random_twitter_user_ids'])) {
            $this->_data_cache['random_twitter_user_ids'] = $this->db->fetchAllCol('
                SELECT id
                FROM twitter_users
                ORDER BY RAND()
                LIMIT 1000
            ');

            $this->_twitter_hits = count($this->_data_cache['random_twitter_user_ids']);
        }
        --$this->_twitter_hits;

        return $this->_getRandomFromCache('random_twitter_user_ids');
    }

    private function _getRandomFromCache($key, $obj_field = null)
    {
        if (!isset($this->_data_cache[$key]) || empty($this->_data_cache[$key])) {
            return;
        }

        $rand = array_rand($this->_data_cache[$key]);
        $data = $this->_data_cache[$key][$rand];

        return $obj_field ? $data->$obj_field : $data;
    }

    private function _addBatchInsert($table, array $data, $ignore = false)
    {
        if ($ignore) {
            if (!isset($this->_batch_insert_ignore[$table])) {
                $this->_batch_insert_ignore[$table] = [];
            }

            $this->_batch_insert_ignore[$table][] = $data;
        } else {
            if (!isset($this->_batch_insert[$table])) {
                $this->_batch_insert[$table] = [];
            }

            $this->_batch_insert[$table][] = $data;
        }
    }

    private function _applyLabelsDb($type, $id)
    {
        if (!isset($this->_label_type_map[$type])) {
            throw new \Exception("Unknown label type $type");
        }

        if (mt_rand(0, 1) == 0) {
            return;
        }

        $labels = mt_rand(0, 4);
        if ($labels && isset($this->_label_type_map[$type])) {
            for ($i = 0; $i < $labels; ++$i) {
                list($table, $field) = $this->_label_type_map[$type];

                $label = $this->faker->word;
                $label = strtolower(trim($label));

                $this->_addBatchInsert($table, [
                    $field  => $id,
                    'label' => $label,
                ], true);

                $type_name = $type.'s';
                if (!isset($this->_batch_insert_label_def[$type_name])) {
                    $this->_batch_insert_label_def[$type_name] = [];
                }
                if (!isset($this->_batch_insert_label_def[$type_name][$label])) {
                    $this->_batch_insert_label_def[$type_name][$label] = 1;
                } else {
                    ++$this->_batch_insert_label_def[$type_name][$label];
                }
            }
        }
    }

    private function _applyLabels($entity)
    {
        if (!method_exists($entity, 'getLabelManager')) {
            return;
        }

        /** @var $manager \Application\DeskPRO\Labels\LabelManager */
        $manager = $entity->getLabelManager();

        $labels = mt_rand(0, 4);
        if ($labels) {
            $this->em->flush(); // must generate an ID first

            for ($i = 0; $i < $labels; ++$i) {
                $label = $manager->addLabel($this->faker->word);
                $this->em->persist($label);
            }
        }
    }

    /**
     * @return string
     */
    private function getEmail()
    {
        return 'mass_generated_'.microtime().$this->num++.'@example.com';
    }
}
