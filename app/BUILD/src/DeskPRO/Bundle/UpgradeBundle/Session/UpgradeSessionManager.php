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

namespace DeskPRO\Bundle\UpgradeBundle\Session;

use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

class UpgradeSessionManager
{
    /**
     * @var string
     */
    private $sessionId;

    /**
     * @var string
     */
    private $tmpPath;

    /**
     * @var UpgradeSession
     */
    private $session;

    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * UpgradeSessionManager constructor.
     *
     * @param $sessionId
     * @param $tmpPath
     */
    public function __construct($sessionId, $tmpPath)
    {
        $this->sessionId = $sessionId;
        $this->tmpPath   = $tmpPath;
        $this->fs        = new Filesystem();

        $this->session = $this->loadSession();
    }

    /**
     * @return string
     */
    public function getSessionFilePath()
    {
        return $this->tmpPath.DIRECTORY_SEPARATOR.'upgrade-session.'.$this->sessionId.'.json';
    }

    /**
     * This loads the upgrade session. Note that this always results in a new UpgradeSession object.
     *
     * @return UpgradeSession
     */
    private function loadSession()
    {
        $filePath = $this->getSessionFilePath();

        if (!file_exists($filePath)) {
            $session = new UpgradeSession();
            $this->saveSession($session);
        } else {
            $session = @file_get_contents($filePath);
            if (!$session) {
                new IOException('Failed to read session data');
            }
            $session = @unserialize($session);
            if (!$session) {
                new IOException('Session file contains invalid data');
            }
        }

        return $session;
    }

    /**
     * @return UpgradeSession
     */
    public function getSession()
    {
        $this->syncSession();

        return $this->session;
    }

    /**
     * Updates the session data based on the filesystem.
     */
    public function syncSession()
    {
        $this->session->merge($this->loadSession());
    }

    /**
     * Saves current session state to the filesystem.
     */
    public function flushSession()
    {
        $this->fs->dumpFile($this->getSessionFilePath(), serialize($this->session), 0777);
    }
}
