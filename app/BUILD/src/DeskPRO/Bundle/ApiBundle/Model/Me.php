<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\ApiBundle\Model;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\ApiBundle\Request\ApiClientInfo;
use DeskPRO\Bundle\ApiBundle\Security\Token\AbstractApiSecurityToken;
use DeskPRO\Bundle\ApiBundle\Security\Token\AgentSessionSecurityToken;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class Me.
 */
class Me
{
    /**
     * Current user auth method.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $authMethod;

    /**
     * Current application id.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $appId;

    /**
     * Current person ID.
     *
     * @todo remove, just person property is enough
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $personId;

    /**
     * Person entity.
     *
     * @JMS\Type("Application\DeskPRO\Entity\Person")
     *
     * @var Person
     */
    private $person;

    /**
     * Api version.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $apiVersion;

    /**
     * Client type.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $clientType;

    /**
     * Client version.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $clientVersion;

    /**
     * Constructor.
     *
     * @param TokenInterface $token
     * @param ApiClientInfo  $clientInfo
     * @param int            $apiVersion
     */
    public function __construct(TokenInterface $token, ApiClientInfo $clientInfo, $apiVersion)
    {
        $this->person   = $token->getUser();
        $this->personId = $this->person->getId();

        if ($token instanceof AbstractApiSecurityToken) {
            $this->authMethod = $token->getName();
        }
        if ($token instanceof AgentSessionSecurityToken) {
            $this->appId = $token->getAppId();
        }

        $this->apiVersion    = $apiVersion;
        $this->clientType    = $clientInfo->getClientType();
        $this->clientVersion = $clientInfo->getClientVersion();
    }
}
