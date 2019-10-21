<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalResponse as ApprovalResponseEntity;
use JMS\Serializer\Annotation as JMS;

/**
 * Class ApprovalResponse
 *
 * @package DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets
 */
class ApprovalResponse
{
    /**
     * ID.
     *
     * @var int
     *
     * @JMS\Type("integer")
     */
    private $id;

    /**
     * Vote, either "1" or "-1"
     *
     * @var int
     *
     * @JMS\Type("integer")
     */
    private $vote;

    /**
     * Vote type
     *
     * @var string
     *
     * @JMS\Type("string")
     */
    private $voteType;

    /**
     * Message.
     *
     * @var string
     *
     * @JMS\Type("string")
     */
    private $message;

    /**
     * Created date/time.
     *
     * @var \DateTimeInterface
     *
     * @JMS\Type("DateTime")
     */
    private $createdAt;

    /**
     * Approver.
     *
     * @var Person
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     */
    private $approver;

    /**
     * @param ApprovalResponseEntity $response
     * @return ApprovalResponse
     */
    public static function createFromEntity($response)
    {
        $model = new self();

        $model->setId($response->getId());
        $model->setVote($response->getVote());
        $model->setVoteType($response->getVoteType());
        $model->setMessage($response->getMessage());
        $model->setCreatedAt($response->getCreatedAt());
        $model->setApprover($response->getApprover());

        return $model;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return ApprovalResponse
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
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
     * @return ApprovalResponse
     */
    public function setVote($vote)
    {
        $this->vote = $vote;

        return $this;
    }

    /**
     * @return string
     */
    public function getVoteType()
    {
        return $this->voteType;
    }

    /**
     * @param string $voteType
     * @return ApprovalResponse
     */
    public function setVoteType($voteType)
    {
        $this->voteType = $voteType;

        return $this;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     * @return ApprovalResponse
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTimeInterface $createdAt
     * @return ApprovalResponse
     */
    public function setCreatedAt(\DateTimeInterface $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
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
     * @return ApprovalResponse
     */
    public function setApprover(Person $approver)
    {
        $this->approver = $approver;

        return $this;
    }
}
