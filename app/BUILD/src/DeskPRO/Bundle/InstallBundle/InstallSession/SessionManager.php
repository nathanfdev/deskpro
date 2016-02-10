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

namespace DeskPRO\Bundle\InstallBundle\InstallSession;

use DeskPRO\Component\Exception\Filesystem\FileWriteException;
use DeskPRO\Component\Util\ExceptionUtils;
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
     * @param bool $restart True to ignore any existing session, if any. Always returns a new session.
     *
     * @return InstallSession
     */
    public function getLastInstallSession($restart = false)
    {
        if (!$restart && file_exists($this->tmp_path.'/install_session.bin')) {
            return unserialize(base64_decode(file_get_contents($this->tmp_path.'/install_session.bin')));
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
        $path          = $this->tmp_path.'/install_session.bin';
        $write_content = base64_encode(serialize($session));
        $einfo         = null;

        if ($session->hasFlag('installer_done')) {
            @unlink($path);
        } else {
            $success = ExceptionUtils::detectSuppressedError(function () use ($path, $write_content) {
                return @file_put_contents($path, $write_content);
            }, $einfo);

            if (!$success) {
                throw new FileWriteException("Could not write to {$this->tmp_path}/install_session.bin", 0, null, @$einfo['message'] ?: 'General write error');
            }
        }
    }
}
