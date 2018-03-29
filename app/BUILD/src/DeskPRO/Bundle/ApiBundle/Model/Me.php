<?php

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
