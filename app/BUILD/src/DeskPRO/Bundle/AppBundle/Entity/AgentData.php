<?php

namespace DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\VoiceAsset\AbstractVoiceAsset;
use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class AgentData.
 *
 * @ORM\Entity()
 * @ORM\Table(name="agent_data", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="unique_extension_numbers", columns={"extension_number"})
 * })
 * @ORM\EntityListeners({
 *     "DeskPRO\Bundle\AppBundle\EventListener\Doctrine\Voice\VoiceWorkerListener",
 *     "DeskPRO\Bundle\AppBundle\EventListener\Doctrine\Voice\VoiceSettingsListener"
 * })
 *
 * @JMS\ExclusionPolicy("all")
 *
 * @UniqueEntity(fields={"extensionNumber"}, errorPath="extensionNumber")
 */
class AgentData implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    const AVAILABLE_STATUS_IDLE          = 'idle';
    const AVAILABLE_STATUS_IDLE_DISABLED = 'idle_disabled';
    const AVAILABLE_STATUS_BUSY          = 'busy';
    const AVAILABLE_STATUS_RESERVED      = 'reserved';
    const AVAILABLE_STATUS_OFFLINE       = 'offline';

    /**
     * The unique ID.
     *
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue()
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="Application\DeskPRO\Entity\Person", mappedBy="agentData")
     *
     * @var Person
     */
    private $person;

    /**
     * @ORM\Column(name="extension_number", type="integer", nullable=true)
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @AppAssert\Voice\VoiceExtension()
     *
     * @var int
     */
    private $extensionNumber;

    /**
     * @ORM\OneToOne(targetEntity="DeskPRO\Bundle\AppBundle\Entity\VoiceAsset\AbstractVoiceAsset", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(name="voicemail_asset_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @JMS\Expose()
     *
     * @Assert\Valid()
     *
     * @var AbstractVoiceAsset
     */
    private $voicemailAsset;

    /**
     * @ORM\Column(name="is_voice_enabled", type="boolean")
     *
     * @JMS\Expose()
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $isVoiceEnabled = false;

    /**
     * @ORM\Column(name="voice_worker_sid", type="string", length=100, nullable=true)
     *
     * @var string
     */
    private $voiceWorkerSid;

    /**
     * @ORM\Column(name="voice_task_queue_sid", type="string", length=100, nullable=true)
     *
     * @var string
     */
    private $voiceTaskQueueSid;

    /**
     * @ORM\Column(name="available_status", type="string",length=100)
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @Assert\NotBlank()
     * @Assert\Choice(choices={"idle", "idle_disabled", "busy", "reserved", "offline"})
     *
     * @var bool
     */
    private $availableStatus = self::AVAILABLE_STATUS_OFFLINE;

    /**
     * @ORM\Column(name="agent_calls_enabled", type="boolean")
     *
     * @JMS\Expose()
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $agentCallsEnabled = false;

    /**
     * @ORM\Column(name="outbound_calls_enabled", type="boolean")
     *
     * @JMS\Expose()
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $outboundCallsEnabled = false;

    /**
     * @ORM\Column(name="can_use_forwarding", type="boolean")
     *
     * @JMS\Expose()
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $canUseForwarding = false;

    /**
     * @ORM\Column(name="agent_can_use_forwarding", type="boolean")
     *
     * @JMS\Expose()
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $agentCanUseForwarding = false;

    /**
     * @ORM\Column(name="forwarding_number", type="string", length=50, nullable=true)
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @AppAssert\CallNumber()
     *
     * @var string
     */
    private $forwardingNumber;

    /**
     * @ORM\Column(name="forwarding_number_type", type="string", length=50, nullable=true)
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    private $forwardingNumberType;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
        if ($person) {
            $person->setAgentData($this);
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getExtensionNumber()
    {
        return $this->extensionNumber;
    }

    /**
     * @param int $extensionNumber
     *
     * @return $this
     */
    public function setExtensionNumber($extensionNumber)
    {
        $this->setModelField('extensionNumber', $extensionNumber);

        return $this;
    }

    /**
     * @return AbstractVoiceAsset
     */
    public function getVoicemailAsset()
    {
        return $this->voicemailAsset;
    }

    /**
     * @param AbstractVoiceAsset $voicemailAsset
     *
     * @return $this
     */
    public function setVoicemailAsset(AbstractVoiceAsset $voicemailAsset = null)
    {
        $this->setModelField('voicemailAsset', $voicemailAsset);

        return $this;
    }

    /**
     * @return bool
     */
    public function isVoiceEnabled()
    {
        return $this->isVoiceEnabled;
    }

    /**
     * @param bool $isVoiceEnabled
     *
     * @return $this
     */
    public function setIsVoiceEnabled($isVoiceEnabled)
    {
        $this->setModelField('isVoiceEnabled', $isVoiceEnabled);

        return $this;
    }

    /**
     * @return string
     */
    public function getVoiceWorkerSid()
    {
        return $this->voiceWorkerSid;
    }

    /**
     * @param string $voiceWorkerSid
     *
     * @return $this
     */
    public function setVoiceWorkerSid($voiceWorkerSid)
    {
        $this->setModelField('voiceWorkerSid', $voiceWorkerSid);

        return $this;
    }

    /**
     * @return string
     */
    public function getVoiceTaskQueueSid()
    {
        return $this->voiceTaskQueueSid;
    }

    /**
     * @param string $voiceTaskQueueSid
     *
     * @return $this
     */
    public function setVoiceTaskQueueSid($voiceTaskQueueSid)
    {
        $this->setModelField('voiceTaskQueueSid', $voiceTaskQueueSid);

        return $this;
    }

    /**
     * @return bool
     */
    public function getAvailableStatus()
    {
        return $this->availableStatus;
    }

    /**
     * @param bool $availableStatus
     *
     * @return $this
     */
    public function setAvailableStatus($availableStatus)
    {
        $this->setModelField('availableStatus', $availableStatus);

        return $this;
    }

    /**
     * @return bool
     */
    public function isAgentCallsEnabled()
    {
        return $this->agentCallsEnabled;
    }

    /**
     * @param bool $agentCallsEnabled
     *
     * @return $this
     */
    public function setAgentCallsEnabled($agentCallsEnabled)
    {
        $this->setModelField('agentCallsEnabled', $agentCallsEnabled);

        return $this;
    }

    /**
     * @return bool
     */
    public function isOutboundCallsEnabled()
    {
        return $this->outboundCallsEnabled;
    }

    /**
     * @param bool $outboundCallsEnabled
     *
     * @return $this
     */
    public function setOutboundCallsEnabled($outboundCallsEnabled)
    {
        $this->setModelField('outboundCallsEnabled', $outboundCallsEnabled);

        return $this;
    }

    /**
     * @return bool
     */
    public function canUseForwarding()
    {
        return $this->canUseForwarding;
    }

    /**
     * @param bool $canUseForwarding
     *
     * @return $this
     */
    public function setCanUseForwarding($canUseForwarding)
    {
        $this->setModelField('canUseForwarding', $canUseForwarding);

        return $this;
    }

    /**
     * @return bool
     */
    public function agentCanUseForwarding()
    {
        return $this->agentCanUseForwarding;
    }

    /**
     * @param bool $agentCanUseForwarding
     *
     * @return $this
     */
    public function setAgentCanUseForwarding($agentCanUseForwarding)
    {
        $this->setModelField('agentCanUseForwarding', $agentCanUseForwarding);

        return $this;
    }

    /**
     * @return string
     */
    public function getForwardingNumber()
    {
        return $this->forwardingNumber;
    }

    /**
     * @param string $forwardingNumber
     *
     * @return $this
     */
    public function setForwardingNumber($forwardingNumber)
    {
        if ($forwardingNumber) {
            if (preg_match('/^sip:/', $forwardingNumber)) {
                $this->setModelField('forwardingNumberType', VoicePhoneCall::EXTERNAL_NUMBER_TYPE_SIP);
            } else {
                $this->setModelField('forwardingNumberType', VoicePhoneCall::EXTERNAL_NUMBER_TYPE_PHONE);
            }
        } else {
            $this->setModelField('forwardingNumberType', '');
        }

        $this->setModelField('forwardingNumber', $forwardingNumber);

        return $this;
    }

    /**
     * @return string
     */
    public function getForwardingNumberType()
    {
        return $this->forwardingNumberType;
    }
}
