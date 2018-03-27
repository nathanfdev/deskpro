<?php

namespace DeskPRO\Bundle\AppBundle\SoftwareService\StatService\StatEvent;

class InstallLogEvent
{
    /**
     * @var string
     */
    private $uuid;

    /**
     * @var string|null
     */
    private $log;

    /**
     * @var \SplFileInfo|null
     */
    private $logFile;

    /**
     * @return static
     */
    public static function create()
    {
        return new static();
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @param string $uuid
     *
     * @return $this
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * @return string
     */
    public function getLog()
    {
        return $this->log;
    }

    /**
     * @param string $log
     *
     * @return $this
     */
    public function setLog($log)
    {
        $this->log = $log;

        return $this;
    }

    /**
     * @return null|\SplFileInfo
     */
    public function getLogFile()
    {
        return $this->logFile;
    }

    /**
     * @param \SplFileInfo $logFile
     *
     * @return $this
     */
    public function setLogFile(\SplFileInfo $logFile)
    {
        $this->logFile = $logFile;

        return $this;
    }
}
