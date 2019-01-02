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
use Application\DeskPRO\Labels\LabelManager;
use DeskPRO\Bundle\AppBundle\ObjectRouter\Configuration\PortalLinkRoute;
use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Orb\Util\Strings;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A conversation between one or more people.
 *
 * @property string $person_name
 * @property string $person_email
 * @PortalLinkRoute("portal_chats_view", route_param_map={"chat":"id"})
 */
class ChatConversation extends DomainObject implements LabelsOwner
{
    const STATUS_OPEN  = 'open';
    const STATUS_ENDED = 'ended';

    const ENDED_TIMEOUT      = 'timeout';
    const ENDED_WAIT_TIMEOUT = 'wait_timeout';
    const ENDED_ABANDONED    = 'abandoned';
    const ENDED_AGENT        = 'agent';
    const ENDED_USER         = 'user';

    /**
     * The unique id of chat conversation.
     *
     * @var int
     */
    protected $id = null;

    /**
     * Department which chat was assigned.
     *
     * @AppAssert\LeafDepartment()
     * @Assert\NotNull()
     *
     * @var \Application\DeskPRO\Entity\Department
     */
    protected $department = null;

    /**
     * Department which chat was assigned.
     *
     * @var \Application\DeskPRO\Entity\Brand
     */
    protected $brand = null;

    /**
     * @var int
     */
    protected $taskId;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection|LabelChatConversation[]
     *
     * @Assert\Valid()
     * @AppAssert\UniqueCollection(property={"label"})
     */
    protected $labels;

    /**
     * Subject of the chat conversation.
     *
     * @var string
     */
    protected $subject = '';

    /**
     * Status of the chat conversation.
     *
     * @var string
     */
    protected $status = 'open';

    /**
     * If this is a user conversation, this is the agent assigned.
     *
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $agent = null;

    /**
     * If this is a team chat, the team it is.
     *
     * @var \Application\DeskPRO\Entity\AgentTeam
     */
    protected $agent_team = null;

    /**
     * If this is a user conversation, this is the user who started the chat.
     *
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person = null;

    /**
     * If this is a user convo, this is the users session.
     *
     * @var \Application\DeskPRO\Entity\Session
     */
    protected $session = null;

    /**
     * @var string|null
     */
    protected $visitor_id = null;

    /**
     * User chat: The users name, if they arent a person.
     *
     * @var string
     */
    protected $person_name = '';

    /**
     * User chat: The users email, if they aren`t a person.
     *
     * @var string
     */
    protected $person_email = '';

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $participants;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $messages;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $custom_data;

    /**
     * @var string
     */
    protected $rating_response_time = null;

    /**
     * @var string
     */
    protected $rating_overall = null;

    /**
     * @var string
     */
    protected $rating_comment = '';

    /**
     * Is this an agent chat.
     *
     * @var bool
     */
    protected $is_agent = false;

    /**
     * If the chat is popped out into a window.
     * This is used to make sure the JS widget on pages doesn't load again.
     *
     * @var bool
     */
    protected $is_window = false;

    /**
     * Date when chat was started.
     *
     * @var \DateTime
     */
    protected $date_created;

    /**
     * Since when the user has started waiting (i.e., time the assignment was 0).
     *
     * @var \DateTime
     */
    protected $date_user_waiting = null;

    /**
     * @var \DateTime
     */
    protected $date_assigned;

    /**
     * Date when agent typed last time.
     *
     * @var \DateTime
     */
    protected $date_agent_typing;

    /**
     * @var \DateTime
     */
    protected $date_first_agent_message;

    /**
     * Date when chat was ended.
     *
     * @var \DateTime
     */
    protected $date_ended;

    /**
     * @var int
     */
    protected $total_to_ended = 0;

    /**
     * Who ended the chat.
     *
     * @var string
     *
     * @Assert\NotNull()
     */
    protected $ended_by = '';

    /**
     * True if transcript should be send.
     *
     * @var bool
     */
    protected $should_send_transcript = false;

    /**
     * Date when transcript was sent.
     *
     * @var \DateTime
     */
    protected $date_transcript_sent = null;

    /**
     * @var string
     */
    protected $email_validation_code = '';

    /**
     * @var bool
     */
    protected $email_validated = false;

    /**
     * @var array
     */
    protected $_created_messages = [];

    /**
     * @var null
     */
    protected $_user_participants = null;

    /**
     * @var null
     */
    protected $_agent_participants = null;

    /**
     * @var \Application\DeskPRO\Labels\LabelManager
     */
    protected $_label_manager = null;

    /**
     * @var string
     */
    protected $accessToken;

    public function getChannelId($name = false)
    {
        return 'chat_convo.'.$this->id.($name ? '.'.$name : '');
    }

    public function __construct()
    {
        $this->labels            = new ArrayCollection();
        $this->participants      = new ArrayCollection();
        $this->messages          = new ArrayCollection();
        $this->custom_data       = new ArrayCollection();
        $this->date_created      = new \DateTime();
        $this->date_user_waiting = new \DateTime();
        $this->accessToken       = Strings::random(30, Strings::CHARS_ALPHANUM);
    }

    /**
     * @return \Application\DeskPRO\Labels\LabelManager
     */
    public function getLabelManager()
    {
        if ($this->_label_manager === null) {
            $this->_label_manager = new LabelManager($this, 'DeskPRO:LabelChatConversation');
        }

        return $this->_label_manager;
    }

    /**
     * @static
     *
     * @param \Application\DeskPRO\Entity\Session $session
     *
     * @return \Application\DeskPRO\Entity\ChatConversation
     */
    public static function newForUserSession($session)
    {
        $convo = new self();
        if ($session->person) {
            $convo->person = $session->person;
        }
        $convo->session = $session;

        return $convo;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setPersonName($name)
    {
        $this->setModelField('person_name', $name);

        return $this;
    }

    /**
     * @param string $email
     *
     * @return $this
     */
    public function setPersonEmail($email)
    {
        $this->setModelField('person_email', $email);

        return $this;
    }

    /**
     * Setting the person copies their name and email address to the chat row for record keeping.
     *
     * @param Person $person
     *
     * @return $this
     */
    public function setPerson(Person $person = null)
    {
        $this->setModelField('person', $person);
        $this->setModelField('person_name', $person ? $person->getDisplayName(false) : '');

        if ($person && $person->getPrimaryEmailAddress()) {
            $this->setModelField('person_email', $person->getPrimaryEmailAddress());
        } else {
            $this->setModelField('person_email', '');
        }

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
     * @return null|string
     */
    public function getAuthId()
    {
        return $this->getSession() ? $this->getId().':'.$this->getSession()->getAuth() : null;
    }

    /**
     * Backward compatibility alias for id.
     *
     * @return int
     */
    public function getConversationId()
    {
        return $this->id;
    }

    /**
     * Is there need to validate email?
     *
     * @return bool
     */
    public function getNeedValidateEmail()
    {
        return $this->getEmailValidationCode() && !$this->getEmailValidated();
    }

    /**
     * Create a new message and then add it to this convo.
     *
     * @param string $content
     * @param Person $author
     * @param bool   $is_html
     *
     * @return
     */
    public function addNewMessage($content, $author, $is_html = false)
    {
        $chat_message               = new ChatMessage();
        $chat_message->conversation = $this;
        $chat_message->author       = $author;
        $chat_message['content']    = $content;

        if ($is_html) {
            $chat_message['is_html'] = true;
        }

        return $this->addMessage($chat_message);
    }

    /**
     * Create a new message for a user based on their session.
     *
     * @param $content
     * @param $session
     *
     * @return ChatMessage
     */
    public function addNewMessageForSession($content, $session)
    {
        $chat_message               = new ChatMessage();
        $chat_message->conversation = $this;

        if ($session->person) {
            $chat_message->author = $session->person;
        }

        $chat_message['content'] = $content;

        return $this->addMessage($chat_message);
    }

    /**
     * Add a system message.
     *
     * @param      $content
     * @param bool $is_user_hidden
     *
     * @return ChatMessage
     */
    public function addSystemMessage($content, $is_user_hidden = false)
    {
        $chat_message                   = new ChatMessage();
        $chat_message->conversation     = $this;
        $chat_message['is_sys']         = true;
        $chat_message['is_user_hidden'] = $is_user_hidden;

        $chat_message['content'] = $content;

        return $this->addMessage($chat_message);
    }

    /**
     * Add a message to this convo.
     *
     * @param
     */
    public function addMessage($message)
    {
        if (!$this->date_first_agent_message and $message->author and $message->author['is_agent']) {
            $this['date_first_agent_message'] = new \DateTime();
        }

        $message->conversation = $this;
        $this->messages->add($message);
        $this->_onPropertyChanged('messages', null, $this->messages, true);

        $this->_created_messages[] = $message;

        return $message;
    }

    /**
     * If the person given is the user on the ticket or is a user participant, then they are considered
     * to be participants on the chat. This is used in portal security checks.
     *
     * @param Person $person
     *
     * @return bool
     */
    public function isParticipating(Person $person)
    {
        // if this is the person on the chat
        if ($this->person === $person) {
            return true;
        }

        // or if this is a user participant
        foreach ($this->getUserParticipants() as $participant) {
            if ($participant->getId() === $person->getId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * If the person given is the manager of the organization of a user on the ticket, they
     * will be able to see the chat. This is used in portal security checks.
     *
     * @param Person $person
     *
     * @return bool
     */
    public function isPersonOrganizationManager(Person $person)
    {
        // if this is the person on the chat
        if ($this->person->getOrganization() === $person->getOrganization()
            && $person->organization_manager
        ) {
            return true;
        }

        return false;
    }

    /**
     * Get an array of only user participants.
     *
     * @return array
     */
    public function getUserParticipants()
    {
        if ($this->_user_participants !== null) {
            return $this->_user_participants;
        }

        $this->_user_participants = [];

        foreach ($this->participants as $p) {
            if (!$p['is_agent']) {
                $this->_user_participants[] = $p;
            }
        }

        return $this->_user_participants;
    }

    /**
     * Get an array of only agent participants (and the agent on chat as well).
     *
     * @return array
     */
    public function getAgentParticipants()
    {
        if ($this->_agent_participants !== null) {
            return $this->_agent_participants;
        }

        $this->_agent_participants = [];

        foreach ($this->participants as $p) {
            if ($p['is_agent']) {
                $this->_agent_participants[] = $p;
            }
        }

        if ($this->agent) {
            $this->_agent_participants[] = $this->agent;
        }

        $this->_agent_participants = array_unique($this->_agent_participants);

        return $this->_agent_participants;
    }

    /**
     * Get a simple array of person ID's of participants.
     *
     * @return array
     */
    public function getParticipantIds()
    {
        $ids = [];
        foreach ($this->participants as $p) {
            $ids[] = $p['id'];
        }

        return $ids;
    }

    public function isAgentChat()
    {
        return $this->is_agent;
    }

    /**
     * Check if a person ID or a person object is current a participant.
     *
     * @param  $person_or_id
     *
     * @return bool
     */
    public function hasParticipant($person_or_id)
    {
        $person_id = $person_or_id;
        if ($person_or_id instanceof Person) {
            $person_id = $person_or_id['id'];
        }

        foreach ($this->participants as $p) {
            if ($p['id'] == $person_id) {
                return $p;
            }
        }

        return false;
    }

    /**
     * Add a participant.
     *
     * @param $person_or_id
     *
     * @return Person
     */
    public function addParticipant($person_or_id, $suppress_sys_msg = false)
    {
        $person = $person_or_id;
        if (!($person instanceof Person)) {
            $person = App::getEntityRepository('DeskPRO:Person')->find($person);
        }

        if ($this->hasParticipant($person)) {
            return $person;
        }

        $this->participants->add($person);

        if ($this->_user_participants !== null and !$person['is_agent']) {
            $this->_user_participants[] = $person;
        }

        return $person;
    }

    /**
     * Remove a participant.
     *
     * @param  $person_or_id
     *
     * @return Person
     */
    public function removeParticipant($person_or_id, $suppress_sys_msg = false)
    {
        $person = $person_or_id;
        if (!($person instanceof Person)) {
            $person = App::getEntityRepository('DeskPRO:Person')->find($person);
        }

        foreach ($this->participants as $k => $p) {
            if ($p['id'] == $person['id']) {
                $this->participants->remove($k);

                return $p;
            }
        }

        return;
    }

    /**
     * Set the status (open or ended).
     *
     * @param string $status
     *
     * @return $this
     */
    public function setStatus($status)
    {
        if ($this->status == $status) {
            return $this;
        }

        $this->setModelField('status', $status);

        if ($status == self::STATUS_ENDED) {
            if (!$this->date_ended) {
                $this['date_ended'] = new \DateTime();
            }
        } else {
            if ($this->date_ended) {
                $this['date_ended'] = null;
            }
        }

        return $this;
    }

    /**
     * Set the agent.
     *
     * @param $agent
     *
     * @return $this
     */
    public function setAgent($agent = null)
    {
        if (!$agent) {
            $agent = null;
        }

        $old_agent = $this->agent;
        if (($agent === null && $old_agent === null) || ($agent && $old_agent && $agent->getId() == $old_agent->getId())
        ) {
            return $this;
        }

        $this->_onPropertyChanged('agent', $old_agent, $agent);

        $this->agent = $agent;
        if ($agent and !$this->date_assigned) {
            $this['date_assigned'] = new \DateTime();
        }

        // Make sure the user isn't both assigned and a part
        if ($agent) {
            $this->removeParticipant($agent, true);
        }

        // Automatically add old assigned guy as part
        if ($old_agent) {
            $this->addParticipant($old_agent, true);
        }

        if ($this->agent) {
            $this->setModelField('date_user_waiting', null);
        } else {
            $this->setModelField('date_user_waiting', new \DateTime());
        }

        return $this;
    }

    /**
     * @return Person|null
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @return string|null
     */
    public function getPersonName()
    {
        return $this->person_name;
    }

    /**
     * @return string|null
     */
    public function getPersonEmail()
    {
        return $this->person_email;
    }

    /**
     * @return Person|null
     */
    public function getAgent()
    {
        return $this->agent;
    }

    public function getAgentId()
    {
        if ($this->agent) {
            return $this->agent->id;
        }

        return 0;
    }

    /**
     * @param Department $department
     *
     * @return $this
     */
    public function setDepartment($department)
    {
        $this->setModelField('department', $department);

        return $this;
    }

    /**
     * @return Department|null
     */
    public function getDepartment()
    {
        return $this->department;
    }

    /**
     * Department identity which chat was assigned.
     *
     * @return int
     */
    public function getDepartmentId()
    {
        if ($this->department) {
            return $this->department->id;
        }

        return 0;
    }

    /**
     * @return Brand
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * @param Brand $brand
     *
     * @return ChatConversation
     */
    public function setBrand($brand)
    {
        $this->setModelField('brand', $brand);

        return $this;
    }

    /**
     * @return int
     */
    public function getTaskId()
    {
        return $this->taskId;
    }

    /**
     * @param int $taskId
     *
     * @return $this
     */
    public function setTaskId($taskId)
    {
        $this->setModelField('taskId', $taskId);

        return $this;
    }

    /**
     * @return Person[]
     */
    public function getParticipants()
    {
        return $this->participants;
    }

    /**
     * Department name which chat was assigned.
     *
     * @return string
     */
    public function getDepartmentName()
    {
        return $this->department ? $this->department->getFullTitle() : '';
    }

    public function getCreatedMessages()
    {
        return $this->_created_messages;
    }

    /**
     * @return ArrayCollection|ChatMessage[]
     */
    public function getMessages()
    {
        return $this->messages;
    }

    public function _clearCreatedMessages()
    {
        $this->_created_messages = [];
    }

    /**
     * @param string $subject
     *
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->setModelField('subject', $subject);

        return $this;
    }

    /**
     * Subject line for sending purposes.
     *
     * @return string
     */
    public function getSubjectLine()
    {
        if ($this->subject) {
            return $this->subject;
        }
        if ($this->person_name && $this->person_email) {
            return $this->person_name.' <'.$this->person_email.'>';
        }
        if ($this->person_name) {
            return $this->person_name;
        }
        if ($this->person_email) {
            return $this->person_email;
        }

        return 'Chat '.$this->id;
    }

    public function getSubjectPreview()
    {
        return trim(substr($this->subject, 0, 80)).(strlen($this->subject) > 80 ? '...' : '');
    }

    /**
     * @param int $rating
     *
     * @return $this
     */
    public function setRatingOverall($rating)
    {
        $rating = (int) $rating;
        if ($rating < 1 || $rating > 10) {
            $rating = 0;
        }

        $this->setModelField('rating_overall', $rating);

        return $this;
    }

    /**
     * @param string $rating_comment
     *
     * @return $this
     */
    public function setRatingComment($rating_comment)
    {
        $this->setModelField('rating_comment', $rating_comment);

        return $this;
    }

    /**
     * @return string
     */
    public function getRatingComment()
    {
        return $this->rating_comment;
    }

    /**
     * @return int
     */
    public function getRatingOverall()
    {
        return $this->rating_overall;
    }

    public function setRatingResponseTime($rating)
    {
        if ($rating != 1 && $rating != -1) {
            $rating = 0;
        }
        $this->setModelField('rating_response_time', $rating);
    }

    /**
     * @param \DateTime $date
     *
     * @return $this
     */
    public function setDateEnded(\DateTime $date = null)
    {
        if ($date) {
            $this->setModelField('date_ended', $date);
            $this->setModelField('total_to_ended', $date->getTimestamp() - $this->date_created->getTimestamp());
        } else {
            $this->setModelField('date_ended', null);
            $this->setModelField('total_to_ended', 0);
        }

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateAgentTyping()
    {
        return $this->date_agent_typing;
    }

    /**
     * @return \DateTime
     */
    public function getDateAssigned()
    {
        return $this->date_assigned;
    }

    /**
     * @param \DateTime $date
     *
     * @return $this
     */
    public function setDateAgentTyping(\DateTime $date = null)
    {
        $this->setModelField('date_agent_typing', $date);

        return $this;
    }

    /**
     * Get a basic array of information. These are generally used in templates or with
     * client messages to render the message.
     *
     * @return array
     */
    public function getInfo()
    {
        $info = [];

        $info['conversation_id'] = $this->id;

        if ($this->person) {
            $info['author_id']    = $this->person->id;
            $info['author_name']  = $this->person->display_name;
            $info['author_email'] = $this->person->getPrimaryEmailAddress();
            $info['author_type']  = $this->person->is_agent ? 'agent' : 'user';
        } else {
            $info['author_id']    = 0;
            $info['author_name']  = $this->person_name ? $this->person_name : '';
            $info['author_email'] = $this->person_email ? $this->person_email : '';
            $info['author_type']  = 'user';
        }

        $info['subject_line']    = $this->getSubjectLine();
        $info['agent_id']        = $this->agent ? $this->agent->id : 0;
        $info['agent_name']      = $this->agent ? $this->agent->getDisplayName() : '';
        $info['department_id']   = $this->department_id;
        $info['department_name'] = $this->department ? $this->department->getFullTitle() : '';
        $info['date_created']    = $this->date_created->getTimestamp();

        if ($this->date_ended) {
            $info['date_ended'] = $this->date_ended->getTimestamp();
            $info['ended_by']   = $this->ended_by;
        }

        return $info;
    }

    /**
     * {@inheritdoc}
     */
    public function removeLabel(Label $label)
    {
        if ($this->labels->contains($label)) {
            $this->labels->removeElement($label);
            $this->_onPropertyChanged('labels', $this->labels, $this->labels);
        }
    }

    /**
     * @return LabelChatConversation[]
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
        $this->_onPropertyChanged('labels', $this->labels, $this->labels);
    }

    /**
     * {@inheritdoc}
     */
    public function addLabel(Label $label)
    {
        $label['chat'] = $this;
        $this->labels->add($label);
    }

    /**
     * @param bool $v
     */
    public function setIsAgent($v)
    {
        $this->setModelField('is_agent', $v);
        if ($v) {
            $this->date_user_waiting = null;
        }
    }

    /**
     * Gets the URL to a picture for the person. Note that this will always return
     * a path to an image, even if it's the default.
     *
     * @param int       $size
     * @param null|bool $secure
     *
     * @return null|string
     */
    public function getPersonPictureUrl($size = 80, $secure = null)
    {
        // Null means detect
        if ($secure === null and App::isWebRequest()) {
            $request = App::getRequest();
            if ($request->isSecure()) {
                $secure = true;
            }
        }

        $url = false;
        if ($this->person) {
            $url = $this->person->getPictureUrl($size, $secure);
        }

        if (!$url) {
            if (App::getSetting('core.use_gravatar') && $this->person_email) {
                $hash = md5(strtolower($this->person_email));
                if ($secure) {
                    $url = 'https://secure.gravatar.com/avatar/'.$hash.'?';
                } else {
                    $url = 'http://www.gravatar.com/avatar/'.$hash.'?';
                }
                $url .= 's='.$size.'&d=mm';
            } else {
                $url = App::get('router')->generate(
                    'serve_default_picture',
                    [
                        's'        => $size,
                        'size-fit' => 1,
                    ],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
            }
        }

        if ($secure) {
            $url = preg_replace('#^http:#', 'https:', $url);
        }

        return $url;
    }

    /**
     * Find an existing data record for a field id.
     *
     * @param CustomDefChat|int $field_id
     *
     * @return CustomDataChat
     */
    public function getCustomDataForField($field_id)
    {
        if ($field_id instanceof CustomDefChat) {
            $field_id = $field_id['id'];
        }

        foreach ($this->custom_data as $data) {
            if ($data['field_id'] == $field_id) {
                return $data;
            }
        }

        return;
    }

    /**
     * @param $field
     */
    public function removeCustomDataForField($field)
    {
        $parent_id = null;
        $field_id  = $field['id'];
        if ($field->parent) {
            $parent_id = $field->parent['id'];
        }

        $change = false;
        foreach ($this->custom_data as $data) {
            if ($data['field_id'] == $field_id or $data['field_id'] == $parent_id) {
                $change = true;
                $this->custom_data->removeElement($data);
            }
        }

        if ($change) {
            $this->_onPropertyChanged('custom_data', null, $this->custom_data);
        }
    }

    /**
     * @return ArrayCollection|CustomDataChat[]
     */
    public function getCustomData()
    {
        return $this->custom_data;
    }

    /**
     * Add a custom data item to this chat.
     *
     * @param CustomDataChat $data
     */
    public function addCustomData(CustomDataChat $data)
    {
        $this->custom_data->add($data);
        $data['conversation'] = $this;
        $this->_onPropertyChanged('custom_data', $this->custom_data, $this->custom_data);
    }

    /**
     * Check if this chat has a custom field.
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

        foreach ($this->custom_data as $data) {
            if ($data->field->parent and $data->field->parent['id'] == $field_id) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return \DateTime
     */
    public function getDateFirstAgentMessage()
    {
        return $this->date_first_agent_message;
    }

    /**
     * @param \DateTime $date
     *
     * @return $this
     */
    public function setDateFirstAgentMessage(\DateTime $date)
    {
        $this->setModelField('date_first_agent_message', $date);

        return $this;
    }

    /**
     * @return bool
     */
    public function getShouldSendTranscript()
    {
        return $this->should_send_transcript;
    }

    /**
     * @param bool $value
     *
     * @return $this
     */
    public function setShouldSendTranscript($value)
    {
        $this->setModelField('should_send_transcript', $value);

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateTranscriptSent()
    {
        return $this->date_transcript_sent;
    }

    /**
     * @param \DateTime|null $date
     *
     * @return $this
     */
    public function setDateTranscriptSent(\DateTime $date = null)
    {
        $this->setModelField('date_transcript_sent', $date);

        return $this;
    }

    /**
     * @param \DateTime $date_created
     *
     * @return $this
     */
    public function setDateCreated($date_created)
    {
        $this->setModelField('date_created', $date_created);

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
    public function getDateEnded()
    {
        return $this->date_ended;
    }

    /**
     * @return string
     */
    public function getEndedBy()
    {
        return $this->ended_by;
    }

    /**
     * @param string $ended_by
     *
     * @return $this
     */
    public function setEndedBy($ended_by)
    {
        $this->setModelField('ended_by', $ended_by);

        return $this;
    }

    /**
     * @return Session
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * @return $this
     */
    public function regenerateEmailValidationCode()
    {
        return $this->setEmailValidationCode(Strings::random(15, Strings::CHARS_KEY));
    }

    /**
     * @param string $code
     *
     * @return $this
     */
    public function setEmailValidationCode($code)
    {
        $this->setModelField('email_validation_code', $code);

        return $this;
    }

    /**
     * @return string
     */
    public function getEmailValidationCode()
    {
        return $this->email_validation_code;
    }

    /**
     * @return null|string
     */
    public function getVisitorId()
    {
        return $this->visitor_id;
    }

    /**
     * @param null|string $visitor_id
     *
     * @return $this
     */
    public function setVisitorId($visitor_id)
    {
        $this->setModelField('visitor_id', $visitor_id);

        return $this;
    }

    /**
     * @return bool
     */
    public function getEmailValidated()
    {
        return $this->email_validated;
    }

    /**
     * @param bool $value
     *
     * @return $this
     */
    public function setEmailValidated($value)
    {
        $this->setModelField('email_validated', $value);

        return $this;
    }

    /**
     * @param CustomDataChat[] $data
     */
    public function setCustomData($data)
    {
        $this->custom_data = $data;
        foreach ($data as $datum) {
            /* @var CustomDataChat $datum */
            $datum->setConversation($this);
        }

        $this->_onPropertyChanged('custom_data', null, $this->custom_data);
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @param string $accessToken
     *
     * @return $this
     */
    public function setAccessToken($accessToken)
    {
        $this->setModelField('accessToken', $accessToken);

        return $this;
    }

    public function toApiData($primary = true, $deep = true, array $visited = [])
    {
        $data = parent::toApiData($primary, $deep, $visited);
        if ($deep) {
            $data['labels'] = [];
            foreach ($this->labels as $label) {
                $data['labels'][] = $label['label'];
            }
        }

        return $data;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\ChatConversation';
        $metadata->setPrimaryTable(
            [
                'name'    => 'chat_conversations',
                'indexes' => [
                    'status_idx'                 => ['columns' => ['status']],
                    'should_send_transcript_idx' => ['columns' => ['should_send_transcript']],
                    'access_token_idx'           => ['columns' => ['access_token']],
                ],
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'id',
                'type'       => 'integer',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'id',
                'id'         => true,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'subject',
                'type'       => 'string',
                'length'     => 255,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'subject',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'status',
                'type'       => 'string',
                'length'     => 15,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'status',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'person_name',
                'type'       => 'string',
                'length'     => 255,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'person_name',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'person_email',
                'type'       => 'string',
                'length'     => 255,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'person_email',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'rating_response_time',
                'type'       => 'integer',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'rating_response_time',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'rating_overall',
                'type'       => 'integer',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'rating_overall',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'rating_comment',
                'type'       => 'text',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'rating_comment',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'is_agent',
                'type'       => 'boolean',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'is_agent',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'is_window',
                'type'       => 'boolean',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'is_window',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'date_created',
                'type'       => 'datetime',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'date_created',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'date_user_waiting',
                'type'       => 'datetime',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'date_user_waiting',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'date_assigned',
                'type'       => 'datetime',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'date_assigned',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'date_agent_typing',
                'type'       => 'datetime',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'date_agent_typing',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'date_first_agent_message',
                'type'       => 'datetime',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'date_first_agent_message',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'date_ended',
                'type'       => 'datetime',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'date_ended',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'should_send_transcript',
                'type'       => 'boolean',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'should_send_transcript',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'date_transcript_sent',
                'type'       => 'datetime',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'date_transcript_sent',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'total_to_ended',
                'type'       => 'integer',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'total_to_ended',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'ended_by',
                'type'       => 'string',
                'length'     => 15,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'ended_by',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'email_validation_code',
                'type'       => 'string',
                'length'     => 15,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'email_validation_code',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'email_validated',
                'type'       => 'boolean',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'email_validated',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'visitor_id',
                'type'       => 'string',
                'length'     => 120,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'visitor_id',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'taskId',
                'type'       => 'integer',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'task_id',
            ]
        );

        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'department',
                'targetEntity' => Department::class,
                'mappedBy'     => null,
                'inversedBy'   => null,
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'department_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'set null',
                        'columnDefinition'     => null,
                    ],
                ],
                'dpApi' => true,
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'brand',
                'targetEntity' => Brand::class,
                'mappedBy'     => null,
                'inversedBy'   => null,
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'brand_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'set null',
                        'columnDefinition'     => null,
                    ],
                ],
                'dpApi' => true,
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'agent',
                'targetEntity' => Person::class,
                'mappedBy'     => null,
                'inversedBy'   => null,
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'agent_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'set null',
                        'columnDefinition'     => null,
                    ],
                ],
                'dpApi' => true,
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'agent_team',
                'targetEntity' => AgentTeam::class,
                'mappedBy'     => null,
                'inversedBy'   => null,
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'agent_team_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'set null',
                        'columnDefinition'     => null,
                    ],
                ],
                'dpApi' => true,
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'person',
                'targetEntity' => Person::class,
                'cascade'      => ['persist'],
                'mappedBy'     => null,
                'inversedBy'   => null,
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'person_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'set null',
                        'columnDefinition'     => null,
                    ],
                ],
                'dpApi' => true,
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'session',
                'targetEntity' => Session::class,
                'mappedBy'     => null,
                'inversedBy'   => null,
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'session_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'set null',
                        'columnDefinition'     => null,
                    ],
                ],
            ]
        );
        $metadata->mapManyToMany(
            [
                'fieldName'    => 'participants',
                'targetEntity' => Person::class,
                'inversedBy'   => 'chats',
                'joinTable'    => [
                    'name'        => 'chat_conversation_to_person',
                    'schema'      => null,
                    'joinColumns' => [
                        0 => [
                            'name'                 => 'conversation_id',
                            'referencedColumnName' => 'id',
                            'nullable'             => true,
                            'onDelete'             => 'cascade',
                            'columnDefinition'     => null,
                        ],
                    ],
                    'inverseJoinColumns' => [
                        0 => [
                            'name'                 => 'person_id',
                            'referencedColumnName' => 'id',
                            'nullable'             => true,
                            'onDelete'             => 'cascade',
                            'columnDefinition'     => null,
                        ],
                    ],
                ],
                'indexBy' => 'id',
                'dpApi'   => true,
            ]
        );
        $metadata->mapOneToMany(
            [
                'fieldName'    => 'messages',
                'targetEntity' => ChatMessage::class,
                'cascade'      => [0 => 'remove', 1 => 'persist', 3 => 'merge'],
                'mappedBy'     => 'conversation',
            ]
        );
        $metadata->mapOneToMany(
            [
                'fieldName'     => 'custom_data',
                'targetEntity'  => CustomDataChat::class,
                'cascade'       => [0 => 'remove', 1 => 'persist', 3 => 'merge'],
                'mappedBy'      => 'conversation',
                'orphanRemoval' => true,
                'dpApi'         => true,
            ]
        );
        $metadata->mapOneToMany(
            [
                'fieldName'     => 'labels',
                'targetEntity'  => LabelChatConversation::class,
                'cascade'       => [0 => 'remove', 1 => 'persist', 3 => 'merge'],
                'mappedBy'      => 'chat',
                'orphanRemoval' => true,
                'dpApi'         => true,
            ]
        );

        $metadata->mapField(
            [
                'fieldName'  => 'accessToken',
                'type'       => 'string',
                'length'     => 30,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'access_token',
            ]
        );
    }
}
