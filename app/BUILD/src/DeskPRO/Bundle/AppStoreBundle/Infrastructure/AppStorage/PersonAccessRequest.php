<?php

namespace DeskPRO\Bundle\AppStoreBundle\Infrastructure\AppStorage;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppStoreBundle\Domain;

class PersonAccessRequest implements AccessRequest
{
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
     * @return PersonAccessRequest
     */
    public static function newReadAccessRequest(Person $authPerson)
    {
        return new PersonAccessRequest($authPerson, Domain\Constants::ACCESS_LEVEL_READ);
    }

    /**
     * @param Person $authPerson
     * @return PersonAccessRequest
     */
    public static function newWriteAccessRequest(Person $authPerson)
    {
        return new PersonAccessRequest($authPerson, Domain\Constants::ACCESS_LEVEL_WRITE);
    }

    /**
     * @param Person $authPerson
     * @param string $accessLevel
     */
    public function __construct(Person $authPerson, $accessLevel) {

        $this->authPerson = $authPerson;
        $this->accessLevel = $accessLevel;
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
