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

namespace Codeception\Module;

use Application\DeskPRO\Entity\Session;
use Orb\Util\Util;

class WebHelper extends \Codeception\Module
{
    private $openAdminInterface_containerCount = null;

    /**
     * Opens admin interface.
     */
    public function openAdminInterface($as_agent_email = null)
    {
        // We dont need to refresh the page and regenerate a new session
        // if we already have a session and the db has not changed
        $container_count = (int) $this->getDpControlHelper()->getContainerCounter();
        if ($container_count === $this->openAdminInterface_containerCount && $this->getModule('WebDriver')->grabCookie('dptest-has-agent-sid') && preg_match('#/admin/#', $this->getModule('WebDriver')->grabFromCurrentUrl())) {
            return;
        }

        $this->openAdminInterface_containerCount = $container_count;

        $this->getModule('WebDriver')->resizeWindow(1430, 800);

        // Browser must be open to the page so cookies are set on the proper domain/path
        $this->getModule('WebDriver')->amOnPage('/');

        $container = $this->getDpControlHelper()->getSymfonyContainer();
        $db        = $container->getDb();

        if ($as_agent_email === null) {
            $agent_id = $db->fetchColumn('
                SELECT people.id
                FROM people
                WHERE people.is_agent = 1 AND people.can_admin = 1
                LIMIT 1
            ');
        } else {
            $agent_id = $db->fetchColumn('
                SELECT people.id
                FROM people
                LEFT JOIN people_emails ON (people_emails.person_id = people.id)
                WHERE people.is_agent = 1 AND people_emails.email = ?
                LIMIT 1
            ', array($as_agent_email));
        }

        @session_start();
        $_SESSION = array(
            '_sf2_attributes' => array(
                'dp_interface'   => 'agent',
                'auth_person_id' => $agent_id,
            ),
            '_sf2_flashes' => array(),
            '_sf2_meta'    => array(),
        );

        $db->executeUpdate("
            INSERT INTO `sessions` (`person_id`, `visitor_id`, `auth`, `interface`, `user_agent`, `ip_address`, `data`, `is_person`, `is_bot`, `is_helpdesk`, `active_status`, `is_chat_available`, `date_created`, `date_last`)
            VALUES (
                $agent_id,
                NULL,
                'HDJWW7T8CWRZ2NN',
                'agent',
                'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/31.0.1650.26 Safari/537.36',
                '192.168.4.2',
                '".session_encode()."',
                1,
                0,
                1,
                '',
                0,
                '".date('Y-m-d H:i:s')."',
                '".date('Y-m-d H:i:s')."'
            )
        ");

        $id = $db->lastInsertId();

        $id_enc = Util::baseEncode($id, Util::BASE36_ALPHABET).'-HDJWW7T8CWRZ2NN';
        $this->getModule('WebDriver')->setCookie('dpsid-agent', $id_enc);
        $this->getModule('WebDriver')->setCookie('dptest-has-agent-sid', '1');
        $this->getModule('WebDriver')->amOnPage('/admin');
        $this->waitForAdminLoad();
    }

    /**
     * Wait for admin to finish loading whatever is going right now.
     */
    public function waitForAdminLoad()
    {
        $this->getModule('WebDriver')->waitForJS('return (window.DP_IS_BOOTED === true && window.DP_DIGEST_RUNNING === false && window.DP_AJAX_RUNNINGCOUNT === 0);', 20);
        $this->getModule('WebDriver')->wait(1.2);
    }

    /**
     * @param string $page
     */
    public function amOnAdminPage($page)
    {
        $page = '/'.ltrim($page, '/');
        $this->getModule('WebDriver')->executeJS('window.DP_NO_DIRTYSTATE_CONFIRM = true;');
        $this->getModule('WebDriver')->executeJS('parent.location.hash = "'.addslashes($page).'";');
        $this->getModule('WebDriver')->wait(1.2);
        $this->waitForAdminLoad();
    }

    /**
     * @return \Codeception\Module\DpControlHelper
     */
    public function getDpControlHelper()
    {
        return $this->getModule('DpControlHelper');
    }
}
