<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model;

use DeskPRO\Bundle\AppBundle\Settings\Model\DiscoverSettings;
use JMS\Serializer\Annotation as JMS;

/**
 * Class ApiToken.
 */
class ApiToken
{
    /**
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     * @JMS\SerializedName("person_id")
     * @JMS\Groups({"token"})
     *
     * @var int
     */
    private $person;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"token"})
     *
     * @var string
     */
    private $token;

    /**
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\DiscoverSettings")
     * @JMS\Groups({"discover"})
     *
     * @var DiscoverSettings
     */
    private $discover;

    /**
     * Constructor.
     *
     * @param \Application\DeskPRO\Entity\ApiToken $apiToken
     * @param DiscoverSettings                     $discover
     */
    public function __construct(\Application\DeskPRO\Entity\ApiToken $apiToken, DiscoverSettings $discover)
    {
        $this->person   = $apiToken->getPerson();
        $this->token    = $apiToken->getKeyString();
        $this->discover = $discover;
    }
}
