<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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
namespace DpBehat\E2E;

use DpBehat\BaseContext;
use DpTestSrc\TestBundle\UserDetailsRepo;

class AuthContext extends BaseContext
{
    /**
     * @var UserDetailsRepo
     */
    private $user_details;

    /**
     * @param UserDetailsRepo $user_details
     */
    public function __construct(UserDetailsRepo $user_details)
    {
        $this->user_details = $user_details;
    }

    /**
     * @Given I log in as :who
     */
    public function iAmAuthenticatedAsUser($who)
    {
        $this->getSession()->getDriver()->resizeWindow(1440, 900, 'current');

        $session = $this->getSession();
        $session->visit($this->locatePath('/login'));

        // Need to execute JS to submit toe login form because no driver ca see the
        // form fields (tried phantomjs and chrome)
        $session->executeScript('document.getElementById("login_username").value = "'.$this->user_details->getEmail($who).'";');
        $session->executeScript('document.getElementById("login_password").value = "'.$this->user_details->getPass($who).'";');
        $session->executeScript('document.getElementById("login").submit();');
    }
}
