<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace Application\DeskPRO\People;

use Application\DeskPRO\App;
use Application\DeskPRO\CustomFields\FieldManager;
use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\EntityRepository\Ticket as TicketEntityRepository;
use Doctrine\ORM\EntityManager;

class PeopleResultsDisplay
{
    /**
     * @var Person[]
     */
    protected $people;

    /**
     * @var Person
     */
    protected $agent;

    /**
     * @var array
     */
    protected $people_ids;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var Connection
     */
    protected $db;

    /**
     * @var FieldManager
     */
    protected $field_manager;

    /**
     * @var int
     */
    protected $people_count;

    /**
     * @var array
     */
    protected $all_labels;

    /**
     * @var array
     */
    protected $people_ticket_counts;

    /**
     * @var array
     */
    protected $primary_emails;

    /**
     * @var array
     */
    protected $people_fields;

    /**
     * @var array
     */
    protected $people_usernames;

    /**
     * @var array
     */
    protected $all_fields_data;

    /**
     * @param Person[] $people
     * @param Person   $agent
     */
    public function __construct(array $people, Person $agent)
    {
        $this->people       = $people;
        $this->agent        = $agent;
        $this->people_count = count($people);
        $this->people_ids   = [];
        foreach ($this->people as $p) {
            $this->people_ids[] = $p->id;
        }

        $this->em            = App::getOrm();
        $this->db            = $this->em->getConnection();
        $this->field_manager = App::getSystemService('person_fields_manager');
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return $this->people_count;
    }

    /**
     * @return Person[]
     */
    public function getPeople()
    {
        return $this->people;
    }

    /**
     * @return array
     */
    public function getAllLabels()
    {
        if ($this->all_labels !== null) {
            return $this->all_labels;
        }

        if (!$this->people_count) {
            $this->all_labels = [];

            return $this->all_labels;
        }

        $people_ids = implode(',', $this->people_ids);

        $this->all_labels = $this->db->fetchAllGrouped("
            SELECT person_id, label
            FROM labels_people
            WHERE person_id IN ($people_ids)
        ", [], 'person_id', null, 'label');

        return $this->all_labels;
    }

    /**
     * @return array
     */
    public function getAllUsernames()
    {
        if ($this->people_usernames !== null) {
            return $this->people_usernames;
        }

        if (!$this->people_count) {
            $this->people_usernames = [];

            return $this->people_usernames;
        }

        $people_ids = implode(',', $this->people_ids);

        $this->people_usernames = $this->db->fetchAllGrouped("
            SELECT person_id, identity_friendly
            FROM person_usersource_assoc
            WHERE person_id IN ($people_ids) AND identity_friendly != ''
        ", [], 'person_id', null, 'identity_friendly');

        return $this->people_usernames;
    }

    /**
     * @param Person $person
     *
     * @return array
     */
    public function getEmail(Person $person)
    {
        if (!$person->primary_email) {
            return;
        }

        if ($this->primary_emails === null) {
            $primary_email_ids = [];
            foreach ($this->people as $p) {
                if ($p->primary_email) {
                    $primary_email_ids[] = $p->primary_email->getId();
                }
            }

            $this->primary_emails = $this->em->getRepository('DeskPRO:PersonEmail')->getByIds($primary_email_ids);
        }

        return $this->primary_emails[$person->primary_email->getId()];
    }

    /**
     * @return array
     */
    public function getAllFieldsData()
    {
        if ($this->all_fields_data !== null) {
            return $this->all_fields_data;
        }
        $data = $this->em->createQuery('
            SELECT d, def, root_def
            FROM DeskPRO:CustomDataPerson AS d
            LEFT JOIN d.field def
            LEFT JOIN d.root_field root_def
            WHERE d.person IN (?0)
        ')->execute([array_values($this->people_ids)]);

        $this->all_fields_data = [];
        foreach ($data as $d) {
            $tid = $d->person->getId();
            if (!isset($this->all_fields_data[$tid])) {
                $this->all_fields_data[$tid] = [];
            }

            $this->all_fields_data[$tid][] = $d;
        }

        return $this->all_fields_data;
    }

    /**
     * @param Person $person
     *
     * @return array
     */
    public function getUserFieldData(Person $person)
    {
        $this->getAllFieldsData();

        return isset($this->all_fields_data[$person->getId()]) ? $this->all_fields_data[$person->getId()] : [];
    }

    /**
     * @param Person $person
     *
     * @return array
     */
    public function getCustomFields(Person $person)
    {
        if ($this->people_fields === null) {
            $field_data = $this->em->createQuery('
                SELECT cp FROM DeskPRO:CustomDataPerson cp
                WHERE cp.person IN (?0)
            ')->execute([$this->people_ids]);

            $person_data = [];

            foreach ($field_data as $data) {
                $pid = $data->person->getId();
                if (!isset($person_data[$pid])) {
                    $person_data[$pid] = [];
                }

                $person_data[$pid][] = $data;
            }

            $this->people_fields = [];
            foreach ($person_data as $pid => $custom_data) {
                $this->people_fields[$pid] = $this->field_manager->getDisplayArray($this->field_manager->createFieldDataFromArray($custom_data), null);
            }
        }

        if (isset($this->people_fields[$person->id])) {
            return $this->people_fields[$person->id];
        } else {
            return [];
        }
    }

    /**
     * Get an array of labels applied to a person.
     *
     * @param Person $person
     *
     * @return array
     */
    public function getPersonLabels(Person $person)
    {
        $this->getAllLabels();

        return empty($this->all_labels[$person->id]) ? [] : $this->all_labels[$person->id];
    }

    /**
     * Get an array of usernames from usersources applied to a person.
     *
     * @param Person $person
     *
     * @return array
     */
    public function getPersonUsernames(Person $person)
    {
        $this->getAllUsernames();

        return empty($this->people_usernames[$person->id]) ? [] : $this->people_usernames[$person->id];
    }

    /**
     * Check if a person has labels.
     *
     * @param Person $person
     *
     * @return bool
     */
    public function hasPersonLabels(Person $person)
    {
        $this->getAllLabels();

        return !empty($this->all_labels[$person->id]);
    }

    /**
     * @return array
     */
    public function getAllPeopleTicketCounts()
    {
        if ($this->people_ticket_counts !== null) {
            return $this->people_ticket_counts;
        }

        /** @var TicketEntityRepository $ticketRepository */
        $ticketRepository           = $this->em->getRepository('DeskPRO:Ticket');
        $this->people_ticket_counts = $ticketRepository->getTicketCountsForPeople($this->people, $this->agent);

        return $this->people_ticket_counts;
    }

    /**
     * Get the number of tickets submitted by a user.
     *
     * @param Person $person
     *
     * @return int
     */
    public function getPersonTicketCount(Person $person)
    {
        $this->getAllPeopleTicketCounts();

        return isset($this->people_ticket_counts[$person->id]) ? $this->people_ticket_counts[$person->id] : 0;
    }
}
