<?php

namespace DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Entity\Person;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Class VoicePhoneCallLog.
 *
 * @ORM\Entity(repositoryClass="DeskPRO\Bundle\AppBundle\Entity\Repository\VoicePhoneCallLogRepository")
 * @ORM\Table(name="voice_phone_call_logs")
 *
 * @JMS\ExclusionPolicy("all")
 */
class VoicePhoneCallLog implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    const ACTION_NEW_INCOMING                         = 'call.new_incoming';
    const ACTION_NEW_OUTGOING                         = 'call.new_outgoing';
    const ACTION_AUTO_ATTENDANT_PRESS_KEY             = 'call.auto_attendant_press_key';
    const ACTION_AUTO_ATTENDANT_PRESS_UNSUPPORTED_KEY = 'call.auto_attendant_press_unsupported_key';
    const ACTION_AUTO_ATTENDANT_PRESS_REPEAT_KEY      = 'call.auto_attendant_press_repeat_key';
    const ACTION_AUTO_ATTENDANT_PRESS_EXTENSION_KEY   = 'call.auto_attendant_press_extension_key';
    const ACTION_AUTO_ATTENDANT_EXTENSION             = 'call.auto_attendant_extension';
    const ACTION_CALL_TARGET                          = 'call.target';
    const ACTION_REJECTED                             = 'call.rejected';
    const ACTION_ANSWERED                             = 'call.answered';
    const ACTION_FORWARD_ANSWERED                     = 'call.forward_answered';
    const ACTION_MUTED                                = 'call.participant_muted';
    const ACTION_UNMUTED                              = 'call.participant_unmuted';
    const ACTION_HOLD                                 = 'call.participant_hold';
    const ACTION_UNHOLD                               = 'call.participant_unhold';
    const ACTION_AGENT_INVITED                        = 'call.agent_invited';
    const ACTION_AGENT_TRANSFER                       = 'call.agent_transfer';
    const ACTION_USER_JOINED                          = 'call.user_joined';
    const ACTION_AGENT_JOINED                         = 'call.agent_joined';
    const ACTION_AGENT_CANCEL_INVITE                  = 'call.agent_cancel_invite';
    const ACTION_AGENT_IGNORE_INVITE                  = 'call.agent_ignore_invite';
    const ACTION_AGENT_LEFT                           = 'call.agent_left';
    const ACTION_USER_LEFT                            = 'call.user_left';
    const ACTION_USER_DISCONNECTED                    = 'call.user_disconnected';
    const ACTION_AGENT_DISCONNECTED                   = 'call.agent_disconnected';
    const ACTION_AGENT_HANGUP                         = 'call.agent_hangup';
    const ACTION_STARTED                              = 'call.started';
    const ACTION_ENDED                                = 'call.ended';
    const ACTION_RECORDING_DOWNLOADED                 = 'call.recording_downloaded';
    const ACTION_RECORDING_DELETED                    = 'call.recording_deleted';

    /**
     * The unique ID.
     *
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue()
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall", inversedBy="phoneCallLogs")
     * @ORM\JoinColumn(name="phone_call_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @var VoicePhoneCall
     */
    private $phoneCall;

    /**
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\Person")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     *
     * @var Person
     */
    private $person;

    /**
     * @ORM\Column(name="action_type", type="string", length=100)
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    private $actionType;

    /**
     * @ORM\Column(type="json_array", nullable=false)
     *
     * @JMS\Expose()
     * @JMS\Type("array")
     *
     * @var array
     */
    private $details = [];

    /**
     * @ORM\Column(name="date_created", type="datetime")
     *
     * @JMS\Expose()
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    private $dateCreated;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->dateCreated = new \DateTime();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return VoicePhoneCall
     */
    public function getPhoneCall()
    {
        return $this->phoneCall;
    }

    /**
     * @param VoicePhoneCall $phoneCall
     *
     * @return $this
     */
    public function setPhoneCall(VoicePhoneCall $phoneCall = null)
    {
        $this->setModelField('phoneCall', $phoneCall);

        return $this;
    }

    /**
     * @return Person
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @param Person $person
     *
     * @return $this
     */
    public function setPerson(Person $person = null)
    {
        $this->setModelField('person', $person);

        return $this;
    }

    /**
     * @return string
     */
    public function getActionType()
    {
        return $this->actionType;
    }

    /**
     * @param string $actionType
     *
     * @return $this
     */
    public function setActionType($actionType)
    {
        $this->setModelField('actionType', $actionType);

        return $this;
    }

    /**
     * @return array
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * @param array $details
     *
     * @return $this
     */
    public function setDetails(array $details)
    {
        $this->setModelField('details', $details);

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     * @param \DateTime $dateCreated
     *
     * @return $this
     */
    public function setDateCreated(\DateTime $dateCreated = null)
    {
        $this->setModelField('dateCreated', $dateCreated);

        return $this;
    }
}
