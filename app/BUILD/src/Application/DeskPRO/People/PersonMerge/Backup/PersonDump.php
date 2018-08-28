<?php

namespace Application\DeskPRO\People\PersonMerge\Backup;

use Application\DeskPRO\Entity\Person;
use Doctrine\ORM\EntityManager;
use Orb\Util\Arrays;

/**
 * Most of the data we load from DB directly.
 */
class PersonDump
{
    /**
     * Max related entities ids count that we can handle
     * if more - don't store anything.
     */
    const MAX_IDS_COUNT = 20000;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param Person $person
     * @param bool   $withRelations
     *
     * @return array
     */
    public function dump(Person $person, $withRelations = true)
    {
        $data                  = [];
        $data['simple_fields'] = $this->getSimpleFields($person);
        $data['custom_data']   = $this->getCustomData($person);
        $data['groups']        = $this->getUserGroups($person);
        $data['brands']        = $this->getBrands($person);
        if ($person->isAgent()) {
            $data['agent_teams'] = $this->getAgentTeams($person);
        }
        if ($withRelations) {
            $data['related_tables_ids'] = $this->getRelatedTablesIds($person);
        }

        return $data;
    }

    /**
     * @param Person $person
     *
     * @return array
     */
    protected function getSimpleFields(Person $person)
    {
        $fields = [
            'first_name',
            'last_name',
            'name',
            'language_id',
            'organization_id',
            'organization_position',
            'date_created',
            'is_agent',
            'can_agent',
            'can_admin',
            'can_billing',
            'can_reports',
            'timezone',
            'primary_email_id',
        ];

        return $this->em->getConnection()->fetchAssoc(
            sprintf(
                'select %s from people where id = :person_id',
                implode(',', $fields)
            ),
            ['person_id' => $person->getId()],
            ['person_id' => \PDO::PARAM_INT]
        );
    }

    /**
     * @param Person $person
     *
     * @return array
     */
    protected function getCustomData(Person $person)
    {
        return $this->em->getConnection()->fetchAll(
            'select field_id, root_field_id, value, input from custom_data_person where person_id = :person_id',
            ['person_id' => $person->getId()],
            ['person_id' => \PDO::PARAM_INT]
        );
    }

    /**
     * @param Person $person
     *
     * @return array
     */
    protected function getUserGroups(Person $person)
    {
        $res = $this->em->getConnection()->fetchAll(
            'select usergroup_id from person2usergroups where person_id = :person_id',
            ['person_id' => $person->getId()],
            ['person_id' => \PDO::PARAM_INT]
        );

        return array_map('current', $res);
    }

    /**
     * @param Person $person
     *
     * @return array
     */
    protected function getBrands(Person $person)
    {
        $res = $this->em->getConnection()->fetchAll(
            'select brand_id from person_to_brand where person_id = :person_id',
            ['person_id' => $person->getId()],
            ['person_id' => \PDO::PARAM_INT]
        );

        return array_map('current', $res);
    }

    /**
     * @param Person $person
     *
     * @return array
     */
    protected function getAgentTeams(Person $person)
    {
        $res = $this->em->getConnection()->fetchAll(
            'select team_id from agent_team_members where person_id = :person_id',
            ['person_id' => $person->getId()],
            ['person_id' => \PDO::PARAM_INT]
        );

        return array_map('current', $res);
    }

    /**
     * @param Person $person
     *
     * @throws DumpLimitException
     *
     * @return array
     */
    protected function getRelatedTablesIds(Person $person)
    {
        $data = [];

        // array of [table name, fk name]
        $tables = [
            ['people_contact_data',             'person_id'],
            ['people_emails',                   'person_id'],

            ['people_twitter_users',            'person_id'],
            // ['labels_people',                   'person_id'], // This table doesn't have an id colum, need special logic to handle this
            ['people_notes',                    'person_id'],
            // ['people_prefs',                    'person_id'], // This table doesn't have an id colum, need special logic to handle this
            ['person_usersource_assoc',         'person_id'],

            ['articles',                        'person_id'],
            ['article_attachments',             'person_id'],
            ['article_comments',                'person_id'],
            ['article_pending_create',          'person_id'],
            ['article_revisions',               'person_id'],
            ['downloads',                       'person_id'],
            ['download_comments',               'person_id'],
            ['download_revisions',              'person_id'],
            ['feedback',                        'person_id'],
            ['feedback_attachments',            'person_id'],
            ['feedback_comments',               'person_id'],
            ['feedback_revisions',              'person_id'],
            ['news',                            'person_id'],
            ['news_comments',                   'person_id'],
            ['news_revisions',                  'person_id'],
            ['tasks',                           'person_id'],
            ['task_associations',               'person_id'],
            ['task_comments',                   'person_id'],

            // There might be too many chats, ignore them
            // ['chat_conversations',              'person_id'],
            // ['chat_conversation_to_person',     'person_id'], // This table doesn't have an id colum, need special logic to handle this
            // ['chat_blocks',                     'by_person_id'],
            // ['chat_messages',                   'author_id'],

            ['tickets',                         'person_id'],
            ['tickets_attachments',             'person_id'],
            // ['tickets_logs',                    'person_id'], // might be too big
            ['tickets_messages',                'person_id'],
            ['tickets_participants',            'person_id'],
            ['tickets_search_active',           'person_id'],
            ['ticket_access_codes',             'person_id'],
            ['ticket_charges',                  'person_id'],
            ['ticket_feedback',                 'person_id'],
        ];

        if ($person->isAgent()) {
            $tables = array_merge($tables, [

                ['tickets',                         'agent_id'],
                ['tickets_search_active',           'agent_id'],
                ['tasks',                           'assigned_agent_id'],

                ['department_permissions',          'person_id'],
                ['permissions',                     'person_id'],
                ['app_instance_permissions',        'person_id'],
                ['ticket_filters',                  'person_id'],
                ['ticket_filter_subscriptions',     'person_id'],
                ['text_snippet_categories',         'person_id'],
                ['text_snippets',                   'person_id'],
                // ['tickets_flagged',                 'person_id'], // This table doesn't have an id colum, need special logic to handle this
            ]);
        }

        $cntTotal = 0;
        foreach ($tables as $table) {
            $ids = $this->getRelatedTableIds($person, $table[0], $table[1]);

            $cntTotal += count($ids);
            if ($cntTotal > self::MAX_IDS_COUNT) {
                throw new DumpLimitException();
            }

            if ($ids) {
                if (!isset($data[$table[0]])) {
                    $data[$table[0]] = [];
                }
                $data[$table[0]][$table[1]] = Arrays::castToType($ids, 'int');
            }
        }

        return $data;
    }

    /**
     * @param string $table
     * @param string $personColumnName
     *
     * @return array
     */
    protected function getRelatedTableIds(Person $person, $table, $personColumnName = 'person_id')
    {
        $res = $this->em->getConnection()->fetchAll(
            sprintf('select id from %s where %s = :person_id', $table, $personColumnName),
            ['person_id' => $person->getId()],
            ['person_id' => \PDO::PARAM_INT]
        );

        return array_map('current', $res);
    }
}
