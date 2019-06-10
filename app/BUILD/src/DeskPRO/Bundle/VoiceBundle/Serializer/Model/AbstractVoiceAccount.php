<?php

namespace DeskPRO\Bundle\VoiceBundle\Serializer\Model;

use DeskPRO\Bundle\AppBundle\Entity\AbstractVoiceAccount as AbstractVoiceAccountEntity;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\InlineCustomSideload;
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
     * @JMS\Type("raw")
     *
     * @var InlineCustomSideload
     */
    protected $availableVoiceCountries;

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

    /**
     * @param InlineCustomSideload $availableVoiceCountries
     */
    public function setAvailableVoiceCountries($availableVoiceCountries)
    {
        $this->availableVoiceCountries = $availableVoiceCountries;
    }
}
