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

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets;

use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\CustomDataTicket;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\EmailAccount;
use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonEmail;
use Application\DeskPRO\Entity\Product;
use Application\DeskPRO\Entity\Ticket as TicketEntity;
use Application\DeskPRO\Entity\TicketCategory;
use Application\DeskPRO\Entity\TicketPriority;
use Application\DeskPRO\Entity\TicketSla;
use Application\DeskPRO\Entity\TicketWorkflow;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as JMS;

/**
 * Class Ticket.
 *
 * @JMS\ExclusionPolicy("all")
 */
class Ticket
{
    /**
     * The unique ID.
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $id;

    /**
     * String reference.
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    private $ref;

    /**
     * Auth string.
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var string
     */
    private $auth;

    /**
     * Parent of this ticket.
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\Ticket>")
     * @JMS\SerializedName("parent_ticket")
     *
     * @var TicketEntity
     */
    private $parentTicket;

    /**
     * Language associated with this ticket.
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\Language>")
     *
     * @var Language
     */
    private $language;

    /**
     * Ticket where this department is processing.
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\Department>")
     *
     * @var Department
     */
    private $department;

    /**
     * Category of this ticket.
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\TicketCategory>")
     *
     * @var TicketCategory
     */
    private $category;

    /**
     * Priority of this ticket.
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\TicketPriority>")
     *
     * @var TicketPriority
     */
    private $priority;

    /**
     * Workflow entity.
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\TicketWorkflow>")
     *
     * @var TicketWorkflow
     */
    private $workflow;

    /**
     * Product about which this ticket.
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\Product>")
     *
     * @var Product
     */
    private $product;

    /**
     * Person created this ticket.
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     *
     * @var Person
     */
    private $person;

    /**
     * Person`s email.
     *
     * @JMS\Expose()
     * @JMS\Type("to_string<Application\DeskPRO\Entity\PersonEmail>")
     * @JMS\SerializedName("person_email")
     *
     * @var PersonEmail
     */
    private $personEmail;

    /**
     * Agent assigned.
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     *
     * @var Person
     */
    private $agent;

    /**
     * Agent team where this ticket is processing.
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\AgentTeam>")
     * @JMS\SerializedName("agent_team")
     *
     * @var AgentTeam
     */
    private $agentTeam;

    /**
     * Organization team where this ticket is processing.
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\Organization>")
     *
     * @var Organization
     */
    private $organization;

    /**
     * ChatConversation where this ticket is discussed.
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\ChatConversation>")
     * @JMS\SerializedName("linked_chat")
     *
     * @var ChatConversation
     */
    private $linkedChat;

    /**
     * Addresses where ticket was send.
     *
     * @JMS\Expose()
     * @JMS\Type("array")
     * @JMS\SerializedName("sent_to_address")
     *
     * @var array
     */
    private $sentToAddress;

    /**
     * Email account used to gather the ticket.
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\EmailAccount>")
     * @JMS\SerializedName("email_account")
     *
     * @var EmailAccount
     */
    private $emailAccount;

    /**
     * Email account address used to gather the ticket.
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     * @JMS\SerializedName("email_account_address")
     *
     * @var string
     */
    private $emailAccountAddress;

    /**
     * How this ticket appears.
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     * @JMS\SerializedName("creation_system")
     *
     * @var string
     */
    private $creationSystem;

    /**
     * Option used by creation system.
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     * @JMS\SerializedName("creation_system_option")
     *
     * @var string
     */
    private $creationSystemOption;

    /**
     * Unique hash.
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     * @JMS\SerializedName("ticket_hash")
     *
     * @var string
     */
    private $ticketHash;

    /**
     * Ticket status.
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    private $status;

    /**
     * Ticket hidden status.
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     * @JMS\SerializedName("hidden_status")
     *
     * @var string
     */
    private $hiddenStatus;

    /**
     * Is this on hold?
     *
     * @JMS\Expose()
     * @JMS\Type("boolean")
     * @JMS\SerializedName("is_hold")
     *
     * @var bool
     */
    private $isHold;

    /**
     * String array of labels.
     *
     * @JMS\Expose()
     * @JMS\Type("array<to_string<Application\DeskPRO\Entity\LabelTicket>>")
     *
     * @var bool
     */
    private $labels;

    /**
     * How urgent this ticket?
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $urgency;

    /**
     * It`s rating based on feedback votes.
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     * @JMS\SerializedName("feedback_rating")
     *
     * @var int
     */
    private $feedbackRating;

    /**
     * When the rating was calculated.
     *
     * @JMS\Expose()
     * @JMS\Type("DateTime")
     * @JMS\SerializedName("date_feedback_rating")
     *
     * @var \DateTime
     */
    private $dateFeedbackRating;

    /**
     * When the ticket was created.
     *
     * @JMS\Expose()
     * @JMS\Type("DateTime")
     * @JMS\SerializedName("date_created")
     *
     * @var \DateTime
     */
    private $dateCreated;

    /**
     * When the ticket was resolved.
     *
     * @JMS\Expose()
     * @JMS\Type("DateTime")
     * @JMS\SerializedName("date_resolved")
     *
     * @var \DateTime
     */
    private $dateResolved;

    /**
     * And archived at last.
     *
     * @JMS\Expose()
     * @JMS\Type("DateTime")
     * @JMS\SerializedName("date_archived")
     *
     * @var \DateTime
     */
    private $dateArchived;

    /**
     * When it was assigned to agent at the very first time.
     *
     * @JMS\Expose()
     * @JMS\Type("DateTime")
     * @JMS\SerializedName("date_first_agent_assign")
     *
     * @var \DateTime
     */
    private $dateFirstAgentAssign;

    /**
     * When it was first time replied.
     *
     * @JMS\Expose()
     * @JMS\Type("DateTime")
     * @JMS\SerializedName("date_first_agent_reply")
     *
     * @var \DateTime
     */
    private $dateFirstAgentReply;

    /**
     * And when it was replied the last time.
     *
     * @JMS\Expose()
     * @JMS\Type("DateTime")
     * @JMS\SerializedName("date_last_agent_reply")
     *
     * @var \DateTime
     */
    private $dateLastAgentReply;

    /**
     * And the date user replied here last time.
     *
     * @JMS\Expose()
     * @JMS\Type("DateTime")
     * @JMS\SerializedName("date_last_user_reply")
     *
     * @var \DateTime
     */
    private $dateLastUserReply;

    /**
     * Time when agent started to wait user.
     *
     * @JMS\Expose()
     * @JMS\Type("DateTime")
     * @JMS\SerializedName("date_agent_waiting")
     *
     * @var \DateTime
     */
    private $dateAgentWaiting;

    /**
     * And the time when user started to wait agent.
     *
     * @JMS\Expose()
     * @JMS\Type("DateTime")
     * @JMS\SerializedName("date_user_waiting")
     *
     * @var \DateTime
     */
    private $dateUserWaiting;

    /**
     * When status was changed.
     *
     * @JMS\Expose()
     * @JMS\Type("DateTime")
     * @JMS\SerializedName("date_status")
     *
     * @var \DateTime
     */
    private $dateStatus;

    /**
     * How much user was waited?
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     * @JMS\SerializedName("total_user_waiting")
     *
     * @var int
     */
    private $totalUserWaiting;

    /**
     * Total waiting before first reply.
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     * @JMS\SerializedName("total_to_first_reply")
     *
     * @var int
     */
    private $totalToFirstReply;

    /**
     * An agent who locked the ticket.
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     * @JMS\SerializedName("locked_by_agent")
     *
     * @var Person
     */
    private $lockedByAgent;

    /**
     * Date time when ticket was locked.
     *
     * @JMS\Expose()
     * @JMS\Type("DateTime")
     * @JMS\SerializedName("date_locked")
     *
     * @var \DateTime
     */
    private $dateLocked;

    /**
     * Does this ticked has attachments?
     *
     * @JMS\Expose()
     * @JMS\Type("boolean")
     * @JMS\SerializedName("has_attachments")
     *
     * @var bool
     */
    private $hasAttachments;

    /**
     * Ticket subject.
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    private $subject;

    /**
     * Original subject given by creator.
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     * @JMS\SerializedName("original_subject")
     *
     * @var string
     */
    private $originalSubject;

    /**
     * Array of properties.
     *
     * @JMS\Expose()
     * @JMS\Type("array")
     *
     * @var array
     */
    private $properties;

    /**
     * An array of associated problems.
     *
     * @JMS\Expose()
     * @JMS\Type("collection<entity<Application\DeskPRO\Entity\Problem>>")
     *
     * @var ArrayCollection
     */
    private $problems;

    /**
     * Count of all replies made by agents.
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     * @JMS\SerializedName("count_agent_replies")
     *
     * @var int
     */
    private $countAgentReplies;

    /**
     * Count of all replies made by user.
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     * @JMS\SerializedName("count_user_replies")
     *
     * @var int
     */
    private $countUserReplies;

    /**
     * Well, this is worst SLA status.
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     * @JMS\SerializedName("worst_sla_status")
     *
     * @var string
     */
    private $worstSlaStatus;

    /**
     * An array of waiting times.
     *
     * @JMS\Expose()
     * @JMS\Type("array")
     * @JMS\SerializedName("waiting_times")
     *
     * @var array
     */
    private $waitingTimes;

    /**
     * All ticket slas.
     *
     * @JMS\Expose()
     * @JMS\Type("collection<Application\DeskPRO\Entity\TicketSla>")
     * @JMS\SerializedName("ticket_slas")
     *
     * @var TicketSla[]
     */
    private $ticketSlas;

    /**
     * Custom ticket fields.
     *
     * @JMS\Expose()
     * @JMS\Type("custom_data<array<Application\DeskPRO\Entity\CustomDataTicket>>")
     *
     * @var CustomDataTicket[]
     */
    private $fields;

    /**
     * Ticket children.
     *
     * @JMS\Expose()
     * @JMS\Type("array<entity<Application\DeskPRO\Entity\Ticket>>")
     *
     * @var TicketEntity[]
     */
    private $children;

    /**
     * Ticket siblings.
     *
     * @JMS\Expose()
     * @JMS\Type("array<entity<Application\DeskPRO\Entity\Ticket>>")
     *
     * @var TicketEntity[]
     */
    private $siblings;

    /**
     * User should be acknowledged about this ticket.
     *
     * @JMS\Expose()
     * @JMS\Type("array<entity<Application\DeskPRO\Entity\Person>>")
     *
     * @var Person[]
     */
    private $cc;

    /**
     * Agents that follows this ticket.
     *
     * @JMS\Expose()
     * @JMS\Type("array<entity<Application\DeskPRO\Entity\Person>>")
     *
     * @var Person[]
     */
    private $followers;

    /**
     * Ticket constructor.
     *
     * @param TicketEntity   $ticket
     * @param TicketEntity[] $children
     * @param TicketEntity[] $siblings
     */
    public function __construct(TicketEntity $ticket, array $children, array $siblings)
    {
        $this->id           = $ticket->getId();
        $this->ref          = $ticket->getRef();
        $this->auth         = $ticket->getAuth();
        $this->parentTicket = $ticket->getParentTicket();
        $this->language     = $ticket->getLanguage();
        $this->department   = $ticket->getDepartment();
        $this->category     = $ticket->getCategory();
        $this->priority     = $ticket->getPriority();
        $this->workflow     = $ticket->getWorkflow();
        $this->product      = $ticket->getProduct();
        $this->person       = $ticket->getPerson();

        if ($ticket->getPersonEmail()) {
            $this->personEmail = $ticket->getPersonEmail();
        } elseif ($ticket->getPerson() && $ticket->getPerson()->getPrimaryEmail()) {
            $this->personEmail = $ticket->getPerson()->getPrimaryEmail();
        } else {
            $this->personEmail = null;
        }

        $this->fields               = $ticket->getCustomData();
        $this->agent                = $ticket->getAgent();
        $this->agentTeam            = $ticket->getAgentTeam();
        $this->organization         = $ticket->getOrganization();
        $this->linkedChat           = $ticket->getLinkedChat();
        $this->sentToAddress        = $ticket->getSentToAddresses();
        $this->emailAccount         = $ticket->getEmailAccount();
        $this->emailAccountAddress  = $ticket->getEmailAccountAddress();
        $this->creationSystem       = $ticket->getCreationSystem();
        $this->creationSystemOption = $ticket->getCreationSystemOption();
        $this->ticketHash           = $ticket->getTicketHash();
        $this->status               = $ticket->getStatus();
        $this->hiddenStatus         = $ticket->getHiddenStatus();
        $this->isHold               = $ticket->isHold();
        $this->labels               = $ticket->getLabels();
        $this->urgency              = $ticket->getUrgency();
        $this->feedbackRating       = $ticket->getFeedbackRating();
        $this->dateFeedbackRating   = $ticket->getDateFeedbackRating();
        $this->dateCreated          = $ticket->getDateCreated();
        $this->dateResolved         = $ticket->getDateResolved();
        $this->dateArchived         = $ticket->getDateArchived();
        $this->dateFirstAgentAssign = $ticket->getDateFirstAgentAssign();
        $this->dateFirstAgentReply  = $ticket->getDateFirstAgentReply();
        $this->dateLastAgentReply   = $ticket->getDateLastAgentReply();
        $this->dateLastUserReply    = $ticket->getDateLastUserReply();
        $this->dateAgentWaiting     = $ticket->getDateAgentWaiting();
        $this->dateUserWaiting      = $ticket->getDateUserWaiting();
        $this->dateStatus           = $ticket->getDateStatus();
        $this->totalUserWaiting     = $ticket->getTotalUserWaiting();
        $this->totalToFirstReply    = $ticket->getTotalToFirstReply();
        $this->lockedByAgent        = $ticket->getLockedByAgent();
        $this->dateLocked           = $ticket->getDateLocked();
        $this->hasAttachments       = $ticket->isHasAttachments();
        $this->subject              = $ticket->getSubject();
        $this->originalSubject      = $ticket->getOriginalSubject();
        $this->properties           = $ticket->getProperties();
        $this->problems             = $ticket->getProblems();
        $this->countAgentReplies    = $ticket->getCountAgentReplies();
        $this->countUserReplies     = $ticket->getCountUserReplies();
        $this->worstSlaStatus       = $ticket->getWorstSlaStatus();
        $this->waitingTimes         = $ticket->getWaitingTimes();
        $this->ticketSlas           = $ticket->getTicketSlas();
        $this->cc                   = $ticket->getUserParticipants();
        $this->followers            = $ticket->getAgentParticipants();

        $this->children = $children;
        $this->siblings = $siblings;
    }
}
