<?php

namespace DeskPRO\Bundle\InstallBundle\InstallSession;

use DeskPRO\Bundle\InstallBundle\InstallSession\Model\DbInfo;
use DeskPRO\Bundle\InstallBundle\InstallSession\Model\Paths;
use DeskPRO\Bundle\InstallBundle\InstallSession\Model\User;

class InstallSession
{
    const SOURCE_DEV            = 'dev';
    const SOURCE_BUILDSERVER    = 'buildserver';
    const SOURCE_WIN_INSTALLER  = 'win_installer';
    const SOURCE_AUTO_INSTALLER = 'automated_installer';

    /**
     * @var string
     */
    private $sessionId;

    /**
     * @var string
     */
    private $sessionUuid = null;

    /**
     * @var \DateTime
     */
    private $startDate;

    /**
     * @var \DateTime
     */
    private $updateDate;

    /**
     * @var User
     */
    private $user;

    /**
     * @var Paths
     */
    private $paths;

    /**
     * @var DbInfo
     */
    private $dbInfo;

    /**
     * @var DbInfo
     */
    private $systemDbInfo;

    /**
     * @var DbInfo
     */
    private $auditDbInfo;

    /**
     * @var string
     */
    private $webUrl;

    /**
     * @var string
     */
    private $source;

    /**
     * @var array
     */
    private $flags = [];

    /**
     * InstallSession constructor.
     *
     * @param string $sessionId
     */
    public function __construct($sessionId)
    {
        $this->sessionId  = $sessionId;
        $this->startDate  = new \DateTime();
        $this->updateDate = new \DateTime();
    }

    /**
     * @return string
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * Get the session uuid.
     *
     * The session ID (above) is unique per install attempt. It's expected that if you made a mistake
     * and re-started the install from scratch that the ID might be different.
     *
     * The uuid is expected to be more persistent. It's used in our reporting to keep track of
     * attempts to try and connect different attempts together.
     *
     * @return string
     */
    public function getSessionUuid()
    {
        return $this->sessionUuid;
    }

    /**
     * @param string $sessionUuid
     */
    public function setSessionUuid($sessionUuid)
    {
        $this->sessionUuid = $sessionUuid;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;
    }

    /**
     * @return Paths
     */
    public function getPaths()
    {
        return $this->paths;
    }

    /**
     * @param Paths $paths
     */
    public function setPaths(Paths $paths = null)
    {
        $this->paths = $paths;
    }

    /**
     * @return DbInfo
     */
    public function getDbInfo()
    {
        return $this->dbInfo;
    }

    /**
     * @param DbInfo $dbInfo
     */
    public function setDbInfo(DbInfo $dbInfo = null)
    {
        $this->dbInfo = $dbInfo;
    }

    /**
     * @return DbInfo
     */
    public function getSystemDbInfo()
    {
        return $this->systemDbInfo;
    }

    /**
     * @param DbInfo $systemDbInfo
     */
    public function setSystemDbInfo(DbInfo $systemDbInfo)
    {
        $this->systemDbInfo = $systemDbInfo;
    }

    /**
     * @return DbInfo
     */
    public function getAuditDbInfo()
    {
        return $this->auditDbInfo;
    }

    /**
     * @param DbInfo $auditDbInfo
     *
     * @return $this
     */
    public function setAuditDbInfo(DbInfo $auditDbInfo)
    {
        $this->auditDbInfo = $auditDbInfo;

        return $this;
    }

    /**
     * @return string
     */
    public function getWebUrl()
    {
        return $this->webUrl;
    }

    /**
     * @param string $webUrl
     */
    public function setWebUrl($webUrl)
    {
        $this->webUrl = $webUrl;
    }

    /**
     * @param string $id
     *
     * @return bool
     */
    public function hasFlag($id)
    {
        return isset($this->flags[$id]);
    }

    /**
     * @param string $id
     */
    public function enableFlag($id)
    {
        $this->flags[$id] = true;
    }

    /**
     * @param string $id
     */
    public function disableFlag($id)
    {
        unset($this->flags[$id]);
    }

    /**
     * Touches the last update date.
     */
    public function touch()
    {
        $this->updateDate = new \DateTime();
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param string $source
     */
    public function setSource($source)
    {
        $this->source = $source;
    }
}
