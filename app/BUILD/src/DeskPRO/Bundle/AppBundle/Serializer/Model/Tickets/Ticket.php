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

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets;

use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\CustomDataTicket;
use Application\DeskPRO\Entity\CustomFieldData;
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
use DeskPRO\Bundle\AppBundle\Serializer\Deferred\CallbackDeferredProperty;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\InlineCustomSideload;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as JMS;

/**
 * Class Ticket.
 */
class Ticket
{
    /**
     * The unique ID.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $id;

    /**
     * String reference.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $ref;

    /**
     * Auth string.
     *
     * @JMS\Type("integer")
     *
     * @var string
     */
    private $auth;

    /**
     * Parent of this ticket.
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\Ticket>")
     *
     * @var TicketEntity
     */
    private $parent;

    /**
     * Language associated with this ticket.
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\Language>")
     *
     * @var Language
     */
    private $language;

    /**
     * Ticket where this department is processing.
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\Department>")
     *
     * @var Department
     */
    private $department;

    /**
     * Category of this ticket.
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\TicketCategory>")
     *
     * @var TicketCategory
     */
    private $category;

    /**
     * Priority of this ticket.
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\TicketPriority>")
     *
     * @var TicketPriority
     */
    private $priority;

    /**
     * Workflow entity.
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\TicketWorkflow>")
     *
     * @var TicketWorkflow
     */
    private $workflow;

    /**
     * Product about which this ticket.
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\Product>")
     *
     * @var Product
     */
    private $product;

    /**
     * Person created this ticket.
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     *
     * @var Person
     */
    private $person;

    /**
     * Person's email.
     *
     * @JMS\Type("to_string<Application\DeskPRO\Entity\PersonEmail>")
     *
     * @var PersonEmail
     */
    private $personEmail;

    /**
     * Agent assigned.
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     *
     * @var Person
     */
    private $agent;

    /**
     * Agent team where this ticket is processing.
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\AgentTeam>")
     *
     * @var AgentTeam
     */
    private $agentTeam;

    /**
     * Organization team where this ticket is processing.
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\Organization>")
     *
     * @var Organization
     */
    private $organization;

    /**
     * ChatConversation where this ticket is discussed.
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\ChatConversation>")
     *
     * @var ChatConversation
     */
    private $linkedChat;

    /**
     * Addresses where ticket was send.
     *
     * @JMS\Type("array")
     *
     * @var array
     */
    private $sentToAddress;

    /**
     * Email account used to gather the ticket.
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\EmailAccount>")
     *
     * @var EmailAccount
     */
    private $emailAccount;

    /**
     * Email account address used to gather the ticket.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $emailAccountAddress;

    /**
     * How this ticket appears.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $creationSystem;

    /**
     * Option used by creation system.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $creationSystemOption;

    /**
     * Unique hash.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $ticketHash;

    /**
     * Ticket status.
     *
     * @JMS\Type("string")
     * @JMS\Since("20170401")
     *
     * @var string
     */
    private $status;

    /**
     * Legacy ticket status.
     *
     * @JMS\Type("string")
     * @JMS\SerializedName("status")
     * @JMS\Until("20170400")
     *
     * @var string
     */
    private $oldStatus;

    /**
     * Legacy ticket hidden status.
     *
     * @JMS\Type("string")
     * @JMS\Until("20170400")
     *
     * @var string
     */
    private $hiddenStatus;

    /**
     * Is this on hold?
     *
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $isHold;

    /**
     * String array of labels.
     *
     * @JMS\Type("deferred<array<label<Application\DeskPRO\Entity\LabelTicket>>>")
     *
     * @var bool
     */
    private $labels;

    /**
     * How urgent this ticket?
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $urgency;

    /**
     * It's rating based on feedback votes.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $feedbackRating;

    /**
     * When the rating was calculated.
     *
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    private $dateFeedbackRating;

    /**
     * When the ticket was created.
     *
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    private $dateCreated;

    /**
     * When the ticket was resolved.
     *
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    private $dateResolved;

    /**
     * And archived at last.
     *
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    private $dateArchived;

    /**
     * When it was assigned to agent at the very first time.
     *
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    private $dateFirstAgentAssign;

    /**
     * When it was first time replied.
     *
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    private $dateFirstAgentReply;

    /**
     * And when it was replied the last time.
     *
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    private $dateLastAgentReply;

    /**
     * And the date user replied here last time.
     *
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    private $dateLastUserReply;

    /**
     * Time when agent started to wait user.
     *
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    private $dateAgentWaiting;

    /**
     * And the time when user started to wait agent.
     *
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    private $dateUserWaiting;

    /**
     * When status was changed.
     *
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    private $dateStatus;

    /**
     * How much user was waited?
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $totalUserWaiting;

    /**
     * Total waiting before first reply.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $totalToFirstReply;

    /**
     * An agent who locked the ticket.
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     *
     * @var Person
     */
    private $lockedByAgent;

    /**
     * Date time when ticket was locked.
     *
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    private $dateLocked;

    /**
     * Does this ticked has attachments?
     *
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $hasAttachments;

    /**
     * Ticket subject.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $subject;

    /**
     * Original subject given by creator.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $originalSubject;

    /**
     * Array of properties.
     *
     * @JMS\Type("array")
     *
     * @var array
     */
    private $properties;

    /**
     * An array of associated problems.
     *
     * @JMS\Type("collection<entity<Application\DeskPRO\Entity\Problem>>")
     *
     * @var ArrayCollection
     */
    private $problems;

    /**
     * Count of all replies made by agents.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $countAgentReplies;

    /**
     * Count of all replies made by user.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $countUserReplies;

    /**
     * Well, this is worst SLA status.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $worstSlaStatus;

    /**
     * An array of waiting times.
     *
     * @JMS\Type("array")
     *
     * @var array
     */
    private $waitingTimes;

    /**
     * All ticket slas.
     *
     * @JMS\Type("deferred<collection<entity<Application\DeskPRO\Entity\TicketSla>>>")
     *
     * @var TicketSla[]
     */
    private $ticketSlas;

    /**
     * Custom ticket fields.
     *
     * @JMS\Type("deferred<custom_data<array<Application\DeskPRO\Entity\CustomDataTicket>>>")
     *
     * @var CustomDataTicket[]
     */
    private $fields;

    /**
     * Contextual ticket fields (per user/org fields).
     *
     * @JMS\Type("custom_per_data<array<Application\DeskPRO\Entity\CustomFieldData>>")
     *
     * @var CustomFieldData[]
     */
    private $contextualFields;

    /**
     * Ticket children.
     *
     * @JMS\Type("array<entity<Application\DeskPRO\Entity\Ticket>>")
     *
     * @var TicketEntity[]
     */
    private $children;

    /**
     * Ticket siblings.
     *
     * @JMS\Type("array<entity<Application\DeskPRO\Entity\Ticket>>")
     *
     * @var TicketEntity[]
     */
    private $siblings;

    /**
     * User should be acknowledged about this ticket.
     *
     * @JMS\Type("deferred<array<entity<Application\DeskPRO\Entity\Person>>>")
     *
     * @var Person[]
     */
    private $cc;

    /**
     * Person star color.
     *
     * @JMS\Type("deferred<string>")
     *
     * @var string
     */
    private $star;

    /**
     * @JMS\Type("raw")
     *
     * @var InlineCustomSideload
     */
    private $ticketLayout;

    /**
     * @JMS\Type("raw")
     *
     * @var InlineCustomSideload
     */
    private $ticketExcerpt;

    /**
     * @JMS\Type("raw")
     *
     * @var InlineCustomSideload
     */
    private $ticketAgentErrors;

    /**
     * @JMS\Type("raw")
     *
     * @var InlineCustomSideload
     */
    private $ticketUserErrors;

    /**
     * Constructor.
     *
     * @param TicketEntity $ticket
     */
    public function __construct(TicketEntity $ticket)
    {
        $this->id         = $ticket->getId();
        $this->ref        = $ticket->getRef();
        $this->auth       = $ticket->getAuth();
        $this->parent     = $ticket->getParentTicket();
        $this->language   = $ticket->getLanguage();
        $this->department = $ticket->getDepartment();
        $this->category   = $ticket->getCategory();
        $this->priority   = $ticket->getPriority();
        $this->workflow   = $ticket->getWorkflow();
        $this->product    = $ticket->getProduct();
        $this->person     = $ticket->getPerson();

        if ($ticket->getTicketPersonEmail()) {
            $this->personEmail = $ticket->getTicketPersonEmail();
        } elseif ($ticket->getPerson() && $ticket->getPerson()->getPrimaryEmail()) {
            $this->personEmail = $ticket->getPerson()->getPrimaryEmail();
        }

        $this->contextualFields     = $ticket->getCustomPerData();
        $this->agent                = $ticket->getAgent();
        $this->agentTeam            = $ticket->getAgentTeam();
        $this->organization         = $ticket->getOrganization();
        $this->linkedChat           = $ticket->getLinkedChat();
        $this->sentToAddress        = array_values($ticket->getSentToAddresses());
        $this->emailAccount         = $ticket->getEmailAccount();
        $this->emailAccountAddress  = $ticket->getEmailAccountAddress();
        $this->creationSystem       = $ticket->getCreationSystem();
        $this->creationSystemOption = $ticket->getCreationSystemOption();
        $this->ticketHash           = $ticket->getTicketHash();
        $this->status               = $ticket->getStatusCode();
        $this->oldStatus            = $ticket->getStatus();
        $this->hiddenStatus         = $ticket->getHiddenStatus();
        $this->isHold               = $ticket->isHold();
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
        $this->waitingTimes         = $ticket->getWaitingTimes();
        $this->children             = $ticket->getChildrenTickets();
        $this->siblings             = $ticket->getSiblingsTickets();
    }

    /**
     * @param string $star
     */
    public function setStar($star)
    {
        $this->star = $star;
    }

    /**
     * @param InlineCustomSideload $ticketLayout
     */
    public function setTicketLayout($ticketLayout)
    {
        $this->ticketLayout = $ticketLayout;
    }

    /**
     * @param InlineCustomSideload $ticketExcerpt
     */
    public function setTicketExcerpt($ticketExcerpt)
    {
        $this->ticketExcerpt = $ticketExcerpt;
    }

    /**
     * @param InlineCustomSideload $ticketAgentErrors
     */
    public function setTicketAgentErrors($ticketAgentErrors)
    {
        $this->ticketAgentErrors = $ticketAgentErrors;
    }

    /**
     * @param InlineCustomSideload $ticketUserErrors
     */
    public function setTicketUserErrors($ticketUserErrors)
    {
        $this->ticketUserErrors = $ticketUserErrors;
    }

    /**
     * @param CallbackDeferredProperty $labels
     *
     * @return $this
     */
    public function setLabels($labels = null)
    {
        $this->labels = $labels;

        return $this;
    }

    /**
     * @param CallbackDeferredProperty $customData
     *
     * @return $this
     */
    public function setCustomData($customData = null)
    {
        $this->fields = $customData;

        return $this;
    }

    /**
     * @param CallbackDeferredProperty $cc
     *
     * @return $this
     */
    public function setCc($cc = null)
    {
        $this->cc = $cc;

        return $this;
    }

    /**
     * @param CallbackDeferredProperty $ticketSlas
     *
     * @return $this
     */
    public function setTicketSlas($ticketSlas = null)
    {
        $this->ticketSlas     = $ticketSlas;
        $this->worstSlaStatus = TicketEntity::calctWorstSlaStatus($ticketSlas);

        return $this;
    }
}
