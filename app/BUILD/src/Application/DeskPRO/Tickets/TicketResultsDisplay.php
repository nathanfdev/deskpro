<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Tickets;

use Application\DeskPRO\App;
use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\People\PersonContextInterface;
use Orb\Util\Arrays;
use Orb\Util\Strings;

class TicketResultsDisplay implements PersonContextInterface
{
    /**
     * @var \Application\DeskPRO\Entity\Ticket[]
     */
    protected $tickets;

    /**
     * @var array
     */
    protected $ticket_ids;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Application\DeskPRO\DBAL\Connection
     */
    protected $db;

    /**
     * @var int
     */
    protected $ticket_count;

    /**
     * @var array
     */
    protected $all_labels;

    /*
     * @var array
     */
    protected $all_problems;

    /**
     * @var array
     */
    protected $all_ticket_slas;

    /**
     * @var array
     */
    protected $all_previews;

    /**
     * @var array
     */
    protected $people;

    /**
     * @var array
     */
    protected $people_ids;

    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person_context;

    /**
     * @var array
     */
    protected $person_flagged;

    /**
     * @var array
     */
    protected $all_ticket_field_data;

    /**
     * @var array
     */
    protected $all_user_field_data;

    /**
     * @var array
     */
    protected $all_org_field_data;

    public function setPersonContext(Person $person)
    {
        $this->person_context = $person;
    }

    /**
     * @param \Application\DeskPRO\Entity\Ticket[] $tickets
     */
    public function __construct(array $tickets)
    {
        $this->tickets      = $tickets;
        $this->ticket_count = count($tickets);
        $this->ticket_ids   = Arrays::flattenToIndex($this->tickets, 'id');

        $this->em = App::getOrm();
        $this->db = $this->em->getConnection();

        $people_ids = [];
        foreach ($tickets as $ticket) {
            if (!$ticket->getPerson()) {
                continue;
            }

            $people_ids[] = $ticket->person->getId();
            if ($ticket->agent) {
                $people_ids[] = $ticket->agent->getId();
            }
        }

        $this->dep_names  = App::getDataService('Department')->getFullNames();
        $this->people     = App::getDataService('Person')->getPeopleResultsFromIds($people_ids);
        $this->people_ids = $people_ids;
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return $this->ticket_count;
    }

    /**
     * @return \Application\DeskPRO\Entity\Ticket[]
     */
    public function getTickets()
    {
        return $this->tickets;
    }

    /**
     * @return array
     */
    public function getAllLabels()
    {
        if ($this->all_labels !== null) {
            return $this->all_labels;
        }

        if (!$this->ticket_count) {
            $this->all_labels = [];

            return $this->all_labels;
        }

        $ticket_ids = implode(',', $this->ticket_ids);

        $this->all_labels = $this->db->fetchAllGrouped("
            SELECT ticket_id, label
            FROM labels_tickets
            WHERE ticket_id IN ($ticket_ids)
        ", [], 'ticket_id', null, 'label');

        return $this->all_labels;
    }

    public function getAllProblems()
    {
        if ($this->all_problems !== null) {
            return $this->all_problems;
        }

        if (!$this->ticket_count) {
            $this->all_problems = [];

            return $this->all_problems;
        }

        $ticket_ids = implode(',', $this->ticket_ids);

        $this->all_problems = $this->db->fetchAllGrouped(
            "
            SELECT pt.ticket_id, p.id, p.title
            FROM problem2tickets pt
            JOIN problems p ON p.id = pt.problem_id
            WHERE pt.ticket_id IN ($ticket_ids)
        ",
            [],
            'ticket_id',
            null,
            'title'
        );

        return $this->all_problems;
    }

    /**
     * @return array
     */
    public function getAllUserFieldData()
    {
        if ($this->all_user_field_data !== null) {
            return $this->all_user_field_data;
        }
        $data = $this->em->createQuery('
            SELECT d, def, root_def
            FROM DeskPRO:CustomDataPerson AS d
            LEFT JOIN d.field def
            LEFT JOIN d.root_field root_def
            WHERE d.person IN (?0)
        ')->execute([array_values($this->people_ids)]);

        $this->all_user_field_data = [];
        foreach ($data as $d) {
            $tid = $d->person->getId();
            if (!isset($this->all_user_field_data[$tid])) {
                $this->all_user_field_data[$tid] = [];
            }

            $this->all_user_field_data[$tid][] = $d;
        }

        return $this->all_user_field_data;
    }

    /**
     * @param Ticket $ticket
     *
     * @return array
     */
    public function getUserFieldData(Person $person)
    {
        $this->getAllUserFieldData();

        return isset($this->all_user_field_data[$person->getId()]) ? $this->all_user_field_data[$person->getId()] : [];
    }

    /**
     * @return array
     */
    public function getAllTicketFieldData()
    {
        if ($this->all_ticket_field_data !== null) {
            return $this->all_ticket_field_data;
        }
        $data = $this->em->createQuery('
            SELECT d, def, root_def
            FROM DeskPRO:CustomDataTicket AS d
            LEFT JOIN d.field def
            LEFT JOIN d.root_field root_def
            WHERE d.ticket IN (?0)
        ')->execute([array_values($this->ticket_ids)]);

        $this->all_ticket_field_data = [];
        foreach ($data as $d) {
            $tid = $d->ticket->getId();
            if (!isset($this->all_ticket_field_data[$tid])) {
                $this->all_ticket_field_data[$tid] = [];
            }

            $this->all_ticket_field_data[$tid][] = $d;
        }

        return $this->all_ticket_field_data;
    }

    /**
     * @param Ticket $ticket
     *
     * @return array
     */
    public function getTicketFieldData(Ticket $ticket)
    {
        $this->getAllTicketFieldData();

        return isset($this->all_ticket_field_data[$ticket->id]) ? $this->all_ticket_field_data[$ticket->id] : [];
    }

    /**
     * Get an array of labels applied to a ticket.
     *
     * @param \Application\DeskPRO\Entity\Ticket $ticket
     *
     * @return array
     */
    public function getTicketLabels(Ticket $ticket)
    {
        $this->getAllLabels();

        return empty($this->all_labels[$ticket->id]) ? [] : $this->all_labels[$ticket->id];
    }

    public function getTicketProblems(Ticket $ticket)
    {
        $this->getAllProblems();

        return empty($this->all_problems[$ticket->id]) ? [] : $this->all_problems[$ticket->id];
    }

    /**
     * Check if a ticket has labels.
     *
     * @param \Application\DeskPRO\Entity\Ticket $ticket
     *
     * @return bool
     */
    public function hasTicketLabels(Ticket $ticket)
    {
        $this->getAllLabels();

        return !empty($this->all_labels[$ticket->id]);
    }

    /**
     * @return array
     */
    public function getAllTicketSlas()
    {
        if ($this->all_ticket_slas !== null) {
            return $this->all_ticket_slas;
        }

        if (!$this->ticket_count) {
            $this->all_ticket_slas = [];

            return $this->all_ticket_slas;
        }

        $ticket_ids = implode(',', $this->ticket_ids);

        $this->all_ticket_slas = $this->db->fetchAllGrouped("
            SELECT ticket_slas.*, slas.title
            FROM ticket_slas
            INNER JOIN slas ON (ticket_slas.sla_id = slas.id)
            WHERE ticket_slas.ticket_id IN ($ticket_ids)
                AND ticket_slas.is_completed = 0
        ", [], 'ticket_id', 'id');

        return $this->all_ticket_slas;
    }

    /**
     * Get an array of labels applied to a ticket.
     *
     * @param \Application\DeskPRO\Entity\Ticket $ticket
     *
     * @return array
     */
    public function getTicketSlas(Ticket $ticket)
    {
        $this->getAllTicketSlas();

        return empty($this->all_ticket_slas[$ticket->id]) ? [] : $this->all_ticket_slas[$ticket->id];
    }

    /**
     * Check if a ticket has an SLA.
     *
     * @param \Application\DeskPRO\Entity\Ticket $ticket
     *
     * @return bool
     */
    public function hasTicketSlas(Ticket $ticket)
    {
        $this->getAllTicketSlas();

        return !empty($this->all_ticket_slas[$ticket->id]);
    }

    public function getNextSlaTriggerDate(array $ticket_sla)
    {
        $times = [];

        if ($ticket_sla['sla_status'] == 'ok' && $ticket_sla['warn_date']) {
            $time = new \DateTime($ticket_sla['warn_date'], new \DateTimeZone('UTC'));
            if ($time->getTimestamp() > time() || !$ticket_sla['fail_date']) {
                $times[] = $time->getTimestamp();
            }
        }

        if ($ticket_sla['fail_date']) {
            $time    = new \DateTime($ticket_sla['fail_date'], new \DateTimeZone('UTC'));
            $times[] = $time->getTimestamp();
        }

        if (!$times) {
            return;
        }

        return new \DateTime('@'.min($times));
    }

    /**
     * @param \Application\DeskPRO\Entity\Ticket $ticket
     *
     * @return \Application\DeskPRO\Entity\Person
     */
    public function getPerson(Ticket $ticket)
    {
        $person = $ticket->getPerson();

        return $person && isset($this->people[$person->getId()]) ? $this->people[$person->getId()] : null;
    }

    /**
     * @param \Application\DeskPRO\Entity\Ticket $ticket
     *
     * @return \Application\DeskPRO\Entity\Person
     */
    public function getAgent(Ticket $ticket)
    {
        $agent = $ticket->getAgent();

        return $agent && isset($this->people[$agent->getId()]) ? $this->people[$agent->getId()] : null;
    }

    /**
     * @param \Application\DeskPRO\Entity\Ticket $ticket
     *
     * @return string
     */
    public function getDepartmentName(Ticket $ticket)
    {
        if (!$ticket->department) {
            return;
        }

        if (!isset($this->dep_names[$ticket->department->getId()])) {
            return '';
        }

        return $this->dep_names[$ticket->department->getId()];
    }

    /**
     * Gets array of previews for each ticket.
     *
     * @return array
     */
    public function getAllTicketPreviews()
    {
        if ($this->all_previews !== null) {
            return $this->all_previews;
        }

        if (!$this->ticket_ids) {
            $this->all_previews = [];

            return $this->all_previews;
        }

        $message_data = $this->db->fetchAllKeyed('
            SELECT
                tickets_messages.id, tickets_messages.ticket_id, tickets_messages.date_created, tickets_messages.message,
                people.id AS person_id, people.name, people.first_name, people.last_name, people.is_agent,
                tickets_messages.is_agent_note
            FROM tickets_messages
            LEFT JOIN people ON (people.id = tickets_messages.person_id)
            JOIN (SELECT MAX(id) AS id FROM tickets_messages WHERE ticket_id IN (?) GROUP by ticket_id) tickets_messages2
            WHERE tickets_messages.id = tickets_messages2.id
        ', [$this->ticket_ids], 'id', [Connection::PARAM_INT_ARRAY]);

        $extra_people     = [];
        $extra_people_ids = [];

        foreach ($message_data as $m) {
            if (!isset($this->people[$m['person_id']])) {
                $extra_people_ids[] = $m['person_id'];
            }
        }
        if ($extra_people_ids) {
            foreach (App::getDataService('Person')->getPeopleResultsFromIds($extra_people) as $k => $v) {
                $extra_people[$k] = $v;
            }
        }

        $languageManager = App::$container->get('language_manager');

        $this->all_previews = [];
        foreach ($message_data as $m) {
            if (!isset($this->all_previews[$m['ticket_id']])) {
                $this->all_previews[$m['ticket_id']] = [];
            }

            $m['status'] = $m['is_agent_note']
                ? $languageManager->phrase('agent.tickets.preview_wrote_note')
                : ($this->tickets[$m['ticket_id']]->date_created->format('Y-m-d H:i:s') === $m['date_created']
                    ? $languageManager->phrase('agent.tickets.preview_created_ticket')
                    : $languageManager->phrase('agent.tickets.preview_replied')
                );

            $m['date_created'] = \DateTime::createFromFormat('Y-m-d H:i:s', $m['date_created']);

            if ($m['first_name'] && $m['last_name']) {
                $m['display_name'] = $m['first_name'].' '.$m['last_name'];
            } elseif ($m['name']) {
                $m['display_name'] = $m['name'];
            } elseif ($m['last_name']) {
                $m['display_name'] = $m['last_name'];
            } elseif ($m['first_name']) {
                $m['display_name'] = $m['first_name'];
            } else {
                $m['display_name'] = 'User';
            }

            $m['preview_text'] = $this->_getMessagePreviewText($m['message'], 750);

            $m['picture_url_16'] = null;
            if (isset($this->people[$m['person_id']])) {
                $m['picture_url_16'] = $this->people[$m['person_id']]->getPictureUrl(16);
            } elseif (isset($extra_people[$m['person_id']])) {
                $m['picture_url_16'] = $extra_people[$m['person_id']]->getPictureUrl(16);
            }

            $this->all_previews[$m['ticket_id']][] = $m;
        }

        return $this->all_previews;
    }

    private function _getMessagePreviewText($message, $max_length = 0, $ellipses = '...')
    {
        $message = preg_replace('#\[attach:([a-zA-Z0-9\-_\.]+):([a-zA-Z0-9\-_\.]+):([a-zA-Z0-9\-_\. ]+)\]#', '', $message);

        $sig_pos = strpos($message, '<div class="dp-signature-start">');

        if ($sig_pos !== false) {
            $message = substr($message, 0, $sig_pos);
        }

        $message = Strings::standardEol($message);
        $message = str_replace(['<br/>', '<br>', '<br />', '<p>', '</p>'], "\n", $message);
        $message = strip_tags($message);
        $message = Strings::decodeHtmlEntities($message);
        $message = preg_replace('#\s+#', ' ', $message);
        $message = trim($message);

        if ($max_length && isset($message[$max_length])) {
            $message = substr($message, 0, $max_length);
            $message = trim($message);
            $message .= $ellipses;
        }

        return $message;
    }

    /**
     * Get an array of ticket message previews.
     *
     * @param Ticket $ticket
     *
     * @return array
     */
    public function getTicketPreview($ticket)
    {
        $this->getAllTicketPreviews();

        if (isset($this->all_previews[$ticket->id])) {
            return $this->all_previews[$ticket->id];
        }

        return [];
    }

    /**
     * @param mixed $ticket
     *
     * @return string
     */
    public function getFlaggedColor($ticket)
    {
        if (!$this->person_context) {
            return;
        }

        if ($this->person_flagged === null) {
            $this->person_flagged = App::getDb()->fetchAllKeyValue('
                SELECT ticket_id, color
                FROM tickets_flagged
                WHERE person_id = ? AND ticket_id IN (?)', [$this->person_context->getId(), $this->ticket_ids], [\PDO::PARAM_INT, Connection::PARAM_INT_ARRAY]);
        }

        $ticket_id = is_object($ticket) ? $ticket->getId() : $ticket;

        return isset($this->person_flagged[$ticket_id]) ? $this->person_flagged[$ticket_id] : null;
    }
}
