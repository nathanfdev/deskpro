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

namespace DpBehat\Portal\Api;

use Application\DeskPRO\Entity\Session;
use DpBehat\BaseContext;

/**
 * Class AuthContext.
 */
class AuthContext extends BaseContext
{
    /**
     * @var array Code indexed array of Session objects
     */
    public static $sessions = [];

    /**
     * @Given I have guest portal api session with code :code
     *
     * @param string $code
     */
    public function iHaveGuestPortalApiSessionCode($code)
    {
        $session = new Session();
        $session->setAuth($code);

        $this->em()->persist($session);
        $this->em()->flush();

        self::$sessions[$code] = $session;
    }

    /**
     * @Given I have authorized portal api session with code :code for :email
     *
     * @param string $code
     * @param string $email
     */
    public function iHaveAuthorizedPortalApiSessionCode($code, $email)
    {
        $session = new Session();
        $session->setAuth($code);
        $session->setPerson($this->findPerson($email));

        $this->em()->persist($session);
        $this->em()->flush();

        self::$sessions[$code] = $session;
    }

    /**
     * @param string $email
     *
     * @return \Application\DeskPRO\Entity\Person
     */
    public function findPerson($email)
    {
        /** @var \Application\DeskPRO\EntityRepository\Person $repository */
        $repository = $this->em()->getRepository('DeskPRO:Person');
        $person     = $repository->findOneByEmail($email);

        if (!$person) {
            throw new \RuntimeException(sprintf('Person with email `%s` not found', $email));
        }

        return $person;
    }
}
