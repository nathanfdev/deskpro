<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Usersource\Sync;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Usersource;
use Exception;

/**
 * Problem syncing this person with this usersource.
 */
class SyncException extends \RuntimeException
{
    /**
     * @var Person|mixed
     */
    private $person_or_identifier;

    /**
     * @var Usersource
     */
    private $usersource;

    public function __construct(
        $message,
        $person_or_identifier,
        Usersource $usersource,
        $code = 0,
        Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->person_or_identifier = $person_or_identifier;
        $this->usersource           = $usersource;
    }

    /**
     * @return Person
     */
    public function getPersonOrIdentifier()
    {
        return $this->person_or_identifier;
    }

    /**
     * @return Usersource
     */
    public function getUsersource()
    {
        return $this->usersource;
    }
}
