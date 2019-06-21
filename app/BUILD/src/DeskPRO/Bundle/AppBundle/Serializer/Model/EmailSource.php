<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model;

use Application\DeskPRO\Entity\EmailSource as EmailSourceModel;
use JMS\Serializer\Annotation as JMS;

/**
 * Class EmailSource.
 */
class EmailSource
{
    /**
     * The unique ID.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $id;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $uuid;

    /**
     * @JMS\Type("Application\DeskPRO\Entity\Blob")
     *
     * @var \Application\DeskPRO\Entity\Blob
     */
    protected $blob;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $objectType;

    /**
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $objectId;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $fromEmail;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $status;

    /**
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    protected $dateStatus;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $errorCode;

    /**
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    protected $dateCreated;

    /**
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $execCount;

    /**
     * Constructor.
     *
     * @param EmailSourceModel $emailSource
     */
    public function __construct(EmailSourceModel $emailSource)
    {
        $this->id          = $emailSource->getId();
        $this->uuid        = $emailSource->getUid();
        $this->blob        = $emailSource->getBlob();
        $this->objectType  = $emailSource->object_type;
        $this->objectId    = $emailSource->object_id;
        $this->fromEmail   = $emailSource->from_email;
        $this->status      = $emailSource->status;
        $this->dateStatus  = $emailSource->date_status;
        $this->errorCode   = $emailSource->getErrorCode();
        $this->dateCreated = $emailSource->date_created;
        $this->execCount   = $emailSource->exec_count;
    }
}
