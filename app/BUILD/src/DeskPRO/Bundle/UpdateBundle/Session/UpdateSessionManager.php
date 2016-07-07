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

namespace DeskPRO\Bundle\UpdateBundle\Session;

use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

class UpdateSessionManager
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
     * @var UpdateSession
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
        return $this->tmpPath.DIRECTORY_SEPARATOR.'update-session.'.$this->sessionId.'.dat';
    }

    /**
     * This loads the upgrade session. Note that this always results in a new UpgradeSession object.
     *
     * @return UpdateSession
     */
    private function loadSession()
    {
        $filePath = $this->getSessionFilePath();

        if (!file_exists($filePath)) {
            $session = new UpdateSession();
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
     * @return UpdateSession
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * Reloads the session and then returns it.
     *
     * @return UpdateSession
     */
    public function reloadSession()
    {
        $this->session = $this->loadSession();

        return $this->session;
    }

    /**
     * Updates the session data based on the filesystem.
     */
    private function syncSession()
    {
        $this->session->merge($this->loadSession());
    }

    /**
     * Saves current session state to the filesystem.
     */
    public function flushSession()
    {
        $fp = @fopen($this->getSessionFilePath(), 'w');
        if (!$fp) {
            throw new IOException('Could not open for writing');
        }

        @flock($fp, LOCK_EX);

        $writeStatus = fwrite($fp, serialize($this->session));
        @fflush($fp);

        @flock($fp, LOCK_EX);
        @fclose($fp);

        if (!$writeStatus) {
            throw new IOException('Could not write');
        }
    }

    /**
     * Use this to make changes to the upgrade session.
     *
     * If your function returns exactly FALSE, then we'll
     * consider it a noop and no write will take plce.
     *
     * @param callable $callable
     *
     * @throws \Exception
     */
    public function mutateSession($callable)
    {
        $this->syncSession();

        if (call_user_func($callable, $this->session) === false) {
            return;
        }

        $this->session->touch();

        for ($i = 0; $i < 3; ++$i) {
            try {
                $e = null;
                $this->flushSession();
                break;
            } catch (\Exception $e) {
                if ($i === 2) {
                    throw $e;
                }
            }
        }
    }
}
