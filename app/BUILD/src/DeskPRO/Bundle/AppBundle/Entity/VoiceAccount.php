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
 *   @ORM\UniqueConstraint(name="account_sid", columns={"account_sid"})
 * })
 * @ORM\EntityListeners({"DeskPRO\Bundle\AppBundle\EventListener\Doctrine\Voice\VoiceAccountListener"})
 *
 * @JMS\ExclusionPolicy("all")
 *
 * @AppAssert\Voice\VoiceAccount()
 * @UniqueEntity("accountSid")
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
