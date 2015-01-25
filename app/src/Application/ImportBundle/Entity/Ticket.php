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


use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use DateTime;

/**
 * Exporting ticket entity
 *
 * Class Ticket
 * @package Application\ImportBundle\Entity
 */
final class Ticket extends AbstractEntity
{
    const STATUS_AWAITING_AGENT = 'awaiting_agent';
    const STATUS_AWAITING_USER  = 'awaiting_user';
    const STATUS_RESOLVED       = 'resolved';
    const STATUS_ARCHIVED       = 'archived';
    const STATUS_HIDDEN         = 'hidden';

    /**
     * @var int
     */
    private $oid;

    /**
     * @var int
     */
    private $ref;

    /**
     * @var string
     */
    private $department;

    /**
     * @var string
     */
    private $person_email;

    /**
     * @var string
     */
    private $agent_email;

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
     * @var DateTime
     */
    private $date_resolved;

    /**
     * @var DateTime
     */
    private $date_archived;

    /**
     * @var string
     */
    private $subject;

    /**
     * @var string
     */
    private $priority;

    /**
     * @var string
     */
    private $language;

    /**
     * @var string
     */
    private $category;

    /**
     * @var string
     */
    private $workflow;

    /**
     * @var string
     */
    private $product;

    /**
     * @var string
     */
    private $organization;

    /**
     * @var bool
     */
    private $is_hold = false;

    /**
     * @var int
     */
    private $urgency = 1;

    /**
     * @var string
     */
    private $participants;

    /**
     * @var string[]
     */
    private $labels = array();

    /**
     * @var Collection
     */
    private $messages;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->messages = new Collection();
    }

    /**
     * @return int
     */
    public function getOid()
    {
        return $this->oid;
    }

    /**
     * @param int $oid
     * @return $this
     */
    public function setOid($oid)
    {
        $this->oid = (int)$oid;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE_TICKET;
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
     * @return string
     */
    public function getDepartment()
    {
        return $this->department;
    }

    /**
     * @param string $department
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
    public function getPersonEmail()
    {
        return $this->person_email;
    }

    /**
     * @param string $person_email
     * @return $this
     */
    public function setPersonEmail($person_email)
    {
        $this->person_email = (string)$person_email;
        return $this;
    }

    /**
     * @return int
     */
    public function getAgentEmail()
    {
        return $this->agent_email;
    }

    /**
     * @param string $agent_email
     * @return $this
     */
    public function setAgentEmail($agent_email)
    {
        $this->agent_email = (string)$agent_email;
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
     * Checks if status is valid
     *
     * @return bool
     */
    public function isStatusValid()
    {
        $statuses = array(
            self::STATUS_AWAITING_AGENT,
            self::STATUS_AWAITING_USER,
            self::STATUS_RESOLVED,
            self::STATUS_ARCHIVED,
            self::STATUS_HIDDEN,
        );

        return in_array($this->status, $statuses, true);
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
    public function setDateCreated(DateTime $date_created)
    {
        $this->date_created = $date_created;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDateArchived()
    {
        return $this->date_archived;
    }

    /**
     * @param DateTime $date_archived
     * @return $this
     */
    public function setDateArchived(DateTime $date_archived)
    {
        $this->date_archived = $date_archived;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDateResolved()
    {
        return $this->date_resolved;
    }

    /**
     * @param DateTime $date_resolved
     * @return $this
     */
    public function setDateResolved(DateTime $date_resolved)
    {
        $this->date_resolved = $date_resolved;
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
     * @return string
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param string $priority
     * @return $this
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
        return $this;
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param string $language
     * @return $this
     */
    public function setLanguage($language)
    {
        $this->language = $language;
        return $this;
    }

    /**
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param string $category
     * @return $this
     */
    public function setCategory($category)
    {
        $this->category = $category;
        return $this;
    }

    /**
     * @return string
     */
    public function getWorkflow()
    {
        return $this->workflow;
    }

    /**
     * @param string $workflow
     * @return $this
     */
    public function setWorkflow($workflow)
    {
        $this->workflow = $workflow;
        return $this;
    }

    /**
     * @return string
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param string $product
     * @return $this
     */
    public function setProduct($product)
    {
        $this->product = $product;
        return $this;
    }

    /**
     * @return string
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @param string $organization
     * @return $this
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isHold()
    {
        return $this->is_hold;
    }

    /**
     * @param boolean $is_hold
     * @return $this
     */
    public function setAsHold($is_hold)
    {
        $this->is_hold = $is_hold;
        return $this;
    }

    /**
     * @return int
     */
    public function getUrgency()
    {
        return $this->urgency;
    }

    /**
     * @param int $urgency
     * @return $this
     */
    public function setUrgency($urgency)
    {
        $this->urgency = $urgency;
        return $this;
    }

    /**
     * @return string
     */
    public function getParticipants()
    {
        return $this->participants;
    }

    /**
     * @param string $participants
     * @return $this
     */
    public function setParticipants($participants)
    {
        $this->participants = $participants;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @param string $label
     * @return $this
     */
    public function addLabel($label)
    {
        $this->labels[] = $label;
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
        $this->messages->attach($message);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        if (!$this->date_created) {
            throw new \Exception('Date created is not set up');
        }

        $messages = array();
        foreach ($this->messages as $message) {
            /** @var TicketMessage $message */
            $messages[] = $message->toArray();
        }

        return array(
            'ref'          => $this->ref,
            'department'   => $this->department,
            'person'       => $this->person_email,
            'agent'        => $this->agent_email,
            'agent_team'   => $this->agent_team,
            'status'       => $this->status,
            'date_created' => $this->date_created->format('Y-m-d H:i:s'),
            'subject'      => $this->subject,
            'priority'     => $this->priority,
            'messages'     => $messages,
        );
    }

    /**
     * Validator class metadata
     *
     * @param ClassMetadata $metadata
     */
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata
            ->addPropertyConstraint('ref', new Constraints\NotBlank())

            ->addPropertyConstraint('person_email', new Constraints\NotBlank())
            ->addPropertyConstraint('person_email', new Constraints\Email())

            ->addPropertyConstraint('agent_email', new Constraints\Email())
            ->addPropertyConstraint('subject', new Constraints\NotBlank())
            ->addPropertyConstraint('status', new Constraints\NotBlank())

            ->addGetterConstraint('statusValid', new Constraints\True());
    }
}
