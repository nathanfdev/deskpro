<?php

namespace DeskPRO\Bundle\AppBundle\Entity;

use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Orb\Util\Strings;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class VoiceAccount.
 *
 * @ORM\Entity(repositoryClass="DeskPRO\Bundle\AppBundle\Entity\Repository\VoiceAccountRepository")
 * @ORM\Table(name="voice_accounts", uniqueConstraints={
 *   @ORM\UniqueConstraint(name="account_sid", columns={"account_sid"}),
 *   @ORM\UniqueConstraint(name="workspace_sid", columns={"workspace_sid"}),
 * })
 * @ORM\EntityListeners({"DeskPRO\Bundle\AppBundle\EventListener\Doctrine\Voice\VoiceAccountListener"})
 *
 * @JMS\ExclusionPolicy("all")
 *
 * @AppAssert\Voice\VoiceAccount()
 * @UniqueEntity("accountSid")
 * @UniqueEntity("workspaceSid")
 */
class VoiceAccount implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

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
     * @ORM\Column(name="account_name", type="string", length=255)
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @Assert\NotBlank()
     *
     * @var string
     */
    private $accountName;

    /**
     * @ORM\Column(name="account_sid", type="string", length=100)
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @Assert\NotBlank()
     *
     * @var string
     */
    private $accountSid;

    /**
     * @ORM\Column(name="workspace_sid", type="string", length=100)
     *
     * @var string
     */
    private $workspaceSid;

    /**
     * @ORM\Column(name="queue_workflow_sid", type="string", length=100, nullable=true)
     *
     * @var string
     */
    private $queueWorkflowSid;

    /**
     * @ORM\Column(name="voicemail_queue_sid", type="string", length=100, nullable=true)
     *
     * @var string
     */
    private $voicemailQueueSid;

    /**
     * @ORM\Column(name="voicemail_worker_sid", type="string", length=100, nullable=true)
     *
     * @var string
     */
    private $voicemailWorkerSid;

    /**
     * @ORM\Column(name="twiml_app_sid", type="string", length=100, nullable=true)
     *
     * @var string
     */
    private $twimlAppSid;

    /**
     * @ORM\Column(name="auth_token", type="string", length=100)
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @Assert\NotBlank()
     *
     * @var string
     */
    private $authToken;

    /**
     * @ORM\Column(name="account_auth", type="string", length=20)
     *
     * @var string
     */
    private $accountAuth;

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
     * @ORM\Column(name="date_sync", type="datetime", nullable=true)
     *
     * @JMS\Expose()
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    private $dateSync;

    /**
     * @ORM\Column(name="date_last_sync", type="datetime", nullable=true)
     *
     * @JMS\Expose()
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    private $dateLastSync;

    /**
     * @ORM\OneToMany(targetEntity="VoiceNumber", mappedBy="account", cascade={"persist", "remove"})
     *
     * @var VoiceNumber[]
     */
    private $numbers;

    /**
     * @ORM\OneToMany(targetEntity="VoiceQueue", mappedBy="account", cascade={"persist", "remove"})
     *
     * @var VoiceQueue[]
     */
    private $queues;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->dateCreated = new \DateTime();
        $this->numbers     = new ArrayCollection();
        $this->queues      = new ArrayCollection();
        $this->accountAuth = Strings::random(20);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getAccountName()
    {
        return $this->accountName;
    }

    /**
     * @param string $accountName
     *
     * @return $this
     */
    public function setAccountName($accountName)
    {
        $this->setModelField('accountName', $accountName);

        return $this;
    }

    /**
     * @return string
     */
    public function getAccountSid()
    {
        return $this->accountSid;
    }

    /**
     * @param string $accountSid
     *
     * @return $this
     */
    public function setAccountSid($accountSid)
    {
        $this->setModelField('accountSid', $accountSid);

        return $this;
    }

    /**
     * @return string
     */
    public function getQueueWorkflowSid()
    {
        return $this->queueWorkflowSid;
    }

    /**
     * @param string $queueWorkflowSid
     *
     * @return $this
     */
    public function setQueueWorkflowSid($queueWorkflowSid)
    {
        $this->setModelField('queueWorkflowSid', $queueWorkflowSid);

        return $this;
    }

    /**
     * @return string
     */
    public function getVoicemailQueueSid()
    {
        return $this->voicemailQueueSid;
    }

    /**
     * @param string $voicemailQueueSid
     *
     * @return $this
     */
    public function setVoicemailQueueSid($voicemailQueueSid)
    {
        $this->setModelField('voicemailQueueSid', $voicemailQueueSid);

        return $this;
    }

    /**
     * @return string
     */
    public function getVoicemailWorkerSid()
    {
        return $this->voicemailWorkerSid;
    }

    /**
     * @param string $voicemailWorkerSid
     *
     * @return $this
     */
    public function setVoicemailWorkerSid($voicemailWorkerSid)
    {
        $this->setModelField('voicemailWorkerSid', $voicemailWorkerSid);

        return $this;
    }

    /**
     * @return string
     */
    public function getWorkspaceSid()
    {
        return $this->workspaceSid;
    }

    /**
     * @param string $workspaceSid
     *
     * @return $this
     */
    public function setWorkspaceSid($workspaceSid)
    {
        $this->setModelField('workspaceSid', $workspaceSid);

        return $this;
    }

    /**
     * @return string
     */
    public function getTwimlAppSid()
    {
        return $this->twimlAppSid;
    }

    /**
     * @param string $twimlAppSid
     *
     * @return $this
     */
    public function setTwimlAppSid($twimlAppSid)
    {
        $this->setModelField('twimlAppSid', $twimlAppSid);

        return $this;
    }

    /**
     * @return string
     */
    public function getAuthToken()
    {
        return $this->authToken;
    }

    /**
     * @param string $authToken
     *
     * @return $this
     */
    public function setAuthToken($authToken)
    {
        $this->setModelField('authToken', $authToken);

        return $this;
    }

    /**
     * @return string
     */
    public function getAccountAuth()
    {
        return $this->accountAuth;
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

    /**
     * @return mixed
     */
    public function getDateSync()
    {
        return $this->dateSync;
    }

    /**
     * @param \DateTime $dateSync
     *
     * @return $this
     */
    public function setDateSync(\DateTime $dateSync = null)
    {
        $this->setModelField('dateSync', $dateSync);

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateLastSync()
    {
        return $this->dateLastSync;
    }

    /**
     * @param \DateTime $dateLastSync
     *
     * @return $this
     */
    public function setDateLastSync(\DateTime $dateLastSync = null)
    {
        $this->setModelField('dateLastSync', $dateLastSync);

        return $this;
    }

    /**
     * @return VoiceQueue[]|ArrayCollection
     */
    public function getQueues()
    {
        return $this->queues;
    }

    /**
     * @return VoiceNumber[]|ArrayCollection
     */
    public function getNumbers()
    {
        return $this->numbers;
    }
}
