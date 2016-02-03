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

namespace Application\InstallBundle\InstallSession;

use Orb\Util\Strings;

class SessionManager
{
    /**
     * @var string
     */
    private $tmp_path;

    /**
     * SessionManager constructor.
     *
     * @param string $tmp_path
     */
    public function __construct($tmp_path)
    {
        $this->tmp_path = $tmp_path;
    }

    /**
     * Get the last install session.
     *
     * @return InstallSession
     */
    public function getLastInstallSession()
    {
        if (file_exists($this->tmp_path.'/install.sess')) {
            return unserialize(file_get_contents($this->tmp_path.'/install.sess'));
        }

        $session = new InstallSession(date('YmdHis').'-'.Strings::random(30));

        return $session;
    }

    /**
     * Persist the install session.
     *
     * @param InstallSession $session
     */
    public function saveInstallSession(InstallSession $session)
    {
        $session->touch();
        file_put_contents($this->tmp_path.'/install.sess', serialize($session));
    }
}
