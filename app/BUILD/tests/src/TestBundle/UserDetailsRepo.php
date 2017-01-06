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

/**
 * DeskPRO.
 */

namespace DpTestSrc\TestBundle;

use Application\DeskPRO\Entity\Person;
use Doctrine\ORM\EntityManager;

class UserDetailsRepo
{
    const ADMIN_EMAIL      = 'admin@deskpro.dev';
    const ADMIN_PASS       = 'pass';
    const ADMIN_FIRST_NAME = 'Admin';
    const ADMIN_LAST_NAME  = 'Admin';

    const AGENT_EMAIL      = 'agent@deskpro.dev';
    const AGENT_PASS       = 'password';
    const AGENT_FIRST_NAME = 'Zelda';
    const AGENT_LAST_NAME  = 'Agent';

    const DELETED_AGENT_EMAIL      = 'deleted-agent@deskpro.dev';
    const DELETED_AGENT_PASS       = 'password';
    const DELETED_AGENT_FIRST_NAME = 'Deleted';
    const DELETED_AGENT_LAST_NAME  = 'Agent';

    const AGENT_CHRIS_EMAIL      = 'agent_chris@deskpro.dev';
    const AGENT_CHRIS_PASS       = 'agent_chris';
    const AGENT_CHRIS_FIRST_NAME = 'Agent';
    const AGENT_CHRIS_LAST_NAME  = 'Chris';

    const USER_EMAIL      = 'user@deskpro.dev';
    const USER_PASS       = '12345';
    const USER_FIRST_NAME = 'Ganon';
    const USER_LAST_NAME  = 'User';

    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param $who
     *
     * @throws \Exception
     *
     * @return Person
     */
    public function getWho($who)
    {
        return $this->em->getRepository('DeskPRO:Person')->findOneByEmail($this->getEmail($who));
    }

    public function getEmail($who)
    {
        switch ($who) {
            case 'admin':
                return self::ADMIN_EMAIL;
            case 'agent':
                return self::AGENT_EMAIL;
            case 'deleted_agent':
                return self::DELETED_AGENT_EMAIL;
            case 'agent_chris':
                return self::AGENT_CHRIS_EMAIL;
            case 'user':
                return self::USER_EMAIL;
        }

        throw new \Exception('unknown user "'.$who.'"');
    }

    public function getPass($who)
    {
        switch ($who) {
            case 'admin':
                return self::ADMIN_PASS;
            case 'agent':
                return self::AGENT_PASS;
            case 'deleted_agent':
                return self::DELETED_AGENT_PASS;
            case 'agent_chris':
                return self::AGENT_CHRIS_PASS;
            case 'user':
                return self::USER_PASS;
        }

        throw new \Exception('unknown user "'.$who.'"');
    }

    public function getFirstName($who)
    {
        switch ($who) {
            case 'admin':
                return self::ADMIN_FIRST_NAME;
            case 'agent':
                return self::AGENT_FIRST_NAME;
            case 'deleted_agent':
                return self::DELETED_AGENT_FIRST_NAME;
            case 'agent_chris':
                return self::AGENT_CHRIS_FIRST_NAME;
            case 'user':
                return self::USER_FIRST_NAME;
        }

        throw new \Exception('unknown user "'.$who.'"');
    }

    public function getLastName($who)
    {
        switch ($who) {
            case 'admin':
                return self::ADMIN_LAST_NAME;
            case 'agent':
                return self::AGENT_LAST_NAME;
            case 'deleted_agent':
                return self::DELETED_AGENT_LAST_NAME;
            case 'agent_chris':
                return self::AGENT_CHRIS_LAST_NAME;
            case 'user':
                return self::USER_LAST_NAME;
        }

        throw new \Exception('unknown user "'.$who.'"');
    }
}
