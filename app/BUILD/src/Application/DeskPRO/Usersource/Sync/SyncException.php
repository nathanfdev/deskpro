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
