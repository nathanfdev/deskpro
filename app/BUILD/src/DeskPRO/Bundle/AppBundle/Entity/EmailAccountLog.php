<?php

namespace DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\EmailAccount;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class EmailAccountLog.
 *
 * @ORM\Entity()
 * @ORM\Table(name="email_account_logs")
 */
class EmailAccountLog implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    /**
     * Protocols.
     */
    const PROTO_POP3     = 'pop3',
          PROTO_IMAP     = 'imap',
          PROTO_EXCHANGE = 'exchange';

    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\EmailAccount", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="email_account_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     *
     * @Assert\NotNull()
     *
     * @var EmailAccount
     */
    private $emailAccount;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     *
     * @Assert\NotNull()
     *
     * @var string
     */
    private $protocol;

    /**
     * @ORM\OneToOne(targetEntity="Application\DeskPRO\Entity\Blob")
     * @ORM\JoinColumn(name="blob_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     *
     * @var Blob
     */
    private $blob;

    /**
     * @ORM\Column(name="num_emails", type="integer", nullable=true)
     *
     * @var int
     */
    private $numEmails;

    /**
     * @ORM\Column(name="date_created", type="datetime", nullable=false)
     *
     * @var \DateTime
     */
    protected $dateCreated;

    /**
     * @var array
     */
    private $supportedProtocols = [
        self::PROTO_POP3,
        self::PROTO_IMAP,
        self::PROTO_EXCHANGE,
    ];

    /**
     * EmailAccountLog constructor.
     *
     * @param EmailAccount $emailAccount
     * @param $protocol
     * @param \DateTime|null $dateCreated
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(EmailAccount $emailAccount, $protocol, \DateTime $dateCreated = null)
    {
        $this->setEmailAccount($emailAccount);
        $this->setProtocol($protocol);

        // set dateCreated if provided
        $this->setDateCreated(
            !is_null($dateCreated) ? $dateCreated : new \DateTime()
        );
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return EmailAccount
     */
    public function getEmailAccount()
    {
        return $this->emailAccount;
    }

    /**
     * @param EmailAccount $emailAccount
     *
     * @return $this
     */
    public function setEmailAccount(EmailAccount $emailAccount)
    {
        $this->setModelField('emailAccount', $emailAccount);

        return $this;
    }

    /**
     * @return string
     */
    public function getProtocol()
    {
        return $this->protocol;
    }

    /**
     * @param string $protocol
     *
     * @throws \InvalidArgumentException
     *
     * @return $this
     */
    public function setProtocol($protocol)
    {
        if (!in_array($protocol, $this->supportedProtocols)) {
            throw new \InvalidArgumentException('Invalid protocol provided');
        }

        $this->setModelField('protocol', $protocol);

        return $this;
    }

    /**
     * @return Blob
     */
    public function getBlob()
    {
        return $this->blob;
    }

    /**
     * @param Blob $blob
     *
     * @return $this
     */
    public function setBlob(Blob $blob)
    {
        $this->setModelField('blob', $blob);

        return $this;
    }

    /**
     * @return int
     */
    public function getNumEmails()
    {
        return $this->numEmails;
    }

    /**
     * @param int $numEmails
     *
     * @return $this
     */
    public function setNumEmails($numEmails)
    {
        $this->setModelField('numEmails', $numEmails);

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
    public function setDateCreated(\DateTime $dateCreated)
    {
        $this->setModelField('dateCreated', $dateCreated);

        return $this;
    }
}
