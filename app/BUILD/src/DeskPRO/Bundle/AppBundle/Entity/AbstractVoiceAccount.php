<?php

namespace DeskPRO\Bundle\AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use Orb\Util\Strings;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class AbstractVoiceAccount.
 *
 * @ORM\Entity(repositoryClass="DeskPRO\Bundle\AppBundle\Entity\Repository\VoiceAccountRepository")
 * @ORM\Table(name="voice_accounts", uniqueConstraints={
 *   @ORM\UniqueConstraint(name="account_id", columns={"account_id", "type"})
 * })
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string", length=30)
 * @ORM\DiscriminatorMap({
 *   "twilio" = "TwilioVoiceAccount",
 *   "plivo" = "PlivoVoiceAccount"
 * })
 *
 * todo add the "type" column to the unique constraint somehow too
 * @UniqueEntity("accountId")
 */
abstract class AbstractVoiceAccount implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    /**
     * The unique ID.
     *
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue()
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(name="account_id", type="string", length=100)
     *
     * @Assert\NotBlank()
     *
     * @var string
     */
    protected $accountId;

    /**
     * @ORM\Column(name="auth_token", type="string", length=100)
     *
     * @Assert\NotBlank()
     *
     * @var string
     */
    protected $authToken;

    /**
     * @ORM\Column(name="account_name", type="string", length=255)
     *
     * @Assert\NotBlank()
     *
     * @var string
     */
    protected $accountName;

    /**
     * @ORM\Column(name="account_auth", type="string", length=20)
     *
     * @var string
     */
    protected $accountAuth;

    /**
     * @ORM\Column(name="date_created", type="datetime")
     *
     * @var \DateTime
     */
    protected $dateCreated;

    /**
     * @ORM\OneToMany(targetEntity="VoiceNumber", mappedBy="account", cascade={"persist", "remove"})
     *
     * @var VoiceNumber[]
     */
    protected $numbers;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->dateCreated = new \DateTime();
        $this->numbers     = new ArrayCollection();
        $this->accountAuth = Strings::random(20);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getAccountId()
    {
        return $this->accountId;
    }

    /**
     * @param string $accountId
     *
     * @return $this
     */
    public function setAccountId($accountId)
    {
        $this->setModelField('accountId', $accountId);

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
     * @return VoiceNumber[]|ArrayCollection
     */
    public function getNumbers()
    {
        return $this->numbers;
    }
}
