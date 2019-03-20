<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\People\PersonMerge;

use Application\DeskPRO\App;
use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\People\ActivityLogger;
use Application\DeskPRO\People\PersonContextInterface;

/**
 * Handles merging of one person into the other.
 */
class PersonMerge implements PersonContextInterface
{
    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person_context;

    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person;

    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $other_person;

    /**
     * @var MergeBackup
     */
    protected $mergeBackup;

    /**
     * @var ActivityLogger\ActivityLogger
     */
    protected $activityLogger;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @param \Application\DeskPRO\Entity\Person   $person_performer
     * @param \Application\DeskPRO\Entity\Person   $person           The base person, this is the one that will still exist at the end
     * @param \Application\DeskPRO\Entity\Feedback $other_person     The other person, the one that will be merged into $person and then deleted
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(Person $person_performer, Person $person, Person $other_person)
    {
        $this->em             = App::getOrm();
        $this->mergeBackup    = App::getContainer()->getSystemService('person_merge_backup');
        $this->activityLogger = App::getContainer()->getPersonActivityLogger();

        $this->person       = $person;
        $this->other_person = $other_person;
        $this->setPersonContext($person_performer);

        if ($person === $other_person) {
            throw new \InvalidArgumentException('You cannot merge a person with itself');
        }
    }

    public function setPersonContext(Person $person)
    {
        $this->person_context = $person;
    }

    public function merge()
    {
        $this->em->beginTransaction();

        try {
            $this->_logMergeAndBackup();

            // todo: organizations cc?
            $standard_prop_names = [
                'language',
                'organization',
                'organization_position',
                'picture_blob',
                'summary',
            ];
            foreach ($standard_prop_names as $prop_name) {
                $prop_standard = new Property\StandardProperty($this->person, $this->other_person);
                $prop_standard->setProperty($prop_name);
                $prop_standard->setStrategy(Property\StandardProperty::STRATEGY_COMBINE);
                $prop_standard->merge();
            }

            if ($this->other_person->date_created < $this->person->date_created) {
                $this->person->date_created = $this->other_person->date_created;
            }

            foreach (['is_agent', 'can_agent', 'can_admin', 'can_billing', 'can_reports'] as $attr) {
                if ($this->person[$attr] || $this->other_person[$attr]) {
                    $this->person[$attr] = true;
                }
            }

            $this->_mergeCustomFields();

            $this->_mergeContactData();
            $this->_mergeOtherPersonData();
            $this->_mergeArticles();
            $this->_mergeChats();
            $this->_mergeDownloads();
            $this->_mergeFeedback();
            $this->_mergeNews();
            $this->_mergeTasks();
            $this->_mergePhoneCalls();
            $this->_mergeTickets();
            $this->_mergeOther();

            $this->em->persist($this->person);
            $this->em->flush();

            $this->em->refresh($this->other_person);
            $this->em->remove($this->other_person);
            $this->em->flush();

            $this->em->commit();
        } catch (\Exception $e) {
            $this->em->rollback();

            throw $e;
        }

        return true;
    }

    protected function _mergeContactData()
    {
        $simple_tables = [
            'people_contact_data',
            'people_emails',
            'people_twitter_users',
        ];

        foreach ($simple_tables as $table) {
            $this->_updateTablePersonId($table, 'person_id');
        }

        $newPersonNumbers = App::getDb()->fetchAllKeyValue('
            SELECT id, number
            FROM phone_numbers
            WHERE person_id = ?
        ', [$this->person['id']], [\PDO::PARAM_INT]);

        $oldPersonNumber = App::getDb()->fetchAllKeyValue('
            SELECT id, number
            FROM phone_numbers
            WHERE person_id = ?
        ', [$this->other_person['id']], [\PDO::PARAM_INT]);

        $toDelete = $toUpdate = [];
        foreach ($oldPersonNumber as $id => $number) {
            if (in_array($number, $newPersonNumbers)) {
                $toDelete[] = $id;
            } else {
                $toUpdate[] = $id;
            }
        }

        App::getDb()->executeQuery('DELETE FROM phone_numbers WHERE id IN (?)', [$toDelete],
            [Connection::PARAM_INT_ARRAY]);

        App::getDb()->executeUpdate('
            UPDATE IGNORE phone_numbers
            SET person_id = ?
            WHERE person_id = ? AND id IN (?)
        ', [$this->person['id'], $this->other_person['id'], $toUpdate], [\PDO::PARAM_INT, \PDO::PARAM_INT, Connection::PARAM_INT_ARRAY]);
    }

    protected function _mergeCustomFields()
    {
        $field_defs = App::getApi('custom_fields.people')->getEnabledFields();
        foreach ($field_defs as $f) {
            $prop_field = new Property\CustomField($this->person, $this->other_person);
            $prop_field->setField($f);
            $prop_field->setStrategy(Property\StandardProperty::STRATEGY_COMBINE);
            $prop_field->merge();
        }
    }

    protected function _mergeOtherPersonData()
    {
        $simple_tables = [
            'labels_people',
            'people_notes',
            'people_prefs',
            'person2usergroups',
            'person_to_brand',
            'person_activity',
            'person_usersource_assoc',
        ];

        foreach ($simple_tables as $table) {
            $this->_updateTablePersonId($table, 'person_id');
        }
    }

    protected function _mergeArticles()
    {
        $simple_tables = [
            'articles',
            'article_attachments',
            'article_comments',
            'article_pending_create',
            'article_revisions',
        ];

        foreach ($simple_tables as $table) {
            $this->_updateTablePersonId($table, 'person_id');
        }
    }

    protected function _mergeChats()
    {
        $simple_tables = [
            'chat_conversations',
            'chat_conversation_to_person',
        ];

        foreach ($simple_tables as $table) {
            $this->_updateTablePersonId($table, 'person_id');
        }

        $this->_updateTablePersonId('chat_blocks', 'by_person_id');
        $this->_updateTablePersonId('chat_messages', 'author_id');
    }

    protected function _mergeDownloads()
    {
        $simple_tables = [
            'downloads',
            'download_comments',
            'download_revisions',
        ];

        foreach ($simple_tables as $table) {
            $this->_updateTablePersonId($table, 'person_id');
        }
    }

    protected function _mergeFeedback()
    {
        $simple_tables = [
            'feedback',
            'feedback_attachments',
            'feedback_comments',
            'feedback_revisions',
        ];

        foreach ($simple_tables as $table) {
            $this->_updateTablePersonId($table, 'person_id');
        }
    }

    protected function _mergeNews()
    {
        $simple_tables = [
            'news',
            'news_comments',
            'news_revisions',
        ];

        foreach ($simple_tables as $table) {
            $this->_updateTablePersonId($table, 'person_id');
        }
    }

    protected function _mergeTasks()
    {
        $simple_tables = [
            'tasks',
            'task_associations',
            'task_comments',
        ];

        foreach ($simple_tables as $table) {
            $this->_updateTablePersonId($table, 'person_id');
        }
    }

    protected function _mergeTickets()
    {
        $simple_tables = [
            'tickets',
            'tickets_attachments',
            'tickets_logs',
            'tickets_messages',
            'tickets_participants',
            'tickets_search_active',
            'ticket_access_codes',
            'ticket_charges',
            'ticket_feedback',
        ];
        $complex_tables = [
            'tickets_deleted' => ['by_person_id'],
        ];

        foreach ($simple_tables as $table) {
            $this->_updateTablePersonId($table, 'person_id');
        }

        if ($this->person->isAgent() && $this->other_person->isAgent()) {
            $this->_updateTablePersonId('tickets', 'agent_id');
            $this->_updateTablePersonId('tickets_search_active', 'agent_id');
        }

        foreach ($complex_tables as $table => $columns) {
            foreach ($columns as $column) {
                $this->_updateTablePersonId($table, $column);
            }
        }
    }

    protected function _mergePhoneCalls()
    {
        $userTables = [
            'voice_phone_calls',
            'voice_phone_call_participants',
            'voice_phone_call_logs',
        ];

        foreach ($userTables as $table) {
            $this->_updateTablePersonId($table, 'person_id');
        }

        if ($this->person->isAgent() && $this->other_person->isAgent()) {
            $agentTables = [
                'voicemail_records',
                'voice_targets',
                'voice_queue_agents',
            ];

            foreach ($agentTables as $table) {
                $this->_updateTablePersonId($table, 'agent_id');
            }
        }
    }

    protected function _mergeOther()
    {
        $simple_tables = [
            'login_log',
            'page_view_log',
            'ratings',
            'searchlog',
        ];

        if ($this->person->isAgent() && $this->other_person->isAgent()) {
            $simple_tables = array_merge($simple_tables, [

                'department_permissions',
                'permissions',
                'person2usergroups',
                'app_instance_permissions',

                'agent_team_members',

                'ticket_filters',
                'ticket_filter_subscriptions',

                'text_snippet_categories',
                'text_snippets',

                'tickets_flagged',

            ]);

            $this->_updateTablePersonId('tasks', 'assigned_agent_id');
        }

        foreach ($simple_tables as $table) {
            $this->_updateTablePersonId($table, 'person_id');
        }
    }

    protected function _updateTablePersonId($table, $column)
    {
        // update ignore lets this work like a "combine" where needed
        App::getDb()->executeUpdate("
            UPDATE IGNORE $table
            SET $column = ?
            WHERE $column = ?
        ", [$this->person['id'], $this->other_person['id']]);
    }

    /**
     * Save Person ActivityStream
     * ActivityStream details includes backupId used to Undo merge if needed.
     */
    protected function _logMergeAndBackup()
    {
        $backupId = $this->mergeBackup->backup($this->person, $this->other_person);
        $action   = new ActivityLogger\ActionType\Merged($this->person, $this->other_person, $backupId);
        $this->activityLogger->saveAction($action);
        // call flush directly to execute it inside of transaction
        $this->activityLogger->flush();
    }
}
