<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
