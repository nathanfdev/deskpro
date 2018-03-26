<?php

namespace DeskPRO\Bundle\AppStoreBundle\Infrastructure\AppStorage;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppStoreBundle\Domain;

class ServiceAccessRequest implements AccessRequest
{
    /**
     * @var string
     */
    private $service;
    /**
     * @var Person
     */
    private $authPerson;
    /**
     * @var string
     */
    private $accessLevel;

    /**
     * @param Person $authPerson
     * @return ServiceAccessRequest
     */
    public static function newAPIWriteAccessRequest(Person $authPerson)
    {
        return new ServiceAccessRequest(Domain\Constants::ACCESS_SERVICE_API, $authPerson, Domain\Constants::ACCESS_LEVEL_WRITE);
    }

    /**
     * @param Person $authPerson
     * @return ServiceAccessRequest
     */
    public static function newAPIReadAccessRequest(Person $authPerson)
    {
        return new ServiceAccessRequest(Domain\Constants::ACCESS_SERVICE_API, $authPerson, Domain\Constants::ACCESS_LEVEL_READ);
    }

    /**
     * @param Person $authPerson
     * @return ServiceAccessRequest
     */
    public static function newProxyReadAccessRequest(Person $authPerson)
    {
        return new ServiceAccessRequest(Domain\Constants::ACCESS_SERVICE_PROXY, $authPerson, Domain\Constants::ACCESS_LEVEL_READ);
    }

    /**
     * @param Person $authPerson
     * @return ServiceAccessRequest
     */
    public static function newProxyWriteAccessRequest(Person $authPerson)
    {
        return new ServiceAccessRequest(Domain\Constants::ACCESS_SERVICE_PROXY, $authPerson, Domain\Constants::ACCESS_LEVEL_WRITE);
    }

    /**
     * @param string $service
     * @param Person $authPerson
     * @param string $accessLevel
     */
    public function __construct($service, Person $authPerson, $accessLevel) {

        $this->service = $service;
        $this->authPerson = $authPerson;
        $this->accessLevel = $accessLevel;
    }

    /**
     * @return string
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * @return Person
     */
    public function getAuthPerson()
    {
        return $this->authPerson;
    }

    /**
     * @return string
     */
    public function getAuthPersonId()
    {
        return (string) $this->authPerson->getId();
    }

    /**
     * @return string
     */
    public function getAccessLevel()
    {
        return $this->accessLevel;
    }
}
