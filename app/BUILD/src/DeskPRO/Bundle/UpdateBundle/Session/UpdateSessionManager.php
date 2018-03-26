<?php

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
