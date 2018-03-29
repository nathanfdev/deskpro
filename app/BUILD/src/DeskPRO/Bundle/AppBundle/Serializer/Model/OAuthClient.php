<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model;

use DeskPRO\Bundle\AppBundle\Entity\OAuthClient as OAuthClientEntity;
use JMS\Serializer\Annotation as JMS;

/**
 * Class OAuthClient.
 */
class OAuthClient
{
    /**
     * The unique ID.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $id;

    /**
     * The unique ID.
     *
     * @JMS\Type("string")
     *
     * @var int
     */
    private $publicId;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $sysName;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $context;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $name;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $randomId;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $secret;

    /**
     * @JMS\Type("array<string>")
     *
     * @var array
     */
    private $redirectUris = [];

    /**
     * @JMS\Type("string")
     *
     * @var array
     */
    private $allowedGrantType;

    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $isEnabled;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $authEndpoint;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $tokenEndpoint;

    /**
     * Constructor.
     *
     * @param OAuthClientEntity $entity
     * @param string            $authEndpoint
     * @param string            $tokenEndpoint
     */
    public function __construct(OAuthClientEntity $entity, $authEndpoint, $tokenEndpoint)
    {
        $this->id               = $entity->getId();
        $this->publicId         = $entity->getPublicId();
        $this->sysName          = $entity->getSysName();
        $this->name             = $entity->getName();
        $this->randomId         = $entity->getRandomId();
        $this->secret           = $entity->getSecret();
        $this->redirectUris     = $entity->getRedirectUris();
        $this->allowedGrantType = $entity->getAllowedGrantType();
        $this->isEnabled        = $entity->isEnabled();
        $this->context          = $entity->getContext();
        $this->authEndpoint     = $authEndpoint;
        $this->tokenEndpoint    = $tokenEndpoint;
    }
}
