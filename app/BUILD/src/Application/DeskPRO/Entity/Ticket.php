<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\App;
use Application\DeskPRO\Domain\DomainObject;
use Application\DeskPRO\Entity\Labels\Label;
use Application\DeskPRO\Entity\Labels\LabelsOwner;
use Application\DeskPRO\Tickets\ExecutorContext;
use Application\DeskPRO\Tickets\TicketChangeTracker;
use DeskPRO\Bundle\AppBundle\Entity\CustomPerDataOwnerInterface;
use DeskPRO\Bundle\AppBundle\Entity\CustomPerDataTrait;
use DeskPRO\Bundle\AppBundle\Entity\TicketFeedbackLink;
use DeskPRO\Bundle\AppBundle\Entity\TicketFollowUp;
use DeskPRO\Bundle\AppBundle\Entity\TicketStatus;
use DeskPRO\Bundle\AppBundle\ObjectRouter\Configuration\PortalLinkCustom;
use DeskPRO\Bundle\AppBundle\ObjectRouter\Configuration\PortalLinkRoute;
use DeskPRO\Bundle\AppBundle\Ticket\VirtualTicketStatus;
use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;
use DeskPRO\Component\Util\RegexUtils;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Proxy\Proxy;
use DpSys\LowError\SystemErrorHandler;
use FOS\ElasticaBundle\Transformer\HighlightableModelInterface;
use Orb\Util\Arrays;
use Orb\Util\DpStrings;
use Orb\Util\OptionsArray;
use Orb\Util\Strings;
use Orb\Util\Util;
use Orb\Util\WorkHoursSet;
use Orb\Util\WorkHoursSetAll;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Ticket.
 *
 * @property int                                 $id
 * @property string                              $ref
 * @property string                              $auth
 * @property Language                            $language
 * @property Brand                               $brand
 * @property Department                          $department
 * @property TicketCategory                      $category
 * @property TicketWorkflow                      $workflow
 * @property TicketPriority                      $priority
 * @property Product                             $product
 * @property Person                              $person
 * @property PersonEmail                         $person_email
 * @property Person                              $agent
 * @property AgentTeam                           $agent_team
 * @property Organization                        $organization
 * @property ChatConversation                    $linked_chat
 * @property TicketAttachment[]|ArrayCollection  $attachments
 * @property TicketAccessCode[]|ArrayCollection  $access_codes
 * @property TicketMessage[]|ArrayCollection     $messages
 * @property TicketSms[]                         $sms_messages
 * @property TicketFeedbackLink[]|ArrayCollection  $feedback_links
 * @property CustomDataTicket[]|ArrayCollection  $custom_data
 * @property LabelTicket[]                       $labels
 * @property string                              $sent_to_address
 * @property EmailAccount                        $email_account
 * @property string                              $email_account_address
 * @property string                              $creation_system
 * @property string                              $creation_system_option
 * @property string                              $ticket_hash
 * @property string                              $status
 * @property TicketStatus                        $ticket_status
 * @property int                                 $urgency
 * @property int                                 $feedback_rating
 * @property \DateTime                           $date_feedback_rating
 * @property \DateTime                           $date_created
 * @property \DateTime                           $date_resolved
 * @property \DateTime                           $date_archived
 * @property \DateTime                           $date_first_agent_assign
 * @property \DateTime                           $date_first_agent_reply
 * @property \DateTime                           $date_last_agent_reply
 * @property \DateTime                           $date_last_user_reply
 * @property \DateTime                           $date_agent_waiting
 * @property \DateTime                           $date_user_waiting
 * @property \DateTime                           $date_status
 * @property int                                 $total_user_waiting
 * @property int                                 $total_to_first_reply
 * @property Person                              $locked_by_agent
 * @property \DateTime                           $date_locked
 * @property bool                                $has_attachments
 * @property string                              $subject
 * @property string                              $original_subject
 * @property array                               $properties
 * @property int                                 $count_agent_replies
 * @property int                                 $count_user_replies
 * @property string|null                         $worst_sla_status
 * @property array                               $waiting_times
 * @property TicketParticipant[]|ArrayCollection $participants
 * @property TicketCharge[]                      $charges
 * @property TicketSla[]|ArrayCollection         $ticket_slas
 *
 * REPEAT THESE ANNOTATIONS IN DeskPRO\Bundle\AppBundle\Model\TicketView
 * @PortalLinkRoute("portal_tickets_guest_view", route_param_map={"auth":"auth"}, type="view_only")
 * @PortalLinkCustom()
 * @PortalLinkCustom(type="edit")
 * @PortalLinkCustom(type="resolve")
 * @PortalLinkCustom(type="unresolve")
 * @PortalLinkCustom(type="add-cc")
 *
 * @AppAssert\Ticket\TicketLink()
 * @AppAssert\Ticket\TicketDupe()
 */
class Ticket extends DomainObject implements HighlightableModelInterface, LabelsOwner, CustomPerDataOwnerInterface
{
    use CustomPerDataTrait;

    const TAC_AUTHCODE_LEN     = 15;
    const TAC_AUTHCODE_LEN_MAX = 30;

    const CREATED_WEB_PERSON           = 'web.person';
    const CREATED_WEB_PERSON_PORTAL    = 'web.person.portal';
    const CREATED_WEB_PERSON_WIDGET    = 'web.person.widget';
    const CREATED_WEB_PERSON_EMBED     = 'web.person.embed';
    const CREATED_WEB_AGENT            = 'web.agent';
    const CREATED_WEB_AGENT_PORTAL     = 'web.agent.portal';
    const CREATED_WEB_API              = 'web.api';
    const CREATED_WEB_API_PERSON       = 'web.api.person';
    const CREATED_WEB_API_AGENT        = 'web.api.agent';
    const CREATED_GATEWAY_PERSON       = 'gateway.person';
    const CREATED_GATEWAY_AGENT        = 'gateway.agent';
    const CREATED_MESSENGER_UNANSWERED = 'messenger.unanswered';

    /**#@+
     * These strings in $notify_email_name have special meanings.
     * NOTIFY_NAME_HELPDESK: The helpdesk name
     * NOTIFY_NAME_PERSON: The person who sent the reply, or if no person (eg auto-response), then the helpdesk
     */
    const NOTIFY_NAME_HELPDESK = '__DP_HELPDESK__';
    const NOTIFY_NAME_PERSON   = '__DP_PERSON__';
    /**#@-*/

    /**
     * @var int
     */
    protected $id = null;

    /**
     * The original id (eg before a delete was made).
     *
     * @var int
     */
    protected $_original_id;

    /**
     * This is a temporary (non-persited) flag that lets us force set an "Access code" for B.C. in emails of old-portal.
     *
     * @var bool
     *
     * @deprecated remove this after we remove code in PortalValidation::sendTicketVerificationEmail that requires it
     */
    protected $_force_access_code = false;

    /**
     * @var string
     */
    protected $ref = null;

    /**
     * @var int
     */
    protected $auth;

    /**
     * Parent ticket.
     *
     * @var \Application\DeskPRO\Entity\Ticket
     */
    protected $parent_ticket = null;

    /**
     * @var ArrayCollection|Ticket[]
     */
    protected $children_tickets;

    /**
     * The language the ticket is in.
     *
     * @var \Application\DeskPRO\Entity\Language
     */
    protected $language = null;

    /**
     * @var \Application\DeskPRO\Entity\Brand
     */
    protected $brand = null;

    /**
     * @var \Application\DeskPRO\Entity\Department
     *
     * @AppAssert\LeafDepartment()
     */
    protected $department = null;

    /**
     * @var \Application\DeskPRO\Entity\TicketCategory
     *
     * @AppAssert\Ticket\TicketLeafCategory()
     */
    protected $category = null;

    /**
     * @var \Application\DeskPRO\Entity\TicketPriority
     */
    protected $priority = null;

    /**
     * @var \Application\DeskPRO\Entity\TicketWorkflow
     */
    protected $workflow = null;

    /**
     * @var \Application\DeskPRO\Entity\Product
     *
     * @AppAssert\Ticket\TicketLeafProduct()
     */
    protected $product = null;

    /**
     * @var \Application\DeskPRO\Entity\Person
     *
     * @Assert\NotNull()
     */
    protected $person = null;

    /**
     * @var \Application\DeskPRO\Entity\PersonEmail
     */
    protected $person_email = null;

    /**
     * @var \Application\DeskPRO\Entity\Person
     *
     * @AppAssert\Person\PersonType(type="agent")
     */
    protected $agent = null;

    /**
     * @var \Application\DeskPRO\Entity\AgentTeam
     */
    protected $agent_team = null;

    /**
     * @var \Application\DeskPRO\Entity\Organization
     */
    protected $organization = null;

    /**
     * @var \Application\DeskPRO\Entity\ChatConversation
     */
    protected $linked_chat = null;

    /**
     * @var ArrayCollection
     */
    protected $attachments;

    /**
     * @var ArrayCollection
     */
    protected $access_codes;

    /**
     * @var ArrayCollection
     */
    protected $messages;

    /**
     * @var ArrayCollection
     */
    protected $sms_messages;

    /**
     * @var TicketFeedbackLink[]|ArrayCollection
     */
    protected $feedback_links;

    /**
     * @var ArrayCollection|CustomDataTicket[]
     */
    protected $custom_data;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @Assert\Valid()
     * @AppAssert\UniqueCollection(property={"label"})
     */
    protected $labels;

    /**
     * The email address the ticket was sent to if it came in via a gateway.
     * This is a full string (e.g., including CC's) of the original.
     *
     * @var string
     */
    protected $sent_to_address = '';

    /**
     * The gateway this ticket originated from.
     *
     * @var \Application\DeskPRO\Entity\EmailAccount
     */
    protected $email_account = null;

    /**
     * The email address (from list of to/cc) that matched with the email account.
     *
     * @var string
     */
    protected $email_account_address = '';

    /**
     * @var string
     */
    protected $creation_system = 'unknown';

    /**
     * Optional information about the creation system. For example, source URL the ticket came from.
     *
     * @var string
     */
    protected $creation_system_option = '';

    /**
     * @var string
     */
    protected $ticket_hash = 'none';

    /**
     * @var string
     */
    protected $status;

    /**
     * @var TicketStatus
     */
    protected $ticket_status = null;

    /**
     * @var int
     */
    protected $urgency = 1;

    /**
     * @var int
     */
    protected $feedback_rating = null;

    /**
     * @var \DateTime
     */
    protected $date_feedback_rating = null;

    /**
     * @var \DateTime
     */
    protected $date_created;

    /**
     * @var \DateTime
     */
    protected $date_resolved = null;

    /**
     * @var \DateTime
     */
    protected $date_archived = null;

    /**
     * @var \DateTime
     */
    protected $date_first_agent_assign = null;

    /**
     * @var \DateTime
     */
    protected $date_first_agent_reply = null;

    /**
     * @var \DateTime
     */
    protected $date_last_agent_reply = null;

    /**
     * @var \DateTime
     */
    protected $date_last_user_reply = null;

    /**
     * @var \DateTime
     */
    protected $date_agent_waiting = null;

    /**
     * @var \DateTime
     */
    protected $date_user_waiting = null;

    /**
     * @var \DateTime
     */
    protected $date_status = null;

    /**
     * @var \DateTime
     */
    protected $date_on_hold = null;

    /**
     * @var int
     */
    protected $total_user_waiting = 0;

    /**
     * @var int
     */
    protected $total_to_first_reply = 0;

    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $locked_by_agent = null;

    /**
     * @var \DateTime
     */
    protected $date_locked = null;

    /**
     * @var bool
     */
    protected $has_attachments = false;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     */
    protected $subject = '';

    /**
     * @var string
     */
    protected $original_subject = '';

    /**
     * @var array
     */
    protected $properties = null;

    /**
     * @var int
     */
    protected $count_agent_replies = 0;

    /**
     * @var int
     */
    protected $count_user_replies = 0;

    /**
     * @var string|null
     */
    protected $worst_sla_status = null;

    /**
     * @var array
     */
    protected $waiting_times = [];

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $participants;

    /**
     * Array cache of user participants.
     *
     * @var array
     *
     * @see getUserParticipants
     */
    protected $_user_participants;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $charges;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $ticket_slas;

    /**
     * An exploded version of sent_to_addresses.
     *
     * @var array
     */
    protected $_sent_to_addresses;

    /**
     * @var null|\Application\DeskPRO\Labels\LabelManager
     */
    protected $_label_manager = null;

    /**
     * @var \Orb\Util\WorkHoursSet|null
     */
    protected $_work_hours_set = null;

    /**
     * The search result highlights.
     *
     * @var array
     */
    protected $_search_highlights;

    /**
     * linked jira issues.
     *
     * @var
     */
    protected $jira_issues;

    /**
     * @var ArrayCollection
     */
    protected $problems;

    /**
     * If the ticket was created from an email just now, then this is the reader.
     *
     * @var \Application\DeskPRO\EmailGateway\Reader\AbstractReader
     */
    public $email_reader;

    /**
     * The action the email reader was used for (reply/note/action).
     *
     * @var string
     */
    public $email_reader_action;

    /**
     * @internal
     */
    public $__dp_is_processing_ticket = false;

    /**
     * @internal
     */
    public $__dp_last_process_save = null;

    /**
     * @internal
     */
    public $__dp_ticket_change_tracker = null;

    /**
     * @internal
     */
    public $__dp_auto_ticket_process = false;

    /**
     * @var bool
     */
    public $__dp_is_autogen_ref = false;

    /**
     * @var bool
     */
    public $_is_new = false;

    /**
     * @var array
     */
    protected $api_data = [];

    /**
     * @var string|null
     */
    protected $api_data_hash = null;

    /**
     * @var TicketFlagged[]
     */
    protected $stars;

    /**
     * @var TicketFollowUp[]
     */
    protected $followUps;

    /**
     * @var TicketLog[]
     */
    protected $logs;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->_original_id     = null;
        $this->_is_new          = true;
        $this->participants     = new ArrayCollection();
        $this->jira_issues      = new ArrayCollection();
        $this->messages         = new ArrayCollection();
        $this->sms_messages     = new ArrayCollection();
        $this->feedback_links   = new ArrayCollection();
        $this->custom_data      = new ArrayCollection();
        $this->customPerData    = new ArrayCollection();
        $this->labels           = new ArrayCollection();
        $this->access_codes     = new ArrayCollection();
        $this->attachments      = new ArrayCollection();
        $this->charges          = new ArrayCollection();
        $this->ticket_slas      = new ArrayCollection();
        $this->problems         = new ArrayCollection();
        $this->children_tickets = new ArrayCollection();
        $this->stars            = new ArrayCollection();
        $this->followUps        = new ArrayCollection();
        $this->logs             = new ArrayCollection();

        // Default ref (is reset with ref generator)
        $this->ref = DpStrings::random(10, Strings::CHARS_ALPHA_IU).'-'.date('YzB');

        // flag used in manager to signal that we should overwrite this with a real ref generator ref
        $this->__dp_is_autogen_ref = true;

        $this['date_created'] = new \DateTime();
        $this['date_status']  = new \DateTime();

        $this['auth'] = DpStrings::random(self::TAC_AUTHCODE_LEN, Strings::CHARS_KEY);
        $this->setModelField('status', TicketStatus::STATUS_TYPE_AWAITING_AGENT);

        $this->__dp_auto_ticket_process = true;
    }

    /**
     * By default, all ticket changes go through the full ticket processing routines
     * (triggers, filters etc) automatically on every flush.
     *
     * This is mostly for legacy reasons though. It's recommended you always handle it yourself.
     * So if you are manually managing the ticket will save the ticket through the TicketManager,
     * you should disable auto-processing.
     *
     * @return $this
     */
    public function disableAutoTicketProcess()
    {
        $this->__dp_auto_ticket_process = false;

        // prevent auto ticket process of parent ticket while saving as well
        // e.g. it's called in snippet formatter of reply action

        $parentTicket = $this->getParentTicket();
        if ($parentTicket instanceof Proxy) {
            // we need to init the proxy to disable auto ticket process properly
            // otherwise it will be re-enabled on proxy init
            $parentTicket->__load();
        }
        if ($parentTicket && $parentTicket->__dp_auto_ticket_process) {
            $parentTicket->disableAutoTicketProcess();
        }

        return $this;
    }

    /**
     * @return bool true if all of the messages on the note are agent notes
     */
    public function hasNotesOnly()
    {
        return $this->date_last_agent_reply === null && $this->date_last_user_reply === null;
    }

    /**
     * Enable auto ticket processing.
     *
     * @see disableAutoTicketProcess
     *
     * @return $this
     */
    public function enableAutoTicketProcess()
    {
        $this->__dp_auto_ticket_process = true;

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int|null
     */
    public function getOriginalId()
    {
        return $this->_original_id;
    }

    /**
     * Returns ticket unique ref.
     *
     * @return string
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * @param string $ref
     *
     * @return $this
     */
    public function setRef($ref)
    {
        $this->setModelField('ref', $ref);

        return $this;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    public function getPlainSubject()
    {
        return $this->subject;
    }

    /**
     * Alias for getSubject.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getSubject();
    }

    /**
     * Get an array of addresses the ticket was sent To or CC's.
     *
     * @return array
     */
    public function getSentToAddresses()
    {
        if (!$this->sent_to_address) {
            return [];
        }

        if ($this->_sent_to_addresses !== null) {
            return $this->_sent_to_addresses;
        }

        $this->_sent_to_addresses = explode(',', $this->sent_to_address);
        $this->_sent_to_addresses = array_combine($this->_sent_to_addresses, $this->_sent_to_addresses);

        return $this->_sent_to_addresses;
    }

    /**
     * Check if an address was in To or CC.
     *
     * @param $address
     *
     * @return bool
     */
    public function hasSentToAddress($address)
    {
        $address = strtolower($address);
        $this->getSentToAddresses();

        return isset($this->_sent_to_addresses[$address]);
    }

    /**
     * @param string $addresses
     */
    public function setSentToAddress($addresses)
    {
        if (is_array($addresses)) {
            $addresses = implode(',', $addresses);
        }

        $addresses = strtolower($addresses);

        $this->setModelField('sent_to_address', $addresses);
        $this->_sent_to_addresses = null;
    }

    /**
     * @param string $subject
     *
     * @return $this
     */
    public function setSubject($subject)
    {
        if (!$subject) {
            $subject = '';
        }

        if (is_string($subject)) {
            $subject = Strings::standardEol($subject);
            $subject = Strings::trimLines($subject);
            $subject = preg_replace("#\n+#", ' ', $subject);
        }

        $this->setModelField('subject', $subject);

        if (!$this->original_subject) {
            $this->setProcessedOriginalSubject($subject);
        }

        return $this;
    }

    /**
     * Parse off common prefixes on subjects and then set the original subject.
     *
     * @param $subject
     */
    public function setProcessedOriginalSubject($subject)
    {
        do {
            $orig    = $subject;
            $subject = RegexUtils::safePregReplace('#^(RE|VS|AW|SV|FW|FWD|VL|WG|FS|VB|RV|VS):\s*#i', '', $subject);
        } while ($orig != $subject);

        $this->setModelField('original_subject', $subject);
    }

    /**
     * @return \Application\DeskPRO\Entity\Person[]
     */
    public function getUserParticipants()
    {
        $ret = [];
        foreach ($this['participants'] as $p) {
            if (!$p['person']['is_agent']) {
                $ret[] = $p->person;
            }
        }

        return $ret;
    }

    /**
     * @return \Application\DeskPRO\Entity\Person[]
     */
    public function getAgentParticipants()
    {
        $ret = [];
        foreach ($this->participants as $p) {
            if ($p->person['is_agent']) {
                $ret[] = $p->person;
            }
        }

        return $ret;
    }

    /**
     * @return TicketParticipant[]|ArrayCollection
     */
    public function getParticipants()
    {
        return $this->participants;
    }

    /**
     * @return TicketParticipant[]|ArrayCollection
     */
    public function getCcs()
    {
        return $this->participants->filter(function (TicketParticipant $participant) {
            return $participant->getPerson() && !$participant->getPerson()->isAgent();
        });
    }

    /**
     * @return TicketParticipant[]|ArrayCollection
     */
    public function getFollowers()
    {
        return $this->participants->filter(function (TicketParticipant $participant) {
            return $participant->getPerson() && $participant->getPerson()->isAgent();
        });
    }

    /**
     * @return Ticket
     */
    public function getParentTicket()
    {
        return $this->parent_ticket;
    }

    /**
     * @param Ticket $ticket
     *
     * @return $this
     */
    public function setParentTicket(Ticket $ticket = null)
    {
        $this->setModelField('parent_ticket', $ticket);

        return $this;
    }

    /**
     * @return Ticket[]|ArrayCollection
     */
    public function getChildrenTickets()
    {
        return $this->children_tickets;
    }

    /**
     * @param Ticket $ticket
     *
     * @return $this
     */
    public function addChildrenTicket(Ticket $ticket)
    {
        $ticket->setParentTicket($this);

        $this->children_tickets->add($ticket);
        $this->_onPropertyChanged('children_tickets', null, $this->children_tickets);

        return $this;
    }

    /**
     * @param Ticket $ticket
     *
     * @return $this
     */
    public function removeChildrenTicket(Ticket $ticket)
    {
        $ticket->setParentTicket(null);

        $this->children_tickets->removeElement($ticket);
        $this->_onPropertyChanged('children_tickets', null, $this->children_tickets);

        return $this;
    }

    /**
     * @return Ticket[]|ArrayCollection
     */
    public function getSiblingsTickets()
    {
        if ($this->parent_ticket) {
            return $this->parent_ticket->getChildrenTickets()->filter(function (Ticket $item) {
                return $item !== $this;
            });
        }

        return new ArrayCollection();
    }

    /**
     * Given an array of agents, sync the current parts with those in the array.
     * So remove ones that aren't in it, or add new ones.
     *
     * @param array|ArrayCollection $agents
     */
    public function setAgentParticipants($agents)
    {
        $current_agent_ids = [];
        foreach ($this->participants as $p) {
            if ($p->person->is_agent) {
                $current_agent_ids[] = $p->person->id;
            }
        }

        $got_agent_ids = [];
        foreach ($agents as $p) {
            $got_agent_ids[] = $p->id;
        }
        foreach ($got_agent_ids as $id) {
            $this->addParticipantPerson($id);
        }

        $remove_agent_ids = array_diff($current_agent_ids, $got_agent_ids);
        foreach ($remove_agent_ids as $id) {
            $this->removeParticipantPerson($id);
        }
    }

    /**
     * Try to find a user that is a part of this tikcet based on
     * their email address.
     *
     * @param string $email_address
     *
     * @return Person
     */
    public function findUserByEmail($email_address)
    {
        $email_address = strtolower($email_address);

        // The author
        if ($this->person->findEmailAddress($email_address)) {
            return $this->person;

        // Any of the participants
        } else {
            foreach ($this->getUserParticipants() as $person) {
                if ($person->findEmailAddress($email_address)) {
                    return $person;
                }
            }
        }

        return;
    }

    /**
     * Try to find an agent that is part of this ticket based on an email addres.
     *
     * @param string $email_address
     *
     * @return Person|null
     */
    public function findAgentByEmail($email_address)
    {
        if ($this->person->is_agent && $this->person->findEmailAddress($email_address)) {
            return $this->person;
        } else {
            foreach ($this->participants as $part) {
                if ($part->person->is_agent && $part->person->findEmailAddress($email_address)) {
                    return $part->person;
                }
            }
        }

        return;
    }

    /**
     * Modify urgency by $mod, which can be positive or negative.
     *
     * @param int $mod
     */
    public function modifyUrgency($mod)
    {
        $old_u = $this->urgency;
        $new_u = \Orb\Util\Numbers::bound($old_u + $mod, 1, 10);

        if ($old_u != $new_u) {
            $this->urgency = $new_u;

            $this->_onPropertyChanged('urgency', $old_u, $new_u);
        }
    }

    /**
     * Set the urgency to a specific value.
     *
     * @param int $set
     */
    public function setUrgency($set)
    {
        $old_u = $this->urgency;
        $new_u = \Orb\Util\Numbers::bound($set, 1, 10);

        if ($old_u != $new_u) {
            $this->urgency = $new_u;
            $this->_onPropertyChanged('urgency', $old_u, $new_u);
        }
    }

    /**
     * @param string $rating
     */
    public function setFeedbackRating($rating)
    {
        $this->setModelField('feedback_rating', $rating);
        $this->setModelField('date_feedback_rating', new \DateTime());
    }

    /**
     * @return string
     */
    public function getFeedbackRatingType()
    {
        if ($this->feedback_rating == 1) {
            return 'positive';
        } elseif ($this->feedback_rating == -1) {
            return 'negative';
        } else {
            return 'neutral';
        }
    }

    public function getFeedbackRating()
    {
        return $this->feedback_rating;
    }

    /**
     * Reset the participants collection.
     * todo add onPropertyChanged() if change tracking is needed.
     *
     * @return $this
     */
    public function resetParticipants()
    {
        $this->participants->clear();

        return $this;
    }

    /**
     * Get a simple array of person ID's of participants.
     *
     * @return array
     */
    public function getParticipantPeopleIds()
    {
        $ids = [];
        foreach ($this->getParticipants() as $p) {
            $ids[] = $p['person']['id'];
        }

        return $ids;
    }

    /**
     * Check if there exists a person on this ticket with a particular email address.
     *
     * @param string $email_address
     *
     * @return Person|bool
     */
    public function hasParticipantEmailAddress($email_address)
    {
        if ($this->agent && $this->agent->hasEmailAddress($email_address)) {
            return $this->agent;
        }

        if ($this->person && $this->person->hasEmailAddress($email_address)) {
            return $this->person;
        }

        foreach ($this->participants as $p) {
            if ($p->person->hasEmailAddress($email_address)) {
                return $p->person;
            }
        }

        return false;
    }

    /**
     * Check if a person ID or a person object is current a participant.
     *
     * @param  $person_or_id
     *
     * @return bool
     */
    public function hasParticipantPerson($person_or_id)
    {
        $person_id = $person_or_id;
        if ($person_or_id instanceof Person) {
            $person_id = $person_or_id->getId();
        }

        // User not commited yet, so obviously they dont exist
        if (!$person_id) {
            return false;
        }

        foreach ($this->participants as $p) {
            if ($p->person->getId() == $person_id) {
                return $p;
            }
        }

        return false;
    }

    /**
     * Check if any of the given ids are a participant.
     *
     * If any given ID is a participant person ID, this is true. Else false.
     *
     * @param array $person_ids
     *
     * @return bool
     */
    public function hasAnyParticipantId(array $person_ids)
    {
        $person_ids = Arrays::flatten($person_ids);
        foreach ($person_ids as $id) {
            if ($this->hasParticipantPerson($id)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Add a participant.
     *
     * @param $person_or_id
     *
     * @return TicketParticipant
     */
    public function addParticipantPerson($person_or_id)
    {
        $person = $person_or_id;
        if (!($person instanceof Person)) {
            $person = App::getEntityRepository('DeskPRO:Person')->find($person);
        }

        if (!$person) {
            return;
        }

        if ($this->person && $person->getId() == $this->person->getId() && ((defined(
                        'DP_INTERFACE'
                    ) && DP_INTERFACE != 'agent') || !defined('DP_INTERFACE'))
        ) {
            return;
        }

        if ($ticket_part = $this->hasParticipantPerson($person)) {
            return $ticket_part;
        }

        $ticket_part           = new TicketParticipant();
        $ticket_part['person'] = $person;
        $ticket_part['ticket'] = $this;
        $this->participants->add($ticket_part);

        if ($this->_user_participants !== null && !$person['is_agent']) {
            $this->_user_participants[] = $ticket_part;
        }

        $this->_onPropertyChanged('participants', null, $this->participants);

        return $ticket_part;
    }

    /**
     * Remove a participant.
     *
     * @param Person|int $person_or_id
     *
     * @return TicketParticipant|null|void
     */
    public function removeParticipantPerson($person_or_id)
    {
        $person = $person_or_id;
        if ($person && !($person instanceof Person)) {
            $person = App::getEntityRepository('DeskPRO:Person')->find($person);
        }

        if (!$person) {
            return;
        }

        foreach ($this->participants as $k => $p) {
            if ($p['person']->getId() == $person->getId()) {
                $this->participants->remove($k);
                $this->_onPropertyChanged('participants', null, $this->participants);

                return $p;
            }
        }

        return;
    }

    /**
     * @param TicketParticipant $part
     *
     * @return $this
     */
    public function addParticipant(TicketParticipant $part)
    {
        $part->ticket = $this;
        $this->participants->add($part);
        $this->_onPropertyChanged('participants', null, $this->participants);

        return $this;
    }

    /**
     * @param TicketParticipant $part
     *
     * @return $this
     */
    public function removeParticipant(TicketParticipant $part)
    {
        $part->ticket = $this;
        $this->participants->removeElement($part);
        $this->_onPropertyChanged('participants', null, $this->participants);

        return $this;
    }

    /**
     * Set agent participants. Agents are added/removed so that
     * all participants on the ticket are in the array.
     *
     * @param array $set_agent_ids
     */
    public function setParticipantAgentIds(array $set_agent_ids)
    {
        $got_agent_ids = [];
        $remove_ks     = [];
        $participants  = [];

        if ($this->getId()) {
            /*
             * Bug in Doctrine: $this->participants only ever has 1 record,
             * so we're fetching them manually
             */

            $participants = App::getOrm()->createQuery(
                '
                SELECT p
                FROM DeskPRO:TicketParticipant p
                WHERE p.ticket = ?1
            '
            )->setParameter(1, $this)->execute();
        }

        foreach ($participants as $k => $part) {
            if (!$part->person['is_agent']) {
                continue;
            }

            if (!in_array($part->person['id'], $set_agent_ids)) {
                $remove_ks[] = $k;
            } else {
                $got_agent_ids[] = $part->person['id'];
            }
        }

        foreach ($remove_ks as $k) {
            App::getOrm()->remove($participants[$k]);
            $this->_onPropertyChanged('participants', null, $this->participants);
        }

        $new_agent_ids = array_diff($set_agent_ids, $got_agent_ids);

        if ($new_agent_ids) {
            foreach ($new_agent_ids as $agent_id) {
                $part              = new \Application\DeskPRO\Entity\TicketParticipant();
                $part['person_id'] = $agent_id;

                $this->addParticipant($part);
            }
        }
    }

    /**
     * Set user participants.
     *
     * If item in $set_user_ids is an array, its expected to be
     * array(person_id, person_email_id)
     *
     * @param array $set_user_ids
     */
    public function setParticipantUserIds(array $set_user_ids)
    {
        $got_user_ids = [];

        $set_user_ids_info = [];
        foreach ($set_user_ids as $id) {
            if (is_array($id)) {
                $set_user_ids_info[$id[0]] = [$id[0], $id[1]];
            } else {
                $set_user_ids_info[$id] = [$id, null];
            }
        }

        $set_user_ids = array_keys($set_user_ids_info);

        if ($this->id > 0) {
            $participants = App::getOrm()->createQuery(
                '
            SELECT p
            FROM DeskPRO:TicketParticipant p
            WHERE p.ticket = ?1
          '
            )->setParameter(1, $this)->execute();
        } else {
            $participants = [];
        }

        foreach ($participants as $k => $part) {
            if ($part->person['is_agent']) {
                continue;
            }

            if (!isset($set_user_ids_info[$part->person['id']])) {
                //$this->participants->remove($k);
                App::getOrm()->remove($participants[$k]);
            } else {
                $got_user_ids[] = $part->person['id'];

                $info = $set_user_ids_info[$part->person['id']];
                if ($info[1] && $info[1] != $part->person_email['id']) {
                    $part->setPersonEmailId($info[1]);
                }
            }
        }

        $new_user_ids = array_diff($set_user_ids, $got_user_ids);

        if ($new_user_ids) {
            foreach ($new_user_ids as $person_id) {
                $part              = new \Application\DeskPRO\Entity\TicketParticipant();
                $part['person_id'] = $person_id;

                $info = $set_user_ids_info[$part->person['id']];
                if ($info[1]) {
                    $part->setPersonEmailId($info[1]);
                }

                $this->addParticipant($part);
            }
        }
    }

    /**
     * @return TicketCharge[]|ArrayCollection
     */
    public function getCharges()
    {
        return $this->charges;
    }

    public function getChargesTotalAmount()
    {
        $sum = 0.0;
        foreach ($this->charges as $c) {
            $sum += $c->getAmount();
        }

        return $sum;
    }

    public function getChargesTotalTime()
    {
        $secs = 0;
        foreach ($this->charges as $c) {
            $secs += $c->getChargeTime();
        }

        return $secs;
    }

    /**
     * @param Person $agent
     * @param int    $time
     * @param int    $amount
     *
     * @return TicketCharge|null
     */
    public function addCharge(Person $agent, $time, $amount = null)
    {
        if ($time !== null) {
            $time = intval($time);
            if ($time == 0) {
                $time = null;
            }
        }
        if ($amount !== null) {
            $amount = floatval($amount) ?: null;
        }

        if ($time === null && $amount === null) {
            return;
        }

        $charge               = new TicketCharge();
        $charge->charge_time  = $time;
        $charge->amount       = $amount;
        $charge->ticket       = $this;
        $charge->person       = $this->person;
        $charge->organization = $this->organization;
        $charge->agent        = $agent;

        $this->charges->add($charge);

        $this->_onPropertyChanged('charges', null, $this->charges);

        return $charge;
    }

    /**
     * @param Sla $sla
     *
     * @return TicketSla
     */
    public function addSla(Sla $sla)
    {
        foreach ($this->ticket_slas as $ticket_sla) {
            if ($ticket_sla->sla === $sla) {
                return $ticket_sla;
            }
        }

        $ticket_sla         = new TicketSla();
        $ticket_sla->ticket = $this;
        $ticket_sla->sla    = $sla;

        $this->ticket_slas->add($ticket_sla);
        $this->_onPropertyChanged('ticket_slas', null, $this->ticket_slas);

        return $ticket_sla;
    }

    /**
     * @param Sla $sla
     *
     * @return TicketSla|null
     */
    public function removeSla(Sla $sla)
    {
        $found = null;
        foreach ($this->ticket_slas as $k => $ticket_sla) {
            if ($ticket_sla->sla === $sla) {
                $this->ticket_slas->remove($k);

                // already found a dupe, dont double log
                if (!$found) {
                    $this->_onPropertyChanged('ticket_slas', null, $this->ticket_slas);
                }
                $this->updateWorstSlaStatus();
                $found = $ticket_sla;
            }
        }

        return $found;
    }

    /**
     * Remove all SLAs from ticket.
     */
    public function removeAllSlas()
    {
        $this->ticket_slas->clear();
        $this->_onPropertyChanged('ticket_slas', null, $this->ticket_slas);

        $this->setModelField('worst_sla_status', null);
    }

    /**
     * @param Sla $sla
     *
     * @return bool
     */
    public function hasSla(Sla $sla)
    {
        foreach ($this->ticket_slas as $ticket_sla) {
            if ($ticket_sla->sla === $sla) {
                return $ticket_sla;
            }
        }

        return false;
    }

    /**
     * @param $sla_id
     *
     * @return TicketSla|void
     */
    public function getSlaById($sla_id)
    {
        foreach ($this->ticket_slas as $ticket_sla) {
            if ($ticket_sla->sla->id == $sla_id) {
                return $ticket_sla;
            }
        }

        return;
    }

    /**
     * @return array
     */
    public function getSlaIds()
    {
        $ids = [];
        foreach ($this->ticket_slas as $ticket_sla) {
            $ids[] = $ticket_sla->sla->id;
        }

        return $ids;
    }

    /**
     * @return TicketMessage[]|ArrayCollection
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @param bool $includeNote
     *
     * @return TicketMessage
     */
    public function getLastReply($includeNote = false)
    {
        $criteria = new Criteria();
        $criteria->setMaxResults(1);
        $criteria->orderBy(['id' => 'desc']);

        if (!$includeNote) {
            $criteria->andWhere($criteria->expr()->eq('is_agent_note', false));
        }

        return $this->messages->matching($criteria)->first();
    }

    /**
     * @return TicketMessage
     */
    public function getFirstMessage()
    {
        $criteria = new Criteria();
        $criteria->setMaxResults(1);
        $criteria->orderBy(['id' => 'asc']);

        return $this->messages->matching($criteria)->first();
    }

    /**
     * Reset the message collection.
     * todo add onPropertyChanged() if change tracking is needed.
     *
     * @return $this
     */
    public function resetMessages()
    {
        $this->messages->clear();

        return $this;
    }

    /**
     * Add a message to this ticket.
     *
     * @param TicketMessage $message
     */
    public function addMessage(TicketMessage $message)
    {
        $changes = $this->getStateChangeRecorder()->getChangesForField('message');
        if ($changes) {
            foreach ($changes as $c) {
                if ($c->getNew() === $message) {
                    // already added
                    return;
                }
            }
        }

        $this->messages->add($message);
        $message->ticket = $this;

        $now = new \DateTime();
        if ($message->person['is_agent'] && !(defined('DP_INTERFACE') && DP_INTERFACE == 'user')) {
            if (!$message->is_agent_note && !$this->_is_new) {
                if (!$this->date_last_agent_reply || $this->date_last_agent_reply < $now) {
                    $this['date_last_agent_reply'] = $now;
                }

                if (!$this->date_first_agent_reply && !$message->is_agent_note) {
                    $this['date_first_agent_reply'] = $now;
                    $this['total_to_first_reply']   = $this->date_first_agent_reply->getTimestamp() - $this->date_created->getTimestamp();
                }
            }
        } else {
            if (!$this->date_last_user_reply || $this->date_last_user_reply < $now) {
                $this['date_last_user_reply'] = $now;
            }
        }

        $this->_onPropertyChanged('messages', null, $message, true);
        $this->getStateChangeRecorder()->record('message', null, $message);

        if (!$message->is_agent_note) {
            $this->setIsHold(false);
        }
    }

    public function addSmsMessage(TicketSms $message)
    {
        $this->sms_messages->add($message);
        $message->ticket = $this;

        $now = new \DateTime();
        if ($message->person['is_agent'] && !(defined('DP_INTERFACE') && DP_INTERFACE == 'user')) {
            if ((bool) $this->_is_new) {
                if (!$this->date_last_agent_reply || $this->date_last_agent_reply < $now) {
                    $this['date_last_agent_reply'] = $now;
                }

                if (!$this->date_first_agent_reply) {
                    $this['date_first_agent_reply'] = $now;
                    $this['total_to_first_reply']   = $this->date_first_agent_reply->getTimestamp() - $this->date_created->getTimestamp();
                }
            }
        } else {
            if (!$this->date_last_user_reply || $this->date_last_user_reply < $now) {
                $this['date_last_user_reply'] = $now;
            }
        }

        $this->_onPropertyChanged('sms_messages', null, $this->sms_messages, true);
        $this->getStateChangeRecorder()->record('sms_message', null, $message);
    }

    /**
     * @param TicketMessage $message
     *
     * @return $this
     */
    public function removeMessage(TicketMessage $message)
    {
        $this->messages->removeElement($message);
        $this->_onPropertyChanged('messages', null, $this->messages);
        $this->getStateChangeRecorder()->record('message', $message, null);

        return $this;
    }

    /**
     * @return TicketFeedbackLink[]|ArrayCollection
     */
    public function getFeedbackLinks()
    {
        return $this->feedback_links;
    }

    /**
     * Add a TicketFeedbackLink to this ticket.
     *
     * @param TicketFeedbackLink $feedbackLink
     */
    public function addFeedbackLink(TicketFeedbackLink $feedbackLink)
    {
        if ($this->feedback_links->contains($feedbackLink)) {
            return;
        }

        $changes = $this->getStateChangeRecorder()->getChangesForField('feedback_links');
        if ($changes) {
            foreach ($changes as $c) {
                if ($c->getNew() === $feedbackLink) {
                    // already added
                    return;
                }
            }
        }

        $this->feedback_links->add($feedbackLink);
        $feedbackLink->setTicket($this);

        $this->_onPropertyChanged('feedback_links', null, $feedbackLink, true);
        $this->getStateChangeRecorder()->record('feedback_link', null, $feedbackLink);
    }

    /**
     * @param TicketFeedbackLink $feedbackLink
     *
     * @return $this
     */
    public function removeFeedbackLink(TicketFeedbackLink $feedbackLink)
    {
        $this->feedback_links->removeElement($feedbackLink);
        $this->_onPropertyChanged('feedback_links', null, $this->feedback_links);
        $this->getStateChangeRecorder()->record('feedback_link', $feedbackLink, null);

        return $this;
    }

    /**
     * @return TicketAttachment[]|ArrayCollection
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * Add a ticket attachment.
     *
     * @param TicketAttachment $attach
     */
    public function addAttachment(TicketAttachment $attach)
    {
        $attach->setTicket($this);
        $this->attachments->add($attach);

        $this->_onPropertyChanged('attachments', null, $this->attachments);
        $this->getStateChangeRecorder()->record('attachments', null, $attach);
    }

    /**
     * @param TicketAttachment $attach
     */
    public function removeAttachment(TicketAttachment $attach)
    {
        $attach->setTicket(null);

        $this->attachments->removeElement($attach);
        if ($attach->getMessage()) {
            $attach->getMessage()->removeAttachment($attach);
        }

        $this->_onPropertyChanged('attachments', null, $this->attachments);
        $this->getStateChangeRecorder()->record('attachments', $attach, null);

        if ($this->attachments->isEmpty()) {
            $this->setModelField('has_attachments', false);
        }
    }

    /**
     * Find an existing data record for a field id.
     *
     * @param int|CustomDefTicket|string $field_id
     *
     * @return CustomDataTicket
     */
    public function getCustomDataForField($field_id)
    {
        if ($field_id instanceof CustomDefTicket) {
            $field_id = $field_id['id'];
        }

        foreach ($this->custom_data as $data) {
            if ($data['field_id'] == $field_id) {
                return $data;
            }
        }

        if (is_string($field_id) && !is_numeric($field_id)) {
            foreach ($this->custom_data as $data) {
                if ($data->getField()->hasAlias($field_id)) {
                    return $data;
                }
            }
        }

        return;
    }

    /**
     * @return CustomDataTicket[]
     */
    public function getCustomData()
    {
        return $this->custom_data;
    }

    /**
     * @param int        $field_id
     * @param int|string $value
     *
     * @return bool
     */
    public function isCustomFieldEqualTo($field_id, $value)
    {
        foreach ($this->custom_data as $custom_data) {
            if ($custom_data->getFieldId() === $field_id) {
                return $custom_data->getData() === $value;
            }
        }

        return is_null($value);
    }

    /**
     * Gets a display array for a specific field.
     *
     * @param $field_id
     *
     * @return array|mixed|null
     */
    public function getCustomFieldDisplayArray($field_id)
    {
        $data = $this->getCustomDataForField($field_id);
        if (!$data) {
            return;
        }

        $ticket_field_defs      = App::getApi('custom_fields.tickets')->getEnabledFields();
        $ticket_data_structured = App::getApi('custom_fields.util')->createDataHierarchy(
            [$data],
            $ticket_field_defs
        );

        $custom_fields = App::getApi('custom_fields.tickets')->getFieldsDisplayArray(
            $ticket_field_defs,
            $ticket_data_structured
        );

        $custom_fields = array_pop($custom_fields);

        return $custom_fields;
    }

    /**
     * @param Collection $custom_data
     */
    public function setCustomData(Collection $custom_data)
    {
        $this->getStateChangeRecorder()->touchField('custom_data');

        $this->custom_data = $custom_data;
        foreach ($custom_data as $cd) {
            $cd->ticket = $this;
        }

        $this->_onPropertyChanged('custom_data', null, $this->custom_data);
    }

    /**
     * Set custom field data for a particular field.
     *
     * @param int   $field_id
     * @param mixed $value_type
     * @param mixed $value
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function setCustomDataField($field_id, $value_type, $value)
    {
        $this->getStateChangeRecorder()->touchField('custom_data');

        $custom_data = $this->getCustomDataForField($field_id);
        $orig_data   = $custom_data;
        $is_new      = false;

        if (!$custom_data) {
            if ($value === null) {
                return;
            }

            $is_new = true;

            $field = App::getEntityRepository('DeskPRO:CustomDefTicket')->find($field_id);
            if (!$field) {
                throw new \Exception("Invalid field_id `$field_id`");
            }
            $custom_data          = new CustomDataTicket();
            $custom_data['field'] = $field;
        }

        $field = $custom_data->field;
        if ($field->parent) {
            foreach ($this->custom_data as $d) {
                if ($d->field && $d->field->parent && $d->field->parent['id'] == $field->parent['id']) {
                    $this->custom_data->removeElement($d);
                }
            }
        }

        $this->custom_data->removeElement($custom_data);

        if ($value === null) {
            $this->custom_data->removeElement($custom_data);

            return;
        }

        if ($field->getTypeName() == 'choice') {
        }

        $custom_data[$value_type] = $value;

        if ($is_new) {
            $this->addCustomData($custom_data);
        } else {
            $this->_onPropertyChanged('custom_data', null, $this->custom_data);
        }

        return $custom_data;
    }

    /**
     * @param $field
     */
    public function removeCustomDataForField(CustomDefTicket $field)
    {
        $this->getStateChangeRecorder()->touchField('custom_data');

        $changed = false;
        foreach ($this->custom_data as $data) {
            if ($data->field->getId() === $field->getId() || $data->root_field->getId() == $field->getId()) {
                $changed = true;
                $this->custom_data->removeElement($data);
            } elseif ($field->parent && ($data->field->getId() === $field->parent->getId()
                    || $data->root_field->getId() === $field->parent->getId())
            ) {
                $changed = true;
                $this->custom_data->removeElement($data);
            }
        }

        if ($changed) {
            $this->_onPropertyChanged('custom_data', null, $this->custom_data);
        }
    }

    /**
     * Reset custom data
     * todo add onPropertyChanged() if change tracking is needed.
     *
     * @return $this
     */
    public function resetCustomData()
    {
        $this->getStateChangeRecorder()->touchField('custom_data');
        $this->custom_data->clear();

        return $this;
    }

    /**
     * Add a custom data item to this ticket.
     *
     * @param CustomDataTicket $data
     */
    public function addCustomData(CustomDataTicket $data)
    {
        $this->getStateChangeRecorder()->touchField('custom_data');

        if ($this->custom_data === null) {
            $this->custom_data = new ArrayCollection();
        }

        $this->custom_data->add($data);
        $data['ticket'] = $this;

        $this->_onPropertyChanged('custom_data', null, $this->custom_data);
    }

    /**
     * Check if this ticket has a custom field.
     *
     * @param $field_id
     *
     * @return bool
     */
    public function hasCustomField($field_id)
    {
        foreach ($this->custom_data as $data) {
            if ($data->field['id'] == $field_id) {
                return true;
            }
        }

        // TODO: this is only 1 level deep for the hierarchy. fine for now, but may need to change.
        foreach ($this->custom_data as $data) {
            if ($data->field->parent and $data->field->parent['id'] == $field_id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Render a custom field.
     *
     * !depreciated
     */
    public function renderCustomField($field_id, $context = 'html')
    {
        $f_def = App::getEntityRepository('DeskPRO:CustomDefTicket')->find($field_id);
        if (!$f_def) {
            return '';
        }

        $data_structured = App::getApi('custom_fields.util')->createDataHierarchy($this->custom_data, [$f_def]);

        $value    = !empty($data_structured[$f_def['id']]) ? $data_structured[$f_def['id']] : null;
        $rendered = $value ? $f_def->getHandler()->renderContext($context, $value) : null;

        return $rendered;
    }

    /**
     * {@inheritdoc}
     */
    public function addLabel(Label $label)
    {
        $label->ticket = $this;
        $this->labels->add($label);
        $this->_onPropertyChanged('labels', null, $this->labels);

        return $label;
    }

    /**
     * {@inheritdoc}
     */
    public function removeLabel(Label $label)
    {
        if ($this->labels->contains($label)) {
            $this->labels->removeElement($label);
            $this->_onPropertyChanged('labels', null, $this->labels);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * {@inheritdoc}
     */
    public function clearLabels()
    {
        $this->labels->clear();
        $this->_onPropertyChanged('labels', null, $this->labels);

        return $this;
    }

    /**
     * @param string $l
     *
     * @return LabelTicket
     */
    public function addLabelByString($l)
    {
        if ($ret = $this->findLabelByString($l)) {
            return $ret;
        }

        $label         = new LabelTicket();
        $label->label  = $l;
        $label->ticket = $this;
        $this->labels->add($label);
        $this->_onPropertyChanged('labels', null, $this->labels);

        return $label;
    }

    /**
     * @param string $l
     *
     * @return LabelTicket|null
     */
    public function removeLabelByString($l)
    {
        $x        = new LabelTicket();
        $x->label = $l;

        foreach ($this->labels as $idx => $label) {
            if (Strings::utf8_strtolower($label->label) == Strings::utf8_strtolower($x->label)) {
                $this->labels->remove($idx);
                $this->_onPropertyChanged('labels', null, $this->labels);

                return $label;
            }
        }

        return;
    }

    /**
     * @param string $l
     *
     * @return LabelTicket|null
     */
    public function findLabelByString($l)
    {
        $x        = new LabelTicket();
        $x->label = $l;

        $l_lower = Strings::utf8_strtolower($l);

        foreach ($this->labels as $l) {
            if (Strings::utf8_strtolower($l->label) === $l_lower) {
                return $l;
            }
        }

        return;
    }

    public function getPersonId()
    {
        return $this->person['id'];
    }

    public function setPersonId($id)
    {
        $person         = App::getOrm()->getRepository('DeskPRO:Person')->find($id);
        $this['person'] = $person;
    }

    /**
     * @param Person $person
     *
     * @return $this
     */
    public function setPerson(Person $person = null)
    {
        $this->setModelField('person', $person);
        if ($person) {
            if ($person->getRealLanguage()) {
                $this['language'] = $person->getRealLanguage();
            }
            if ($organization = $person->getOrganization()) {
                $this->setOrganization($organization);
            }
            if ($this->person_email && $this->person_email->person->getId() != $person->getId()) {
                $this['person_email'] = null;
            }
        }

        return $this;
    }

    /**
     * Set ticket organization.
     *
     * @param Organization $organization
     *
     * @return $this
     */
    public function setOrganization(Organization $organization = null)
    {
        $this->setModelField('organization', $organization);

        return $this;
    }

    /**
     * @deprecated
     *
     * @return PersonEmail
     */
    public function getPersonEmail()
    {
        if ($this->person_email) {
            return $this->person_email;
        } elseif ($this->person) {
            return $this->person->getPrimaryEmail();
        }
    }

    /**
     * Gets the email address that should be used for this ticket.
     *
     * @return PersonEmail
     */
    public function getTicketPersonEmail()
    {
        if ($this->person_email) {
            return $this->person_email;
        } else {
            return $this->person['primary_email'];
        }
    }

    /**
     * @param PersonEmail $person_email
     *
     * Set the email address that should be used for this ticket. Setting to null
     * means use the person's primary email (see self::getTicketPersonEmail).
     *
     * Note that this is a necessary method for the form component propery accessor
     */
    public function setTicketPersonEmail(PersonEmail $person_email = null)
    {
        $this->setModelField('person_email', $person_email);
    }

    public function getPersonEmailAddress()
    {
        $email = $this->getTicketPersonEmail();

        return $email['email'];
    }

    /**
     * @return Brand
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * @return int
     */
    public function getBrandId()
    {
        if (!$this->brand) {
            return 0;
        }

        return $this->brand->getId();
    }

    /**
     * @param Brand $brand
     *
     * @return $this
     */
    public function setBrand($brand)
    {
        $this->setModelField('brand', $brand);

        return $this;
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setBrandId($id)
    {
        if ($id) {
            /** @var Brand $brand */
            $brand = App::getOrm()->getRepository(Brand::class)->find($id);
            $this->setBrand($brand);
        } else {
            $this->setBrand(null);
        }

        return $this;
    }

    /**
     * @return int|mixed
     */
    public function getDepartmentId()
    {
        if (!$this->department) {
            return 0;
        }

        return $this->department->getId();
    }

    /**
     * @return Department
     */
    public function getDepartment()
    {
        return $this->department;
    }

    /**
     * Failproof method to avoid errors if ticket has no department.
     *
     * @return Department
     */
    public function getDepartmentOrDefault()
    {
        if (!$this->department) {
            return App::getDataService('Department')->getDefaultTicketDepartment();
        }

        return $this->department;
    }

    /**
     * @return EmailAccount
     */
    public function getEmailAccount()
    {
        return $this->email_account;
    }

    /**
     * @return string
     */
    public function getEmailAccountAddress()
    {
        return $this->email_account_address;
    }

    public function getEmailAccountId()
    {
        if (!$this->email_account) {
            return 0;
        }

        return $this->email_account->getId();
    }

    /**
     * Set the ticket department.
     *
     * @param Department|null $department
     *
     * @return $this
     */
    public function setDepartment(Department $department = null)
    {
        $this->setModelField('department', $department);

        return $this;
    }

    public function setDepartmentId($id)
    {
        if ($id) {
            $dep                = App::getOrm()->getRepository('DeskPRO:Department')->find($id);
            $this['department'] = $dep;
        } else {
            $this['department'] = null;
        }

        return $this;
    }

    public function isLangSet()
    {
        return $this->language ? true : false;
    }

    public function getRealLanguage()
    {
        return $this->language;
    }

    public function getLanguage()
    {
        if ($this->language) {
            return $this->language;
        } elseif ($this->person && $this->person->getRealLanguage()) {
            return $this->person->getRealLanguage();
        }

        return;
    }

    public function getLanguageId()
    {
        $l = $this->getLanguage();

        return $l ? $l->getId() : 0;
    }

    /**
     * Set language.
     *
     * @param Language|null $language
     *
     * @return $this
     */
    public function setLanguage(Language $language = null)
    {
        $this->setModelField('language', $language);

        return $this;
    }

    /**
     * Set language by id.
     *
     * @param int $id
     *
     * @return $this
     */
    public function setLanguageId($id)
    {
        if ($id) {
            $lang             = App::getOrm()->getRepository('DeskPRO:Language')->find($id);
            $this['language'] = $lang;
        } else {
            $this['language'] = null;
        }

        return $this;
    }

    public function getCategoryId()
    {
        if (!$this->category) {
            return 0;
        }

        return $this->category['id'];
    }

    /**
     * Set ticket category.
     *
     * @param TicketCategory $category
     *
     * @return $this
     */
    public function setCategory(TicketCategory $category = null)
    {
        $this->setModelField('category', $category);

        return $this;
    }

    public function setCategoryId($id)
    {
        if ($id) {
            $cat              = App::getOrm()->getRepository('DeskPRO:TicketCategory')->find($id);
            $this['category'] = $cat;
        } else {
            $this['category'] = null;
        }

        return $this;
    }

    public function getProductId()
    {
        if (!$this->product) {
            return 0;
        }

        return $this->product['id'];
    }

    public function setProductId($id)
    {
        if ($id) {
            $prod            = App::getOrm()->getRepository('DeskPRO:Product')->find($id);
            $this['product'] = $prod;
        } else {
            $this['product'] = null;
        }
    }

    public function setProduct(Product $product = null)
    {
        $this->setModelField('product', $product);

        return $this;
    }

    public function getProblemIds()
    {
        $ids = [];
        foreach ($this->problems as $problem) {
            $ids[] = $problem->getId();
        }

        return $ids;
    }

    public function getPriorityId()
    {
        if (!$this->priority) {
            return 0;
        }

        return $this->priority['id'];
    }

    /**
     * Set ticket priority.
     *
     * @param TicketPriority $priority
     *
     * @return $this
     */
    public function setPriority(TicketPriority $priority = null)
    {
        $this->setModelField('priority', $priority);

        return $this;
    }

    public function setPriorityId($id)
    {
        if ($id) {
            $pri              = App::getOrm()->getRepository('DeskPRO:TicketPriority')->find($id);
            $this['priority'] = $pri;
        } else {
            $this['priority'] = null;
        }
    }

    public function getWorkflowId()
    {
        if (!$this->workflow) {
            return 0;
        }

        return $this->workflow['id'];
    }

    public function setWorkflowId($id)
    {
        if ($id) {
            $work             = App::getOrm()->getRepository('DeskPRO:TicketWorkflow')->find($id);
            $this['workflow'] = $work;
        } else {
            $this['workflow'] = null;
        }
    }

    public function setWorkflow(TicketWorkflow $workflow = null)
    {
        $this->setModelField('workflow', $workflow);

        return $this;
    }

    /**
     * @return Person|null
     */
    public function getAgent()
    {
        return $this->agent;
    }

    /**
     * @return int|mixed
     */
    public function getAgentId()
    {
        if (!$this->agent) {
            return 0;
        }

        return $this->getAgent()->getId();
    }

    /**
     * Set ticket agent.
     *
     * @param Person|null $agent
     *
     * @return $this
     */
    public function setAgent(Person $agent = null)
    {
        if ($agent) {
            // Do we need to update the first assign date?
            if (is_null($this->date_first_agent_assign)) {
                $this['date_first_agent_assign'] = new \DateTime();
            }
        }

        $this->setModelField('agent', $agent);

        return $this;
    }

    /**
     * Set ticket agent by id.
     *
     * @param int $id
     *
     * @return $this
     */
    public function setAgentId($id)
    {
        $agent = $id ? App::getOrm()->getRepository('DeskPRO:Person')->find($id) : null;

        return $this->setAgent($agent);
    }

    public function getAgentTeamId()
    {
        if (!$this->agent_team) {
            return 0;
        }

        return $this->agent_team['id'];
    }

    /**
     * @param AgentTeam|null $team
     *
     * @return $this
     */
    public function setAgentTeam(AgentTeam $team = null)
    {
        $this->setModelField('agent_team', $team);

        return $this;
    }

    /**
     * @return AgentTeam
     */
    public function getAgentTeam()
    {
        return $this->agent_team;
    }

    public function setAgentTeamId($id)
    {
        if ($id) {
            $agent_team         = App::getOrm()->getRepository('DeskPRO:AgentTeam')->find($id);
            $this['agent_team'] = $agent_team;
        } else {
            $this['agent_team'] = null;
        }
    }

    public function getIsAssigned()
    {
        if ($this->agent or $this->agent_team) {
            return true;
        }

        return false;
    }

    public function getAssignedName()
    {
        if ($this->agent) {
            return $this->agent['display_name'];
        } elseif ($this->agent_team) {
            return $this->agent_team['name'];
        } else {
            return;
        }
    }

    public function setLockedByAgentId($agent_id)
    {
        if ($agent_id) {
            $agent = App::getOrm()->getRepository('DeskPRO:Person')->find($agent_id);
            $this->setLockedByAgent($agent);
        } else {
            $this->setLockedByAgent(null);
        }
    }

    public function setLockedByAgent(Person $agent = null)
    {
        $this->setModelField('locked_by_agent', $agent);
        if ($agent) {
            $this->setModelField('date_locked', new \DateTime());
        } else {
            $this->setModelField('date_locked', null);
        }
    }

    public function unlockTicket()
    {
        $this->setLockedByAgentId(null);
    }

    public function getIsLocked()
    {
        return $this->isLocked();
    }

    public function hasLock()
    {
        return $this->locked_by_agent ? true : false;
    }

    public function isLocked(Person $current_agent = null)
    {
        if (!$this->locked_by_agent) {
            return false;
        }

        if ($current_agent === null) {
            $current_agent = App::getCurrentPerson();
        }
        if ($current_agent && $this->locked_by_agent['id'] == $current_agent['id']) {
            return false;
        }

        return true;
    }

    public function getIsArchived()
    {
        return $this->isArchived();
    }

    /**
     * Gets messages we should be showing to the user. In other words,
     * messages that are not private agent notes.
     *
     * @return array
     */
    public function getDisplayableMessages()
    {
        $ret = [];

        foreach ($this->messages as $msg) {
            if (!$msg['is_agent_note']) {
                $ret[] = $msg;
            }
        }

        return $ret;
    }

    /**
     * Set a flag color for this ticket for a particular perosn.
     * $color of null or 'none' removes the flag.
     *
     * @param Person $person
     * @param string $color
     */
    public function setFlagForPerson($person, $color = null)
    {
        if ($color == 'none') {
            $color = null;
        }

        if ($color) {
            App::getDb()->replace(
                'tickets_flagged',
                [
                    'person_id' => $person['id'],
                    'ticket_id' => $this->id,
                    'color'     => $color,
                ]
            );
        } else {
            App::getDb()->delete(
                'tickets_flagged',
                [
                    'person_id' => $person['id'],
                    'ticket_id' => $this->id,
                ]
            );
        }
    }

    /**
     * Get the deletion record if there is one.
     *
     * @return \Application\DeskPRO\Entity\TicketDeleted
     */
    public function getDeletionRecord()
    {
        $del = App::getOrm()->createQuery(
            '
            SELECT d
            FROM DeskPRO:TicketDeleted d
            WHERE d.ticket_id = ?1
        '
        )->setParameter(1, $this->id)->getOneOrNullResult();

        return $del;
    }

    public function isHidden()
    {
        return $this->getIsHidden();
    }

    public function isResolved()
    {
        return $this->status == TicketStatus::STATUS_TYPE_RESOLVED;
    }

    public function getIsHidden()
    {
        if ($this->status == TicketStatus::STATUS_TYPE_HIDDEN) {
            return true;
        }

        return false;
    }

    public function isDeleted()
    {
        return $this->getIsDeleted();
    }

    /**
     * Is the ticket archived? Archived tickets are closed to replies.
     *
     * @return bool
     */
    public function isArchived()
    {
        if ($this->status != 'archived') {
            return false;
        }

        return true;
    }

    /**
     * Is this ticket deleted?
     *
     * @return bool
     */
    public function getIsDeleted()
    {
        if ($this->getTicketStatus() && $this->getTicketStatus()->isDeleted()) {
            return true;
        }

        return false;
    }

    public function getRealTotalUserWaiting()
    {
        $secs = $this->total_user_waiting;

        if ($this->date_user_waiting && $this->status == 'awaiting_agent') {
            $secs += time() - $this->date_user_waiting->getTimestamp();
        }

        return $secs;
    }

    public function getTotalUserWaitingWorkTime()
    {
        $work_hours_set = $this->getWorkHoursSet();

        $time = 0;
        if ($this->waiting_times) {
            foreach ($this->waiting_times as $waiting) {
                if ($waiting['type'] == 'user') {
                    $time += $work_hours_set->getWorkTimeBetween($waiting['start'], $waiting['end']);
                }
            }
        }

        if ($this->date_user_waiting && $this->status == 'awaiting_agent') {
            $time += $work_hours_set->getWorkTimeBetween($this->date_user_waiting);
        }

        return $time;
    }

    public function getCurrentUserWaitingTime()
    {
        if ($this->date_user_waiting && $this->status == 'awaiting_agent') {
            return time() - $this->date_user_waiting->getTimestamp();
        }

        return;
    }

    public function getCurrentUserWaitingWorkTime()
    {
        if ($this->date_user_waiting && $this->status == 'awaiting_agent') {
            return $this->getWorkHoursSet()->getWorkTimeBetween($this->date_user_waiting);
        }

        return;
    }

    public function getWorkTimeToFirstReply()
    {
        if ($this->date_first_agent_reply) {
            return $this->getWorkHoursSet()->getWorkTimeBetween($this->date_created, $this->date_first_agent_reply);
        }

        return;
    }

    /**
     * Get how long, in seconds, the ticket was open for. This only applies
     * for tikcets that are resolved (or archived).
     *
     * @return int
     */
    public function getTimeUntilResolution()
    {
        if (!$this->date_resolved && !$this->date_archived) {
            return;
        }

        $date = $this->date_resolved;
        if (!$date || ($this->date_archived && $date > $this->date_archived)) {
            $date = $this->date_archived;
        }

        $secs = $date->getTimestamp() - $this->date_created->getTimestamp();

        return $secs;
    }

    public function getWorkTimeUntilResolution()
    {
        if (!$this->date_resolved && !$this->date_archived) {
            return;
        }

        $date = $this->date_resolved;
        if (!$date || ($this->date_archived && $date > $this->date_archived)) {
            $date = $this->date_archived;
        }

        return $this->getWorkHoursSet()->getWorkTimeBetween($this->date_created, $date);
    }

    /**
     * @return \DateTime
     */
    public function getLastActivityDate()
    {
        $dates = [];
        if ($this->date_last_agent_reply) {
            $dates[] = $this->date_last_agent_reply;
        }
        if ($this->date_last_user_reply) {
            $dates[] = $this->date_last_user_reply;
        }

        if (!$dates) {
            return $this->date_created;
        }

        $use_date = $this->date_created;
        foreach ($dates as $d) {
            if ($d > $use_date) {
                $use_date = $d;
            }
        }

        return $use_date;
    }

    /**
     * @deprecated Use TicketMessage repository getLastAgentReply() instead
     *
     * @return TicketMessage|null
     */
    public function getLastAgentMessage()
    {
        return App::getEntityRepository(TicketMessage::class)->getLastAgentReply($this);
    }

    /**
     * @deprecated use setTicketStatus instead
     *
     * @param string $status
     *
     * @return $this
     */
    public function setStatus($status)
    {
        // fallback to support these 2 statuses for cases which has not been updated
        if (in_array($status, ['hidden.deleted', 'hidden.spam'])) {
            return $this->setTicketStatus(App::getContainer()->getTicketStatuses()->findStatusOrException($status));
        }

        $this['date_status'] = new \DateTime();

        $old_status      = $this->status;
        $old_status_code = $this->getStatusCode();

        // This method deprecated
        // unset ticket_status if this method called
        $this->setModelField('ticket_status', null);

        $status_code = $status;
        $hstatus     = null;
        if (strpos($status, '.')) {
            list($status, $hstatus) = explode('.', $status, 2);
        }

        // Note: No early return here
        // and no logic here about checking old status against new status
        // because default status is awaiting_agent and we still need to run
        // through all of this date_X sets on newticket. If we returned early
        // that wouldn't run because awaiting_agent==awaiting_agent

        $this['date_status'] = new \DateTime();

        if (!$status) {
            $status = 'awaiting_agent';
        }
        if (!in_array($status, ['awaiting_agent', 'pending']) && in_array($old_status, ['awaiting_agent', 'pending']) && $this->date_user_waiting) {
            $this->setModelField(
                'total_user_waiting',
                $this->total_user_waiting + time() - $this->date_user_waiting->getTimestamp()
            );
            $this->addWaitingTimeRecord('user', $this->date_user_waiting);
        }
        if (in_array($status, ['awaiting_agent', 'pending']) && !$this->date_user_waiting) {
            $this->setModelField('date_user_waiting', new \DateTime());
        }
        if (!in_array($status, ['awaiting_agent', 'pending']) && $this->date_user_waiting) {
            $this->setModelField('date_user_waiting', null);
        }

        if ($status != 'awaiting_user' && $old_status == 'awaiting_user' && $this->date_agent_waiting) {
            $this->addWaitingTimeRecord('agent', $this->date_agent_waiting);
        }
        if ($status == 'awaiting_user' && !$this->date_agent_waiting) {
            $this->setModelField('date_agent_waiting', new \DateTime());
        }
        if ($status != 'awaiting_user' && $this->date_agent_waiting) {
            $this->setModelField('date_agent_waiting', null);
        }

        if ($status == 'archived' && !$this->date_archived) {
            $this['date_archived'] = new \DateTime();
        }
        if ($status != 'archived' && $this->date_archived) {
            $this->setModelField('date_archived', null);
        }

        // date_resolved matters with either resolved or archived
        // because to reach archived, you must "go through" resolved first
        if (($status == 'resolved' || $status == 'archived') && !$this->date_resolved) {
            $this['date_resolved'] = new \DateTime();
        }
        if ($status != 'resolved' && $status != 'archived' && $this->date_resolved) {
            $this->setModelField('date_resolved', null);
        }

        if (!$status || !TicketStatus::isValidStatusType($status)) {
            throw new \InvalidArgumentException("Invalid status `$status`");
        }

        if ($hstatus && !in_array(
                $hstatus,
                [
                    TicketStatus::SYS_ID_DELETED,
                    TicketStatus::SYS_ID_SPAM,
                ]
            )
        ) {
            throw new \InvalidArgumentException("Invalid hidden status `$hstatus`");
        }

        if ($hstatus && $status != 'hidden') {
            throw new \InvalidArgumentException(
                "Invalid status must be hidden to set a hidden status, got `$status` instead."
            );
        }

        $this->setModelField('status', $status);

        if ($hstatus) {
            $ticketStatus = $hstatus == TicketStatus::SYS_ID_DELETED
                ? App::getContainer()->getTicketStatuses()->getDeletedStatus()
                : App::getContainer()->getTicketStatuses()->getSpamStatus();
            $this->setTicketStatus($ticketStatus);
        }

        $this->getStateChangeRecorder()->record('status_code', $old_status_code, $this->getStatusCode());

        if ($old_status == TicketStatus::STATUS_TYPE_HIDDEN) {
            $deletedTicketStatus = App::getContainer()->getTicketStatuses()->getDeletedStatus();
            if ($old_status_code == $deletedTicketStatus->getStatusCode()
                && $status_code != $deletedTicketStatus->getStatusCode()
            ) {
                $this->undeleteTicket();
            }
        }

        return $this;
    }

    /**
     * @return TicketStatus
     */
    public function getTicketStatus()
    {
        return $this->ticket_status ?: new VirtualTicketStatus($this->status);
    }

    /**
     * @param TicketStatus $ticket_status
     *
     * @return $this
     */
    public function setTicketStatus(TicketStatus $ticket_status = null)
    {
        if (!$ticket_status) {
            $this->setModelField('ticket_status', $ticket_status);

            return $this;
        }

        // we need to call this first
        $this->setStatus($ticket_status->getStatusType());

        if ($ticket_status instanceof VirtualTicketStatus) {
            $this->setModelField('ticket_status', null);
        } else {
            $this->setModelField('ticket_status', $ticket_status);
        }

        return $this;
    }

    /**
     * Set date created.
     *
     * @param \DateTime $dateCreated
     *
     * @return $this
     */
    public function setDateCreated(\DateTime $dateCreated = null)
    {
        if (!$dateCreated) {
            $dateCreated = new \DateTime();
        }

        $this->setModelField('date_created', $dateCreated);

        return $this;
    }

    /**
     * Set date resolved.
     *
     * @param \DateTime $dateResolved
     *
     * @return $this
     */
    public function setDateResolved(\DateTime $dateResolved = null)
    {
        if (!$dateResolved) {
            $dateResolved = new \DateTime();
        }

        $this->setModelField('date_resolved', $dateResolved);

        return $this;
    }

    /**
     * Set date when status was changed.
     *
     * @param \DateTime $dateStatus
     *
     * @return $this
     */
    public function setDateStatus(\DateTime $dateStatus = null)
    {
        if (!$dateStatus) {
            $dateStatus = new \DateTime();
        }

        $this->setModelField('date_status', $dateStatus);

        return $this;
    }

    /**
     * Set date archived.
     *
     * @param \DateTime $date_archived
     *
     * @return $this
     */
    public function setDateArchived(\DateTime $date_archived = null)
    {
        $this->setModelField('date_archived', $date_archived);

        return $this;
    }

    /**
     * Set hidden status.
     *
     * @param string $hidden_status
     *
     * @return $this
     */
    public function setHiddenStatus($hidden_status)
    {
        $status = null;
        if (!$hidden_status) {
            if ($this->status == 'hidden') {
                $status = 'awaiting_agent';
            }
        } else {
            $status = 'hidden.'.$hidden_status;
        }

        if ($status) {
            $this->setTicketStatus(App::getContainer()->getTicketStatuses()->findStatusOrException($status));
        }

        return $this;
    }

    /**
     * Returns status code.
     *
     * @return string
     */
    public function getStatusCode()
    {
        return $this->getTicketStatus()->getStatusCode();
    }

    /**
     * Returns status code.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getHiddenStatus()
    {
        $ticketStatus = $this->getTicketStatus();

        return $ticketStatus->isDeleted() || $ticketStatus->isSpam() ? $ticketStatus->getSysId() : null;
    }

    public function isAwaitingUser()
    {
        return $this->status === TicketStatus::STATUS_TYPE_AWAITING_USER;
    }

    public function isAwaitingAgent()
    {
        return $this->status === TicketStatus::STATUS_TYPE_AWAITING_AGENT;
    }

    public function isOpen()
    {
        return $this->status === TicketStatus::STATUS_TYPE_AWAITING_USER || $this->status === TicketStatus::STATUS_TYPE_AWAITING_AGENT;
    }

    /**
     * @return bool
     */
    public function isHold()
    {
        return $this->status === TicketStatus::STATUS_TYPE_PENDING;
    }

    /**
     * Mark as hold.
     *
     * @param bool $is_hold
     *
     * @return $this
     */
    public function setIsHold($is_hold)
    {
        if ($is_hold) {
            $this->setStatus(TicketStatus::STATUS_TYPE_PENDING);
            $this->setModelField('date_on_hold', new \DateTime());
        } else {
            if ($this->status === TicketStatus::STATUS_TYPE_PENDING) {
                $this->setStatus(TicketStatus::STATUS_TYPE_AWAITING_AGENT);
            }
        }

        return $this;
    }

    /**
     * Undelete a ticket.
     *
     * This will set the status to 'awaiting_agent' if it wasn't changed before.
     */
    public function undeleteTicket()
    {
        if (!$this->id) {
            return;
        }

        $del = $this->getDeletionRecord();
        if (!$del) {
            return;
        }

        if ($this->status == 'hidden') {
            $this->setModelField('status', TicketStatus::STATUS_TYPE_AWAITING_AGENT);
        }

        App::getOrm()->remove($del);
        App::getOrm()->persist($this);
    }

    /**
     * Soft-delete a ticket.
     *
     * @param null   $person
     * @param string $reason
     * @param bool   $persist_self
     */
    public function deleteTicket($person = null, $reason = '', $persist_self = true)
    {
        if ($persist_self) {
            $this->setTicketStatus(App::getContainer()->getTicketStatuses()->getDeletedStatus());
            $tm      = App::$container->getTicketManager();
            $context = $tm->createAgentExecutorContext($person, $reason, '');
            $tm->saveTicket($this, $context);
        }
        $del = $this->getDeletionRecord();
        if (!$del) {
            $del = new TicketDeleted();
        }

        $del['ticket_id']     = $this->id;
        $del['old_ptac']      = $this->auth;
        $del['by_person']     = $person;
        $del['new_ticket_id'] = 0;
        $del['reason']        = $reason;

        App::getOrm()->persist($del);
        App::getOrm()->flush();
    }

    public function updateWorstSlaStatus()
    {
        $status = $this->getWorstSlaStatus();
        $this->setModelField('worst_sla_status', $status);

        return $status;
    }

    /**
     * @return TicketCategory
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @return TicketWorkflow
     */
    public function getWorkflow()
    {
        return $this->workflow;
    }

    /**
     * @return TicketPriority
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @return string
     */
    public function getAuth()
    {
        return $this->auth;
    }

    public function getWorstSlaStatus()
    {
        return self::calctWorstSlaStatus($this->ticket_slas);
    }

    /**
     * @param $ticketSlas
     *
     * @return string
     */
    public static function calctWorstSlaStatus($ticketSlas)
    {
        if (!count($ticketSlas)) {
            return;
        }

        $status = null;
        foreach ($ticketSlas as $ticket_sla) {
            if ($ticket_sla->is_completed) {
                continue;
            }

            if (!$status) {
                $status = $ticket_sla->sla_status;
            } elseif ($ticket_sla->sla_status == 'fail') {
                $status = 'fail';
            } elseif ($ticket_sla->sla_status == 'warning' && $status !== 'fail') {
                $status = 'warning';
            }
        }

        return $status;
    }

    public function addWaitingTimeRecord($type, $start_ts, $end_ts = null)
    {
        $start_ts = ($start_ts instanceof \DateTime ? $start_ts->getTimestamp() : intval($start_ts));
        $end_ts   = ($end_ts instanceof \DateTime ? $end_ts->getTimestamp() : intval($end_ts));

        if (!$end_ts) {
            $end_ts = time();
        }

        if ($end_ts <= $start_ts) {
            return;
        }

        if (!is_array($this->waiting_times)) {
            $this->waiting_times = [];
        }

        $old                   = $this->waiting_times;
        $this->waiting_times[] = [
            'type'   => $type,
            'start'  => $start_ts,
            'end'    => $end_ts,
            'length' => ($end_ts - $start_ts),
        ];
        $this->_onPropertyChanged('waiting_times', $old, $this->waiting_times);
    }

    public function getWaitingTimes()
    {
        if (!is_array($this->waiting_times)) {
            return [];
        } else {
            return $this->waiting_times;
        }
    }

    /**
     * Add an access code for a person.
     *
     * @param Person $person
     *
     * @return TicketAccessCode
     */
    public function addAccessCodeForPerson(Person $person)
    {
        if ($tac = $this->findAccessCodeForPerson($person)) {
            return $tac;
        }

        $tac = new TicketAccessCode();
        $tac->setTicket($this);
        $tac->setPerson($person);
        $this->access_codes->add($tac);

        return $tac;
    }

    /**
     * Find the access code for a person if it exists.
     *
     * @return TicketAccessCode
     */
    public function findAccessCodeForPerson(Person $person)
    {
        foreach ($this->access_codes as $tac) {
            if ($tac->person === $person) {
                return $tac;
            }
        }

        return;
    }

    /**
     * Find an access code.
     *
     * @return TicketAccessCode
     */
    public function findAccessCode($auth)
    {
        foreach ($this->access_codes as $tac) {
            if ($tac['auth'] == $auth) {
                return $tac;
            }
        }

        return;
    }

    /**
     * Goes through everyone associated with this ticket (user owner, agent, participants)
     * and fetches their preferred email address.
     *
     * Returns null if the person isn't on the ticket or if they don't have any email
     * addresses.
     *
     * @param Person $person
     *
     * @return PersonEmail|null
     */
    public function findEmailForPerson(Person $person)
    {
        if ($this->person === $person) {
            if ($this->person_email) {
                return $this->person_email;
            } else {
                return $this->person->primary_email;
            }
        } elseif ($this->agent === $person) {
            return $this->agent->primary_email;
        } else {
            foreach ($this->participants as $part) {
                if ($part->person === $person) {
                    if ($part->person_email) {
                        return $part->person_email;
                    } else {
                        return $part->person->primary_email;
                    }
                }
            }
        }

        return;
    }

    /**
     * Gets the access code which is an encoded ticket ID and authcode into one string.
     *
     * @return string
     */
    public function getAccessCode()
    {
        // this is for B.C. in emails for new-portal. see the property docblock for $_force_access_code.
        if ($this->_force_access_code) {
            return $this->_force_access_code;
        }

        $str = Util::baseEncode($this->id, 'letters');
        $str .= $this->auth;

        return $str;
    }

    /**
     * @return TicketAccessCode[]|ArrayCollection
     */
    public function getAccessCodes()
    {
        return $this->access_codes;
    }

    public function forceSetAccessCode($code)
    {
        $this->_force_access_code = $code;
    }

    /**
     * Get the Message-ID field for an email regarding this ticket, with the
     * embedded PTAC code.
     *
     * @return string
     */
    public function getUniqueEmailMessageId()
    {
        $uid = 'PTAC-'.$this->getAccessCode().'.';
        $uid .= uniqid('', true).'-'.App::getSetting('core.site_id');
        $uid .= '@'.md5(App::getSetting('core.site_url', 'deskpro'));

        return $uid;
    }

    /**
     * @return string
     */
    public function getEmailReferencesHeader()
    {
        $uid = 'TICKET-'.$this->getAccessCode().'.';
        $uid .= App::getSetting('core.site_id');
        $uid .= '@'.md5(App::getSetting('core.site_url', 'deskpro'));

        return $uid;
    }

    /**
     * Get the ID used in the interface for links etc.
     *
     * @return int
     */
    public function getPublicId()
    {
        if (App::getSetting('core_tickets.use_ref')) {
            return $this->ref;
        }

        return $this->id;
    }

    /**
     * Did this ticket originate from a gateway?
     *
     * @return bool
     */
    public function isFromGateway()
    {
        if (strpos($this->creation_system, 'gateway') === 0) {
            return true;
        }

        return false;
    }

    /**
     * Decodes an access code into a ticket id and the standalone auth.
     *
     * @param  $access_code
     *
     * @return array
     */
    public static function decodeAccessCode($access_code)
    {
        $len = self::TAC_AUTHCODE_LEN;
        if (strlen($access_code) < ($len + 1)) {
            return false;
        }

        $matches = Strings::extractRegexMatch('#^(.+)(.{'.$len.'})$#', $access_code, -1);
        if (!$matches) {
            return false;
        }

        list(, $ticket_id, $auth) = $matches;

        $ticket_id = Util::baseDecode($ticket_id, 'letters');

        return [
            'ticket_id' => $ticket_id,
            'auth'      => $auth,
        ];
    }

    /**
     * @return string
     */
    public function getTicketHash()
    {
        if (!$this->ticket_hash) {
            $this->initHashCode();
        }

        return $this->ticket_hash;
    }

    /**
     * Resets the ticket hash.
     */
    public function recomputeHash()
    {
        $hashes   = [];
        $hashes[] = sha1(
            $this->subject
            .($this->person ? $this->person->getEmailAddress() : '')
            .$this->getAgentId()
            .$this->getAgentTeamId()
            .$this->getDepartmentId()
            .$this->getCategoryId()
            .$this->getWorkflowId()
            .$this->getPriorityId()
            .$this->getProductId()
            .$this->getBrandId()
        );

        foreach ($this->custom_data as $d) {
            $hashes[] = sha1($d['field_id'].$d['value'].$d['input']);
        }

        if ($this->messages->containsKey(0)) {
            $hashes[] = $this->messages->get(0)->getMessageHash();
        }

        sort($hashes, \SORT_STRING);

        $ticket_hash = sha1(implode('', $hashes));
        $this->setModelField('ticket_hash', $ticket_hash);
    }

    public function initHashCode()
    {
        if ($this->ticket_hash) {
            return;
        }

        $this->recomputeHash();
    }

    /**
     * @return \Application\DeskPRO\Labels\LabelManager
     */
    public function getLabelManager()
    {
        if ($this->_label_manager === null) {
            $this->_label_manager = new \Application\DeskPRO\Labels\LabelManager($this, 'DeskPRO:LabelTicket');
        }

        return $this->_label_manager;
    }

    /**
     * Copy this ticket properties to a new ticket.
     *
     * @return Ticket
     */
    public function copy()
    {
        $ticket = new self();
        $this->copyTo($ticket);

        return $ticket;
    }

    /**
     * Copy this ticket properties on to another ticket.
     *
     * @param Ticket $ticket
     */
    public function copyTo(Ticket $ticket)
    {
        $load = [
            'agent',
            'agent_team',
            'person',
            'person_email',
            'department',
            'category',
            'product',
            'workflow',
            'language',
            'organization',
            'status',
            'ticket_status',
            'subject',
            'urgency',
        ];

        foreach ($load as $k) {
            $ticket[$k] = $this[$k];
        }

        // Custom field data
        foreach ($this->custom_data as $custom_data) {
            $new_custom_data         = clone $custom_data;
            $new_custom_data->ticket = $ticket;

            $ticket->addCustomData($new_custom_data);
        }
    }

    public static function getStatusInt($status_code)
    {
        $status   = $status_code;
        $idStatus = null;
        if (strpos($status, '.')) {
            list($status, $idStatus) = explode('.', $status, 2);
        }

        switch ($status) {
            case TicketStatus::STATUS_TYPE_AWAITING_AGENT:
                return 100;
            case TicketStatus::STATUS_TYPE_AWAITING_USER:
                return 110;
            case TicketStatus::STATUS_TYPE_RESOLVED:
                return 200;
            case TicketStatus::STATUS_TYPE_ARCHIVED:
                return 210;
            case TicketStatus::STATUS_TYPE_HIDDEN:
                if ($idStatus && $status_code == App::getContainer()->getTicketStatuses()->getDeletedStatus()->getStatusCode()) {
                    return 310;
                } elseif ($idStatus && $status_code == App::getContainer()->getTicketStatuses()->getSpamStatus()->getStatusCode()) {
                    return 320;
                } else {
                    return 300;
                }
                break;
            case TicketStatus::STATUS_TYPE_PENDING:
                return 400;
        }

        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function toApiData($primary = true, $deep = true, array $visited = [])
    {
        $data = parent::toApiData($primary, $deep, $visited);
        if ($deep) {
            $data['labels'] = [];
            foreach ($this->labels as $label) {
                $data['labels'][] = $label['label'];
            }

            $data['problems'] = [];
            foreach ($this->problems as $problem) {
                $data['problems'][] = [
                    'id'    => $problem->id,
                    'title' => $problem->title,
                ];
            }
        }

        if (!empty($data['email_account'])) {
            unset($data['email_account']['incoming_account']);
            unset($data['email_account']['outgoing_account']);
        }

        $data['department']['parent'] = $this->department && $this->department->getParent()
            ? $this->department->getParent()->toApiData(true, false)
            : [];

        $data['total_user_waiting_real']   = $this->getRealTotalUserWaiting();
        $data['total_user_waiting_work']   = $this->getTotalUserWaitingWorkTime();
        $data['current_user_waiting']      = $this->getCurrentUserWaitingTime();
        $data['current_user_waiting_work'] = $this->getCurrentUserWaitingWorkTime();
        $data['total_to_first_reply_work'] = $this->getWorkTimeToFirstReply();
        $data['total_to_resolution']       = $this->getTimeUntilResolution();
        $data['total_to_resolution_work']  = $this->getWorkTimeUntilResolution();

        if ($this->agent) {
            $data['agent']['display_name']      = $this->agent->getDisplayNameUser();
            $data['agent']['display_name_real'] = $this->agent->getDisplayName();
        }

        $data['access_code']                    = $this->getAccessCode();
        $data['access_code_email_body_token']   = '(#'.$this->getAccessCode().')';
        $data['access_code_email_header_token'] = 'PTAC-'.$this->getAccessCode();

        $data['status']           = $this->getStatusCode();
        $data['ticket_status_id'] = $this->getTicketStatus()->getId() ?: null;

        // Render custom fields to text values
        $field_manager = App::getContainer()->getTicketFieldManager();
        $field_manager->addApiData($this, $data);

        return $data;
    }

    /**
     * @param string      $string
     * @param Person|null $performer
     * @param bool        $escape
     * @param bool        $to_user
     *
     * @return mixed
     */
    public function replaceVarsInString($string, Person $performer = null, $escape = false, $to_user = true)
    {
        $repl = [];

        $display_name = $to_user ? $this->person->getDisplayNameUser() : $this->person->getDisplayName();

        $repl = array_merge(
            [
                'user.name'                  => $display_name,
                'user.email'                 => $this->person->getPrimaryEmailAddress(),
                'user.organization_position' => $this->person->organization_position,
                'org.name'                   => $this->person->organization ? $this->person->organization->name : '',
            ],
            $repl
        );

        // Custom user fields: {{ user.field23 }}
        $field_manager = App::getSystemService('person_fields_manager');
        $custom_fields = $field_manager->getRenderedToTextForObject($this->person);
        foreach ($custom_fields as $f) {
            $repl["user.field{$f['id']}"] = $f['rendered'];
        }

        // Custom org fields: {{ agent.field23 }}
        if ($this->person->organization) {
            $field_manager = App::getSystemService('org_fields_manager');
            $custom_fields = $field_manager->getRenderedToTextForObject($this->person->organization);
            foreach ($custom_fields as $f) {
                $repl["org.field{$f['id']}"] = $f['rendered'];
            }
        }

        if (!$performer && App::getCurrentPerson() && App::getCurrentPerson()->getId()) {
            $performer = App::getCurrentPerson();
        }

        if ($performer) {
            $display_name = $to_user ? $performer->getDisplayNameUser() : $performer->getDisplayName();

            $repl = array_merge(
                [
                    'performer.name'                  => $display_name,
                    'performer.email'                 => $performer->getPrimaryEmailAddress(),
                    'performer.organization_position' => $performer->organization_position,
                    'performer.org.name'              => $performer->organization ? $performer->organization->name : '',
                ],
                $repl
            );

            $field_manager = App::getSystemService('person_fields_manager');
            $custom_fields = $field_manager->getRenderedToTextForObject($performer);
            foreach ($custom_fields as $f) {
                $repl["performer.field{$f['id']}"] = $f['rendered'];
            }

            if ($performer->organization) {
                $field_manager = App::getSystemService('org_fields_manager');
                $custom_fields = $field_manager->getRenderedToTextForObject($performer->organization);
                foreach ($custom_fields as $f) {
                    $repl["performer.org.field{$f['id']}"] = $f['rendered'];
                }
            }
        } else {
            $repl = array_merge(
                [
                    'performer.name'                  => '',
                    'performer.email'                 => '',
                    'performer.organization_position' => '',
                    'performer.org.name'              => '',
                ],
                $repl
            );
        }

        if ($this->agent) {
            $agent_display_name = $to_user ? $this->agent->getDisplayNameUser() : $this->agent->getDisplayName();
        } else {
            $agent_display_name = '';
        }

        $repl = array_merge(
            [
                'ticket.id'         => $this->id,
                'ticket.ref'        => $this->ref,
                'ticket.subject'    => $this->subject,
                'ticket.department' => $this->department ? $this->department->full_title : '',
                'ticket.product'    => $this->product ? $this->product->full_title : '',
                'ticket.category'   => $this->category ? $this->category->full_title : '',
                'ticket.workflow'   => $this->workflow ? $this->workflow->title : '',
                'ticket.priority'   => $this->priority ? $this->priority->title : '',
                'agent.name'        => $agent_display_name,
                'agent.email'       => $this->agent ? $this->agent->getPrimaryEmailAddress() : '',
                'agent_team.name'   => $this->agent_team ? $this->agent_team->name : '',
            ],
            $repl
        );

        // Custom ticket fields: {{ ticket.field23 }}
        $field_manager = App::getSystemService('ticket_fields_manager');
        $custom_fields = $field_manager->getRenderedToTextForObject($this);
        foreach ($custom_fields as $f) {
            $repl["ticket.field{$f['id']}"] = $f['rendered'];
        }

        foreach ($repl as $k => $v) {
            if ($escape) {
                $v = htmlspecialchars($v);
            }
            $string = str_replace('{{'.$k.'}}', $v, $string);
            $string = str_replace('{{'.$k.'}}', $v, $string);
        }

        return $string;
    }

    /**
     * @deprecated use $this->get('object_router')->getPortalPath($ticket) instead
     */
    public function getPath()
    {
        SystemErrorHandler::logExceptionIfUniqueBacktrace(
            new \Exception('DEPRECATED METHOD CALL: '.get_called_class().'::getPath()')
        );

        return App::getObjectRouter()->getPortalPath($this);
    }

    public function isAgentCreated()
    {
        return strpos($this->creation_system, '.agent') !== false;
    }

    public function getWorkHoursSet()
    {
        if (!$this->_work_hours_set) {
            try {
                $work_hours = App::getSetting('core_tickets.work_hours');
                if ($work_hours && !is_array($work_hours)) {
                    $work_hours = @unserialize($work_hours);
                }
                if ($work_hours) {
                    $work_hours = Arrays::removeEmptyArray($work_hours);
                    $work_hours = Arrays::removeNull($work_hours);
                    $work_hours = Arrays::removeEmptyString($work_hours);

                    $work_hours = new OptionsArray($work_hours);

                    return new WorkHoursSet(
                        $work_hours->get('start_hour', 9) * 3600 + $work_hours->get('start_min', 0) * 60,
                        $work_hours->get('end_hour', 18) * 3600 + $work_hours->get('end_min', 0) * 60,
                        $work_hours->get('work_days', [1, 2, 3, 4, 5]),
                        $work_hours->get('timezone', 'UTC'),
                        $work_hours->get('holidays', [])
                    );
                } else {
                    return new WorkHoursSetAll();
                }
            } catch (\Exception $e) {
                $this->_work_hours_set = new \Orb\Util\WorkHoursSetAll();
            }
        }

        return $this->_work_hours_set;
    }

    /**
     * Get property from properties array.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getProperty($key, $default = null)
    {
        return $this->properties !== null && isset($this->properties[$key]) ? $this->properties[$key] : $default;
    }

    /**
     * Set properties.
     *
     * @param  $key
     * @param  $value
     */
    public function setProperty($key, $value)
    {
        $old = $this->properties;

        if ($value === null) {
            if ($this->properties) {
                unset($this->properties[$key]);
            }
            if (!$this->properties) {
                $this->properties = null;
            }
        } else {
            if ($this->properties === null) {
                $this->properties = [];
            }
            $this->properties[$key] = $value;
        }

        $this->_onPropertyChanged('properties', $old, $this->properties);
    }

    /**
     * Returns the data as it would be returned from the database.
     *
     * @return array
     */
    public function getDbRow()
    {
        $row_data = [
            'id'                     => $this->id,
            'language_id'            => $this->language ? $this->language->id : null,
            'department_id'          => $this->department ? $this->department->id : null,
            'category_id'            => $this->category ? $this->category->id : null,
            'priority_id'            => $this->priority ? $this->priority->id : null,
            'workflow_id'            => $this->workflow ? $this->workflow->id : null,
            'product_id'             => $this->product ? $this->product->id : null,
            'person_id'              => $this->person ? $this->person->id : null,
            'person_email_id'        => $this->person_email ? $this->person_email->id : null,
            'agent_id'               => $this->agent ? $this->agent->id : null,
            'agent_team_id'          => $this->agent_team ? $this->agent_team->id : null,
            'organization_id'        => $this->organization ? $this->organization->id : null,
            'linked_chat_id'         => $this->linked_chat ? $this->linked_chat->id : null,
            'email_account_id'       => $this->email_account ? $this->email_account->id : null,
            'locked_by_agent'        => $this->locked_by_agent ? $this->locked_by_agent->id : null,
            'ref'                    => $this->ref,
            'auth'                   => $this->auth,
            'sent_to_address'        => $this->sent_to_address,
            'creation_system'        => $this->creation_system,
            'creation_system_option' => $this->creation_system_option,
            'ticket_hash'            => $this->ticket_hash,
            'status'                 => $this->status,
            'ticket_status'          => $this->ticket_status,
            'is_hold'                => $this->isHold(),
            'urgency'                => $this->urgency,
            'count_agent_replies'    => $this->count_agent_replies,
            'count_user_replies'     => $this->count_user_replies,
            'feedback_rating'        => $this->feedback_rating,
            'date_feedback_rating'   => $this->date_feedback_rating ? $this->date_feedback_rating->format(
                'Y-m-d H:i:s'
            ) : null,
            'date_created'            => $this->date_created->format('Y-m-d H:i:s'),
            'date_resolved'           => $this->date_resolved ? $this->date_resolved->format('Y-m-d H:i:s') : null,
            'date_archived'           => $this->date_archived ? $this->date_archived->format('Y-m-d H:i:s') : null,
            'date_first_agent_assign' => $this->date_first_agent_assign ? $this->date_first_agent_assign->format(
                'Y-m-d H:i:s'
            ) : null,
            'date_first_agent_reply' => $this->date_first_agent_reply ? $this->date_first_agent_reply->format(
                'Y-m-d H:i:s'
            ) : null,
            'date_last_agent_reply' => $this->date_last_agent_reply ? $this->date_last_agent_reply->format(
                'Y-m-d H:i:s'
            ) : null,
            'date_last_user_reply' => $this->date_last_user_reply ? $this->date_last_user_reply->format(
                'Y-m-d H:i:s'
            ) : null,
            'date_agent_waiting' => $this->date_agent_waiting ? $this->date_agent_waiting->format(
                'Y-m-d H:i:s'
            ) : null,
            'date_user_waiting' => $this->date_user_waiting ? $this->date_user_waiting->format(
                'Y-m-d H:i:s'
            ) : null,
            'date_status'          => $this->date_status->format('Y-m-d H:i:s'),
            'total_user_waiting'   => $this->total_user_waiting,
            'total_to_first_reply' => $this->total_to_first_reply,
            'date_locked'          => $this->date_locked ? $this->date_locked->format('Y-m-d H:i:s') : null,
            'has_attachments'      => $this->has_attachments,
            'subject'              => $this->subject,
            'original_subject'     => $this->original_subject,
            'properties'           => $this->properties ? serialize($this->properties) : null,
            'worst_sla_status'     => $this->worst_sla_status,
            'waiting_times'        => $this->waiting_times ? serialize($this->waiting_times) : null,
        ];

        return $row_data;
    }

    /**
     * @return \Application\DeskPRO\Tickets\StateChangeRecorder
     */
    public function getStateChangeRecorder()
    {
        //This is overridden just so phpcod returns proper subclass
        return parent::getStateChangeRecorder();
    }

    /**
     * @return TicketChangeTracker
     */
    public function getTicketLogger()
    {
        if ($this->__dp_ticket_change_tracker) {
            return $this->__dp_ticket_change_tracker;
        }

        $this->__dp_ticket_change_tracker = new TicketChangeTracker($this);

        return $this->__dp_ticket_change_tracker;
    }

    /**
     * Set ElasticSearch highlight data.
     *
     * @param array $highlights array of highlight strings
     */
    public function setElasticHighlights(array $highlights)
    {
        if (!empty($highlights)) {
            $this->_search_highlights = $highlights;
        }
    }

    /**
     * Get Elasticsearch highlight data.
     *
     * @param null $field
     *
     * @return array|null
     */
    public function getElasticHighlights($field = null)
    {
        if (is_null($field)) {
            return $this->_search_highlights;
        } else {
            if (isset($this->_search_highlights[$field])) {
                return $this->_search_highlights[$field];
            } else {
                return;
            }
        }
    }

    /**
     * @return Person
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @return Organization
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @return int|null
     */
    public function getOrganizationId()
    {
        return $this->organization ? $this->organization->getId() : null;
    }

    public function isOwner(Person $person)
    {
        return $person === $this->getPerson();
    }

    public function isParticipant(Person $person)
    {
        return (bool) $this->hasParticipantPerson($person);
    }

    public function isOrganizationManager(Person $person)
    {
        return $person->isOrganizationManager()
        && $person->getOrganization() !== null
        && $person->getOrganization() === $this->getOrganization();
    }

    /**
     * @param Person $person
     * @param string $context Which context to check in: user or agent
     *
     * @return bool
     */
    public function isInvolved(Person $person, $context = 'user')
    {
        if ($context === 'user') {
            return $this->isOwner($person)
            || (!$person->isAgent() && $this->isParticipant($person))
            || $this->isOrganizationManager($person);
        } else {
            return $this->isOwner($person)
            || $this->isParticipant($person)
            || $this->getAgent() === $person
            || $this->isOrganizationManager($person);
        }
    }

    public function hasVisibleStatus()
    {
        return $this->status !== 'hidden';
    }

    /**
     * @deprecated This exists only for old customised email templates
     *
     * @return string
     */
    public function getLink()
    {
        $container = App::getContainer();
        if ($container->has('object_router')) {
            $r = $container->get('object_router');

            return $r->getPortalUrl($this);
        }

        return '';
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public function _setOriginalId()
    {
        $this->_original_id             = $this->id;
        $this->__dp_auto_ticket_process = true;
    }

    public function _autoProcessTicket()
    {
        if ($this->__dp_is_processing_ticket) {
            return;
        }

        if ($this->__dp_auto_ticket_process) {

            // Detect when we last did a save
            if ($this->__dp_last_process_save) {
                $state = $this->getStateChangeRecorder();
                if ($state->getStateVersion() <= $this->__dp_last_process_save) {
                    return;
                }
            }

            $tm = App::$container->getTicketManager();

            $context = new ExecutorContext();
            if (App::getCurrentPerson()) {
                $context->setPersonContext(App::getCurrentPerson(), true);
            }
            $context->getVars()->set('custom_field_manager', App::$container->getCustomFieldManager());

            $state = $this->getStateChangeRecorder();
            if ($state->isNewTicket()) {
                $event_type = 'newticket';
            } elseif ($state->hasNewReply()) {
                $event_type = 'newreply';
            } else {
                $event_type = 'update';
            }

            if (defined('DP_INTERFACE')) {
                $person = App::getCurrentPerson();

                if (!$person || !$person->id) {
                    $person = null;
                }

                switch (DP_INTERFACE) {
                    case 'test':
                        /*
                         * @todo it's just a stub to pass some tests
                         * please look here - https://trello.com/c/rn1XPVSu/544-tickettimelinedataservice-crash
                         */
                        break;
                    case 'admin':
                    case 'agent':
                        $context = $tm->createAgentExecutorContext(
                            $person,
                            $event_type,
                            'web'
                        );
                        break;
                    case 'user':
                        if (!$person && $this->person) {
                            $person = $this->person;
                        }
                        $context = $tm->createUserExecutorContext(
                            $person,
                            $event_type,
                            'portal'
                        );
                        break;
                    case 'api':
                        $context = $tm->createAgentExecutorContext(
                            $person,
                            $event_type,
                            'api'
                        );
                        break;
                    default:
                        $context = $tm->createSystemExecutorContext();
                        break;
                }
            }

            $this->__dp_is_processing_ticket = true;

            try {
                $tm->saveTicket($this, $context);
                $this->__dp_is_processing_ticket = false;
            } catch (\Exception $e) {
                $this->__dp_is_processing_ticket = false;
                throw $e;
            }
        }
    }

    /**
     * We can't set invalid data to the db so check properties before flush.
     */
    public function _onValidateProps()
    {
        if ($this->agent && !$this->agent->isAgent()) {
            throw new \InvalidArgumentException(sprintf('%s is not an agent', $this->agent->getId()));
        }
    }

    /**
     * @param Problem|null $problem
     */
    public function associateProblem(Problem $problem = null)
    {
        if (!$problem) {
            return;
        }

        if ($old = $this->problems->first()) {
            foreach ($problem->tickets as $ticket) {
                if ($ticket['id'] == $this->id) {
                    $problem->tickets->removeElement($ticket);
                }
            }
            $this->problems->removeElement($old);
        }

        $this->problems->add($problem);
        $problem->tickets->count(); // explicit init
        $problem->tickets->add($this);

        $this->_onPropertyChanged('problems', null, $this->problems);
    }

    public function disassociateProblem()
    {
        foreach ($this->problems as $pk => $problem) {
            foreach ($problem->tickets as $tk => $ticket) {
                if ($ticket['id'] === $this->id) {
                    $problem->tickets->remove($tk);
                }
            }
            $this->problems->remove($pk);
        }
        $this->_onPropertyChanged('problems', null, $this->problems);
    }

    /**
     * @return ChatConversation
     */
    public function getLinkedChat()
    {
        return $this->linked_chat;
    }

    /**
     * @param string $creation_system
     *
     * @return $this
     */
    public function setCreationSystem($creation_system)
    {
        $this->setModelField('creation_system', $creation_system);

        return $this;
    }

    /**
     * @param string $creation_system_option
     *
     * @return $this
     */
    public function setCreationSystemOption($creation_system_option)
    {
        $this->setModelField('creation_system_option', $creation_system_option);

        return $this;
    }

    /**
     * @return string
     */
    public function getCreationSystem()
    {
        return $this->creation_system;
    }

    /**
     * @return string
     */
    public function getCreationSystemOption()
    {
        return $this->creation_system_option;
    }

    /**
     * @return \DateTime
     */
    public function getDateFeedbackRating()
    {
        return $this->date_feedback_rating;
    }

    /**
     * @return TicketSla[]|ArrayCollection
     */
    public function getTicketSlas()
    {
        return $this->ticket_slas;
    }

    /**
     * @param TicketSla $ticketSla
     *
     * @return $this
     */
    public function removeTicketSla(TicketSla $ticketSla)
    {
        $this->ticket_slas->removeElement($ticketSla);
        $this->_onPropertyChanged('ticket_slas', null, $this->ticket_slas);

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * @return \DateTime
     */
    public function getDateResolved()
    {
        return $this->date_resolved;
    }

    /**
     * @return \DateTime
     */
    public function getDateArchived()
    {
        return $this->date_archived;
    }

    /**
     * @return \DateTime
     */
    public function getDateFirstAgentAssign()
    {
        return $this->date_first_agent_assign;
    }

    /**
     * @return \DateTime
     */
    public function getDateFirstAgentReply()
    {
        return $this->date_first_agent_reply;
    }

    /**
     * @return \DateTime
     */
    public function getDateLastAgentReply()
    {
        return $this->date_last_agent_reply;
    }

    /**
     * @return \DateTime
     */
    public function getDateLastUserReply()
    {
        return $this->date_last_user_reply;
    }

    /**
     * @return \DateTime
     */
    public function getDateAgentWaiting()
    {
        return $this->date_agent_waiting;
    }

    /**
     * @return \DateTime
     */
    public function getDateUserWaiting()
    {
        return $this->date_user_waiting;
    }

    /**
     * @return \DateTime
     */
    public function getDateStatus()
    {
        return $this->date_status;
    }

    /**
     * @return \DateTime
     */
    public function getDateOnHold()
    {
        return $this->date_on_hold;
    }

    /**
     * @param \DateTime $date_on_hold
     *
     * @return $this
     */
    public function setDateOnHold(\DateTime $date_on_hold = null)
    {
        $this->setModelField('date_on_hold', $date_on_hold);

        return $this;
    }

    /**
     * @return int
     */
    public function getTotalUserWaiting()
    {
        return $this->total_user_waiting;
    }

    /**
     * @return int
     */
    public function getTotalToFirstReply()
    {
        return $this->total_to_first_reply;
    }

    /**
     * @return Person
     */
    public function getLockedByAgent()
    {
        return $this->locked_by_agent;
    }

    /**
     * @return \DateTime
     */
    public function getDateLocked()
    {
        return $this->date_locked;
    }

    /**
     * @return bool
     */
    public function hasAttachments()
    {
        return $this->has_attachments;
    }

    /**
     * @return string
     */
    public function getOriginalSubject()
    {
        return $this->original_subject;
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @return int
     */
    public function getCountAgentReplies()
    {
        return $this->count_agent_replies;
    }

    /**
     * @return int
     */
    public function getCountUserReplies()
    {
        return $this->count_user_replies;
    }

    /**
     * @param ArrayCollection $problems
     *
     * @return $this
     */
    public function setProblems($problems)
    {
        $this->setModelField('problems', $problems);

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getProblems()
    {
        return $this->problems;
    }

    /**
     * @return int
     */
    public function getUrgency()
    {
        return $this->urgency;
    }

    /**
     * @return TicketFlagged[]|ArrayCollection
     */
    public function getStars()
    {
        return $this->stars;
    }

    /**
     * @param Person $person
     *
     * @return TicketFlagged|null
     */
    public function getPersonStar(Person $person)
    {
        return $this->stars->matching(new Criteria(Criteria::expr()->eq('person_id', $person->getId())))->first();
    }

    /**
     * @return \DeskPRO\Bundle\AppBundle\Entity\TicketFollowUp[]
     */
    public function getFollowUps()
    {
        return $this->followUps;
    }

    /**
     * @param TicketFollowUp $followUp
     *
     * @return $this
     */
    public function addFollowUp(TicketFollowUp $followUp)
    {
        $this->followUps->add($followUp);
        $followUp->setTicket($this);

        $this->_onPropertyChanged('followUps', null, $this->followUps);
        $this->getStateChangeRecorder()->record('followUp', null, $followUp);

        return $this;
    }

    /**
     * @param TicketFollowUp $followUp
     *
     * @return $this
     */
    public function removeFollowUp(TicketFollowUp $followUp)
    {
        $this->followUps->removeElement($followUp);
        $followUp->setTicket(null);

        $this->_onPropertyChanged('followUps', null, $this->followUps);
        $this->getStateChangeRecorder()->record('followUp', $followUp, null);

        return $this;
    }

    /**
     * @return TicketLog[]|ArrayCollection
     */
    public function getLogs()
    {
        return $this->logs;
    }

    /**
     * @return array
     */
    public static function getTicketStatuses()
    {
        return [
            TicketStatus::STATUS_TYPE_AWAITING_AGENT,
            TicketStatus::STATUS_TYPE_AWAITING_USER,
            TicketStatus::STATUS_TYPE_ARCHIVED,
            TicketStatus::STATUS_TYPE_RESOLVED,
            TicketStatus::STATUS_TYPE_HIDDEN,
            TicketStatus::STATUS_TYPE_HIDDEN.'.'.TicketStatus::SYS_ID_SPAM,
            TicketStatus::STATUS_TYPE_HIDDEN.'.'.TicketStatus::SYS_ID_DELETED,
        ];
    }

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->inheritanceType           = ClassMetadataInfo::INHERITANCE_TYPE_NONE;
        $metadata->changeTrackingPolicy      = ClassMetadataInfo::CHANGETRACKING_NOTIFY;
        $metadata->generatorType             = ClassMetadataInfo::GENERATOR_TYPE_IDENTITY;
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\Ticket';
        $metadata->addLifecycleCallback('_setOriginalId', 'postLoad');
        $metadata->addLifecycleCallback('_onValidateProps', 'prePersist');
        $metadata->addLifecycleCallback('_onValidateProps', 'preUpdate');
        $metadata->addLifecycleCallback('_autoProcessTicket', 'postPersist');
        $metadata->addLifecycleCallback('_autoProcessTicket', 'postUpdate');
        $metadata->setPrimaryTable(
            [
                'name'    => 'tickets',
                'indexes' => [
                    'date_created_idx' => ['columns' => ['date_created']],
                    'date_locked_idx'  => ['columns' => ['date_locked']],
                    'status_idx'       => ['columns' => ['status', 'ticket_status_id']],
                ],
                'uniqueConstraints' => [
                    'ref_idx' => ['columns' => ['ref']],
                ],
            ]
        );

        $metadata->mapField(
            [
                'columnName' => 'id',
                'fieldName'  => 'id',
                'type'       => 'integer',
                'id'         => true,
                'nullable'   => false,
            ]
        );
        $metadata->mapField(
            [
                'columnName' => 'ref',
                'fieldName'  => 'ref',
                'type'       => 'string',
                'length'     => 100,
                'nullable'   => false,
            ]
        );
        $metadata->mapField(
            [
                'columnName' => 'auth',
                'fieldName'  => 'auth',
                'type'       => 'string',
                'length'     => 20,
                'nullable'   => false,
            ]
        );
        $metadata->mapField(
            [
                'columnName' => 'sent_to_address',
                'fieldName'  => 'sent_to_address',
                'type'       => 'string',
                'length'     => 200,
                'nullable'   => false,
            ]
        );
        $metadata->mapField(
            [
                'columnName' => 'email_account_address',
                'fieldName'  => 'email_account_address',
                'type'       => 'string',
                'length'     => 255,
                'nullable'   => false,
            ]
        );
        $metadata->mapField(
            [
                'columnName' => 'creation_system',
                'fieldName'  => 'creation_system',
                'type'       => 'string',
                'length'     => 100,
                'nullable'   => false,
            ]
        );
        $metadata->mapField(
            [
                'columnName' => 'creation_system_option',
                'fieldName'  => 'creation_system_option',
                'type'       => 'string',
                'length'     => 1000,
                'nullable'   => false,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'ticket_hash',
                'columnName' => 'ticket_hash',
                'type'       => 'string',
                'length'     => 40,
                'nullable'   => false,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'status',
                'columnName' => 'status',
                'type'       => 'string',
                'length'     => 30,
                'nullable'   => false,
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'ticket_status',
                'targetEntity' => 'DeskPRO\\Bundle\\AppBundle\\Entity\\TicketStatus',
                'joinColumns'  => [
                    [
                        'name'                 => 'ticket_status_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'set null',
                    ],
                ],
                'dpApi' => true,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'urgency',
                'columnName' => 'urgency',
                'type'       => 'integer',
                'nullable'   => false,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'count_agent_replies',
                'columnName' => 'count_agent_replies',
                'type'       => 'integer',
                'nullable'   => false,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'count_user_replies',
                'columnName' => 'count_user_replies',
                'type'       => 'integer',
                'nullable'   => false,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'feedback_rating',
                'columnName' => 'feedback_rating',
                'type'       => 'integer',
                'nullable'   => true,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'date_feedback_rating',
                'columnName' => 'date_feedback_rating',
                'type'       => 'datetime',
                'nullable'   => true,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'date_created',
                'columnName' => 'date_created',
                'type'       => 'datetime',
                'nullable'   => false,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'date_resolved',
                'columnName' => 'date_resolved',
                'type'       => 'datetime',
                'nullable'   => true,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'date_archived',
                'columnName' => 'date_archived',
                'type'       => 'datetime',
                'nullable'   => true,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'date_first_agent_assign',
                'columnName' => 'date_first_agent_assign',
                'type'       => 'datetime',
                'nullable'   => true,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'date_first_agent_reply',
                'columnName' => 'date_first_agent_reply',
                'type'       => 'datetime',
                'nullable'   => true,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'date_last_agent_reply',
                'columnName' => 'date_last_agent_reply',
                'type'       => 'datetime',
                'nullable'   => true,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'date_last_user_reply',
                'columnName' => 'date_last_user_reply',
                'type'       => 'datetime',
                'nullable'   => true,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'date_agent_waiting',
                'columnName' => 'date_agent_waiting',
                'type'       => 'datetime',
                'nullable'   => true,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'date_user_waiting',
                'columnName' => 'date_user_waiting',
                'type'       => 'datetime',
                'nullable'   => true,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'date_status',
                'columnName' => 'date_status',
                'type'       => 'datetime',
                'nullable'   => false,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'date_on_hold',
                'columnName' => 'date_on_hold',
                'type'       => 'datetime',
                'nullable'   => true,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'total_user_waiting',
                'columnName' => 'total_user_waiting',
                'type'       => 'integer',
                'nullable'   => false,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'total_to_first_reply',
                'columnName' => 'total_to_first_reply',
                'type'       => 'integer',
                'nullable'   => false,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'date_locked',
                'columnName' => 'date_locked',
                'type'       => 'datetime',
                'nullable'   => true,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'has_attachments',
                'columnName' => 'has_attachments',
                'type'       => 'boolean',
                'nullable'   => false,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'subject',
                'columnName' => 'subject',
                'type'       => 'string',
                'length'     => 255,
                'nullable'   => false,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'original_subject',
                'columnName' => 'original_subject',
                'type'       => 'string',
                'length'     => 255,
                'nullable'   => false,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'properties',
                'columnName' => 'properties',
                'type'       => 'array',
                'nullable'   => true,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'worst_sla_status',
                'columnName' => 'worst_sla_status',
                'type'       => 'string',
                'length'     => 20,
                'nullable'   => true,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'waiting_times',
                'columnName' => 'waiting_times',
                'type'       => 'array',
                'nullable'   => true,
            ]
        );
        $metadata->mapManyToOne([
            'fieldName'    => 'parent_ticket',
            'targetEntity' => self::class,
            'inversedBy'   => 'children_tickets',
            'joinColumns'  => [
                [
                    'name'                 => 'parent_ticket_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'set null',
                ],
            ],
            'dpApi' => true,
        ]);
        $metadata->mapOneToMany([
            'fieldName'    => 'children_tickets',
            'targetEntity' => self::class,
            'mappedBy'     => 'parent_ticket',
            'fetch'        => ClassMetadataInfo::FETCH_EXTRA_LAZY,
        ]);
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'language',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\Language',
                'cascade'      => ['persist'],
                'joinColumns'  => [
                    [
                        'name'                 => 'language_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'set null',
                    ],
                ],
                'dpApi' => true,
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'brand',
                'targetEntity' => Brand::class,
                'cascade'      => ['persist'],
                'joinColumns'  => [
                    [
                        'name'                 => 'brand_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'set null',
                    ],
                ],
                'dpApi' => true,
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'department',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\Department',
                'cascade'      => ['persist'],
                'joinColumns'  => [
                    [
                        'name'                 => 'department_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'set null',
                    ],
                ],
                'dpApi' => true,
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'category',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\TicketCategory',
                'joinColumns'  => [
                    [
                        'name'                 => 'category_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'set null',
                    ],
                ],
                'dpApi' => true,
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'priority',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\TicketPriority',
                'joinColumns'  => [
                    [
                        'name'                 => 'priority_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'set null',
                    ],
                ],
                'dpApi' => true,
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'workflow',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\TicketWorkflow',
                'joinColumns'  => [
                    [
                        'name'                 => 'workflow_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'set null',
                    ],
                ],
                'dpApi' => true,
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'product',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\Product',
                'joinColumns'  => [
                    [
                        'name'                 => 'product_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'set null',
                    ],
                ],
                'dpApi' => true,
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'person',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\Person',
                'cascade'      => ['persist'],
                'joinColumns'  => [
                    [
                        'name'                 => 'person_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'cascade',
                    ],
                ],
                'dpApi'     => true,
                'dpApiDeep' => true,
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'person_email',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\PersonEmail',
                'joinColumns'  => [
                    [
                        'name'                 => 'person_email_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'set null',
                    ],
                ],
                'dpApi' => true,
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'agent',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\Person',
                'joinColumns'  => [
                    [
                        'name'                 => 'agent_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'set null',
                    ],
                ],
                'dpApi' => true,
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'agent_team',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\AgentTeam',
                'joinColumns'  => [
                    [
                        'name'                 => 'agent_team_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'set null',
                    ],
                ],
                'dpApi' => true,
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'organization',
                'inversedBy'   => 'tickets',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\Organization',
                'cascade'      => ['persist'],
                'joinColumns'  => [
                    [
                        'name'                 => 'organization_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'set null',
                    ],
                ],
                'dpApi' => true,
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'linked_chat',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\ChatConversation',
                'joinColumns'  => [
                    [
                        'name'                 => 'linked_chat_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'set null',
                    ],
                ],
            ]
        );
        $metadata->mapOneToMany(
            [
                'fieldName'     => 'attachments',
                'targetEntity'  => 'Application\\DeskPRO\\Entity\\TicketAttachment',
                'cascade'       => ['remove', 'persist', 'merge'],
                'mappedBy'      => 'ticket',
                'fetch'         => ClassMetadataInfo::FETCH_EXTRA_LAZY,
                'orphanRemoval' => true,
                'dpApi'         => true,
                'dpApiDeep'     => true,
            ]
        );
        $metadata->mapOneToMany(
            [
                'fieldName'    => 'access_codes',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\TicketAccessCode',
                'cascade'      => ['persist', 'merge'],
                'mappedBy'     => 'ticket',
                'onDelete'     => 'cascade',
            ]
        );
        $metadata->mapOneToMany(
            [
                'fieldName'     => 'messages',
                'targetEntity'  => 'Application\\DeskPRO\\Entity\\TicketMessage',
                'cascade'       => ['remove', 'persist', 'merge'],
                'mappedBy'      => 'ticket',
                'fetch'         => ClassMetadataInfo::FETCH_EXTRA_LAZY,
                'orderBy'       => ['date_created' => 'ASC'],
                'orphanRemoval' => true,
            ]
        );
        $metadata->mapOneToMany(
            [
                'fieldName'     => 'logs',
                'targetEntity'  => 'Application\\DeskPRO\\Entity\\TicketLog',
                'cascade'       => ['remove', 'persist', 'merge'],
                'mappedBy'      => 'ticket',
                'fetch'         => ClassMetadataInfo::FETCH_EXTRA_LAZY,
                'orderBy'       => ['date_created' => 'ASC'],
                'orphanRemoval' => true,
            ]
        );
        $metadata->mapOneToMany(
            [
                'fieldName'     => 'sms_messages',
                'targetEntity'  => 'Application\\DeskPRO\\Entity\\TicketSms',
                'cascade'       => ['remove', 'persist', 'merge'],
                'mappedBy'      => 'ticket',
                'fetch'         => ClassMetadataInfo::FETCH_EXTRA_LAZY,
                'orderBy'       => ['date_created' => 'ASC'],
                'orphanRemoval' => true,
            ]
        );
        $metadata->mapOneToMany(
            [
                'fieldName'     => 'feedback_links',
                'targetEntity'  => 'DeskPRO\\Bundle\\AppBundle\\Entity\\TicketFeedbackLink',
                'cascade'       => ['remove', 'persist', 'merge'],
                'mappedBy'      => 'ticket',
                'fetch'         => ClassMetadataInfo::FETCH_EXTRA_LAZY,
                'orderBy'       => ['date_created' => 'ASC'],
                'orphanRemoval' => true,
            ]
        );
        $metadata->mapOneToMany(
            [
                'fieldName'     => 'stars',
                'targetEntity'  => TicketFlagged::class,
                'cascade'       => ['remove', 'persist', 'merge'],
                'mappedBy'      => 'ticket',
                'fetch'         => ClassMetadataInfo::FETCH_EXTRA_LAZY,
                'orphanRemoval' => true,
            ]
        );
        $metadata->mapOneToMany(
            [
                'fieldName'     => 'followUps',
                'targetEntity'  => TicketFollowUp::class,
                'cascade'       => ['remove', 'persist', 'merge'],
                'mappedBy'      => 'ticket',
                'fetch'         => ClassMetadataInfo::FETCH_EXTRA_LAZY,
                'orphanRemoval' => true,
            ]
        );
        $metadata->mapOneToMany(
            [
                'fieldName'     => 'custom_data',
                'targetEntity'  => 'Application\\DeskPRO\\Entity\\CustomDataTicket',
                'cascade'       => ['remove', 'persist', 'merge'],
                'mappedBy'      => 'ticket',
                'orphanRemoval' => true,
                'dpApi'         => true,
            ]
        );
        $metadata->mapOneToMany(
            [
                'fieldName'     => 'labels',
                'targetEntity'  => 'Application\\DeskPRO\\Entity\\LabelTicket',
                'cascade'       => ['remove', 'persist', 'merge'],
                'mappedBy'      => 'ticket',
                'orphanRemoval' => true,
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'email_account',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\EmailAccount',
                'joinColumns'  => [
                    [
                        'name'                 => 'email_account_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'set null',
                    ],
                ],
                'dpApi' => true,
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'locked_by_agent',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\Person',
                'joinColumns'  => [
                    [
                        'name'                 => 'locked_by_agent',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'set null',
                        'columnDefinition'     => null,
                    ],
                ],
            ]
        );
        $metadata->mapOneToMany(
            [
                'fieldName'     => 'participants',
                'targetEntity'  => 'Application\\DeskPRO\\Entity\\TicketParticipant',
                'cascade'       => ['remove', 'persist', 'merge'],
                'mappedBy'      => 'ticket',
                'orphanRemoval' => true,
                'dpApi'         => true,
                'dpApiDeep'     => true,
            ]
        );
        $metadata->mapOneToMany(
            [
                'fieldName'     => 'charges',
                'targetEntity'  => 'Application\\DeskPRO\\Entity\\TicketCharge',
                'cascade'       => ['remove', 'persist', 'merge'],
                'mappedBy'      => 'ticket',
                'orphanRemoval' => true,
                'dpApi'         => true,
                'dpApiDeep'     => true,
            ]
        );
        $metadata->mapOneToMany(
            [
                'fieldName'     => 'ticket_slas',
                'targetEntity'  => 'Application\\DeskPRO\\Entity\\TicketSla',
                'cascade'       => ['persist', 'merge'],
                'mappedBy'      => 'ticket',
                'orphanRemoval' => true,
                'dpApi'         => true,
                'dpApiDeep'     => true,
            ]
        );
        $metadata->mapOneToMany(
            [
                'fieldName'     => 'jira_issues',
                'targetEntity'  => 'Application\\DeskPRO\\Entity\\JiraIssue',
                'cascade'       => ['persist', 'merge', 'remove'],
                'mappedBy'      => 'ticket',
                'orphanRemoval' => true,
                'dpApi'         => false,
                'dpApiDeep'     => false,
            ]
        );
        $metadata->mapManyToMany(
            [
                'fieldName'    => 'problems',
                'targetEntity' => Problem::class,
                'inversedBy'   => 'tickets',
                'cascade'      => ['persist', 'merge'],
                'joinTable'    => [
                    'name'               => 'problem2tickets',
                    'inverseJoinColumns' => [
                        [
                            'name'                 => 'problem_id',
                            'referencedColumnName' => 'id',
                            'nullable'             => true,
                            'onDelete'             => 'cascade',
                        ],
                    ],
                    'joinColumns' => [
                        [
                            'name'                 => 'ticket_id',
                            'referencedColumnName' => 'id',
                            'nullable'             => true,
                            'onDelete'             => 'cascade',
                        ],
                    ],
                ],
            ]
        );
    }
}
