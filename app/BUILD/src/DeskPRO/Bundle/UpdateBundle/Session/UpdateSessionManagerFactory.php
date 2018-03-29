<?php

namespace DeskPRO\Bundle\UpdateBundle\Session;

class UpdateSessionManagerFactory
{
    /**
     * @var string
     */
    private $sessionId;

    /**
     * @var UpdateSessionManager
     */
    private $sessionManager;

    /**
     * @var string
     */
    private $tmpPath;

    /**
     * UpgradeSessionManagerFactory constructor.
     *
     * @param string $tmpPath
     */
    public function __construct($tmpPath)
    {
        $this->tmpPath = $tmpPath;
    }

    /**
     * @param string $sessionId
     */
    public function enableSessionId($sessionId)
    {
        if ($this->sessionId) {
            throw new \RuntimeException('Session has already been set');
        }
        $this->sessionId = $sessionId;
    }

    /**
     * @return string
     */
    public function getSessionid()
    {
        return $this->sessionId;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->sessionId !== null;
    }

    /**
     * @return UpdateSessionManager
     */
    public function getManager()
    {
        if (!$this->sessionId) {
            throw new \RuntimeException('No session has been started');
        }

        if (!$this->sessionManager) {
            $this->sessionManager = new UpdateSessionManager($this->sessionId, $this->tmpPath);
        }

        return $this->sessionManager;
    }
}
