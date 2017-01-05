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

namespace DeskPRO\Bundle\AppBundle\Security;

use Application\DeskPRO\Entity\Person;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

/**
 * This is a token we use if the person is logged in implicitly via being logged in from another interface.
 *
 * For example, if you are unauthenticated in portal, but you are authenticated in agent or admin, then when
 * you visit the portal we have the ability to grant you this token because we can detect those other
 * sessions. See DpTransferSessionAuthListener.
 */
class DpTransferSessionAuthToken extends AbstractToken
{
    protected $session_id;

    public function __construct(Person $person = null, $session_id)
    {
        if ($person) {
            parent::__construct($person->getRoles());
            $this->setUser($person);
        } else {
            parent::__construct([]);
        }

        $this->session_id = $session_id;
        $this->setAuthenticated($person && count($this->getRoles()) > 0);
    }

    public function getCredentials()
    {
        return $this->session_id;
    }
}
