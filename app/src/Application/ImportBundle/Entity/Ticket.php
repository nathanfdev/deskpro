<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

namespace Application\ImportBundle\Entity;

use DateTime;

/**
 * Exported ticket entity
 *
 * Class Ticket
 * @package Application\ImportBundle\Entity
 */
final class Ticket implements EntityInterface
{
    /**
     * @var string
     */
    private $destination;

    /**
     * @var int
     */
    private $ref;

    /**
     * @var int or object?
     */
    private $department;

    /**
     * @var string or object?
     */
    private $person;

    /**
     * @var int or object?
     */
    private $agent;

    /**
     * @var int or object?
     */
    private $agent_team;

    /**
     * @var string
     */
    private $status;

    /**
     * @var DateTime
     */
    private $date_created;

    /**
     * @var string
     */
    private $subject;

    /**
     * @var int ?
     */
    private $priority;

    /**
     * @var array
     */
    private $messages = array();

    /**
     * @param string $destination
     * @return $this
     */
    public function setDestination($destination)
    {
        $this->destination = $destination;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDestination()
    {
        return $this->destination;
    }

    /**
     * @return int
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * @param int $ref
     * @return $this
     */
    public function setRef($ref)
    {
        $this->ref = $ref;
        return $this;
    }

    /**
     * @return int
     */
    public function getDepartment()
    {
        return $this->department;
    }

    /**
     * @param int $department
     * @return $this
     */
    public function setDepartment($department)
    {
        $this->department = $department;
        return $this;
    }

    /**
     * @return string
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @param string $person
     * @return $this
     */
    public function setPerson($person)
    {
        $this->person = $person;
        return $this;
    }

    /**
     * @return int
     */
    public function getAgent()
    {
        return $this->agent;
    }

    /**
     * @param int $agent
     * @return $this
     */
    public function setAgent($agent)
    {
        $this->agent = $agent;
        return $this;
    }

    /**
     * @return int
     */
    public function getAgentTeam()
    {
        return $this->agent_team;
    }

    /**
     * @param int $agent_team
     * @return $this
     */
    public function setAgentTeam($agent_team)
    {
        $this->agent_team = $agent_team;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * @param DateTime $date_created
     * @return $this
     */
    public function setDateCreated($date_created)
    {
        $this->date_created = $date_created;
        return $this;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param int $priority
     * @return $this
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
        return $this;
    }

    /**
     * Add a ticket message
     *
     * @param TicketMessage $message
     * @return $this
     */
    public function addMessage(TicketMessage $message)
    {
        $this->messages[] = $message;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        $messages = array();
        foreach ($this->messages as $message) {
            /** @var TicketMessage $message */
            $messages[] = $message->toArray();
        }

        return array(
            'ref'          => $this->ref,
            'department'   => $this->department,
            'person'       => $this->person,
            'agent'        => $this->agent,
            'agent_team'   => $this->agent_team,
            'status'       => $this->status,
            'date_created' => $this->date_created->format('Y-m-d H:i:s'),
            'subject'      => $this->subject,
            'priority'     => $this->priority,
            'messages'     => $messages,
        );
    }
}
