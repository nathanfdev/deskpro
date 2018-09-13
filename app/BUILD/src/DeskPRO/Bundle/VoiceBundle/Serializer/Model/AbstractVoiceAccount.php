<?php

namespace DeskPRO\Bundle\VoiceBundle\Serializer\Model;

use DeskPRO\Bundle\AppBundle\Entity\AbstractVoiceAccount as AbstractVoiceAccountEntity;
use JMS\Serializer\Annotation as JMS;

/**
 * Class AbstractVoiceAccount.
 */
abstract class AbstractVoiceAccount
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
    protected $accountId;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $authToken;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $accountName;

    /**
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    protected $dateCreated;

    /**
     * Constructor.
     *
     * @param AbstractVoiceAccountEntity $entity
     */
    public function __construct(AbstractVoiceAccountEntity $entity)
    {
        $this->id          = $entity->getId();
        $this->accountId   = $entity->getAccountId();
        $this->authToken   = $entity->getAuthToken();
        $this->accountName = $entity->getAccountName();
        $this->dateCreated = $entity->getDateCreated();
    }
}
