<?php

namespace DeskPRO\Bundle\AppBundle\Entity\Approval;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\EntityInterface;
use DeskPRO\Bundle\AppBundle\Entity\NotifyPropertyChangedTrait;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Class ApprovalType
 *
 * @ORM\Entity
 * @ORM\Table(name="approval_responses")
 * @ORM\ChangeTrackingPolicy("NOTIFY")
 * @ORM\InheritanceType("NONE")
 *
 * @JMS\ExclusionPolicy("all")
 *
 * @category Entities
 */
class ApprovalResponse implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    /**
     * Vote types
     */
    const VOTE_APPROVE = 1;
    const VOTE_REJECT = -1;

    /**
     * Vote type to pretty name map
     *
     * @var array
     */
    private static $voteTypeMap = [
        self::VOTE_APPROVE => 'approve',
        self::VOTE_REJECT => 'reject',
    ];

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="bigint", nullable=false)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @JMS\Expose
     * @JMS\Type("integer")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="vote", type="smallint", length=1, nullable=false)
     *
     * @JMS\Expose
     * @JMS\Type("integer")
     */
    private $vote;

    /**
     * @var string|null
     *
     * @ORM\Column(name="message", type="text", nullable=true)
     *
     * @JMS\Expose
     * @JMS\Type("string")
     */
    private $message;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     *
     * @JMS\Expose
     * @JMS\Type("DateTime<'c'>")
     */
    private $createdAt;

    /**
     * @var Person
     *
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\Person")
     * @ORM\JoinColumn(name="approver_id", nullable=false, onDelete="CASCADE")
     *
     * @JMS\Expose
     * @JMS\Type("integer")
     * @JMS\Accessor(getter="getApproverId")
     */
    private $approver;

    /**
     * @var AbstractBaseApproval
     *
     * @ORM\ManyToOne(targetEntity="DeskPRO\Bundle\AppBundle\Entity\Approval\AbstractBaseApproval", inversedBy="responses")
     * @ORM\JoinColumn(name="approval_id", nullable=false, onDelete="CASCADE")
     *
     * @JMS\Expose
     * @JMS\Type("integer")
     * @JMS\Accessor(getter="getApprovalId")
     */
    private $approval;

    /**
     * ApprovalResponse constructor.
     *
     * @throws \Exception
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * @param Person $approver
     * @return ApprovalResponse
     * @throws \Exception
     */
    public static function createApprovalResponse(Person $approver)
    {
        $response = new self();
        $response->setVote(self::VOTE_APPROVE);
        $response->setApprover($approver);

        return $response;
    }

    /**
     * @param Person $approver
     * @return ApprovalResponse
     * @throws \Exception
     */
    public static function createRejectionResponse(Person $approver)
    {
        $response = new self();
        $response->setVote(self::VOTE_REJECT);
        $response->setApprover($approver);

        return $response;
    }

    /**
     * {@inheritDoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getVote()
    {
        return $this->vote;
    }

    /**
     * @param int $vote
     * @return self
     */
    public function setVote($vote)
    {
        if (!in_array($vote, $types = array_keys(self::$voteTypeMap))) {
            throw new \InvalidArgumentException(sprintf('Vote must be either [%s]', implode(',', $types)));
        }

        $this->setModelField('vote', $vote);

        return $this;
    }

    /**
     * Get pretty name for a vote
     *
     * @JMS\Expose
     * @JMS\Type("string")
     * @JMS\SerializedName("vote_type")
     * @JMS\VirtualProperty
     *
     * @return string
     */
    public function getVoteType()
    {
        if (isset(self::$voteTypeMap[$this->vote])) {
            return self::$voteTypeMap[$this->vote];
        }

        return 'unknown';
    }

    /**
     * Is an "Approve" response
     *
     * @return bool
     */
    public function isApproved()
    {
        return (self::VOTE_APPROVE === $this->vote);
    }

    /**
     * Is an "Reject" response
     *
     * @return bool
     */
    public function isRejected()
    {
        return (self::VOTE_REJECT === $this->vote);
    }

    /**
     * @return string|null
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string|null $message
     * @return self
     */
    public function setMessage($message)
    {
        $this->setModelField('message', $message);

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return Person
     */
    public function getApprover()
    {
        return $this->approver;
    }

    /**
     * @param Person $approver
     * @return self
     */
    public function setApprover(Person $approver)
    {
        $this->setModelField('approver', $approver);

        return $this;
    }

    /**
     * @return int
     */
    public function getApproverId()
    {
        return $this->approver->getId();
    }

    /**
     * @return AbstractBaseApproval
     */
    public function getApproval()
    {
        return $this->approval;
    }

    /**
     * @param AbstractBaseApproval $approval
     * @return self
     */
    public function setApproval(AbstractBaseApproval $approval)
    {
        $this->setModelField('approval', $approval);

        return $this;
    }

    /**
     * @return int
     */
    public function getApprovalId()
    {
        return $this->approval->getId();
    }
}
