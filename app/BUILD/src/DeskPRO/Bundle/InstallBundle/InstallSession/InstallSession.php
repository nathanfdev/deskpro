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

use DeskPRO\Bundle\InstallBundle\InstallSession\Model\DbInfo;
use DeskPRO\Bundle\InstallBundle\InstallSession\Model\Paths;
use DeskPRO\Bundle\InstallBundle\InstallSession\Model\User;

class InstallSession
{
    const SOURCE_DEV         = 'dev';
    const SOURCE_BUILDSERVER = 'buildserver';

    /**
     * @var string
     */
    private $session_id;

    /**
     * @var \DateTime
     */
    private $start_date;

    /**
     * @var \DateTime
     */
    private $update_date;

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
    private $dbinfo;

    /**
     * @var string
     */
    private $web_url;

    /**
     * @var string
     */
    private $source;

    /**
     * @var array
     */
    private $flags = [];

    public function __construct($session_id)
    {
        $this->session_id  = $session_id;
        $this->start_date  = new \DateTime();
        $this->update_date = new \DateTime();
    }

    /**
     * @return string
     */
    public function getSessionId()
    {
        return $this->session_id;
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
        return $this->dbinfo;
    }

    /**
     * @param DbInfo $dbinfo
     */
    public function setDbInfo(DbInfo $dbinfo = null)
    {
        $this->dbinfo = $dbinfo;
    }

    /**
     * @return string
     */
    public function getWebUrl()
    {
        return $this->web_url;
    }

    /**
     * @param string $web_url
     */
    public function setWebUrl($web_url)
    {
        $this->web_url = $web_url;
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
     * Touches the last update date.
     */
    public function touch()
    {
        $this->update_date = new \DateTime();
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
